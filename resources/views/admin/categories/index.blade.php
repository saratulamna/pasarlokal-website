
@extends('layouts.admin')

@section('title', 'Kategori')
@section('breadcrumb', 'Kategori')
@section('body-class', 'admin-categories-page')

@section('content')

<div class="ac-page">

    {{-- HEADER --}}

    <section class="ac-intro">
        <div>
            <p class="ac-eyebrow">ADMIN CONSOLE / KATALOG</p>

            <h1>
                Atur <span>kategori.</span>
            </h1>

            <p class="ac-description">
                Kelompokkan produk lokal agar katalog PasarLokal
                lebih terstruktur dan mudah ditemukan.
            </p>
        </div>

        <a href="{{ route('admin.categories.create') }}"
           class="ac-primary-button">
            <span>+</span>
            Tambah kategori
        </a>
    </section>


    {{-- NOTIFIKASI --}}

    @if (session('success'))
        <div class="ac-alert ac-alert-success" role="status">
            ✓ {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="ac-alert ac-alert-error" role="alert">
            ! {{ session('error') }}
        </div>
    @endif


    {{-- STATISTIK --}}

    <section class="ac-stats">

        <article class="ac-stat">
            <span class="ac-stat-label">TOTAL KATEGORI</span>
            <strong>{{ number_format($stats['total']) }}</strong>
            <small>Semua kategori terdaftar</small>
        </article>

        <article class="ac-stat ac-stat-active">
            <span class="ac-stat-label">KATEGORI AKTIF</span>
            <strong>{{ number_format($stats['active']) }}</strong>
            <small>Dapat digunakan di katalog</small>
        </article>

        <article class="ac-stat ac-stat-inactive">
            <span class="ac-stat-label">NONAKTIF</span>
            <strong>{{ number_format($stats['inactive']) }}</strong>
            <small>Tidak tersedia untuk dipilih</small>
        </article>

        <article class="ac-stat ac-stat-products">
            <span class="ac-stat-label">TOTAL PRODUK</span>
            <strong>{{ number_format($stats['products']) }}</strong>
            <small>Produk seluruh kategori</small>
        </article>

    </section>


    {{-- DAFTAR KATEGORI --}}

    <section class="ac-panel">

        <div class="ac-panel-heading">
            <div>
                <p class="ac-eyebrow">CATEGORY DIRECTORY</p>
                <h2>Daftar kategori</h2>
                <p>Kelola nama, gambar, dan ketersediaan kategori.</p>
            </div>

            <span class="ac-count">
                {{ number_format($categories->total()) }} kategori
            </span>
        </div>


        {{-- FILTER DAN PENCARIAN --}}

        <form
            action="{{ route('admin.categories.index') }}"
            method="GET"
            class="ac-filters"
        >

            <label class="ac-search">
                <span aria-hidden="true">⌕</span>

                <input
                    type="search"
                    name="q"
                    value="{{ $search }}"
                    placeholder="Cari nama atau deskripsi kategori..."
                    aria-label="Cari kategori"
                >
            </label>

            <select name="status"
                    class="ac-select"
                    aria-label="Filter status kategori">

                <option value="semua" @selected($status === 'semua')>
                    Semua status
                </option>

                <option value="aktif" @selected($status === 'aktif')>
                    Aktif
                </option>

                <option value="nonaktif" @selected($status === 'nonaktif')>
                    Nonaktif
                </option>

            </select>

            <button type="submit" class="ac-filter-button">
                Terapkan
            </button>

            @if ($search !== '' || $status !== 'semua')
                <a href="{{ route('admin.categories.index') }}"
                   class="ac-reset-button">
                    Reset
                </a>
            @endif

        </form>


        {{-- TABEL --}}

        <div class="ac-table-wrap">
            <table class="ac-table">

                <thead>
                    <tr>
                        <th>KATEGORI</th>
                        <th>SLUG</th>
                        <th>JUMLAH PRODUK</th>
                        <th>STATUS</th>
                        <th>DIBUAT</th>
                        <th class="ac-action-heading">AKSI</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($categories as $category)

                        @php
                            $categoryImage = $category->gambar
                                ? (
                                    \Illuminate\Support\Str::startsWith(
                                        $category->gambar,
                                        ['http://', 'https://']
                                    )
                                        ? $category->gambar
                                        : asset('storage/' . ltrim($category->gambar, '/'))
                                )
                                : null;
                        @endphp

                        <tr>

                            <td>
                                <div class="ac-category-cell">

                                    <div class="ac-category-image">
                                        @if ($categoryImage)
                                            <img
                                                src="{{ $categoryImage }}"
                                                alt="{{ $category->nama }}"
                                                loading="lazy"
                                            >
                                        @else
                                            <span>✳</span>
                                        @endif
                                    </div>

                                    <div class="ac-category-info">
                                        <strong>{{ $category->nama }}</strong>
                                        <small>
                                            {{ \Illuminate\Support\Str::limit($category->deskripsi ?: 'Belum ada deskripsi kategori.', 75) }}
                                        </small>
                                    </div>

                                </div>
                            </td>

                            <td>
                                <code class="ac-slug">
                                    {{ $category->slug }}
                                </code>
                            </td>

                            <td>
                                <span class="ac-product-count">
                                    {{ number_format((int) $category->products_count) }}
                                    produk
                                </span>
                            </td>

                            <td>
                                @if ($category->is_active)
                                    <span class="ac-status ac-status-active">
                                        <i></i> Aktif
                                    </span>
                                @else
                                    <span class="ac-status ac-status-inactive">
                                        <i></i> Nonaktif
                                    </span>
                                @endif
                            </td>

                            <td>
                                <span class="ac-date">
                                    {{ $category->created_at?->format('d M Y') ?? '—' }}
                                </span>
                            </td>

                            <td>
                                <div class="ac-actions">

                                    <a
                                        href="{{ route('admin.categories.edit', $category) }}"
                                        class="ac-action-edit"
                                        aria-label="Edit kategori {{ $category->nama }}"
                                        title="Edit kategori"
                                    >
                                        ✎
                                    </a>

                                    <form
                                        action="{{ route('admin.categories.destroy', $category) }}"
                                        method="POST"
                                        onsubmit="return confirm('Hapus kategori {{ addslashes($category->nama) }}? Kategori yang masih digunakan produk tidak dapat dihapus.');"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="ac-action-delete"
                                            aria-label="Hapus kategori {{ $category->nama }}"
                                            title="Hapus kategori"
                                        >
                                            ×
                                        </button>
                                    </form>

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6">

                                <div class="ac-empty">
                                    <span>✳</span>

                                    <h3>
                                        @if ($search !== '')
                                            Kategori tidak ditemukan
                                        @else
                                            Belum ada kategori
                                        @endif
                                    </h3>

                                    <p>
                                        @if ($search !== '')
                                            Coba gunakan kata kunci yang berbeda.
                                        @else
                                            Tambahkan kategori pertama untuk mulai
                                            mengelompokkan produk lokal.
                                        @endif
                                    </p>

                                    @if ($search === '')
                                        <a href="{{ route('admin.categories.create') }}"
                                           class="ac-primary-button">
                                            + Tambah kategori
                                        </a>
                                    @endif
                                </div>

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>


        {{-- PAGINATION --}}

        @if ($categories->hasPages())
            <div class="ac-pagination">

                <span>
                    Menampilkan {{ $categories->firstItem() }}–{{ $categories->lastItem() }}
                    dari {{ $categories->total() }} kategori
                </span>

                {{ $categories->links() }}

            </div>
        @endif

    </section>


    <footer class="ac-footer">
        <span>© {{ date('Y') }} PASARLOKAL / ADMIN STUDIO</span>
        <span>Mengembangkan ekonomi lokal, bersama. ✳</span>
    </footer>

</div>

@endsection
