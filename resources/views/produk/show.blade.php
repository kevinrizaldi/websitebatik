<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <title>Detail Produk</title>

    <link rel="stylesheet"
        href="{{ asset('css/produk.css') }}">
</head>

<body>

<div class="form-container">

    <h1>{{ $produk->nama }}</h1>

    @if($produk->gambar)

        <img
            src="{{ asset('storage/' . $produk->gambar) }}"
            style="
                width:200px;
                height:220px;
                object-fit:cover;
                border-radius:12px;
                margin-bottom:20px;
            ">

    @endif


    <p>
        <strong>SKU:</strong>
        {{ $produk->sku }}
    </p>

    <p>
        <strong>Kategori:</strong>
        {{ $produk->kategori }}
    </p>

    <p>
        <strong>Harga:</strong>
        Rp {{ number_format($produk->harga, 0, ',', '.') }}
    </p>

    <p>
        <strong>Stok:</strong>
        {{ $produk->stok }} pcs
    </p>

    <p>
        <strong>Status:</strong>
        {{ $produk->status }}
    </p>

    <p>
        <strong>Deskripsi Produk:</strong>
        {{ $produk->deskripsi ?? '-' }}
    </p>

    <p>
        <strong>Material:</strong>
        {{ $produk->material ?? '-' }}
    </p>


    <div class="form-actions">

        <a href="{{ route('produk.index') }}">
            Kembali
        </a>

        <a href="{{ route('produk.edit', $produk) }}">
            Edit
        </a>

    </div>

</div>

</body>

</html>
