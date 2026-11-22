# Video Script — Demo Hamzah Style (≤5 menit)

Skrip shot-by-shot untuk video demo akhir. **Target total: 5 menit tepat (300 detik), batas ≤5:00 terpenuhi.** Semua rute/URL di bawah diverifikasi dari kode: `routes/web.php`, `routes/api.php`, dan `mobilebatik/lib/router/app_router.dart`. Alur demo mengikuti `../mobilebatik/README.md` §Alur demo dan `docs/manual-book.md`.

## Tabel waktu (bukti total ≤5:00)

| # | Segmen | Durasi | Akumulasi |
|---|---|---|---|
| 1 | Intro — masalah + overview | 30 detik | 0:30 |
| 2 | Login web + mobile | 45 detik | 1:15 |
| 3 | Katalog & detail produk | 45 detik | 2:00 |
| 4 | Keranjang → checkout → bayar Snap | 90 detik | 3:30 |
| 5 | Pesanan & transisi status admin | 60 detik | 4:30 |
| 6 | Penutup — keamanan & pengujian | 30 detik | 5:00 |
| | **Total** | **300 detik = 5:00** | **5:00 ≤ 5:00 ✓** |

## Alat & pra-syarat

- **Perekam**: OBS Studio **atau** Xbox Game Bar (`Win`+`Alt`+`R`) — rekam layar penuh 16:9.
- **Backend**: Laragon (Apache/MySQL) + `php artisan serve` → `http://127.0.0.1:8000`; jalankan `npm run dev` (atau `npm run build`) bila aset perlu dibangun ulang.
- **Mobile**: emulator/device + `flutter run` dari repo `mobilebatik` — default API emulator Android `http://10.0.2.2:8000/api` (lihat `mobilebatik/README.md`).
- **Data**: `php artisan migrate --seed` — akun admin & pelanggan demo serta produk ter-seed (akun tercatat di `database/seeders/AdminSeeder.php`; jangan pakai akun pribadi).
- **Pembayaran**: akun **Midtrans sandbox** + `MIDTRANS_*` di `.env` (tabel lengkap di `DEPLOY.md` §1). Bila key belum diisi, backend otomatis memakai token mock `MOCK_SNAP_*` — **hanya di non-production**; untuk tampilan jendela Snap asli, isi key sandbox.
- **Ongkir**: `RAJAONGKIR_API_KEY` di `.env` agar pilihan kurir/ongkir muncul di checkout (server-side, tidak pernah di klien).
- **Browser**: dua tab — pelanggan (`127.0.0.1:8000`) dan admin (login terpisah; tab inkognito membantu).

## Segmen 1 — Intro (0:00–0:30, 30 detik)

**Narasi (ucapkan, ±71 kata):**

> Belanja batik konvensional mengharuskan pembeli datang ke toko, sementara pembeli masa kini ingin melihat motif, ukuran, dan stok dari mana saja. Hamzah Style menjawab kebutuhan itu dengan tiga pintu yang saling terhubung: website pelanggan dan panel admin berbasis Laravel, REST API ber-token Sanctum, serta aplikasi mobile Flutter. Ketiganya berbagi satu basis data, satu alur pembayaran Midtrans Snap, dan satu layanan ongkir RajaOngkir. Mari ikuti alurnya dari login sampai pesanan selesai.

**Aksi layar:**

1. `0:00` — Buka `http://127.0.0.1:8000/` (beranda: banner + produk unggulan).
2. `0:08` — Pindah cepat ke jendela emulator yang sudah membuka aplikasi Flutter (layar `/home`, mode tamu).
3. `0:15` — Pindah cepat ke tab admin `http://127.0.0.1:8000/dashboard` (atau halaman login admin).
4. `0:22` — Kembali ke beranda web, zoom pelan ke grid produk; sisakan waktu untuk jeda napas sebelum transisi.

**Prasyarat/props:** backend jalan + seeded; emulator jalan; ketiga jendela sudah ditata berdampingan sebelum rekaman (jangan rekam proses menyiapkan).

## Segmen 2 — Login web + mobile (0:30–1:15, 45 detik)

**Narasi (±95 kata):**

> Kita mulai dari dua klien dengan backend yang sama. Di browser, buka 127.0.0.1:8000/login; bila belum punya akun, daftar lewat /register dengan nama, email, telepon, dan password. Di aplikasi Flutter, aplikasi langsung membuka /home dalam mode tamu; untuk masuk, tap ikon profil atau tekan + Keranjang lalu pilih Login pada ajakan yang muncul — setelah berhasil, guard router membawa pengguna kembali ke /home. Perhatikan perbedaannya: web memakai sesi cookie dan CSRF, sedangkan mobile menyimpan token dari POST /api/login lalu mengirimkannya sebagai Authorization: Bearer. Dua mekanisme autentikasi, satu API, dan satu basis data pengguna yang sama — semua data yang tampil di kedua layar datang dari endpoint yang sama.

**Aksi layar:**

1. `0:30` — Web: buka `http://127.0.0.1:8000/register` (Breeze), isi formulir dengan akun demo, **atau** langsung `http://127.0.0.1:8000/login` bila akun sudah ada.
2. `0:45` — Web: setelah masuk, arahkan singkat ke halaman profil `http://127.0.0.1:8000/profile` sebagai bukti sesi aktif.
3. `0:55` — Mobile: dari `/home` tap ikon profil → `/login` → buka `/register` (nama, email, telepon, password + konfirmasi) → submit → guard `app_router.dart` mengarahkan ke `/home`. Alternatif cepat: login via `/login` (atau lewat SnackBar "+ Keranjang" tadi).
4. `1:05` — Tampilkan dua layar berdampingan (browser + emulator) pada posisi login-sukses masing-masing.

**Prasyarat/props:** akun demo dari seeder (atau akun baru yang dibuat khusus rekaman); jangan tampilkan password di layar — ketik singkat atau pakai autofill tersembunyi.

## Segmen 3 — Katalog & detail produk (1:15–2:00, 45 detik)

**Narasi (±100 kata):**

> Sekarang katalognya. Di web, buka /koleksi: seluruh produk tampil dengan pencarian, filter kategori, filter harga, dan urutan termurah–termahal. Klik satu produk untuk masuk ke /produk-detail: gambar, harga, stok per ukuran seperti S, M, L, rating, dan ulasan yang sudah disetujui admin. Di mobile, alurnya sama di /home lalu /katalog — pencarian, filter kategori, sortir harga — dan detail produk di /detail-produk dengan stok per ukuran serta rating. Perhatikan angka stok per ukuran: angka inilah yang menjaga keranjang tidak pernah melebihi stok nyata.

**Aksi layar:**

1. `1:15` — Web: buka `http://127.0.0.1:8000/koleksi`; ketik kata kunci di kolom pencarian, ubah sortir ke "termurah", klik filter kategori.
2. `1:30` — Web: klik satu produk → `http://127.0.0.1:8000/produk-detail/{id}`; tunjukkan pilihan ukuran + angka stok per ukuran, lanjut scroll ke bagian rating & ulasan pelanggan.
3. `1:45` — Mobile: `/home` → `/katalog` (cari + sortir) → tap produk → `/detail-produk`; tunjukkan stok ukuran dan ringkasan ulasan.
4. `1:55` — Tahan shot detail produk 2–3 detik sebelum transisi.

**Prasyarat/props:** produk ter-seed dengan stok per ukuran berbeda (untuk mendemokan validasi stok di segmen 4); minimal satu produk sudah punya ulasan berstatus **Disetujui** (hasil moderasi admin di `/admin/ulasans`) agar bagian ulasan tidak kosong.

## Segmen 4 — Keranjang → checkout → bayar Snap (2:00–3:30, 90 detik)

**Narasi (±215 kata):**

> Kita coba alur pembelian di aplikasi mobile. Di /detail-produk, pilih ukuran lalu isi jumlah melebihi stok dan tekan Tambah ke Keranjang: server menolak dengan pesan stok tidak mencukupi — ini validasi 422 dari API, bukan sekadar aturan di layar. Ulangi dengan jumlah yang wajar, lalu buka /keranjang, atur jumlah, dan lanjut ke /checkout. Isi nama penerima, telepon, alamat, pilih kurir beserta ongkir dari RajaOngkir, lalu tekan Buat Pesanan. POST /api/checkout membuat pesanan berstatus Menunggu Pembayaran dan mengembalikan snap_token beserta snap_redirect_url. Di /pembayaran, tekan Bayar: halaman Snap Midtrans terbuka di browser eksternal, kita selesaikan pembayarannya, lalu kembali ke aplikasi. Begitu webhook Midtrans diterima dan signature-nya cocok, status pesanan berubah sendiri. Di versi web, langkahnya sama: /keranjang, /checkout, lalu masuk ke /pembayaran/{kode pesanan} dan klik Bayar.

**Aksi layar:**

1. `2:00` — Mobile `/detail-produk`: pilih ukuran, naikkan qty melebihi stok → **Tambah ke Keranjang** → tampilkan pesan error stok (respons `422` dari `POST /api/cart`).
2. `2:15` — Mobile: ulangi dengan qty wajar → sukses → buka `/keranjang` → ubah qty / centang item → **Checkout**.
3. `2:35` — Mobile `/checkout`: isi data pengiriman, pilih kurir + layanan ongkir (RajaOngkir), lihat ringkasan total → **Buat Pesanan** (`POST /api/checkout` → `snap_token` + `snap_redirect_url`).
4. `2:55` — Mobile `/pembayaran`: tampilkan kode pesanan, total, hitung mundur batas bayar → tekan **Bayar** → `launchUrl` membuka Snap di browser eksternal → selesaikan pembayaran sandbox → kembali ke aplikasi.
5. `3:15` — Muat ulang status (tarik halaman / buka `/pesanan`) → status berubah dari **Menunggu Pembayaran**; bila webhook belum masuk, tekan sinkronisasi status (endpoint `GET /api/orders/{id}/payment` me-refresh dari Midtrans).
6. `3:25` — Shot penutup segmen: baris pesanan baru dengan status terbaru.

**Prasyarat/props:** ongkir RajaOngkir aktif (key di `.env`); alamat sudah diisi (atau isi manual saat rekaman); **varian**: bila `MIDTRANS_*` belum terisi, yang terbuka adalah simulasi lokal `MOCK_SNAP_*` → `/midtrans/finish?...&mock=1` (hanya non-production) — untuk jendela Snap sandbox sungguhan, isi key sandbox lebih dulu. Siapkan satu produk berstok kecil untuk memunculkan error 422.

## Segmen 5 — Pesanan & transisi status admin (3:30–4:30, 60 detik)

**Narasi (±140 kata):**

> Pesanan sudah terbayar. Pelanggan membukanya di /pesanan — status terbaru tampil di daftar, detailnya di /detail-pesanan, dan pelacakan resi di /lacak-pesanan. Sebaliknya, admin masuk ke panel lalu membuka /admin/orders. Di sini transisi status dijaga state machine: Menunggu Pembayaran menjadi Diproses, lalu Diproses menjadi Dikirim — dan langkah terakhir wajib mengisi nomor resi lewat /admin/pengiriman. Bila webhook telat, admin bisa menarik status terbaru lewat sinkronisasi pembayaran di /admin/pembayaran. Setelah barang sampai, pelanggan menekan Terima; status menjadi Selesai — dan hanya pesanan Selesai yang bisa diulas lewat /beri-ulasan. Pesanan yang belum bayar pun bisa dibatalkan sendiri oleh pelanggan.

**Aksi layar:**

1. `3:30` — Web pelanggan: `http://127.0.0.1:8000/pesanan` — tunjukkan daftar + status; klik detail (kode, item, status bayar).
2. `3:45` — Web admin: login admin → `http://127.0.0.1:8000/admin/orders` → buka pesanan → ubah status **Menunggu Pembayaran → Diproses** (hanya transisi yang diizinkan yang tampil).
3. `4:05` — Admin: `http://127.0.0.1:8000/admin/pengiriman` — isi ekspedisi + **nomor resi** + tanggal kirim → simpan; status pesanan menjadi **Dikirim**.
4. `4:20` — Web/mobile pelanggan: buka `/lacak-pesanan` (resi tampil) → tekan **Terima** (konfirmasi terima; di API: `POST /api/orders/{id}/confirm`, hanya dari `Dikirim`) → status **Selesai**; singgung `/beri-ulasan` (gerbang: hanya status Selesai).

**Prasyarat/props:** akun admin dari seeder; satu pesanan berstatus menunggu pembayaran hasil segmen 4; satu pesanan lama berstatus **Dikirim** sebagai cadangan bila proses bayar segmen 4 molor (agar transisi Dikirim → Selesai tetap bisa didemokan).

## Segmen 6 — Penutup (4:30–5:00, 30 detik)

**Narasi (±60 kata):**

> Penutup: pembayaran mock otomatis mati di production, webhook Midtrans diverifikasi dengan signature SHA-512, semua kunci API hanya ada di file .env, dan router mobile menjaga setiap rute yang butuh login. Pengujian menutup kontrak ini: 308 test Laravel dan 42 test Flutter, semuanya hijau. Kode dan dokumentasi lengkapnya ada di repository — terima kasih.

**Aksi layar:**

1. `4:30` — Cepat: terminal menampilkan hasil `php artisan test --compact` (308 hijau) bergantian dengan hasil `flutter test` (42 hijau).
2. `4:45` — Cepat: buka `docs/api.md` atau struktur repo di layar (jangan tunjukkan isi `.env`).
3. `4:55` — Frame penutup: logo/nama Hamzah Style + URL repository.

**Prasyarat/props:** jalankan kedua suite **sebelum** rekaman agar output hijau bisa direkam sekali; jangan tampilkan isi `.env`, token, atau key apa pun di layar.

## Tips editing

- **Pangkas dead air** dan perpindahan tab yang lambat; kecepatan bicara acuan ±2,5 kata/detik (total narasi kedua segmen harus tetap ±300 kata).
- **Zoom / lingkari kursor** saat menunjukkan route penting (`/checkout`, `/admin/orders`, tombol Bayar); overlay waktu kecil per segmen agar durasi terkontrol.
- **Sembunyikan data sensitif**: jangan rekam isi `.env`, token API, key Midtrans/RajaOngkir, atau data pribadi nyata (alamat & telepon asli) — pakai data dummy.
- **Akun demo saja** — jangan pakai akun pribadi; password tidak diketik terbuka di layar.
- Konsisten 16:9, subtitle bahasa Indonesia opsional, transisi cepat antar segmen (10–15 frame).
- Hasil akhir: unggah ke YouTube **unlisted**, tempel link di laporan akhir (H13).
