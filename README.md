# Hamzah Style — Website Batik (websitebatik)

Marketplace batik (**Hamzah Style**): storefront web + panel admin + REST API (Sanctum) + aplikasi mobile Flutter (`mobilebatik`). Pelanggan bisa telusuri katalog, keranjang, checkout dengan ongkir RajaOngkir, bayar via Midtrans Snap, melacak pesanan, dan memberi ulasan; admin mengelola produk/stok/pesanan/pengiriman/pembayaran sampai laporan penjualan.

## Fitur

- **Web pelanggan**: beranda & koleksi, pencarian/filter kategori, detail produk + ulasan, keranjang, checkout (ongkir RajaOngkir), pembayaran Midtrans Snap, riwayat pesanan (batal / konfirmasi terima), ulasan, profil & alamat, lupa password, login Google (opsional).
- **Panel admin**: dashboard, produk (SKU, stok per ukuran, gambar), kategori, stok (aman/menipis/habis), pesanan (state machine status), pengiriman (ekspedisi + resi), pembayaran (rekonsiliasi + sync Midtrans), pelanggan, moderasi ulasan, laporan penjualan (cetak/PDF/Excel/CSV), pengaturan toko.
- **REST API** untuk mobile: auth, katalog, keranjang, checkout, pesanan, pembayaran Snap, ulasan, alamat — dokumentasi lengkap di [`docs/api.md`](docs/api.md).
- **Mobile Flutter** (`mobilebatik`): katalog, cart, checkout, pembayaran, pesanan, ulasan, profil — lihat repo tersebut.
- **Otomasi**: pembatalan pesanan kedaluwarsa tiap 15 menit (`orders:expire-overdue`), webhook Midtrans dengan verifikasi signature.

## Tech stack

| Lapisan | Teknologi |
|---|---|
| Backend | PHP 8.3+, Laravel 13, Sanctum 4, Socialite, `midtrans/midtrans-php` |
| Frontend web | Blade + Tailwind CSS + Vite |
| Database | MySQL (default Laravel) |
| Pembayaran | Midtrans Snap (sandbox default) |
| Ongkos kirim | RajaOngkir/Komerce API |
| Mobile | Flutter (go_router, http, shared_preferences) — repo `mobilebatik` |
| Testing | PHPUnit (feature/unit), Flutter test |

## Instalasi (lokal)

Prasyarat: PHP 8.3+ (dengan ekstensi yang diminta Laravel), Composer, Node.js, MySQL.

```bash
composer install
cp .env.example .env          # lalu isi DB, MIDTRANS_*, RAJAONGKIR_API_*
php artisan key:generate
php artisan migrate
php artisan storage:link      # foto produk (public/storage)
npm ci && npm run build       # atau: npm run dev
php artisan serve             # http://127.0.0.1:8000
```

Variabel env wajib (DB, `MIDTRANS_*`, `RAJAONGKIR_*`, `GOOGLE_*`, dsb.) beserta nilai produksi tercatat di tabel **[`DEPLOY.md`](DEPLOY.md) §1** — jangan commit `.env`.

Untuk admin: buat user lalu set `role = admin` (middleware `admin` melindungi route panel).

## Menjalankan test

```bash
php artisan test --compact     # seluruh suite (297 test)
```

Jalankan per file: `php artisan test tests/Feature/ApiCartTest.php`. Formatter gaya (bila menyentuh PHP): `vendor/bin/pint --dirty --format agent`.

## Ringkasan API

Base URL: `/api` — auth memakai **Bearer token Sanctum** (dari `POST /api/register|login`). Contoh & skema field lengkap: [`docs/api.md`](docs/api.md).

| Endpoint | Method | Auth | Tujuan |
|---|---|---|---|
| `/api/health` | GET | – | Cek kesehatan API |
| `/api/register` | POST | – | Registrasi + token |
| `/api/login` | POST | – | Login + token |
| `/api/me`, `/api/user` | GET | Bearer | Profil user |
| `/api/user` | PATCH | Bearer | Update profil |
| `/api/logout` | POST | Bearer | Cabut token |
| `/api/categories` | GET | – | Daftar kategori |
| `/api/products` | GET | – | Katalog (search/category/sort/per_page) |
| `/api/products/{id}` | GET | – | Detail produk + ulasan |
| `/api/products/{id}/reviews` | GET | – | Ulasan produk (Disetujui) |
| `/api/media/{path}` | GET | – | Gambar CORS-safe |
| `/api/addresses` | GET/POST | Bearer | Daftar/tambah alamat |
| `/api/addresses/{id}` | PUT/PATCH/DELETE | Bearer | Ubah/hapus alamat |
| `/api/cart` | GET/POST | Bearer | Lihat/tambah keranjang |
| `/api/cart/{id}` | PATCH/DELETE | Bearer | Ubah/hapus item |
| `/api/orders` | GET | Bearer | Daftar pesanan |
| `/api/checkout` | POST | Bearer | Buat pesanan + Snap token |
| `/api/orders/{id}` | GET | Bearer | Detail pesanan |
| `/api/orders/{id}/payment` | GET | Bearer | Status bayar + Snap token |
| `/api/orders/{id}/confirm` | POST | Bearer | Konfirmasi terima |
| `/api/orders/{id}/cancel` | POST | Bearer | Batalkan belum bayar |
| `/api/orders/{id}/reviews` | POST | Bearer | Beri ulasan (status Selesai) |

Web-only (bukan API mobile): webhook `POST /midtrans/notification`, proxy `GET|POST /rajaongkir/*`, halaman pembayaran Snap.

## Struktur proyek

```
app/
  Http/Controllers/Api/      # REST API (Auth, Product, Cart, Order, Profile, Media)
  Http/Controllers/Admin/    # Panel admin (orders, stok, laporan, pengiriman, …)
  Http/Controllers/          # Web storefront + Midtrans webhook + RajaOngkir
  Models/                    # User, Product, Order, Payment, Shipment, Review, …
  Services/                  # CheckoutService, OrderLifecycleService, Midtrans/*, RajaOngkirService
routes/                      # api.php (REST), web.php (storefront+admin), console.php (scheduler)
database/migrations/         # 16 file → 19 tabel (lihat docs/erd.md)
resources/views/             # Blade (storefront + admin)
tests/                       # Feature/unit PHPUnit (Api*, Admin*, web flow)
docs/                        # erd.md, use-case.md, api.md, manual-book.md
DEPLOY.md                    # Checklist hosting + tabel env lengkap
```

## Dokumen lain

- [`docs/erd.md`](docs/erd.md) — ERD dari migration (Acara 51).
- [`docs/use-case.md`](docs/use-case.md) — use case pelanggan/admin/sistem (Acara 50).
- [`docs/api.md`](docs/api.md) — referensi API lengkap.
- [`docs/manual-book.md`](docs/manual-book.md) — panduan penggunaan pelanggan & admin.
- `../mobilebatik/README.md` — cara menjalankan aplikasi mobile.

## Kredit

- [Laravel](https://laravel.com), [Laravel Sanctum](https://laravel.com/docs/sanctum), [Midtrans](https://midtrans.com/id), [RajaOngkir/Komerce](https://rajaongkir.com), [Flutter](https://flutter.dev).
- Dibuat untuk tugas akhir semester 3 (proyek e-commerce batik).
