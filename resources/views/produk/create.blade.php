<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Produk</title>

    <link rel="stylesheet" href="{{ asset('css/produk.css') }}">
</head>

<body>

<div class="produk-shell">

    <aside class="produk-sidebar">
        <div class="brand-box">
            <div class="brand-logo">B</div>
            <div>
                <div class="brand-name">Batik Store</div>
                <small>Admin Panel</small>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="{{ route('produk.index') }}" class="{{ request()->routeIs('produk.*') ? 'active' : '' }}">
                <i class="bi bi-box-seam"></i>
                Produk
            </a>
            <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                <i class="bi bi-bag-check"></i>
                Orders
            </a>
            <a href="{{ route('profile.edit') }}">
                <i class="bi bi-person-circle"></i>
                Profile
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-box">
                <div class="user-avatar">{{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}</div>
                <div>
                    <strong>{{ Auth::user()->name ?? 'Admin' }}</strong>
                    <small>{{ Auth::user()->email ?? 'admin@example.com' }}</small>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn">
                    <i class="bi bi-box-arrow-right"></i>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <main class="produk-main">
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
                action="{{ route('produk.store') }}"
                method="POST"
                enctype="multipart/form-data">

                @csrf

                <div class="form-group">

                    <label>Nama Produk</label>

                    <input
                        type="text"
                        name="nama"
                        value="{{ old('nama') }}"
                        placeholder="Contoh: Kemeja Batik Parang"
                        required>

                </div>


                <div class="form-group">

                    <label>SKU</label>

                    <input
                        type="text"
                        name="sku"
                        value="{{ old('sku') }}"
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
                            value="{{ old('harga') }}"
                            min="0"
                            required>

                    </div>


                    <div class="form-group">

                        <label>Stok</label>

                        <input
                            type="number"
                            name="stok"
                            value="{{ old('stok', 0) }}"
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
                        value="{{ old('deskripsi') }}"
                        placeholder="Contoh: Kualitas premium, cocok untuk acara formal">

                </div>


                <div class="form-group">

                    <label>Material</label>

                    <input
                        type="text"
                        name="material"
                        value="{{ old('material') }}"
                        placeholder="Contoh: Katun Prima">

                </div>


                <div class="form-group">

                    <label>Gambar Produk</label>

                    <input
                        type="file"
                        name="gambar"
                        accept="image/*">

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
    </main>
</div>

</body>

</html>
