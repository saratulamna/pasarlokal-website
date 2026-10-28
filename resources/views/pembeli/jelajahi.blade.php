@extends('layouts.buyer')

@section('title', 'Jelajahi Produk — PasarLokal')
@section('breadcrumb', 'Jelajahi Produk')
@section('eyebrow', 'TEMUKAN PRODUK LOKAL')
@section('page-title', 'Jelajahi produk')
@section('page-description', 'Temukan produk pilihan dari UMKM lokal dan dukung bisnis di sekitarmu.')

@push('page-styles')
    @vite('resources/css/pages/pembeli/jelajahi.css')
@endpush

@section('page-actions')
    <a href="{{ route('cart.index') }}" class="explore-cart-link">
        <span aria-hidden="true">↗</span>
        Keranjang
    </a>
@endsection

@section('content')
@php
    $imageUrl = function ($path) {
        if (!$path) {
            return null;
        }

        if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return \Illuminate\Support\Facades\Storage::url($path);
    };
@endphp

<div class="explore-page">

    <section class="explore-intro">
        <div class="explore-intro-copy">
            <span class="explore-kicker">
                <span class="explore-kicker-dot"></span>
                PILIHAN LOKAL, PILIHAN BERMAKNA
            </span>

            <h2>
                Temukan sesuatu
                <span>yang istimewa.</span>
            </h2>

            <p>
                Dari karya kreatif hingga kebutuhan sehari-hari,
                temukan produk lokal yang cocok untukmu.
            </p>
        </div>

        <div class="explore-intro-mark" aria-hidden="true">
            <span>PL</span>
            <span class="explore-mark-star">✳</span>
        </div>
    </section>

    <section class="explore-toolbar" aria-label="Pencarian dan filter produk">
        <form action="{{ route('pembeli.products.index') }}"
              method="GET"
              class="explore-search-form">

            <label class="explore-search-box">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle cx="10.8" cy="10.8" r="6.8"/>
                    <path d="m16 16 4.5 4.5"/>
                </svg>

                <input
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Cari nama produk atau UMKM..."
                    aria-label="Cari produk"
                >

                @if(request('kategori'))
                    <input type="hidden" name="kategori" value="{{ request('kategori') }}">
                @endif

                @if(request('sort'))
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                @endif
            </label>

            <button type="submit" class="explore-search-button">
                Cari produk
            </button>
        </form>

        <form action="{{ route('pembeli.products.index') }}"
              method="GET"
              class="explore-sort-form">

            <input type="hidden" name="q" value="{{ request('q') }}">

            @if(request('kategori'))
                <input type="hidden" name="kategori" value="{{ request('kategori') }}">
            @endif

            <label for="explore-sort">Urutkan</label>

            <select id="explore-sort" name="sort" onchange="this.form.submit()">
                <option value="terbaru" @selected(request('sort', 'terbaru') === 'terbaru')>
                    Terbaru
                </option>
                <option value="harga_terendah" @selected(request('sort') === 'harga_terendah')>
                    Harga terendah
                </option>
                <option value="harga_tertinggi" @selected(request('sort') === 'harga_tertinggi')>
                    Harga tertinggi
                </option>
                <option value="terlaris" @selected(request('sort') === 'terlaris')>
                    Terlaris
                </option>
            </select>
        </form>
    </section>

    <section class="explore-categories" aria-label="Kategori produk">
        <div class="explore-category-heading">
            <h3>Kategori</h3>

            @if(request('kategori') || request('q'))
                <a href="{{ route('pembeli.products.index') }}">Reset filter ↗</a>
            @endif
        </div>

        <div class="explore-category-list">
            <a
                href="{{ route('pembeli.products.index', array_filter([
                    'q' => request('q'),
                    'sort' => request('sort'),
                ])) }}"
                class="explore-category-chip {{ !request('kategori') ? 'is-active' : '' }}"
            >
                Semua produk
            </a>

            @foreach($categories as $category)
                <a
                    href="{{ route('pembeli.products.index', array_filter([
                        'q' => request('q'),
                        'sort' => request('sort'),
                        'kategori' => $category->slug,
                    ])) }}"
                    class="explore-category-chip {{ request('kategori') === $category->slug ? 'is-active' : '' }}"
                >
                    {{ $category->nama }}
                </a>
            @endforeach
        </div>
    </section>

    <section class="explore-products-section">
        <div class="explore-products-heading">
            <div>
                <span class="explore-section-kicker">KOLEKSI PASARLOKAL</span>
                <h2>
                    {{ request('q') ? 'Hasil pencarian' : 'Produk pilihan' }}
                </h2>
            </div>

            <span class="explore-result-count">
                {{ $products->total() }} produk
            </span>
        </div>

        @if($products->count())
            <div class="explore-product-grid">
                @foreach($products as $product)
                    <article class="explore-product-card">
                        <a
                            href="{{ route('products.show', $product->slug) }}"
                            class="explore-product-image-link"
                            aria-label="Lihat {{ $product->nama }}"
                        >
                            <div class="explore-product-image">
                                @if($imageUrl($product->gambar))
                                    <img
                                        src="{{ $imageUrl($product->gambar) }}"
                                        alt="{{ $product->nama }}"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="explore-image-placeholder">
                                        <span>✳</span>
                                        <small>PRODUK LOKAL</small>
                                    </div>
                                @endif

                                @if(($product->terjual ?? 0) > 0)
                                    <span class="explore-product-badge">Pilihan lokal</span>
                                @endif
                            </div>
                        </a>

                        <div class="explore-product-content">
                            <span class="explore-product-category">
                                {{ $product->category?->nama ?? 'Produk lokal' }}
                            </span>

                            <h3>
                                <a href="{{ route('products.show', $product->slug) }}">
                                    {{ $product->nama }}
                                </a>
                            </h3>

                            <p class="explore-product-store">
                                Oleh {{ $product->umkm?->nama_toko ?? 'UMKM lokal' }}
                            </p>

                            <div class="explore-product-meta">
                                <div class="explore-product-rating">
                                    <span aria-hidden="true">★</span>
                                    @if(($product->reviews_count ?? 0) > 0)
                                        {{ number_format((float) ($product->reviews_avg_rating ?? 0), 1) }}
                                        <small>({{ $product->reviews_count }})</small>
                                    @else
                                        <small>Belum ada ulasan</small>
                                    @endif
                                </div>

                                @if(isset($product->terjual))
                                    <span class="explore-product-sold">
                                        {{ number_format((int) $product->terjual) }} terjual
                                    </span>
                                @endif
                            </div>

                            <div class="explore-product-bottom">
                                <strong>
                                    Rp{{ number_format((float) $product->harga, 0, ',', '.') }}
                                </strong>

                                <a
                                    href="{{ route('products.show', $product->slug) }}"
                                    class="explore-detail-link"
                                    aria-label="Lihat detail {{ $product->nama }}"
                                >
                                    <span>Lihat detail</span>
                                    <span aria-hidden="true">↗</span>
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="explore-pagination">
                {{ $products->links() }}
            </div>
        @else
            <div class="explore-empty-state">
                <div class="explore-empty-icon">⌕</div>
                <h3>Produk belum ditemukan</h3>
                <p>
                    Coba gunakan kata kunci lain atau pilih kategori yang berbeda.
                </p>
                <a href="{{ route('pembeli.products.index') }}" class="explore-empty-button">
                    Lihat semua produk <span aria-hidden="true">↗</span>
                </a>
            </div>
        @endif
    </section>

    <section class="explore-closing">
        <span class="explore-closing-mark">✳</span>
        <div>
            <strong>Setiap produk punya cerita.</strong>
            <p>Terima kasih telah mendukung UMKM Indonesia.</p>
        </div>
    </section>

</div>
@endsection
