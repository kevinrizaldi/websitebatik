# DEPLOY — Hosting Kampus

Checklist eksekusi saat hosting siap. Semua langkah wajib dicentang sebelum serah terima.

## 1. Environment variables (`.env` production)

Copy dari `.env.example`, isi nilai nyata. **Jangan pernah commit `.env`.**

| Var | Nilai production | Catatan |
|---|---|---|
| `APP_ENV` | `production` | |
| `APP_DEBUG` | `false` | wajib — jangan pernah `true` di publik |
| `APP_URL` | `https://<domain-kampus>` | tanpa trailing slash |
| `APP_KEY` | hasil `php artisan key:generate --show` | |
| `DB_CONNECTION` | `mysql` | |
| `DB_HOST` / `DB_PORT` / `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | kredensial hosting kampus | verify dengan pihak kampus |
| `SESSION_DRIVER` | `database` | shared hosting: pastikan tabel sessions ikut termigrasi (sudah ada di migration) |
| `CACHE_STORE` | `database` | |
| `QUEUE_CONNECTION` | `database` | |
| `FILESYSTEM_DISK` | `local` | `php artisan storage:link` wajib dijalankan (foto produk) |
| `MAIL_MAILER` | `log` (default) / `smtp` jika kampus kasih SMTP | |
| `MAIL_HOST` / `MAIL_PORT` / `MAIL_USERNAME` / `MAIL_PASSWORD` / `MAIL_FROM_ADDRESS` / `MAIL_FROM_NAME` | sesuai SMTP | |
| `MIDTRANS_MERCHANT_ID` | sandbox merchant id | |
| `MIDTRANS_CLIENT_KEY` | sandbox client key | |
| `MIDTRANS_SERVER_KEY` | sandbox server key | rahasia — hanya di `.env` |
| `MIDTRANS_IS_PRODUCTION` | `false` | **tetap sandbox** kecuali kampus mendukung akun production Midtrans |
| `RAJAONGKIR_API_KEY` | key aktif | **rotate dulu — lihat §4** |
| `RAJAONGKIR_BASE_URL` | `https://rajaongkir.komerce.id/api/v1` | |
| `RAJAONGKIR_ORIGIN_ID` / `RAJAONGKIR_ORIGIN_CITY_ID` / `RAJAONGKIR_ORIGIN_NAME` / `RAJAONGKIR_ORIGIN_PROVINCE_NAME` | default Solo (61663 / 444) | ganti hanya jika toko pindah |
| `RAJAONGKIR_COURIERS` | `jne,sicepat,jnt,pos,tiki` | |
| `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` / `GOOGLE_REDIRECT_URI` | OAuth Google | redirect = `https://<domain>/auth/google/callback`; verify dengan kampus (boleh di-skip = login Google mati) |
| `SANCTUM_STATEFUL_DOMAINS` | `<domain-kampus>` | |
| `SESSION_SECURE_COOKIE` | `true` | hanya jika HTTPS aktif |

## 2. Build & install (di server / lokal lalu upload)

- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] `npm ci && npm run build` (butuh Node; hasil build = `public/build`)
- [ ] Set `.env` production (tabel §1), lalu `php artisan config:cache`
- [ ] `php artisan migrate --force`
- [ ] `php artisan storage:link`
- [ ] `php artisan optimize` (route + view cache)

## 3. Scheduler (order expire tiap 15 menit)

Job `orders:expire-overdue` terjadwal `everyFifteenMinutes()` (`routes/console.php:12`) — pesanan pending lewat 24 jam dibatalkan otomatis.

- [ ] Cron utama (hosting dengan akses cron):
  `* * * * * php /path/ke/app/artisan schedule:run >/dev/null 2>&1`
- [ ] **Shared hosting tanpa cron** — risiko: pesanan tidak pernah kedaluwarsa otomatis. Alternatif:
  - cPanel → Cron Jobs → tambah `*/15 * * * *` jika shell cron tersedia walau terbatas;
  - fallback manual: jalankan `php artisan orders:expire-overdue` **1x sehari** (catat sebagai known limitation di laporan).

## 4. Webhook Midtrans

- [ ] Daftarkan URL `https://<domain-kampus>/midtrans/notification` di dashboard Midtrans **sandbox** (Settings → Configuration → Notification URL). Alias `/midtrans/webhook` juga aktif, pakai satu saja.
- [ ] `MIDTRANS_IS_PRODUCTION=false` tetap — ganti ke `true` hanya jika kampus menyediakan akun production.

## 5. Security checklist

- [ ] **Rotate key RajaOngkir**: key lama pernah ada di default `config/rajaongkir.php` dan masih di **git history** → buat key baru di dashboard Komerce, taruh hanya di `.env`, key lama dicabut.
- [ ] `APP_DEBUG=false`, `APP_ENV=production`
- [ ] `.env` tidak ter-commit (sudah di `.gitignore` — verifikasi `git ls-files .env` kosong)
- [ ] `APP_KEY` unik per environment (jangan salin dari `.env` lokal)
- [ ] `MIDTRANS_SERVER_KEY`, `RAJAONGKIR_API_KEY`, `GOOGLE_CLIENT_SECRET` hanya di `.env`
- [ ] HTTPS aktif (cert kampus) → `SESSION_SECURE_COOKIE=true`

## 6. Smoke test (setelah deploy)

- [ ] Buka homepage — tanpa error 500
- [ ] Register / login (web)
- [ ] Checkout 1 produk penuh: cart → checkout → ongkir tampil (bukti RajaOngkir key aktif) → bayar Snap sandbox → status berubah
- [ ] Notifikasi webhook masuk: status pesanan update tanpa buka Midtrans dashboard
- [ ] Login admin → lihat pesanan masuk
- [ ] Login Google (jika OAuth dikonfigurasi)

## 7. Build APK (Flutter)

Emulator default pakai `http://10.0.2.2:8000/api`. Untuk production:

```bash
flutter build apk --release --dart-define=API_BASE_URL=https://<domain-kampus>/api
```

- [ ] Build ulang APK setiap ganti domain/API
- [ ] Test APK di device fisik: login + checkout 1x

## 8. Rollback

- [ ] Sebelum deploy: `git tag deploy-<tanggal>` pada commit yang terakhir diketahui baik
- [ ] Rollback: `git checkout <tag>` (atau revert commit rusak), ulangi §2, `php artisan migrate:rollback` hanya jika migration ikut berubah
- [ ] Simpan `.env` lama terpisah — jangan tertimpa rollback
