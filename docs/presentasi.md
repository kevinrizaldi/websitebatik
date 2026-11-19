# Presentasi — Hamzah Style (30 menit)

Outline presentasi luring untuk sidang/ujian akhir. **Target total: 30 menit tepat**, format *skipped/pitch* (ringkas, tanpa baca slide). Semua angka, rute, dan klaim di bawah diverifikasi dari kode + suite test berjalan: `php artisan test --compact` = **308/308 hijau**, `flutter test` = **42/42 hijau**.

## Slide sampul (1 menit pertama dari Bagian 1)

- **Judul:** *Hamzah Style — Marketplace Batik Berbasis Web, REST API, dan Aplikasi Mobile*
- **Sub-judul:** *Implementasi Laravel 13 + Sanctum + Flutter pada Proyek Akhir*
- **Identitas:** nama, NIM, kelas, dosen pengampu, mata kuliah, tanggal sidang.
- **Satu kalimat ringkas:** "Tiga pintu — web pelanggan + panel admin, REST API ber-token Sanctum, dan aplikasi Flutter — berbagi satu basis data, satu alur pembayaran Midtrans Snap, dan satu layanan ongkir RajaOngkir."
- **Peta isi:** sebutkan enam bagian + alokasinya (problem 2m, arsitektur 5m, API & integrasi 8m, demo live 10m, keamanan & pengujian 3m, penutup 2m).

## Alur 30 menit (pembagian waktu per bagian)

| # | Bagian | Waktu | Slide | Isi pokok |
|---|---|---|---|---|
| 1 | Pembuka & problem | 2 menit | 1–2 | Sampul; masalah batik konvensional; solusi tiga pintu; lingkup proyek |
| 2 | Arsitektur web | 5 menit | 3–5 | Stack (Laravel 13, Blade+Tailwind, MySQL, Breeze); fitur storefront + panel admin; state machine pesanan & moderasi ulasan; ongkir RajaOngkir server-side |
| 3 | API & integrasi | 8 menit | 6–9 | Kontrak API (`{data}`, 28 kombinasi method+path = 8 publik + 18 terlindungi inti + alias `/user` + `POST /auth/google`); autentikasi ganda (cookie+CSRF vs Bearer Sanctum); arsitektur Flutter (service → store `ChangeNotifier` → GoRouter guard, token di `shared_preferences`); Google Sign-In (Socialite redirect di web, `id_token` fail-closed di mobile) |
| 4 | Demo live | 10 menit | 10 | Alur penuh: login → katalog → stok 422 → cart → checkout+ongkir → bayar Snap → transisi admin+resi → terima → ulasan (checklist di bawah) |
| 5 | Keamanan & pengujian | 3 menit | 11–12 | Guard mock payment non-production; kunci API hanya di `.env` (RajaOngkir default `''`); webhook SHA-512; token Sanctum; router guard mobile; 308 + 42 test hijau; dokumen |
| 6 | Penutup | 2 menit | 13 | Kesimpulan, kontribusi/nilai plus, keterbatasan jujur, terima kasih + tanya jawab |
| | **Total** | **30 menit** | | |

**Catatan penyampaian (rubrik ketepatan waktu):**

- Latihan 2x dengan timer; bagian 4 (demo) adalah yang paling rawan molor — pakai varian cepat bila lewat 11 menit (lihat "rencana cadangan" di bawah).
- Angka yang wajib hafal & benar: **308 test Laravel**, **42 test Flutter**, **15+1 tabel** (19 tabel: 12 domain + 7 framework — `docs/erd.md`), **28 kombinasi method+path API**.
- Jangan sebut angka test lama (297/34) di mana pun.

### Ringkasan isi per bagian (poin bicara)

**Bagian 1 — Problem (2 menit):** Belanja batik konvensional butuh datang ke toko; pembeli kini ingin melihat motif/ukuran/stok di mana saja. Solusi: web Laravel (storefront + admin), REST API Sanctum, app Flutter — satu basis data. Lingkup: katalog, keranjang, checkout, pembayaran, pesanan, ulasan; **bukan** (non-goal): GetX, sensor, Play Store.

**Bagian 2 — Arsitektur web (5 menit):** Laravel 13 + Breeze (sesi cookie + CSRF), Blade + Tailwind + Vite, MySQL. Fitur pelanggan: `/koleksi`, `/produk-detail` (stok per ukuran + rating), `/keranjang`, `/checkout` (ongkir RajaOngkir lewat proxy server `/rajaongkir/*`), `/pembayaran` (Snap), `/pesanan`, `/lacak-pesanan`, `/beri-ulasan`. Fitur admin: dashboard, produk/SKU/stok, pesanan, pengiriman (resi), pembayaran (sync Midtrans), pelanggan, moderasi ulasan, laporan. **State machine** di `app/Http/Controllers/Admin/OrderController.php::$allowedTransitions`: `Menunggu Pembayaran → Diproses → Dikirim → Selesai` (transisi lain dibatasi; `Dikirim` wajib resi di `/admin/pengiriman`; `Selesai` = daun). Gerbang API: `confirm` hanya dari `Dikirim`, `cancel` hanya sebelum bayar.

**Bagian 3 — API & integrasi (8 menit):** `routes/api.php` = 27 registrasi route = 28 kombinasi method+path: **8 endpoint publik inti** (`health`, `register`, `login`, `categories`, `products`, `products/{id}`, `products/{id}/reviews`, `media/{path}`) + **18 endpoint terlindungi inti** (`me`, `PATCH user`, `logout`, `addresses`×4, `cart`×4, `orders`, `checkout`, `orders/{id}`, `orders/{id}/payment`, `confirm`, `cancel`, `reviews`) + `POST /auth/google` (publik) + alias `GET /user`. Respons kanonik **`{data}`**; key legacy `token`/`user` dipertahankan untuk kompatibilitas app yang sudah berjalan (`docs/api.md`). Dua mekanisme auth pada satu API: web = sesi cookie + CSRF (same-origin), mobile = `Authorization: Bearer` token Sanctum yang disimpan di `shared_preferences`. Flutter: pola **service (I/O) → store (`ChangeNotifier`) → router (GoRouter + guard)** tanpa framework state berat; rute yang butuh login dijaga `app_router.dart` (`/checkout`, `/pembayaran`, `/pesanan`, `/detail-pesanan`, `/lacak-pesanan`, `/beri-ulasan`, `/profil`). Google Sign-In: web lewat Socialite (`GET /auth/google` + callback), mobile mengirim `id_token` ke `POST /api/auth/google` yang **fail-closed** (tolak bila `GOOGLE_CLIENT_ID` kosong, `aud` tidak cocok, `exp/iat/nbf` tidak ada/kedaluwarsa).

**Bagian 4 — Demo live (10 menit):** ikuti checklist di bawah; ucapkan alasan tiap aksi (validasi stok server, ongkir server-side, transisi status, gerbang ulasan).

**Bagian 5 — Keamanan & pengujian (3 menit):** (1) token/mock payment hanya di non-production — `MIDTRANS_IS_PRODUCTION=false` default; di production fallback `MOCK_SNAP_*` **ditolak** (`MidtransService` menolak `mock=1`); diuji `PaymentMockGuardTest`. (2) `RAJAONGKIR_API_KEY` default `''` di `config/rajaongkir.php` — kunci hanya di `.env`, pernah dipanggil server saja, klien tidak pernah memegangnya; tanpa key → layanan memberi fallback offline. (3) Webhook Midtrans: field wajib divalidasi dulu, lalu **signature `SHA-512(order_id + status_code + gross_amount + server_key)`** diverifikasi sebelum menyentuh database → `403` bila salah. (4) Token Sanctum bisa dicabut (`POST /api/logout`). (5) Router guard mobile. (6) Pengujian: **308 test Laravel (308/308)** + **42 test Flutter (42/42)**; dokumen: ERD, use case, api.md, manual book.

**Bagian 6 — Penutup (2 menit):** kontribusi (integrasi tiga platform + state machine + keamanan pembayaran + cakupan test), keterbatasan jujur (katalog mobile ambil halaman pertama `per_page` default 20 — belum infinite scroll; Midtrans & RajaOngkir dalam mode sandbox/opsional), penutup + terima kasih.

## Live demo checklist

### Pra-syarat server (Laragon / lokal)

- [ ] MySQL aktif; database proyek ter-migrasi & ter-seed: `php artisan migrate --seed` (akun & produk dari `database/seeders/AdminSeeder.php`).
- [ ] Backend jalan: `php artisan serve` → `http://127.0.0.1:8000`; bila aset berubah: `npm run build` (atau `npm run dev`).
- [ ] `.env` terisi (jangan pernah ditampilkan di layar): `MIDTRANS_*` sandbox (untuk jendela Snap asli) atau biarkan kosong → mock `MOCK_SNAP_*` otomatis; `RAJAONGKIR_API_KEY` agar pilihan kurir/ongkir tampil.
- [ ] Cek cepat: buka `http://127.0.0.1:8000/api/health` → `{"ok": true}`.
- [ ] Akun admin & pelanggan ada (idempoten dari seeder).

### Pra-syarat mobile (adb / emulator / device)

- [ ] Emulator Android berjalan (atau HP fisik via Wi-Fi).
- [ ] Dari repo `mobilebatik`: `flutter pub get` lalu `flutter run` — default API emulator `http://10.0.2.2:8000/api`; HP fisik: `flutter run --dart-define=API_BASE_URL=http://<IP_LAPTOP>:8000/api`.
- [ ] Aplikasi terbuka di `/login` (atau langsung `/home` bila sesi masih ada — logout dulu agar demo login tampil).
- [ ] Cadangan: `flutter test` & `php artisan test --compact` sudah dijalankan sekali sebelum demo (output hijau direkam/ditampilkan di bagian 5).

### Akun demo (satu-satunya akun yang boleh muncul)

| Peran | Email | Password | Sumber |
|---|---|---|---|
| Admin | `admin@webstorebatik.com` | `admin12345` | `database/seeders/AdminSeeder.php` |
| Pelanggan | `customer@webstorebatik.com` | `password123` | `database/seeders/AdminSeeder.php` |

- Browser: tab pelanggan + tab admin (inkognito agar sesi terpisah). Jangan ketik password terbuka (autofill/tekan cepat), jangan tampilkan `.env`, token, atau key.

### Urutan langkah demo (target 10 menit)

| Menit | Aksi | Yang diucapkan |
|---|---|---|
| 0:00–0:45 | Login web pelanggan (`/login`) + login/register mobile (`/login` `/register`) | cookie+CSRF vs Bearer Sanctum, satu API & satu basis data |
| 0:45–1:30 | Katalog web `/koleksi` (cari, filter kategori, sortir) + mobile `/katalog` | kontrak `products` sama; stok per ukuran |
| 1:30–2:15 | Detail produk (web `/produk-detail/{id}` + mobile `/detail-produk`): ukuran, stok, rating & ulasan | ulasan hanya yang **Disetujui** (moderasi admin) |
| 2:15–2:45 | Mobile: qty melebihi stok → **error 422** dari `POST /api/cart`; ulangi wajar → sukses | validasi stok di **server**, bukan hanya di layar |
| 2:45–4:15 | `/keranjang` → `/checkout`: data pengiriman, kurir + ongkir RajaOngkir → **Buat Pesanan** (`POST /api/checkout` → `snap_token` + `snap_redirect_url`) | ongkir dihitung server-side, key tidak pernah di klien |
| 4:15–5:30 | `/pembayaran` → **Bayar** (Snap sandbox atau simulasi `MOCK_SNAP_*` non-production) → kembali → muat ulang status | webhook Midtrans / sinkronisasi `GET /api/orders/{id}/payment` |
| 5:30–7:00 | Admin `/admin/orders`: `Menunggu Pembayaran → Diproses → Dikirim` (wajib resi di `/admin/pengiriman`) | state machine menolak transisi liar |
| 7:00–8:15 | Pelanggan `/pesanan` → `/lacak-pesanan` → **Terima** (`POST /api/orders/{id}/confirm`, hanya dari `Dikirim`) → `/beri-ulasan` | gerbang ulasan: hanya status **Selesai** |
| 8:15–9:00 | Admin `/admin/ulasans`: moderasi ulasan baru → **Disetujui** → tampil di produk | moderasi konten pelanggan |
| 9:00–10:00 | Tutup: `php artisan test --compact` (308 hijau) + `flutter test` (42 hijau) + struktur `docs/` | pengujian menutup kontrak; dokumentasi lengkap |

**Rencana cadangan (bila demo gagal/molor):**

- Midtrans offline/key kosong → mock `MOCK_SNAP_*` otomatis (non-production) tetap jalan; jelaskan guard-nya.
- RajaOngkir key kosong → tampilkan fallback ongkir/daftar wilayah offline, sebut variabel `.env`-nya.
- Emulator bermasalah → ganti ke **video 5 menit** (`docs/video-script.md`, YouTube unlisted).
- Lewat 11 menit → potong langkah moderasi ulasan (8:15–9:00), lanjut ke pengujian.

## Q&A prep (kemungkinan pertanyaan + jawaban ringkas)

1. **Kenapa Laravel Sanctum, bukan JWT buatan sendiri?**
   Sanctum menyediakan *personal access token* siap pakai: penerbitan/penarikan token (`createToken`/`revoke`), middleware `auth:sanctum`, dan integrasi natural dengan Laravel — tanpa mengelola refresh token sendiri. Cukup untuk kebutuhan app mobile ini; web tetap memakai sesi Breeze.

2. **Web pakai sesi cookie, mobile pakai token — mengapa berbeda?**
   Web same-origin: cookie + CSRF (Breeze) praktis dan aman karena browser menempelkan cookie otomatis. Mobile lintas platform tidak andalkan cookie → server menerbitkan token Sanctum saat `POST /api/login|register`, disimpan di `shared_preferences`, dan dikirim sebagai `Authorization: Bearer` pada tiap request. Dua mekanisme, satu API dan satu tabel pengguna.

3. **Kenapa ada pembayaran mock? Tidak berbahaya di production?**
   Mock hanya fallback ketika key Midtrans kosong/sandbox dan `MIDTRANS_IS_PRODUCTION=false` (default demo). Saat production, token `MOCK_SNAP_*` dan redirect `mock=1` **tidak pernah** dibuat — permintaan ditolak dan dicatat ke log (`MidtransService`); perilaku ini diuji `PaymentMockGuardTest` (mock diabaikan saat `is_production=true`).

4. **Bagaimana state machine status pesanan bekerja?**
   `Admin\OrderController::$allowedTransitions` memetakan status asal → status yang boleh (mis. `Diproses → Dikirim`; `Selesai`/`Dibatalkan` daun). UI hanya menampilkan transisi yang diizinkan; langkah `Dikirim` wajib mengisi nomor resi. Di API, gerbang serupa: `confirm` hanya dari `Dikirim`, `cancel` hanya sebelum bayar — diverifikasi test.

5. **Bagaimana keamanan webhook Midtrans?**
   Urutan di `MidtransWebhookController`: (1) field wajib (`order_id`, `status_code`, `gross_amount`, `signature_key`, `transaction_status`) harus string non-kosong → `400`; (2) signature `SHA-512(order_id + status_code + gross_amount + server_key)` dibandingkan dengan `hash_equals` → `403` bila salah; (3) baru mencari pembayaran dan memproses transisi. Verifikasi terjadi **sebelum** menyentuh database.

6. **Kenapa key RajaOngkir default-nya string kosong?**
   `config('rajaongkir.api_key', '')` — default aman agar repo tidak pernah berisi rahasia; key hanya hidup di `.env`. Server-side proxy `/rajaongkir/*` yang memakainya, jadi klien web/mobile tidak pernah melihat key; tanpa key, layanan jatuh ke dataset fallback offline, bukan error bocor.

7. **Login Google bagaimana? Kenapa verifikasinya fail-closed?**
   Web: Socialite (`GET /auth/google` + callback). Mobile: `google_sign_in` mengambil `id_token`, dikirim ke `POST /api/auth/google`, diverifikasi server via endpoint tokeninfo Google. Fail-closed = menolak ketika `GOOGLE_CLIENT_ID` belum diset, `aud` tidak cocok, atau klaim `exp/iat/nbf` tidak ada/kedaluwarsa — tidak ada jalan "terima saja karena proses gagal".

8. **Kenapa pakai bottom navigation, bukan drawer?**
   Lima tujuan paling sering (Home, Produk, Keranjang, Pesanan, Profil) berada satu ketukan dari mana saja; drawer butuh dua ketukan dan menyembunyikan status keranjang/pesanan. Pola ini juga konsisten dengan aplikasi e-commerce pada umumnya (`bottom_nav_bar.dart`).

9. **Kenapa state management `ChangeNotifier` sederhana, bukan Bloc/Riverpod/GetX?**
   Skala proyek dan waktu dua minggu: pola **service (I/O HTTP) → store (`ChangeNotifier`) → router (guard)** sudah cukup, minim dependensi, dan mudah diuji (`cart_store_test`, `checkout_flow_test`). Non-goal proyek memang menyebut GetX tidak dipakai.

10. **Sudah pakai lazy loading/pagination?**
    API sudah siap: `GET /api/products` mendukung `per_page` 1–50 beserta `meta`/`links` paginasi. Web `/koleksi` memuat semua produk sekaligus (data seed kecil), mobile mengambil halaman pertama (default 20) dengan *pull-to-refresh* — infinite scroll belum diterapkan; jujur sebagai keterbatasan, bukan klaim sudah ada.

11. **Apa bedanya kontrak `{data}` dan key legacy (`token`, `user`)?**
    `{data}` adalah bentuk kanonik baru; key top-level `token`/`user` dipertahankan agar versi Flutter yang sudah berjalan tidak rusak saat kontrak dinormalisasi. Nilainya sama; app baru disarankan membaca `data` (tercatat di `docs/api.md`).

12. **Berapa test dan apa yang dicakup?**
    **308 test Laravel (308/308 hijau, 1529 assertion)** dan **42 test Flutter (42/42)**. Cakupan penting: guard mock payment, verifikasi signature webhook, cart/checkout/gerbang status & ulasan, kontrak API `{data}`, autentikasi Google fail-closed (backend); api client, cart store, checkout flow, auth guard router, widget (Flutter).

## Referensi dokumen

| Dokumen | Isi | Dipakai untuk |
|---|---|---|
| `docs/erd.md` | 19 tabel (12 domain + 7 framework) + diagram mermaid + relasi FK | Bagian 2 (arsitektur data) |
| `docs/use-case.md` | Aktor pelanggan/admin/sistem Midtrans/RajaOngkir + diagram use case | Bagian 1–2 (lingkup) |
| `docs/api.md` | 28 kombinasi method+path, skema respons, bentuk error | Bagian 3 (kontrak API) |
| `docs/manual-book.md` | Panduan langkah pelanggan & admin | Bagian 4 (demo) & jawaban "bagaimana pakai" |
| `docs/video-script.md` | Skrip video ≤5 menit (300 detik) dengan tabel waktu | Cadangan demo & referensi angka test terkini |
| `README.md` (websitebatik) | Deskripsi, instalasi, ringkasan API, cara run test | Lampiran/tautan LMS |
| `mobilebatik/README.md` | Cara run, `--dart-define=API_BASE_URL`, alur demo, arsitektur | Bagian 3–4 (mobile) |
| `DEPLOY.md` | **Sudah dihapus dari repo** (commit `5c3e81a` bersama `AGENTS.md`/`CLAUDE.md`); penggantinya: tabel variabel env di `README.md` §Instalasi | Sebut sebagai "checklist deploy" — jangan janjikan file ini ada |

**Tautan yang boleh disebut saat penutup:** repository source code websitebatik + mobilebatik, video demo (YouTube unlisted), dan seluruh `docs/` di atas.
