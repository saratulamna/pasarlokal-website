
@extends('layouts.admin')

@section('title', 'Produk')
@section('breadcrumb', 'Produk')
@section('body-class', 'admin-products-page')

@section('content')

@php
    $statusLabels = [
        'aktif' => 'Aktif',
        'active' => 'Aktif',
        'published' => 'Aktif',
        'publish' => 'Aktif',
        'tersedia' => 'Aktif',
        'menunggu_verifikasi' => 'Menunggu',
        'pending' => 'Menunggu',
        'draft' => 'Draft',
        'ditolak' => 'Ditolak',
        'rejected' => 'Ditolak',
        'nonaktif' => 'Nonaktif',
        'inactive' => 'Nonaktif',
    ];
@endphp

<div class="ap-page">

    {{-- HEADER --}}

    <section class="ap-intro">
        <div>
            <p class="ap-eyebrow">ADMIN CONSOLE / KATALOG</p>

            <h1>
                Produk <span>lokal.</span>
            </h1>

            <p class="ap-description">
                Pantau katalog marketplace, harga, stok, dan produk
                yang ditawarkan oleh seluruh UMKM PasarLokal.
            </p>
        </div>

        <div class="ap-live-pill">
            <span></span>
            INVENTAR PRODUK
        </div>
    </section>


    {{-- STATISTIK --}}

    <section class="ap-stats">

        <article class="ap-stat ap-stat-total">
            <span class="ap-stat-label">TOTAL PRODUK</span>
            <strong>{{ number_format($stats['total'], 0, ',', '.') }}</strong>
            <span class="ap-stat-foot">Produk dalam katalog</span>
        </article>

        <article class="ap-stat ap-stat-active">
            <span class="ap-stat-label">PRODUK AKTIF</span>
            <strong>{{ number_format($stats['active'], 0, ',', '.') }}</strong>
            <span class="ap-stat-foot">Status aktif yang dikenali</span>
        </article>

        <article class="ap-stat ap-stat-low">
            <span class="ap-stat-label">STOK MENIPIS</span>
            <strong>{{ number_format($stats['low_stock'], 0, ',', '.') }}</strong>
            <span class="ap-stat-foot">Stok 1–5 unit</span>
        </article>

        <article class="ap-stat ap-stat-out">
            <span class="ap-stat-label">STOK HABIS</span>
            <strong>{{ number_format($stats['out_stock'], 0, ',', '.') }}</strong>
            <span class="ap-stat-foot">Stok nol atau kurang</span>
        </article>

    </section>


    {{-- RINGKASAN INVENTAR --}}

    <section class="ap-inventory-strip">
        <div class="ap-inventory-mark">▦</div>

        <div class="ap-inventory-copy">
            <strong>Inventaris PasarLokal</strong>
            <span>
                {{ number_format($stats['sold'], 0, ',', '.') }}
                unit tercatat terjual secara keseluruhan
            </span>
        </div>

        <div class="ap-inventory-end">
            <span>KATEGORI</span>
            <strong>{{ number_format($categories->count(), 0, ',', '.') }}</strong>
        </div>
    </section>


    {{-- PANEL PRODUK --}}

    <section class="ap-panel" id="daftar-produk">

        <div class="ap-panel-heading">
            <div>
                <p class="ap-eyebrow">PRODUCT DIRECTORY</p>
                <h2>Daftar produk</h2>
                <p>Temukan produk dan pantau ketersediaan stok.</p>
            </div>

            <span class="ap-total">
                {{ number_format($products->total(), 0, ',', '.') }}
                produk ditemukan
            </span>
        </div>


        {{-- SEARCH DAN FILTER --}}

        <form
            action="{{ route('admin.products.index') }}"
            method="GET"
            class="ap-filters"
        >

            <label class="ap-search">
                <span aria-hidden="true">⌕</span>

                <input
                    type="search"
                    name="q"
                    value="{{ $search }}"
                    placeholder="Cari nama, toko, atau kategori..."
                    aria-label="Cari produk"
                >
            </label>


            <select
                name="category"
                class="ap-select"
                aria-label="Filter kategori"
            >
                <option value="">Semua kategori</option>

                @foreach ($categories as $item)
                    <option
                        value="{{ $item->id }}"
                        @selected((string) $category === (string) $item->id)
                    >
                        {{ $item->nama }}
                    </option>
                @endforeach
            </select>


            <select
                name="status"
                class="ap-select"
                aria-label="Filter status produk"
            >
                <option value="">Semua status</option>

                @foreach ($statuses as $itemStatus)
                    <option
                        value="{{ $itemStatus }}"
                        @selected($status === $itemStatus)
                    >
                        {{ $statusLabels[$itemStatus] ?? ucfirst(str_replace('_', ' ', $itemStatus)) }}
                    </option>
                @endforeach
            </select>


            <select
                name="stock"
                class="ap-select"
                aria-label="Filter stok"
            >
                <option value="">Semua stok</option>
                <option value="tersedia" @selected($stock === 'tersedia')>
                    Stok tersedia
                </option>
                <option value="menipis" @selected($stock === 'menipis')>
                    Stok menipis
                </option>
                <option value="habis" @selected($stock === 'habis')>
                    Stok habis
                </option>
            </select>


            <button type="submit" class="ap-filter-button">
                Terapkan
            </button>

            @if ($search !== '' || $category !== '' || $status !== '' || $stock !== '')
                <a
                    href="{{ route('admin.products.index') }}"
                    class="ap-reset-button"
                >
                    Reset
                </a>
            @endif

        </form>


        {{-- TABEL PRODUK --}}

        <div class="ap-table-wrap">

            <table class="ap-table">

                <thead>
                    <tr>
                        <th>PRODUK</th>
                        <th>UMKM / TOKO</th>
                        <th>KATEGORI</th>
                        <th>HARGA</th>
                        <th>STOK</th>
                        <th>TERJUAL</th>
                        <th>STATUS</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($products as $product)

                        @php
                            $productStatus = strtolower(
                                trim((string) $product->status)
                            );

                            if (in_array($productStatus, [
                                'aktif',
                                'active',
                                'published',
                                'publish',
                                'tersedia',
                            ], true)) {
                                $statusClass = 'ap-status-active';
                            } elseif (in_array($productStatus, [
                                'ditolak',
                                'rejected',
                                'nonaktif',
                                'inactive',
                            ], true)) {
                                $statusClass = 'ap-status-rejected';
                            } elseif (in_array($productStatus, [
                                'menunggu_verifikasi',
                                'pending',
                            ], true)) {
                                $statusClass = 'ap-status-pending';
                            } else {
                                $statusClass = 'ap-status-neutral';
                            }

                            $productName = $product->nama ?: 'Produk tanpa nama';

                            $imagePath = $product->gambar;

                            $imageUrl = $imagePath
                                ? (
                                    \Illuminate\Support\Str::startsWith(
                                        $imagePath,
                                        ['http://', 'https://']
                                    )
                                        ? $imagePath
                                        : asset('storage/' . ltrim($imagePath, '/'))
                                )
                                : null;
                        @endphp

                        <tr>

                            {{-- PRODUK --}}

                            <td>
                                <div class="ap-product-cell">

                                    <div class="ap-product-image">
                                        @if ($imageUrl)
                                            <img
                                                src="{{ $imageUrl }}"
                                                alt="{{ $productName }}"
                                                loading="lazy"
                                            >
                                        @else
                                            <span>✳</span>
                                        @endif
                                    </div>

                                    <div class="ap-product-info">
                                        <strong>{{ $productName }}</strong>

                                        <small>
                                            PL-{{ str_pad((string) $product->id, 5, '0', STR_PAD_LEFT) }}
                                        </small>
                                    </div>

                                </div>
                            </td>


                            {{-- TOKO --}}

                            <td>
                                <span class="ap-store-name">
                                    {{ $product->admin_store_name ?? 'Toko tidak tersedia' }}
                                </span>
                            </td>


                            {{-- KATEGORI --}}

                            <td>
                                <span class="ap-category">
                                    {{ $product->admin_category_name ?? 'Tanpa kategori' }}
                                </span>
                            </td>


                            {{-- HARGA --}}

                            <td>
                                <strong class="ap-price">
                                    Rp{{ number_format((float) $product->harga, 0, ',', '.') }}
                                </strong>
                            </td>


                            {{-- STOK --}}

                            <td>
                                @if ((int) $product->stok <= 0)
                                    <span class="ap-stock ap-stock-out">
                                        Habis
                                    </span>
                                @elseif ((int) $product->stok <= 5)
                                    <span class="ap-stock ap-stock-low">
                                        {{ number_format((int) $product->stok) }} unit
                                    </span>
                                @else
                                    <span class="ap-stock ap-stock-ok">
                                        {{ number_format((int) $product->stok) }} unit
                                    </span>
                                @endif
                            </td>


                            {{-- TERJUAL --}}

                            <td>
                                <span class="ap-sold">
                                    {{ number_format((int) $product->terjual, 0, ',', '.') }}
                                </span>
                            </td>


                            {{-- STATUS --}}

                            <td>
                                <span class="ap-status {{ $statusClass }}">
                                    <i></i>

                                    {{ $statusLabels[$productStatus] ?? ($productStatus !== '' ? ucfirst(str_replace('_', ' ', $productStatus)) : 'Belum diatur') }}
                                </span>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7">
                                <div class="ap-empty">

                                    <div class="ap-empty-icon">▦</div>

                                    <h3>
                                        @if ($search || $category || $status || $stock)
                                            Produk tidak ditemukan
                                        @else
                                            Belum ada produk di katalog
                                        @endif
                                    </h3>

                                    <p>
                                        @if ($search || $category || $status || $stock)
                                            Ubah kata kunci atau filter pencarian untuk melihat hasil lain.
                                        @else
                                            Produk dari UMKM akan ditampilkan di sini setelah tersedia.
                                        @endif
                                    </p>

                                    @if ($search || $category || $status || $stock)
                                        <a
                                            href="{{ route('admin.products.index') }}"
                                            class="ap-filter-button"
                                        >
                                            Hapus semua filter
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

        @if ($products->hasPages())
            <div class="ap-pagination">

                <div class="ap-pagination-info">
                    Menampilkan
                    <strong>{{ $products->firstItem() }}</strong>–<strong>{{ $products->lastItem() }}</strong>
                    dari
                    <strong>{{ $products->total() }}</strong> produk
                </div>

                <div class="ap-pagination-links">
                    {{ $products->links() }}
                </div>

            </div>
        @endif

    </section>

    <footer class="ap-footer">
        <span>© {{ date('Y') }} PASARLOKAL / ADMIN STUDIO</span>
        <span>Mengembangkan ekonomi lokal, bersama. <b>✳</b></span>
    </footer>

</div>

@endsection
