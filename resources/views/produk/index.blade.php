<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Produk - Dashboard</title>

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

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
        <div class="container-produk">

            {{-- HEADER --}}
            <div class="top-header">

                <div>
                    <h1>Produk & SKU</h1>
                    <p>Kelola produk, stok dan informasi material</p>
                </div>

                <a href="{{ route('produk.create') }}" class="btn-tambah">
                    <i class="bi bi-plus-lg"></i>
                    Tambah Produk
                </a>

            </div>


            {{-- ALERT --}}
            @if(session('success'))
                <div class="alert-success">
                    <i class="bi bi-check-circle"></i>
                    {{ session('success') }}
                </div>
            @endif


            {{-- TOOLBAR --}}
            <div class="toolbar">

                <div class="toolbar-left">

                    <label class="checkbox-wrapper">
                        <input type="checkbox" id="selectAll">
                        <span>Pilih Semua</span>
                    </label>

                    <span class="separator"></span>

                    <button class="btn-toolbar">
                        <i class="bi bi-arrow-left-right"></i>
                        Ubah Status
                    </button>

                    <button class="btn-toolbar danger">
                        <i class="bi bi-trash"></i>
                        Hapus Pilihan
                    </button>

                </div>

                <div class="total">
                    Menampilkan
                    {{ $produks->firstItem() ?? 0 }}
                    –
                    {{ $produks->lastItem() ?? 0 }}
                    dari
                    {{ $produks->total() }}
                    produk
                </div>

            </div>


            {{-- TABLE --}}
            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>

                            <th class="check-column"></th>

                            <th>PRODUK & SKU</th>

                            <th>KATEGORI</th>

                            <th>HARGA<br>RESMI</th>

                            <th>STOK</th>

                            <th>STATUS</th>

                            <th>DESKRIPSI<br>PRODUK &<br>MATERIAL</th>

                            <th>AKSI</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($produks as $produk)

                        <tr>

                            {{-- CHECKBOX --}}
                            <td>
                                <input
                                    type="checkbox"
                                    class="product-checkbox"
                                    value="{{ $produk->id }}">
                            </td>


                            {{-- PRODUK --}}
                            <td>

                                <div class="product">

                                    @if($produk->gambar)

                                        <img
                                            src="{{ asset('storage/' . $produk->gambar) }}"
                                            alt="{{ $produk->nama }}">

                                    @else

                                        <div class="no-image">
                                            <i class="bi bi-image"></i>
                                        </div>

                                    @endif

                                    <div class="product-info">

                                        <strong>
                                            {{ $produk->nama }}
                                        </strong>

                                        <div class="sku">
                                            <span>
                                                {{ $produk->sku }}
                                            </span>

                                            <span class="dot">•</span>

                                            <small>
                                                {{ $produk->material ?? '-' }}
                                            </small>
                                        </div>

                                    </div>

                                </div>

                            </td>


                            {{-- KATEGORI --}}
                            <td>

                                <span class="category">
                                    {{ $produk->kategori }}
                                </span>

                            </td>


                            {{-- HARGA --}}
                            <td>

                                <strong class="price">

                                    Rp
                                    {{ number_format($produk->harga, 0, ',', '.') }}

                                </strong>

                            </td>


                            {{-- STOK --}}
                            <td>

                                <span class="stock
                                    {{ $produk->stok <= 0 ? 'empty' : ($produk->stok <= 5 ? 'low-number' : '') }}">

                                    {{ $produk->stok }}

                                    <small>pcs</small>

                                </span>

                            </td>


                            {{-- STATUS --}}
                            <td>

                                @if($produk->status === 'Tersedia')

                                    <span class="status available">
                                        <span></span>
                                        Tersedia
                                    </span>

                                @elseif($produk->status === 'Stok Menipis')

                                    <span class="status low">
                                        <span></span>
                                        Stok Menipis
                                    </span>

                                @else

                                    <span class="status empty-status">
                                        <span></span>
                                        Habis
                                    </span>

                                @endif

                            </td>


                            {{-- MATERIAL --}}
                            <td>

                                <div class="material">

                                    @if($produk->deskripsi)
                                        <span>
                                            <i class="bi bi-recycle"></i>
                                            {{ $produk->deskripsi }}
                                        </span>
                                    @endif

                                    @if($produk->material)
                                        <span>
                                            <i class="bi bi-box"></i>
                                            {{ $produk->material }}
                                        </span>
                                    @endif

                                </div>

                            </td>


                            {{-- AKSI --}}
                            <td>

                                <div class="actions">

                                    <a
                                        href="{{ route('produk.show', $produk) }}"
                                        title="Lihat">

                                        <i class="bi bi-eye"></i>

                                    </a>

                                    <a
                                        href="{{ route('produk.edit', $produk) }}"
                                        title="Edit">

                                        <i class="bi bi-pencil"></i>

                                    </a>

                                    <form
                                        action="{{ route('produk.destroy', $produk) }}"
                                        method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus produk ini?')">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" title="Hapus">

                                            <i class="bi bi-trash"></i>

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td colspan="8" class="empty-table">

                                <i class="bi bi-box-seam"></i>

                                <h3>Belum ada produk</h3>

                                <p>Silakan tambahkan produk baru.</p>

                                <a href="{{ route('produk.create') }}">
                                    Tambah Produk
                                </a>

                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- PAGINATION --}}
            <div class="pagination-area">

                <div>

                    Menampilkan
                    {{ $produks->firstItem() ?? 0 }}
                    –
                    {{ $produks->lastItem() ?? 0 }}
                    dari
                    {{ $produks->total() }}
                    produk

                </div>

                <div class="pagination">

                    {{ $produks->links() }}

                </div>

            </div>

        </div>
    </main>
</div>


<script>

    document
        .getElementById('selectAll')
        .addEventListener('change', function () {

            document
                .querySelectorAll('.product-checkbox')
                .forEach(function (checkbox) {

                    checkbox.checked = this.checked;

                }, this);

        });

</script>

</body>

</html>
