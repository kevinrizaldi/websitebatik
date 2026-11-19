# Manual Book — Panduan Penggunaan Hamzah Style

Panduan langkah-demi-langkah untuk **pelanggan** (web storefront) dan **admin** (panel pengelolaan). Ditulis bahasa sederhana untuk pembaca laporan; menyertakan deskripsi layar/alur tiap langkah.

---

## Bagian A — Pelanggan (Web)

### A1. Registrasi akun baru

1. Buka halaman utama situs, klik **Masuk/Daftar** (menu navigasi atas).
2. Layar **Daftar** menampilkan formulir: Nama, Email, No. Telepon, Password, Konfirmasi Password.
3. Isi semua kolom. Nama hanya boleh huruf/angka/spasi; password minimal 8 karakter dan konfirmasi harus sama.
4. Klik **Daftar**. Jika berhasil, Anda otomatis masuk dan diarahkan ke halaman beranda dengan menu akun aktif.
5. *Jika email sudah terdaftar atau data tidak valid, muncul pesan merah di kolom yang salah.*

### A2. Login

1. Klik **Masuk**. Layar **Login** meminta Email dan Password.
2. Isi email terdaftar dan password, klik **Masuk**.
3. Berhasil → beranda tampil dengan nama Anda di menu. Akun admin otomatis diarahkan ke halaman **Dashboard**.
4. *Lupa password*: klik **Lupa password?** → masukkan email → buka link reset dari email (pada environment lokal link tercatat di log mail) → buat password baru.

### A3. Telusuri katalog (beranda & koleksi)

1. **Beranda** menampilkan produk terbaru (maks 12) dan daftar kategori.
2. Buka **Koleksi** untuk melihat semua produk.
3. Gunakan **kolom pencarian** untuk mencari nama/deskripsi produk, atau klik salah satu **kategori** untuk menyaring.
4. Pilih pengurutan (mis. termurah/terbaru) bila tersedia pada halaman.
5. Klik salah satu kartu produk untuk membuka halaman detail.

### A4. Detail produk

1. Layar **Detail Produk** menampilkan gambar utama + galeri, nama, harga, stok, pilihan ukuran (dengan stok per ukuran), bahan/ukuran, deskripsi, serta **rating dan ulasan** (hanya ulasan yang disetujui).
2. Pilih **ukuran** (wajib bila produk punya stok per ukuran).
3. Klik **Tambah ke Keranjang**. Pesan sukses muncul; ikon keranjang bertambah.
4. *Jika stok ukuran tidak cukup, muncul pesan "Stok … tidak mencukupi (tersedia: N)".*

### A5. Keranjang

1. Buka **Keranjang**. Daftar item tampil beserta harga satuan, jumlah, dan subtotal tiap baris.
2. Ubah **jumlah** item lewat tombol +/- atau kolom qty, lalu simpan perubahan.
3. **Centang/batal centang** item yang ingin ikut checkout (subtotal dihitung dari item tercentang).
4. Klik ikon hapus untuk membuang item.
5. Klik **Checkout** untuk lanjut.

### A6. Checkout

1. Layar **Checkout** menampilkan: data pengiriman (nama penerima, telepon, alamat, kota/provinsi, kode pos) dan pilihan **ongkos kirim** dari RajaOngkir (courier + layanan + harga), ringkasan belanja, serta **metode pembayaran**.
2. Pilih/isi alamat pengiriman (bisa memilih alamat tersimpan di menu Alamat).
3. Pilih kurir dan layanan ongkir — harga ongkir dihitung dari **kota asal toko** (Pengaturan Toko) ke kota tujuan.
4. Pastikan ringkasan total (produk + ongkir) benar, pilih metode pembayaran, klik **Buat Pesanan / Bayar Sekarang**.
5. Pesanan dibuat dengan status **Menunggu Pembayaran**, stok produk otomatis dipotong, dan Anda diarahkan ke halaman pembayaran.

### A7. Pembayaran (Midtrans Snap)

1. Layar **Pembayaran** menampilkan kode pesanan, total tagihan, batas waktu bayar (24 jam), dan tombol **Bayar**.
2. Klik **Bayar** → jendela Snap Midtrans terbuka: pilih kanal (Virtual Account, QRIS, e-wallet, dst.) dan ikuti instruksi pembayaran.
3. Setelah bayar, Anda dikembalikan ke situs. Status pesanan berubah menjadi **Sudah Dibayar / Diproses** begitu notifikasi Midtrans diterima server (bisa beberapa detik; bila belum, muat ulang halaman pembayaran atau tunggu admin sinkronkan).
4. *Jika pembayaran gagal*: ulangi langkah 2, Snap token baru disiapkan selama batas waktu masih berlaku.
5. *Jika lewat 24 jam tanpa bayar*: pesanan otomatis dibatalkan sistem dan stok kembali.

### A8. Melihat & mengelola pesanan

1. Buka menu **Pesanan**. Daftar pesanan tampil dengan kode, tanggal, total, dan status:
   - **Menunggu Pembayaran** → belum/lunas menunggu proses
   - **Diproses** → admin sedang menyiapkan
   - **Dikirim** → dalam perjalanan (nomor resi tampil)
   - **Selesai** → sudah diterima
   - **Dibatalkan** → dibatalkan kedaluwarsa/dibatalkan
2. Klik pesanan untuk **detail**: daftar item, alamat, kurir, resi, riwayat pembayaran.
3. **Batalkan pesanan** (hanya status Menunggu Pembayaran/Belum Dibayar): klik **Batalkan** → stok kembali, pembayaran pending dibatalkan.
4. **Konfirmasi penerimaan** (hanya status Dikirim): klik **Terima Barang** → status menjadi **Selesai** dan tombol ulasan terbuka.
5. Lacak pengiriman bila tersedia (nomor resi ekspedisi).

### A9. Beri ulasan

1. Setelah pesanan **Selesai**, buka pesanan → klik **Beri Ulasan**.
2. Pilih **rating** 1–5 dan tulis komentar, klik **Kirim**.
3. Ulasan tersimpan (satu ulasan per produk per pesanan) dan tampil publik di halaman produk setelah disetujui.
4. *Jika status belum Selesai*: tombol ulasan tidak tersedia / server menolak dengan pesan "Ulasan hanya dapat diberikan setelah …".

### A10. Profil & alamat

1. **Profil** (menu akun): ubah Nama, Email, No. Telepon → **Simpan**. Nama hanya huruf/angka/spasi; email tidak boleh dipakai akun lain.
2. **Alamat**: daftar alamat tersimpan dengan tanda **Utama**.
   - Tambah: **+ Tambah Alamat** → isi Label (Rumah/Kantor), Nama Penerima, Telepon, Alamat Lengkap, Kota, Provinsi, Kode Pos → simpan. Alamat pertama otomatis utama.
   - Ubah: klik ubah pada alamat → ubah isian → simpan.
   - Jadikan utama: centang **Utama** (alamat utama lama otomatis berubah).
   - Hapus: klik hapus — bila yang dihapus utama, alamat lain otomatis jadi utama.
3. Alamat utama ikut terisi otomatis saat checkout.

---

## Bagian B — Admin (Panel Web)

### B1. Login admin

1. Buka halaman login. Masuk dengan akun yang memiliki peran **admin**.
2. Berhasil → langsung diarahkan ke **Dashboard** (ringkasan pesanan, penjualan, produk).
3. Sidebar admin memuat menu: Dashboard, Produk, Kategori, Stok, Pesanan, Ulasan, Pembayaran, Pengiriman, Pelanggan, Laporan, Pengaturan, Profil.

### B2. Kelola produk

1. Buka **Produk** → daftar produk dengan pencarian, filter kategori, dan urutan.
2. **Tambah produk**: klik **Tambah** → isi Nama, SKU (unik), Kategori, Harga, Deskripsi, Bahan, Ukuran + **stok per ukuran** (S/M/L/…), gambar utama & gambar tambahan → **Simpan**.
   - Stok total otomatis dihitung dari jumlah stok per ukuran; status (`Tersedia`/habis) otomatis mengikuti stok.
3. **Edit**: klik produk → ubah data → simpan (validasi SKU unik & field wajib berlaku).
4. **Hapus**: dari daftar/detail → konfirmasi hapus (produk ikut menghapus ulasan & baris keranjang terkait).
5. **Detail**: lihat data + gambar tersimpan.

### B3. Kelola kategori

1. Buka **Kategori** (satu halaman + modal).
2. Tambah: **Tambah Kategori** → isi nama (dan opsional deskripsi/gambar) → simpan.
3. Ubah: klik ikon edit pada baris → ubah → simpan.
4. Hapus: klik ikon hapus → konfirmasi (produk terkait tidak ikut terhapus — kategori jadi kosong).

### B4. Kelola stok & inventaris

1. Buka **Stok**. Kartu ringkasan menampilkan: total item fisik, jumlah SKU, stok **aman** (>5), **menipis** (1–5), **habis** (≤0).
2. Gunakan pencarian (nama/SKU/bahan/kategori) dan filter status untuk mempersempit daftar.
3. Perubahan stok dilakukan lewat **Produk** (edit stok per ukuran); halaman Stok adalah pusat pemantauan.

### B5. Proses pesanan

1. Buka **Pesanan**. Tab menghitung: Menunggu Pembayaran, Diproses, Dikirim, Selesai, Dibatalkan; pencarian berdasarkan kode/nama/telepon.
2. Klik pesanan untuk **detail**: item, pembayaran (termasuk status Midtrans), pengiriman, data pelanggan.
3. **Ubah status** pada detail — hanya transisi yang diizinkan:
   - `Menunggu Pembayaran` → `Diproses` / `Sudah Dibayar` / `Dibatalkan`
   - `Diproses` → `Dikirim` (**wajib mengisi Nomor Resi**; resi unik, huruf/angka/dash)
   - `Dikirim` → `Selesai` (atau pelanggan menekan Terima sendiri)
   - Status akhir (`Selesai`, `Dibatalkan`) tidak bisa diubah.
4. **Batalkan pesanan** (belum final): stok dikembalikan, pembayaran dicancel (lokal + Midtrans).
5. **Sinkron pembayaran**: tombol sync menarik status transaksi terbaru dari Midtrans bila webhook terlambat.

### B6. Proses pengiriman

1. Buka **Pengiriman**. Kartu/tabs: belum dikirim, siap serah, dalam perjalanan, diterima + filter ekspedisi & pencarian (kode, nama, alamat, resi).
2. Untuk memproses: buka pesanan (atau baris pengiriman) → isi **Ekspedisi** (JNE/dll.), **Nomor Resi** (unik), **Tanggal Kirim**, **Status Pengiriman** (`Menunggu Pengiriman`/`Diproses`/`Dikirim`/`Diterima`), catatan opsional → simpan.
3. Saat status pengiriman `Dikirim`, status pesanan ikut menjadi **Dikirim**; `Diterima` → pesanan **Selesai**. Nomor resi tampil di halaman pesanan pelanggan.

### B7. Rekonsiliasi pembayaran

1. Buka **Pembayaran**. Kartu KPI: menunggu (jumlah + total nominal), berhasil, gagal, serta total transaksi gateway.
2. Filter status (pending/success/failed) dan pencarian (kode, nama, metode, telepon).
3. Gunakan **Sync** untuk menarik status terbaru dari Midtrans pada pesanan yang perlu diverifikasi manual.

### B8. Kelola pelanggan

1. Buka **Pelanggan**. Daftar pelanggan non-admin dengan pencarian (nama/email/telepon), filter tier, dan urutan total belanja.
2. Informasi: jumlah pesanan, total belanja, alamat utama — untuk analisis pelanggan (tidak ada aksi hapus pelanggan di halaman ini).

### B9. Moderasi ulasan

1. Buka **Ulasan**. Daftar ulasan masuk dengan status.
2. **Setujui** → ulasan tampil publik di halaman produk; **Tolak/Hapus** → tidak tampil.

### B10. Laporan penjualan

1. Buka **Laporan**.
2. Pilih jenis laporan: **Harian / Mingguan / Bulanan / Tahunan**.
3. Atur **tanggal mulai** dan **tanggal selesai** (format YYYY-MM-DD) serta filter status pesanan (opsional) → klik tampilkan.
4. Tabel ringkasan per periode tampil: periode, jumlah pesanan, pendapatan + total pendapatan.
5. Ekspor sesuai kebutuhan laporan:
   - **Cetak** → tampilan siap cetak (print dialog),
   - **PDF** → halaman cetak disimpan sebagai PDF,
   - **Excel** → unduh berkas `.xls`,
   - **CSV** → unduh berkas `.csv` (dibuka Excel/Google Sheets).

### B11. Pengaturan toko

1. Buka **Pengaturan**. Isi Nama Toko, Kota Asal, Provinsi Asal, Kode Pos Asal, Alamat, Telepon, Email, Deskripsi.
2. **Kota asal** dipakai sebagai origin perhitungan ongkos kirim RajaOngkir → pastikan benar.
3. Simpan perubahan.

---

## Bagian C — Catatan teknis untuk pengujian laporan

- Alur pembayaran memakai **Midtrans sandbox** (`MIDTRANS_IS_PRODUCTION=false`); notifikasi masuk via `POST /midtrans/notification` (signature diverifikasi).
- Ongkir memakai **RajaOngkir (Komerce)** dengan key di `.env` — pastikan key aktif agar opsi ongkir muncul di checkout.
- Pesanan kedaluwarsa otomatis oleh scheduler `orders:expire-overdue` (tiap 15 menit; lihat DEPLOY.md bila hosting tanpa cron).
- Aplikasi mobile Flutter mengikuti API yang sama (lihat `docs/api.md`); alur pelanggan di aplikasi identik dengan Bagian A.
