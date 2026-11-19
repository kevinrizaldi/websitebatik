# Use Case — Hamzah Style (Website Batik)

Aktor: **Pelanggan** (pengguna web storefront & aplikasi mobile), **Admin** (pengelola toko), **Sistem Midtrans** (gateway pembayaran Snap), **RajaOngkir** (penyedia data provinsi/kota/ongkos kirim — bertindak sebagai sistem eksternal yang dipanggil server).

## Diagram use case (mermaid)

```mermaid
usecaseDiagram
    actor "Pelanggan" as C
    actor "Admin" as A
    actor "Sistem Midtrans" as M
    actor "RajaOngkir" as R

    package "Autentikasi" {
        usecase "UC-01 Registrasi" as UC01
        usecase "UC-02 Login" as UC02
        usecase "UC-03 Lupa/reset password" as UC03
        usecase "UC-20 Login Admin" as UC20
    }
    package "Katalog & Keranjang" {
        usecase "UC-04 Telusuri katalog\n(cari/filter/sortir)" as UC04
        usecase "UC-05 Detail produk + ulasan" as UC05
        usecase "UC-06 Kelola keranjang" as UC06
        usecase "UC-07 Kelola alamat" as UC07
        usecase "UC-08 Checkout" as UC08
        usecase "UC-09 Bayar (Snap)" as UC09
        usecase "UC-10 Lihat pesanan" as UC10
        usecase "UC-11 Konfirmasi barang diterima" as UC11
        usecase "UC-12 Batalkan pesanan belum bayar" as UC12
        usecase "UC-13 Beri ulasan" as UC13
        usecase "UC-14 Edit profil" as UC14
    }
    package "Admin" {
        usecase "UC-21 Kelola produk" as UC21
        usecase "UC-22 Kelola kategori" as UC22
        usecase "UC-23 Kelola stok" as UC23
        usecase "UC-24 Proses status pesanan" as UC24
        usecase "UC-25 Proses pengiriman + resi" as UC25
        usecase "UC-26 Rekonsiliasi pembayaran" as UC26
        usecase "UC-27 Kelola pelanggan" as UC27
        usecase "UC-28 Moderasi ulasan" as UC28
        usecase "UC-29 Laporan + ekspor" as UC29
        usecase "UC-30 Pengaturan toko" as UC30
    }
    package "Sistem Eksternal" {
        usecase "UC-40 Terima notifikasi\nwebhook pembayaran" as UC40
        usecase "UC-41 Sediakan Snap token\n/ redirect bayar" as UC41
        usecase "UC-42 Sediakan provinsi/\nkota/destinasi" as UC42
        usecase "UC-43 Hitung ongkos kirim" as UC43
    }

    C --> UC01
    C --> UC02
    C --> UC03
    C --> UC04
    C --> UC05
    C --> UC06
    C --> UC07
    C --> UC08
    C --> UC09
    C --> UC10
    C --> UC11
    C --> UC12
    C --> UC13
    C --> UC14

    A --> UC20
    A --> UC21
    A --> UC22
    A --> UC23
    A --> UC24
    A --> UC25
    A --> UC26
    A --> UC27
    A --> UC28
    A --> UC29
    A --> UC30

    M --> UC40
    M --> UC41
    UC09 ..> UC41 : <<include>>
    UC08 ..> UC43 : <<include>>
    R --> UC42
    R --> UC43
```

## Tabel use case

| ID | Aktor | Tujuan | Alur utama |
|---|---|---|---|
| UC-01 | Pelanggan | Registrasi akun baru | Buka `/register` → isi nama, email, no. HP, password + konfirmasi → submit → akun dibuat, login otomatis (session) |
| UC-02 | Pelanggan | Login | Buka `/login` → isi email + password → submit → session aktif → diarahkan ke beranda |
| UC-03 | Pelanggan | Lupa password | `/forgot-password` → isi email → link reset (mail log/SMTP) → `/reset-password` → password baru |
| UC-04 | Pelanggan | Telusuri katalog | Beranda `/` atau `/koleksi` → cari kata kunci / pilih kategori → urutkan (harga terbaru/murah/mahal) → buka halaman produk |
| UC-05 | Pelanggan | Lihat detail produk | Klik produk → `/produk-detail/{id}` → lihat gambar, harga, stok per ukuran, rating, ulasan Disetujui |
| UC-06 | Pelanggan | Kelola keranjang | Tambah dari halaman produk → `/keranjang` → ubah jumlah/centang item → hapus item |
| UC-07 | Pelanggan | Kelola alamat | `/alamat` → tambah alamat (label, penerima, HP, alamat, kota, provinsi, kode pos, RajaOngkir IDs) → ubah / jadikan default / hapus |
| UC-08 | Pelanggan | Checkout | Dari keranjang → `/checkout` → isi data pengiriman + pilih ongkir (RajaOngkir) + metode bayar → submit → pesanan dibuat, stok dipotong |
| UC-09 | Pelanggan | Bayar pesanan | Halaman `/pembayaran/{id}` → ambil Snap token → bayar via Midtrans Snap (VA/QRIS/dll.) → tunggu notifikasi webhook |
| UC-10 | Pelanggan | Lihat riwayat pesanan | `/pesanan` → daftar pesanan + status → buka detail |
| UC-11 | Pelanggan | Konfirmasi barang diterima | Pesanan `Dikirim` → tombol Terima → status → `Selesai` (membuka kelayakan ulasan) |
| UC-12 | Pelanggan | Batalkan pesanan belum bayar | Pesanan `Menunggu Pembayaran`/`Belum Dibayar` → batal → stok dikembalikan, pembayaran pending dibatalkan |
| UC-13 | Pelanggan | Beri ulasan | Pesanan `Selesai` → form ulasan (rating 1–5 + komentar) → ulasan tersimpan (satu per produk per pesanan) |
| UC-14 | Pelanggan | Edit profil | `/profile` → ubah nama/email/HP → simpan |
| UC-20 | Admin | Login panel admin | `/login` dengan akun `role=admin` → diarahkan ke `/dashboard` |
| UC-21 | Admin | Kelola produk | `/produk` → tambah/edit/hapus produk (SKU, harga, stok per ukuran, gambar, kategori) |
| UC-22 | Admin | Kelola kategori | `/admin/kategori` → tambah/ubah/hapus kategori (modal di satu halaman) |
| UC-23 | Admin | Kelola stok | `/admin/stok` → cari/filter (aman/menipis/habis) → lihat metrik inventaris |
| UC-24 | Admin | Proses status pesanan | `/admin/orders` → buka pesanan → ubah status sesuai transisi yang diizinkan |
| UC-25 | Admin | Proses pengiriman | `/admin/pengiriman` atau detail pesanan → isi ekspedisi + nomor resi + tanggal kirim → status `Dikirim` |
| UC-26 | Admin | Rekonsiliasi pembayaran | `/admin/pembayaran` → filter status → tombol sync status dari Midtrans |
| UC-27 | Admin | Kelola pelanggan | `/admin/pelanggan` → cari, lihat tier & total belanja |
| UC-28 | Admin | Moderasi ulasan | `/admin/ulasans` → setujui/tolak/hapus ulasan pelanggan |
| UC-29 | Admin | Laporan penjualan | `/admin/laporan` → pilih jenis laporan + rentang tanggal → tampil/ cetak/ ekspor PDF, Excel, CSV |
| UC-30 | Admin | Pengaturan toko | `/admin/pengaturan` → ubah nama, kota asal, kontak toko (dipakai perhitungan ongkir) |
| UC-40 | Sistem Midtrans | Kirim notifikasi status bayar | Midtrans POST `/midtrans/notification` → server verifikasi signature → update `payments` + `orders` |
| UC-41 | Sistem Midtrans | Sediakan pembayaran Snap | Server minta Snap token → pelanggan bayar di halaman Midtrans → redirect kembali ke toko |
| UC-42 | RajaOngkir | Sediakan provinsi/kota/destinasi | Server proxy `/rajaongkir/*` → RajaOngkir API → daftar wilayah untuk form alamat/checkout |
| UC-43 | RajaOngkir | Hitung ongkos kirim | Checkout → POST `/rajaongkir/cost` (origin toko + destinasi + berat + kurir) → daftar ongkir |

## Alur alternatif (use case kritis)

### UC-01 Registrasi
1. **Alur utama**: isi formulir valid → user dibuat (`role=customer`) → session aktif → beranda.
2. **Alternatif A**: email sudah terdaftar → 422, error pada field `email`.
3. **Alternatif B**: nama mengandung simbol / password < 8 karakter atau tidak sama dengan konfirmasi → validasi gagal, form ditampilkan lagi dengan error.

### UC-02 Login
1. **Alur utama**: email + password cocok → session aktif → beranda (admin: `/dashboard`).
2. **Alternatif A**: email atau password salah → pesan "Email atau kata sandi salah." (422 di API).
3. **Alternatif B**: login Google (jika `GOOGLE_CLIENT_ID` dikonfigurasi) → OAuth callback → akun dengan `google_id` dibuat/terhubung → session aktif.

### UC-04 Telusuri katalog
1. **Alur utama**: beranda/`/koleksi` menampilkan produk terbaru → pencarian atau filter kategori → sortir → buka detail.
2. **Alternatif A**: tidak ada hasil → daftar kosong + pesan "tidak ditemukan".
3. **Alternatif B (API)**: param `search`, `category`, `sort` (`price_asc`/`price_desc`/`latest`/`oldest`), `per_page` (maks 50) → respons terpaginasi `data` + `meta` + `links`.

### UC-06 Kelola keranjang
1. **Alur utama**: tambah produk (+ukuran, qty) → cek keranjang → ubah jumlah/centang → checkout.
2. **Alternatif A**: stok tidak mencukupi → 422 "Stok … tidak mencukupi (tersedia: N)" — aturan sama dengan web `CartService`.
3. **Alternatif B**: item sudah ada di keranjang (produk + ukuran sama) → qty ditambahkan, bukan baris baru.

### UC-08 + UC-09 Checkout & Bayar
1. **Alur utama**: checkout → pesanan `Menunggu Pembayaran` + Snap token dibuat → pelanggan bayar di Snap → Midtrans kirim webhook → `payment_status=paid`, status → `Sudah Dibayar`/`Diproses` → admin memproses.
2. **Alternatif A — belum bayar lewat deadline (24 jam)**: scheduler `orders:expire-overdue` (tiap 15 menit) → pesanan `Dibatalkan`, `payment_status=expired`, stok dikembalikan, attempt pending dibatalkan juga di Midtrans.
3. **Alternatif B — pelanggan membatalkan sendiri** (UC-12): status `Menunggu Pembayaran`/`Belum Dibayar` → `Dibatalkan`, stok kembali, attempt pending dibatalkan.
4. **Alternatif C — pembayaran gagal/ditolak Midtrans**: `payments.status=failed`; pesanan masih bisa dibayar ulang (Snap token baru via halaman bayar) selama belum expired.
5. **Alternatif D — keranjang kosong / tidak ada item tercentang** → 422 "Keranjang kosong atau belum ada item yang dipilih."
6. **Alternatif E — webhook tidak masuk**: admin bisa manual sync dari `/admin/pembayaran` (UC-26) atau pelanggan buka ulang halaman bayar (GET `/api/orders/{id}/payment` me-refresh status dari Midtrans).

### UC-11 Konfirmasi terima
1. **Alur utama**: pesanan `Dikirim` → konfirmasi → `Selesai`, `shipments.status_pengiriman=Diterima`.
2. **Alternatif A**: status bukan `Dikirim` → 422 "Pesanan tidak dapat dikonfirmasi pada status saat ini."

### UC-13 Beri ulasan
1. **Alur utama**: pesanan `Selesai` → isi rating 1–5 + komentar → ulasan tersimpan `status=Disetujui` → tampil publik di produk.
2. **Alternatif A**: pesanan belum `Selesai` → 422 (gerbang "Selesai" — aturan sama dengan web `UlasanController`).
3. **Alternatif B**: sudah pernah mengulas produk yang sama pada pesanan yang sama → 422 (unique `(order_id, product_id)`).
4. **Alternatif C (moderasi, UC-28)**: admin menolak ulasan → `status` bukan `Disetujui` → tidak tampil publik.

### UC-21/UC-23 Kelola produk & stok (admin)
1. **Alur utama**: `/produk` → Tambah → isi data (SKU unik, harga, stok per ukuran, gambar, kategori) → simpan; stok total dihitung dari `stok_ukuran`, `status` otomatis dari stok (`Tersedia`/habis).
2. **Alternatif A**: edit stok via `/admin/stok` (filter aman >5 / menipis 1–5 / habis ≤0).
3. **Alternatif B**: SKU duplikat / field wajib kosong → validasi gagal, form error.

### UC-24/UC-25 Proses pesanan & pengiriman (admin)
1. **Alur utama**: `/admin/orders` → buka pesanan → ubah status mengikuti transisi yang diizinkan: `Menunggu Pembayaran → Diproses|Sudah Dibayar|Dibatalkan`, `Diproses → Dikirim` (wajib isi nomor resi), `Dikirim → Selesai`.
2. **Alternatif A — lewat halaman pengiriman**: isi ekspedisi + resi unik + tanggal kirim + status pengiriman → baris `shipments` di-sync dan status pesanan ikut menyesuaikan (`Dikirim`/`Selesai`).
3. **Alternatif B — status final** (`Selesai`, `Dibatalkan`) → perubahan ditolak.
4. **Alternatif C — batal (hanya sebelum dikirim/selesai)**: stok dikembalikan, `payment_status=cancelled`, attempt pending dibatalkan (termasuk di Midtrans).

### UC-29 Laporan (admin)
1. **Alur utama**: `/admin/laporan` → pilih laporan (`sales_daily|weekly|monthly|yearly`) + rentang tanggal (`start_date`, `end_date`, format `Y-m-d`) → tabel ringkasan periode → tampil layar / cetak (`/admin/laporan/print`).
2. **Alternatif A — ekspor**: `pdf` (print view), `excel` (`.xls`), `csv` (download, formula-injection di-guard dengan prefix `'`).
3. **Alternatif B — filter status**: parameter `status` menyaring pesanan per status sebelum dihitung.

### UC-40 Webhook Midtrans (sistem)
1. **Alur utama**: Midtrans POST `/midtrans/notification` → wajib field (`order_id`, `status_code`, `gross_amount`, `signature_key`, `transaction_status`) → verifikasi signature (HMAC) → cocokkan `gross_amount` → update `payments` + `orders` → 200 `{"message":"OK"}`.
2. **Alternatif A**: signature tidak cocok → 403 `{"message":"Invalid signature."}`.
3. **Alternatif B**: field hilang/tipe salah → 400; pembayaran tidak ditemukan → 404.
4. **Alternatif C — pembayaran untuk pesanan yang sudah dibatalkan**: dicatat `critical` + penanda `attention_required` pada `payment_details` (refund manual).
