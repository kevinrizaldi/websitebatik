<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Produk</title>

    <link rel="stylesheet" href="{{ asset('css/produk.css') }}">
</head>

<body>

<div class="form-container">

    <h1>Tambah Produk</h1>

    @if($errors->any())

        <div class="alert-error">

            <ul>

                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach

            </ul>

        </div>

    @endif


    <form
        action="{{ route('produk.update', $produk) }}"
        method="POST"
        enctype="multipart/form-data">

        @csrf
        @method('PUT')

        <div class="form-group">

            <label>Nama Produk</label>

            <input
                type="text"
                name="nama"
                value="{{ old('nama', $produk->nama) }}"
                placeholder="Contoh: Kemeja Batik Parang"
                required>

        </div>


        <div class="form-group">

            <label>SKU</label>

            <input
                type="text"
                name="sku"
                value="{{ old('sku', $produk->sku) }}"
                placeholder="HSO-BJK-001"
                required>

        </div>


        <div class="form-group">

            <label>Kategori</label>

            <select name="kategori" required>

                <option value="">-- Pilih Kategori --</option>

                <option value="Baju Batik">
                    Baju Batik
                </option>

                <option value="Olahan Kain">
                    Olahan Kain
                </option>

                <option value="Kain Batik">
                    Kain Batik
                </option>

            </select>

        </div>


        <div class="form-row">

            <div class="form-group">

                <label>Harga</label>

                <input
                    type="number"
                    name="harga"
                    value="{{ old('harga', $produk->harga) }}"
                    min="0"
                    required>

            </div>


            <div class="form-group">

                <label>Stok</label>

                <input
                    type="number"
                    name="stok"
                    value="{{ old('stok', $produk->stok) }}"
                    min="0"
                    required>

            </div>

        </div>


        <div class="form-group">

            <label>Status</label>

            <select name="status" required>

                <option value="Tersedia">
                    Tersedia
                </option>

                <option value="Stok Menipis">
                    Stok Menipis
                </option>

                <option value="Habis">
                    Habis
                </option>

            </select>

        </div>


        <div class="form-group">

            <label>Deskripsi Produk</label>

            <input
                type="text"
                name="deskripsi"
                value="{{ old('deskripsi', $produk->deskripsi) }}"
                placeholder="Contoh: Kualitas premium, cocok untuk acara formal">

        </div>


        <div class="form-group">

            <label>Material</label>

            <input
                type="text"
                name="material"
                value="{{ old('material', $produk->material) }}"
                placeholder="Contoh: Katun Prima">

        </div>


        <div class="form-group">

            <label>Gambar Produk</label>

            <input
                type="file"
                name="gambar"
                accept="image/*">
                value="{{ old('gambar', $produk->gambar) }}"

        </div>


        <div class="form-actions">

            <a href="{{ route('produk.index') }}">
                Batal
            </a>

            <button type="submit">
                Simpan Produk
            </button>

        </div>

    </form>

</div>

</body>

</html>
