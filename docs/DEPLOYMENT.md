# Deployment — Staging & Production

Dua environment, satu skrip deploy, dua workflow GitHub Actions.

| | Staging | Production |
|---|---|---|
| URL | `https://staging.theday.id` | `https://theday.id` |
| Branch | `develop` | `main` |
| Path server | `~/domains/staging.theday.id/public_html` | `~/domains/theday.id/public_html` |
| `APP_ENV` | `staging` | `production` |
| `APP_DEBUG` | `true` | **`false`** |
| Database | `u144336260_theday_stg` | database prod (terpisah) |
| Akses | HTTP basic auth + `X-Robots-Tag: noindex` | publik |
| Email | `MAIL_MAILER=log` (tidak terkirim) | SMTP asli |
| Midtrans | sandbox | production |
| Workflow | `.github/workflows/deploy-staging.yml` | `.github/workflows/deploy.yml` |

Server: Hostinger shared, `u144336260@46.202.138.29:65002`. PHP 8.4, Composer 2.9,
MySQL. **Tidak ada Node di server** — lihat [Aset frontend](#aset-frontend).

---

## Alur kerja harian

```
feature branch ──PR──▶ develop ──auto──▶ staging.theday.id
                          │
                          └──merge──▶ main ──auto──▶ theday.id
```

1. Kerjakan di feature branch.
2. Merge ke `develop` → staging ke-deploy otomatis. Uji di sana.
3. Kalau aman, merge `develop` → `main` → production ke-deploy otomatis.

Deploy manual tanpa push: tab **Actions** di GitHub → pilih workflow → **Run workflow**.

---

## Aset frontend

`public/build` **di-commit ke git** (lihat `.gitignore`, baris `# /public/build`).
Server tidak punya Node, jadi bundle harus sudah jadi sebelum di-push.

```bash
npm run build
git add public/build
git commit -m "build: assets"
```

Kalau lupa, deploy tetap sukses tapi UI-nya kode lama. Ini jebakan paling sering.

---

## Yang dilakukan `scripts/deploy.sh`

Dipanggil workflow setelah `git reset --hard origin/<branch>`:

1. **Validasi** — cocokkan `APP_ENV` di `.env` server dengan target deploy.
   Salah target = batal, bukan menimpa. Production juga ditolak kalau
   `APP_DEBUG=true`.
2. **Backup DB** — `mysqldump` ke `storage/backups/<env>-<timestamp>.sql.gz`,
   simpan 5 terakhir.
3. **Maintenance mode** — `artisan down`, dilepas lewat `trap` apa pun hasilnya.
4. **`composer install --no-dev`** + **`artisan migrate --force`**.
5. **Rebuild cache** — `optimize:clear`, lalu config/route/view/event cache,
   `storage:link`, `queue:restart`.
6. **Health check** — `GET $APP_URL/up`, 5x percobaan. Kalau gagal → **rollback
   otomatis** ke commit sebelumnya (`DEPLOY_PREVIOUS_SHA`) dan workflow merah.

> Rollback hanya membalik **kode**, bukan **migrasi**. Kalau yang rusak migrasi,
> restore dari `storage/backups/`.

Flag override (untuk jalan manual):

```bash
SKIP_BACKUP=1 SKIP_HEALTHCHECK=1 bash scripts/deploy.sh staging
```

---

## Setup awal staging (sekali saja)

### 1. hPanel — bikin subdomain

Hostinger → **Domains → Subdomains** → subdomain `staging` untuk `theday.id`.
Pastikan SSL aktif (**SSL → Install** untuk `staging.theday.id`).

### 2. hPanel — bikin database

**Databases → MySQL Databases**. Catat nama DB, user, password.
Konvensi: `u144336260_theday_stg`.

### 3. GitHub — secrets

**Settings → Secrets and variables → Actions**:

| Secret | Nilai |
|---|---|
| `SSH_HOST` | `46.202.138.29` |
| `SSH_USER` | `u144336260` |
| `SSH_PORT` | `65002` |
| `SSH_PRIVATE_KEY` | isi `~/.ssh/id_ed25519_deploy` (private key, bukan `.pub`) |

`SSH_PRIVATE_KEY` lebih dipilih daripada `SSH_PASSWORD` — password auth ditolak
server ini, key auth jalan.

### 4. Server — jalankan bootstrap

```bash
ssh -i ~/.ssh/id_ed25519_deploy -p 65002 u144336260@46.202.138.29
cd ~/domains/theday.id/public_html
bash scripts/bootstrap-staging.sh
```

Jalan pertama akan berhenti setelah membuat `.env` dari template. Isi nilai
`<...>`-nya (`nano ~/domains/staging.theday.id/public_html/.env`), lalu jalankan
ulang skrip yang sama. Skrip idempotent.

### 5. Layanan eksternal

- **Google OAuth** — tambahkan `https://staging.theday.id/auth/google/callback`
  ke Authorized redirect URIs.
- **Midtrans** — pakai sandbox key, set webhook ke `https://staging.theday.id/webhooks/...`.
- **R2/S3** — bucket terpisah supaya upload staging tidak mengotori prod.

---

## Basic auth staging

`app/Http/Middleware/StagingBasicAuth.php` menggembok seluruh app saat
`APP_ENV=staging`, dan menambah `X-Robots-Tag: noindex, nofollow` di semua
response supaya staging tidak pernah muncul di Google.

Kredensial dari `.env`: `STAGING_AUTH_USER` / `STAGING_AUTH_PASSWORD`.
**Password kosong = semua request ditolak** (fail closed, bukan fail open).

Dikecualikan dari gembok, karena dipanggil server lain yang tidak bisa kirim
kredensial:

- `/up` — health check
- `/webhooks/*` — callback pembayaran

---

## Refresh data staging dari prod

Tidak otomatis, dan sengaja. Kalau perlu:

```bash
ssh -i ~/.ssh/id_ed25519_deploy -p 65002 u144336260@46.202.138.29
cd ~/domains/theday.id/public_html

# Dump prod
mysqldump --single-transaction --no-tablespaces \
  -u <prod_user> -p <prod_db> | gzip > /tmp/prod.sql.gz

# Muat ke staging
gunzip < /tmp/prod.sql.gz | mysql -u <stg_user> -p <stg_db>
rm /tmp/prod.sql.gz

cd ~/domains/staging.theday.id/public_html
php artisan optimize:clear && php artisan config:cache
```

Data user asli ikut tersalin. Setelah restore, pastikan `MAIL_MAILER=log` di
`.env` staging masih aktif sebelum menjalankan apa pun yang mengirim email.

---

## Rollback manual

```bash
ssh -i ~/.ssh/id_ed25519_deploy -p 65002 u144336260@46.202.138.29
cd ~/domains/theday.id/public_html

git log --oneline -10          # cari commit yang bagus
git reset --hard <sha>
composer install --no-dev --optimize-autoloader --no-interaction
php artisan optimize:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan up
```

Restore database:

```bash
ls -lt storage/backups/
gunzip < storage/backups/production-<timestamp>.sql.gz | mysql -u <user> -p <db>
```

---

## Troubleshooting

**Deploy sukses tapi UI kode lama** — lupa `npm run build` + commit `public/build`.

**500 setelah deploy** — cek `storage/logs/laravel.log`. Sering karena config
cache basi: `php artisan optimize:clear && php artisan config:cache`.

**Nyangkut di maintenance mode** — `php artisan up`, atau
`rm -f storage/framework/maintenance.php`.

**Workflow gagal di `git reset --hard`** — ada perubahan tak ter-commit di
server. `git status` di server, `git stash` atau buang.

**Staging balas 401 terus** — `STAGING_AUTH_PASSWORD` kosong di `.env`.
Fail closed by design.

**Deploy "salah target"** — `APP_ENV` di `.env` server tidak cocok dengan
argumen `deploy.sh`. Ini pengaman; perbaiki `.env`-nya, jangan skrip-nya.
