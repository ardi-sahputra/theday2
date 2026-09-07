#!/usr/bin/env bash
#
# Deploy TheDay ke satu environment di Hostinger.
#
#   bash scripts/deploy.sh <staging|production>
#
# Dipanggil oleh .github/workflows/deploy-staging.yml dan deploy-production.yml
# setelah working tree di server sudah di-reset ke commit target.
#
# Env var opsional:
#   DEPLOY_PREVIOUS_SHA  commit sebelum deploy; dipakai untuk auto-rollback
#                        kalau health check gagal.
#   SKIP_BACKUP=1        lewati dump database.
#   SKIP_HEALTHCHECK=1   lewati health check + rollback.

set -euo pipefail

ENVIRONMENT="${1:-}"
APP_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$APP_ROOT"

BACKUP_DIR="$APP_ROOT/storage/backups"
BACKUP_KEEP=5
MAINTENANCE_ON=0

log()  { printf '\n\033[1;36m==>\033[0m %s\n' "$*"; }
warn() { printf '\033[1;33m[warn]\033[0m %s\n' "$*"; }
die()  { printf '\033[1;31m[fail]\033[0m %s\n' "$*" >&2; exit 1; }

# Baca satu key dari .env tanpa mem-parse seluruh file.
env_get() {
    local key="$1" line
    line="$(grep -m1 -E "^${key}=" .env || true)"
    line="${line#*=}"
    line="${line%\"}"; line="${line#\"}"
    line="${line%\'}"; line="${line#\'}"
    printf '%s' "$line"
}

# --------------------------------------------------------------------------
# 1. Validasi
# --------------------------------------------------------------------------
case "$ENVIRONMENT" in
    staging|production) ;;
    *) die "Usage: bash scripts/deploy.sh <staging|production>" ;;
esac

[ -f .env ] || die ".env tidak ada di $APP_ROOT. Copy dari .env.staging.example dulu."

ACTUAL_ENV="$(env_get APP_ENV)"
if [ "$ACTUAL_ENV" != "$ENVIRONMENT" ]; then
    die "Salah target. .env di $APP_ROOT punya APP_ENV=$ACTUAL_ENV tapi deploy diminta untuk $ENVIRONMENT. Batal — cek path deploy di workflow."
fi

if [ "$ENVIRONMENT" = "production" ] && [ "$(env_get APP_DEBUG)" = "true" ]; then
    die "APP_DEBUG=true di production. Set false dulu — stack trace bocor ke publik."
fi

APP_URL="$(env_get APP_URL)"
log "Deploy $ENVIRONMENT -> $APP_ROOT ($APP_URL)"
git log --oneline -1 || true

# --------------------------------------------------------------------------
# 2. Backup database (sebelum migrate)
# --------------------------------------------------------------------------
if [ "${SKIP_BACKUP:-0}" != "1" ] && [ "$(env_get DB_CONNECTION)" = "mysql" ]; then
    log "Backup database"
    mkdir -p "$BACKUP_DIR"
    BACKUP_FILE="$BACKUP_DIR/${ENVIRONMENT}-$(date +%Y%m%d-%H%M%S).sql.gz"
    if MYSQL_PWD="$(env_get DB_PASSWORD)" mysqldump \
        --host="$(env_get DB_HOST)" \
        --port="$(env_get DB_PORT)" \
        --user="$(env_get DB_USERNAME)" \
        --single-transaction --quick --no-tablespaces \
        "$(env_get DB_DATABASE)" 2>/dev/null | gzip > "$BACKUP_FILE"
    then
        echo "    $BACKUP_FILE ($(du -h "$BACKUP_FILE" | cut -f1))"
        # Simpan N backup terakhir saja.
        ls -1t "$BACKUP_DIR"/${ENVIRONMENT}-*.sql.gz 2>/dev/null \
            | tail -n +$((BACKUP_KEEP + 1)) | xargs -r rm -f
    else
        rm -f "$BACKUP_FILE"
        warn "mysqldump gagal — lanjut tanpa backup."
    fi
fi

# --------------------------------------------------------------------------
# 3. Maintenance mode (dilepas apa pun yang terjadi)
# --------------------------------------------------------------------------
bring_app_up() {
    if [ "$MAINTENANCE_ON" = "1" ]; then
        php artisan up >/dev/null 2>&1 || rm -f storage/framework/maintenance.php
        MAINTENANCE_ON=0
    fi
}
trap bring_app_up EXIT

log "Masuk maintenance mode"
php artisan down --retry=15 >/dev/null 2>&1 && MAINTENANCE_ON=1 || warn "artisan down gagal, lanjut tanpa maintenance mode"

# --------------------------------------------------------------------------
# 4. Dependencies + migrasi
# --------------------------------------------------------------------------
log "composer install"
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

log "artisan migrate"
php artisan migrate --force --no-interaction

# --------------------------------------------------------------------------
# 5. Cache
# --------------------------------------------------------------------------
log "Rebuild cache"
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan storage:link 2>/dev/null || true
php artisan queue:restart 2>/dev/null || true

bring_app_up
log "Keluar maintenance mode"

# --------------------------------------------------------------------------
# 6. Health check + rollback
# --------------------------------------------------------------------------
if [ "${SKIP_HEALTHCHECK:-0}" = "1" ] || [ -z "$APP_URL" ]; then
    warn "Health check dilewati."
    log "Deploy $ENVIRONMENT selesai: $(date)"
    exit 0
fi

# Staging dilindungi basic auth — kirim kredensialnya biar /up bisa dibaca.
CURL_AUTH=()
if [ "$ENVIRONMENT" = "staging" ]; then
    STG_PASS="$(env_get STAGING_AUTH_PASSWORD)"
    [ -n "$STG_PASS" ] && CURL_AUTH=(--user "$(env_get STAGING_AUTH_USER):$STG_PASS")
fi

log "Health check $APP_URL/up"
STATUS=000
for attempt in 1 2 3 4 5; do
    STATUS="$(curl -sS -o /dev/null -w '%{http_code}' --max-time 20 \
        "${CURL_AUTH[@]}" "$APP_URL/up" || echo 000)"
    [ "$STATUS" = "200" ] && break
    echo "    percobaan $attempt: HTTP $STATUS"
    sleep 5
done

if [ "$STATUS" = "200" ]; then
    log "Deploy $ENVIRONMENT selesai — HTTP 200 · $(date)"
    exit 0
fi

# --- gagal ---
warn "Health check gagal (HTTP $STATUS)."

if [ -z "${DEPLOY_PREVIOUS_SHA:-}" ]; then
    die "Tidak ada DEPLOY_PREVIOUS_SHA — rollback otomatis tidak bisa. Perbaiki manual."
fi

log "Rollback ke $DEPLOY_PREVIOUS_SHA"
git reset --hard "$DEPLOY_PREVIOUS_SHA"
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up >/dev/null 2>&1 || true

warn "Kode sudah di-rollback. MIGRASI TIDAK di-rollback — kalau migrate yang bikin rusak, restore dari $BACKUP_DIR."
die "Deploy $ENVIRONMENT gagal, sudah rollback ke commit sebelumnya."
