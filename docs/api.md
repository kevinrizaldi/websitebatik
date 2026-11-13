# API Docs — Hamzah Style (Laravel REST API)

Dokumen ini pengganti koleksi Postman. Semua endpoint di bawah **diverifikasi dari kode** (`routes/api.php` + `app/Http/Controllers/Api/*`) — bukan dari rencana/ideal desain.

- **Base URL**: `http://127.0.0.1:8000/api` (lokal) / `https://<domain>/api` (produksi).
- **Auth**: Bearer token **Laravel Sanctum** — login/register mengembalikan `token`, kirim di header `Authorization: Bearer <token>`.
- **Format**: JSON (kecuali endpoint media/gambar yang mengembalikan file).
- **Wrapper `data`**: respons kanonik memakai `data`. Beberapa endpoint **juga** menyediakan key legacy top-level (`token`, `user`) untuk kompatibilitas app Flutter yang sudah berjalan — keduanya berisi nilai sama. **Mobile baru sebaiknya membaca `data`.**

## Ringkasan endpoint

| Method + Path | Auth | Tujuan |
|---|---|---|
| `GET /api/health` | publik | Cek kesehatan API |
| `POST /api/register` | publik | Registrasi pelanggan + token |
| `POST /api/login` | publik | Login + token |
| `GET /api/me` | Bearer | Profil user aktif |
| `GET /api/user` | Bearer | Alias `/me` (Flutter) |
| `PATCH /api/user` | Bearer | Update profil |
| `POST /api/logout` | Bearer | Cabut token aktif |
| `GET /api/categories` | publik | Daftar kategori |
| `GET /api/products` | publik | Katalog (cari/filter/sort/paginasi) |
| `GET /api/products/{product}` | publik | Detail produk + ulasan |
| `GET /api/products/{product}/reviews` | publik | Ulasan Disetujui sebuah produk |
| `GET /api/media/{path}` | publik | Serve gambar (CORS-safe) |
| `GET /api/addresses` | Bearer | Daftar alamat |
| `POST /api/addresses` | Bearer | Tambah alamat |
| `PUT/PATCH /api/addresses/{address}` | Bearer | Ubah alamat |
| `DELETE /api/addresses/{address}` | Bearer | Hapus alamat |
| `GET /api/cart` | Bearer | Isi keranjang + subtotal |
| `POST /api/cart` | Bearer | Tambah item (cek stok) |
| `PATCH /api/cart/{cartItem}` | Bearer | Ubah qty / seleksi item |
| `DELETE /api/cart/{cartItem}` | Bearer | Hapus item |
| `GET /api/orders` | Bearer | Daftar pesanan (filter status) |
| `POST /api/checkout` | Bearer | Buat pesanan + Snap token |
| `GET /api/orders/{order}` | Bearer | Detail pesanan |
| `GET /api/orders/{order}/payment` | Bearer | Status bayar + Snap token |
| `POST /api/orders/{order}/confirm` | Bearer | Konfirmasi terima (`Dikirim`→`Selesai`) |
| `POST /api/orders/{order}/cancel` | Bearer | Batalkan pesanan belum bayar |
| `POST /api/orders/{order}/reviews` | Bearer | Beri ulasan (gerbang `Selesai`) |

> 27 registrasi route = **28 kombinasi method+path** (`PUT` dan `PATCH` alamat berbagi satu route).

## Bentuk error

| Kode | Bentuk | Kapan |
|---|---|---|
| `401` | `{"message":"Unauthenticated."}` | Token tidak ada / kadaluarsa / salah |
| `403` | `{"message":"Tidak diizinkan."}` | Resource bukan milik user (atau webhook signature salah) |
| `404` | `{"message":"No query results for model ..."}` / `Not Found` | ID tidak ada (route model binding) |
| `422` | `{"message":"...","errors":{"field":["..."]}}` | Validasi gagal / aturan bisnis (stok, gerbang review, dst.) |
| `429` | `{"message":"Too Many Attempts."}` | Rate limit (throttle route) |

---

## Health

### `GET /api/health` — publik

Respons `200`:

```json
{ "ok": true }
```

---

## Auth

### `POST /api/register` — publik

Body:

| Field | Wajib | Tipe | Aturan |
|---|---|---|---|
| `name` | ya | string | maks 255, hanya huruf/angka/spasi |
| `email` | ya | string | email, lowercase, unik |
| `phone` | tidak | string | maks 20 |
| `password` | ya | string | wajib `password_confirmation`, default rule Laravel (min 8) |
| `password_confirmation` | ya | string | sama dengan `password` |

Respons `201`:

```json
{
  "message": "Registrasi berhasil.",
  "data": {
    "user": { "id": 1, "name": "Budi", "email": "budi@example.com", "phone": "08123456789", "avatar": null, "role": "customer" },
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
  },
  "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
  "user": { "id": 1, "name": "Budi", "email": "budi@example.com", "phone": "08123456789", "avatar": null, "role": "customer" }
}
```

> `token`/`user` top-level = legacy duplikat dari `data` (Flutter).

### `POST /api/login` — publik

Body: `email` (ya), `password` (ya).

Respons `200`: sama seperti register (tanpa `message` beda — `"message":"Login berhasil."`).

Error `422` (kredensial salah):

```json
{
  "message": "Email atau kata sandi salah.",
  "errors": { "email": ["Email atau kata sandi salah."] }
}
```

### `GET /api/me` — Bearer (alias: `GET /api/user`)

Respons `200`:

```json
{
  "data": { "user": { "id": 1, "name": "Budi", "email": "budi@example.com", "phone": "08123456789", "avatar": null, "role": "customer" } },
  "user": { "id": 1, "name": "Budi", "email": "budi@example.com", "phone": "08123456789", "avatar": null, "role": "customer" }
}
```

### `PATCH /api/user` — Bearer

Body: `name` (ya, regex huruf/angka/spasi), `email` (ya, unik kecuali milik sendiri), `phone` (opsional).

Respons `200`: `{"message":"Profil berhasil diperbarui.","data":{"user":{...}},"user":{...}}`

### `POST /api/logout` — Bearer

Menghapus token aktif. Respons `204` (body kosong).

---

## Catalog

### `GET /api/categories` — publik

Respons `200`:

```json
{
  "data": [
    { "id": 1, "name": "Kemeja", "slug": "kemeja", "description": null, "image_url": "http://127.0.0.1:8000/api/media/images/beranda/folded-shirts.jpg", "image": "http://127.0.0.1:8000/api/media/images/beranda/folded-shirts.jpg" }
  ]
}
```

### `GET /api/products` — publik

Query params:

| Param | Default | Catatan |
|---|---|---|
| `search` | `""` | cocokkan nama/kategori/deskripsi (EN & ID) |
| `category` | `""` | nama kategori atau ID numerik; `semua` = tanpa filter |
| `sort` | `latest` | `price_asc`/`price_low`, `price_desc`/`price_high`, `oldest`, `latest` |
| `per_page` | `20` | dibatasi 1–50 |

Respons `200`:

```json
{
  "data": [
    {
      "id": 12,
      "sku": "BT-0012",
      "name": "Kemeja Batik Pekalongan",
      "category": { "id": 1, "name": "Kemeja" },
      "category_name": "Kemeja",
      "category_object": { "id": 1, "name": "Kemeja" },
      "product_type": "Kemeja",
      "price": 285000.0,
      "stock": 10,
      "status": "Tersedia",
      "description": "Batik tulis pekalongan",
      "material": "Katun",
      "size": "S, M, L",
      "size_stock": { "S": 3, "M": 4, "L": 3 },
      "sizes": { "S": 3, "M": 4, "L": 3 },
      "image_url": "http://127.0.0.1:8000/api/media/storage/products/x.jpg",
      "image": "http://127.0.0.1:8000/api/media/storage/products/x.jpg",
      "other_images": [],
      "rating": 4.5,
      "reviews_count": 2,
      "review_count": 2
    }
  ],
  "meta": { "current_page": 1, "last_page": 3, "per_page": 20, "total": 45 },
  "links": { "first": "...", "last": "...", "prev": null, "next": "http://127.0.0.1:8000/api/products?page=2" }
}
```

> `category` berbentuk **objek** `{id, name}` (bukan string) — kontrak yang sudah dipakai Flutter. Key ganda (`category_name`, `category_object`, `sizes`, `review_count`) ada untuk kompatibilitas.

### `GET /api/products/{product}` — publik

Respons `200`: `{"data": {...field produk seperti di atas... , "reviews": [ {"id":1,"user_name":"Budi","rating":5,"comment":"Mantap","created_at":"2026-11-10T10:00:00+07:00"} ]}}`

### `GET /api/products/{product}/reviews` — publik

Hanya ulasan `status = Disetujui`.

```json
{
  "data": [ { "id": 1, "user_name": "Budi", "rating": 5, "comment": "Mantap", "status": "Disetujui", "created_at": "2026-11-10T10:00:00+07:00" } ]
}
```

### `GET /api/media/{path}` — publik

Serve file gambar dari `public/` atau `storage/app/public/` lewat Laravel (header CORS untuk Flutter Web). Path absolut `storage/...` → disk storage. Balasan: file biner (`Cache-Control: public, max-age=86400`); path tidak valid/`..` → `404`.

---

## Cart (Bearer)

### `GET /api/cart`

```json
{
  "data": [
    {
      "id": 5,
      "product_id": 12,
      "name": "Kemeja Batik Pekalongan",
      "size": "M",
      "variant": null,
      "quantity": 2,
      "selected": true,
      "unit_price": 285000.0,
      "image_url": "http://127.0.0.1:8000/api/media/storage/products/x.jpg",
      "product": { "id": 12, "name": "Kemeja Batik Pekalongan", "price": 285000.0, "image": "http://127.0.0.1:8000/api/media/storage/products/x.jpg" },
      "ukuran": "M",
      "qty": 2,
      "subtotal": 570000.0
    }
  ],
  "subtotal": 570000.0
}
```

`subtotal` = jumlah `unit_price × quantity` hanya untuk item `selected=true`.

### `POST /api/cart`

Body (dua bentuk diterima — Flutter & kanonik):

| Field | Wajib | Catatan |
|---|---|---|
| `product_id` | ya | harus ada di `products` |
| `size` / `ukuran` | tidak | maks 50 |
| `variant` | tidak | maks 100 |
| `quantity` / `qty` | tidak | 1–99, default 1 |

Respons `201`:

```json
{ "message": "Produk ditambahkan ke keranjang.", "data": { "id": 5, "...": "field seperti GET /cart" } }
```

Item dengan produk + ukuran sama **ditambahkan qty-nya** (bukan baris baru). Cek stok memakai `getStokForUkuran(size)` — sama dengan web.

Error `422` (stok):

```json
{ "message": "Stok Kemeja Batik Pekalongan (Ukuran M) tidak mencukupi (tersedia: 3)." }
```

### `PATCH /api/cart/{cartItem}`

Body: `quantity`/`qty` (1–99) dan/atau `selected` (boolean). Item milik user lain → `403 {"message":"Tidak diizinkan."}`.

Respons `200`: `{"message":"Keranjang diperbarui.","data":{...}}`

### `DELETE /api/cart/{cartItem}`

Respons `204` (body kosong). Bukan milik user → `403`.

---

## Orders & Checkout (Bearer)

### `GET /api/orders`

Query: `status` (opsional, persis sama dengan `orders.status`, mis. `Diproses`).

Respons `200`: `{"data": [ <order>, ... ]}` — urutan terbaru, relasi `items.product`, `shipment`, `reviews` sudah dimuat.

Bentuk `order` (dipakai semua endpoint orders):

```json
{
  "id": 7,
  "code": "ORD-20261110-0007",
  "customer_name": "Budi",
  "phone": "08123456789",
  "address": "Jl. Merdeka 1, Solo",
  "status": "Menunggu Pembayaran",
  "status_index": 1,
  "payment_status": "pending",
  "payment_method": "Transfer Bank",
  "shipping_cost": 15000.0,
  "total_price": 585000.0,
  "courier": "JNE Reguler",
  "tracking_number": null,
  "has_review": false,
  "created_at": "2026-11-10T10:00:00+07:00",
  "payment_deadline": "2026-11-11T10:00:00+07:00",
  "items": [
    { "id": 1, "product_id": 12, "name": "Kemeja Batik Pekalongan (M)", "price": 285000.0, "quantity": 2, "subtotal": 570000.0, "image_url": "..." }
  ],
  "payment": { "status": "pending" }
}
```

`status_index`: `1` belum bayar/proses awal, `2` diproses/konfirmasi, `3` dikirim, `4` selesai.

### `POST /api/checkout`

Body (dua bentuk diterima):

| Field | Wajib | Catatan |
|---|---|---|
| `customer_name` **atau** `recipient_name` | ya* | *salah satu |
| `phone` | ya | maks 20 |
| `address` | ya | maks 1000 |
| `city`, `province`, `postal_code` | tidak | dipakai sinkron alamat default |
| `shipping_option` **atau** `courier` | tidak | mis. `jne` |
| `shipping_cost` | tidak | angka (ongkir dari RajaOngkir di sisi web) |
| `payment_method` | tidak | default `Transfer Bank` |
| `voucher` | tidak | kode voucher |
| `notes` **atau** `note` | tidak | maks 500 |
| `cart_item_ids[]` | tidak | checkout item terpilih tertentu; kosong = semua `selected=true` |
| `items[]` | tidak | beli langsung (Buy Now): `{product_id, size?, quantity}` per entri, `min:1` |

Sumber item: `items` (langsung) → jika kosong, `cart_item_ids` → jika kosong, semua item terpilih.

Respons `201`:

```json
{
  "message": "Pesanan berhasil dibuat.",
  "data": {
    "id": 7,
    "code": "ORD-20261110-0007",
    "...": "field order seperti di atas",
    "order": { "...": "duplikat order di dalam data" },
    "snap_token": "MOCK_SNAP_abc123",
    "snap_redirect_url": "http://127.0.0.1:8000/midtrans/finish?order_id=ORD-20261110-0007&mock=1"
  },
  "totals": { "subtotal": 570000.0, "discount": 0, "shipping": 15000.0, "grand_total": 585000.0, "voucher": null },
  "snap_token": "MOCK_SNAP_abc123"
}
```

> `data` berisi **field order flat + `order` + `snap_token` + `snap_redirect_url`** (dual-shape: `checkout_screen.dart` membaca `res['data']` sebagai order). `totals` mengikuti `CheckoutService::calculateOrderTotals` — `voucher` berupa kode voucher (`null` bila tidak ada).

Error penting:
- `422` keranjang kosong: `{"message":"Keranjang kosong atau belum ada item yang dipilih."}`
- `422` stok/argumen CheckoutService: `{"message":"<pesan>"}`

### `GET /api/orders/{order}`

Respons `200`: `{"data": <order>}`. Bukan milik user → `403`.

### `GET /api/orders/{order}/payment`

Refresh status dari Midtrans dulu (best effort), lalu pastikan Snap token ada untuk status yang masih bisa dibayar (`Menunggu Pembayaran`, `Belum Dibayar`, `Menunggu Konfirmasi`).

```json
{
  "data": {
    "payment_status": "pending",
    "snap_token": "MOCK_SNAP_abc123",
    "snap_redirect_url": "https://app.sandbox.midtrans.com/snap/v1/transactions/MOCK_SNAP_abc123/redirect"
  }
}
```

Mock token (`MOCK_SNAP_*`) → redirect ke route finish lokal; token asli → endpoint Snap Midtrans. Bukan milik user → `403`.

### `POST /api/orders/{order}/confirm`

Gerbang status: hanya dari `Dikirim`.

- `200`: `{"message":"Pesanan telah dikonfirmasi diterima.","data":<order>}` (status → `Selesai`, pengiriman → `Diterima`)
- `422`: `{"message":"Pesanan tidak dapat dikonfirmasi pada status saat ini."}`
- `403`: bukan milik user.

### `POST /api/orders/{order}/cancel`

Gerbang status: hanya `Menunggu Pembayaran` / `Belum Dibayar`.

- `200`: `{"message":"Pesanan berhasil dibatalkan.","data":<order>}` — stok dikembalikan, `payment_status` → `cancelled`, attempt pending dibatalkan (lokal + Midtrans).
- `422`: `{"message":"Pesanan tidak dapat dibatalkan pada status ini."}`

---

## Review (Bearer)

### `POST /api/orders/{order}/reviews`

Body:

| Field | Wajib | Aturan |
|---|---|---|
| `product_id` | tidak | default produk pertama pesanan; harus ada di pesanan |
| `rating` | ya | integer 1–5 |
| `comment` | ya | maks 1000 |

Aturan: pesanan milik user, **status `Selesai`**, produk termasuk pesanan, **belum pernah mengulas produk yang sama pada pesanan ini**.

Respons `201`:

```json
{ "message": "Ulasan berhasil disimpan.", "data": { "id": 3, "rating": 5, "comment": "Bahannya adem" } }
```

Error `422` (contoh):
- `{"message":"Ulasan hanya dapat diberikan setelah Anda menyelesaikan pembelian produk ini (pesanan berstatus Selesai)."}`
- `{"message":"Anda sudah memberi ulasan untuk produk ini pada pesanan #ORD-...."}`
- `{"message":"Produk pesanan tidak ditemukan."}` / `{"message":"Produk tersebut tidak ada dalam pesanan ini."}`

---

## Address (Bearer)

### `GET /api/addresses`

```json
{
  "data": [
    { "id": 2, "label": "Rumah", "recipient_name": "Budi", "phone": "08123456789", "full_address": "Jl. Merdeka 1", "city": "Solo", "province": "Jawa Tengah", "postal_code": "51111", "is_primary": true }
  ]
}
```

Urutan: `is_primary` dulu, lalu terbaru.

### `POST /api/addresses`

| Field | Wajib | Catatan |
|---|---|---|
| `recipient_name` | ya | |
| `phone` | ya | |
| `full_address` **atau** `street` | ya* | alias `street` dinormalisasi |
| `label` | tidak | default `Rumah` |
| `city`, `province`, `postal_code` | tidak | default `Indonesia` / `""` |
| `province_id`, `city_id` | tidak | ID RajaOngkir |
| `is_primary` | tidak | default: true bila ini alamat pertama |

Respons `201`: `{"message":"Alamat berhasil ditambahkan.","data":<address>}` — jika primary, primary lain direset.

### `PUT/PATCH /api/addresses/{address}`

Field sama, semuanya `sometimes`. `is_primary=true` → reset primary lain. Bukan milik user → `403`.

Respons `200`: `{"message":"Alamat berhasil diperbarui.","data":<address>}`

### `DELETE /api/addresses/{address}`

Respons `200`: `{"message":"Alamat berhasil dihapus."}` — bila yang dihapus primary, alamat terbaru sisanya jadi primary.

---

## Web-only (BUkan untuk mobile)

Endpoint di bawah **hanya untuk server web Laravel** — jangan dipanggil dari Flutter:

| Method + Path | Auth | Catatan |
|---|---|---|
| `POST /midtrans/notification` (alias `/midtrans/webhook`) | signature HMAC | Callback Midtrans; field wajib `order_id`, `status_code`, `gross_amount`, `signature_key`, `transaction_status`. 400 field invalid, 403 signature salah, 404 pembayaran tidak ditemukan, 200 `{"message":"OK"}`. Daftarkan URL ini di dashboard Midtrans. |
| `GET /rajaongkir/provinces`, `GET /rajaongkir/cities?province_id=`, `GET /rajaongkir/destinations?search=`, `POST /rajaongkir/cost` | session web | Proxy server → RajaOngkir API; dipakai form checkout web (server-side key `RAJAONGKIR_API_KEY`). |
| `POST /payment/token`, `GET /pembayaran/{id}/snap-token`, `POST /payment/sync`, dll. | session web | Alur pembayaran Snap halaman web. |

Mobile cukup memakai `POST /api/checkout` + `GET /api/orders/{id}/payment` untuk memperoleh `snap_token`/`snap_redirect_url`.
