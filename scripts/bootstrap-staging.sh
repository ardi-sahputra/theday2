#!/usr/bin/env bash
#
# Sekali jalan: menyiapkan environment staging di Hostinger.
#
# Jalankan DARI SERVER, setelah subdomain staging.theday.id dan database
# staging sudah dibuat di hPanel:
#
#   ssh -p 65002 u144336260@46.202.138.29
#   bash <(curl -s https://raw.githubusercontent.com/.../scripts/bootstrap-staging.sh)
#
# atau salin file ini ke server lalu: bash bootstrap-staging.sh
#
# Idempotent — aman dijalankan ulang.

set -euo pipefail

REPO="https://github.com/ardi-sahputra/theday2.git"
BRANCH="develop"
STAGING_ROOT="$HOME/domains/theday.id/public_html/staging"

log()  { printf '\n\033[1;36m==>\033[0m %s\n' "$*"; }
die()  { printf '\033[1;31m[fail]\033[0m %s\n' "$*" >&2; exit 1; }

# --------------------------------------------------------------------------
log "Cek subdomain"
[ -d "$STAGING_ROOT" ] \
    || die "Docroot $STAGING_ROOT belum ada. Buat subdomain 'staging' untuk theday.id di hPanel dulu."

mkdir -p "$STAGING_ROOT"

# --------------------------------------------------------------------------
log "Siapkan repo di $STAGING_ROOT"
if [ -d "$STAGING_ROOT/.git" ]; then
    cd "$STAGING_ROOT"
    git fetch origin "$BRANCH"
    git reset --hard "origin/$BRANCH"
else
    # public_html biasanya sudah berisi index.html bawaan Hostinger.
    find "$STAGING_ROOT" -mindepth 1 -maxdepth 1 -name 'default*' -o -name 'index.html' \
        | xargs -r rm -f
    git clone --branch "$BRANCH" "$REPO" "$STAGING_ROOT.tmp"
    mv "$STAGING_ROOT.tmp"/.git "$STAGING_ROOT/.git"
    rm -rf "$STAGING_ROOT.tmp"
    cd "$STAGING_ROOT"
    git reset --hard "origin/$BRANCH"
fi

# --------------------------------------------------------------------------
log "Siapkan .env"
if [ -f .env ]; then
    echo "    .env sudah ada — dibiarkan."
else
    cp .env.staging.example .env
    echo ""
    echo "  .env dibuat dari template. ISI DULU nilai <...> sebelum lanjut:"
    echo "    nano $STAGING_ROOT/.env"
    echo ""
    echo "  Yang wajib: DB_DATABASE, DB_USERNAME, DB_PASSWORD, STAGING_AUTH_PASSWORD"
    echo "  Lalu jalankan ulang skrip ini."
    exit 0
fi

grep -q '<' .env && die ".env masih punya placeholder <...>. Isi dulu: nano $STAGING_ROOT/.env"

# --------------------------------------------------------------------------
log "APP_KEY"
if grep -q '^APP_KEY=base64:' .env; then
    echo "    sudah ada."
else
    php artisan key:generate --force
fi

# --------------------------------------------------------------------------
log "Rewrite rules (public_html -> public/)"
if [ ! -f .htaccess ]; then
    die ".htaccess tidak ada di repo — seharusnya ikut ter-clone."
fi

log "Permission storage"
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
mkdir -p storage/backups

# --------------------------------------------------------------------------
log "Deploy pertama"
# SSL subdomain kadang belum aktif di menit-menit pertama, jadi health check
# dilewati. Cek manual setelah ini.
SKIP_BACKUP=1 SKIP_HEALTHCHECK=1 bash scripts/deploy.sh staging

# --------------------------------------------------------------------------
log "Seed data awal (plans, template, checklist, admin)"
if [ "$(php artisan tinker --execute='echo \App\Models\Plan::count();' 2>/dev/null | tail -1)" = "0" ]; then
    php artisan db:seed --force
else
    echo "    sudah ada data — dilewati. Untuk seed ulang: php artisan db:seed --force"
fi

log "Selesai. Buka https://staging.theday.id (login basic auth pakai STAGING_AUTH_*)"
echo "   Health check: curl -u \$USER:\$PASS -o /dev/null -w '%{http_code}\\n' https://staging.theday.id/up"
