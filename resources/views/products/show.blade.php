
@extends('layouts.public')

@section('title', ($product->nama ?? 'Detail Produk') . ' — PasarLokal')

@section('meta_description', \Illuminate\Support\Str::limit(
    strip_tags((string) ($product->deskripsi ?? 'Temukan produk lokal berkualitas di PasarLokal.')),
    155
))

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

    $productImage = $imageUrl($product->gambar ?? null);
    $shop = $product->umkm ?? null;
    $category = $product->category ?? null;

    $reviews = method_exists($product, 'reviews')
        ? $product->reviews
        : collect();

    $reviewCount = $reviews->count();

    $ratingAverage = $reviewCount > 0
        ? (float) $reviews->avg('rating')
        : (float) ($product->rating_avg ?? $product->rating ?? 0);

    $favoriteCount = method_exists($product, 'favorites')
        ? $product->favorites()->count()
        : (int) ($product->favorite_count ?? $product->favorites_count ?? 0);

    $stock = max(0, (int) ($product->stok ?? 0));

    $soldCount = max(0, (int) (
        $product->terjual
        ?? $product->jumlah_terjual
        ?? 0
    ));

    $price = number_format((float) ($product->harga ?? 0), 0, ',', '.');

    $description = trim((string) ($product->deskripsi ?? ''));
@endphp

@push('page-styles')
    @vite('resources/css/public/product-show.css')
@endpush

@push('page-scripts')
    @vite('resources/js/public/product-show.js')
@endpush

@section('content')
<main class="pd-page">

    <div class="pd-container">

        {{-- BREADCRUMB --}}
        <nav class="pd-breadcrumb" aria-label="Breadcrumb" data-reveal>
            <a href="{{ route('home') }}">Beranda</a>
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="m9 18 6-6-6-6" />
            </svg>

            @if ($category)
                <a href="{{ route('home') }}#kategori">
                    {{ $category->nama }}
                </a>

                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="m9 18 6-6-6-6" />
                </svg>
            @endif

            <span aria-current="page">{{ $product->nama }}</span>
        </nav>

        {{-- PRODUCT OVERVIEW --}}
        <section class="pd-product-grid">

            {{-- LEFT: PRODUCT IMAGE --}}
            <div class="pd-gallery" data-reveal>

                <div class="pd-gallery-heading">
                    <div>
                        <span class="pd-section-kicker">PILIHAN LOKAL</span>
                        <h2>Detail produk</h2>
                    </div>

                    <span class="pd-product-reference">
                        #PL-{{ str_pad((string) $product->id, 4, '0', STR_PAD_LEFT) }}
                    </span>
                </div>

                <div class="pd-image-stage">

                    <div class="pd-image-decoration pd-decoration-one"></div>
                    <div class="pd-image-decoration pd-decoration-two"></div>

                    @if ($productImage)
                        <img
                            class="pd-product-image"
                            src="{{ $productImage }}"
                            alt="{{ $product->nama }}"
                            fetchpriority="high"
                        >
                    @else
                        <div class="pd-image-placeholder">
                            <span class="pd-placeholder-mark">P.</span>
                            <strong>{{ $product->nama }}</strong>
                            <span>Produk lokal pilihan</span>
                        </div>
                    @endif

                    <div class="pd-image-badge">
                        <span class="pd-badge-symbol">✳</span>
                        Produk Lokal
                    </div>

                    @if ($stock > 0 && $stock <= 5)
                        <div class="pd-stock-badge">
                            Stok terbatas
                        </div>
                    @endif

                    <span class="pd-image-index">PASARLOKAL / GOODS</span>
                </div>

                <div class="pd-gallery-caption">
                    <span class="pd-caption-icon">✳</span>
                    <p>
                        Pilihan produk dari pelaku usaha lokal Indonesia.
                    </p>
                </div>

                {{-- QUICK FACTS --}}
                <div class="pd-facts">

                    <div class="pd-fact">
                        <span class="pd-fact-icon pd-fact-icon-green">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 3 14.5 9.5 21 12l-6.5 2.5L12 21l-2.5-6.5L3 12l6.5-2.5L12 3Z" />
                            </svg>
                        </span>

                        <div>
                            <strong>{{ number_format($soldCount) }}</strong>
                            <span>Terjual</span>
                        </div>
                    </div>

                    <div class="pd-fact">
                        <span class="pd-fact-icon pd-fact-icon-lime">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M20.5 8.5c0 5-8.5 11-8.5 11s-8.5-6-8.5-11a4.5 4.5 0 0 1 8.5-2 4.5 4.5 0 0 1 8.5 2Z" />
                            </svg>
                        </span>

                        <div>
                            <strong>{{ number_format($favoriteCount) }}</strong>
                            <span>Favorit</span>
                        </div>
                    </div>

                    <div class="pd-fact">
                        <span class="pd-fact-icon pd-fact-icon-peach">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="m12 3 2.7 5.5 6.1.9-4.4 4.3 1 6.1-5.4-2.9-5.4 2.9 1-6.1-4.4-4.3 6.1-.9L12 3Z" />
                            </svg>
                        </span>

                        <div>
                            <strong>
                                {{ $ratingAverage > 0 ? number_format($ratingAverage, 1, ',', '.') : '—' }}
                            </strong>
                            <span>{{ number_format($reviewCount) }} ulasan</span>
                        </div>
                    </div>

                </div>
            </div>

            {{-- RIGHT: PRODUCT INFORMATION --}}
            <div class="pd-details" data-reveal>

                <div class="pd-product-tags">
                    @if ($category)
                        <span class="pd-category-tag">
                            {{ $category->nama }}
                        </span>
                    @endif

                    <span class="pd-local-tag">
                        <span></span>
                        PILIHAN UMKM
                    </span>
                </div>

                <h1 class="pd-product-title">
                    {{ $product->nama }}
                </h1>

                <p class="pd-product-intro">
                    Produk lokal dengan cerita dan keunikan tersendiri.
                    Temukan detailnya dan dukung usaha di balik produk ini.
                </p>

                <div class="pd-rating-row">
                    <span class="pd-stars" aria-label="Rating {{ number_format($ratingAverage, 1) }} dari 5">
                        @for ($i = 1; $i <= 5; $i++)
                            <span class="{{ $ratingAverage >= $i ? 'is-filled' : '' }}">★</span>
                        @endfor
                    </span>

                    @if ($reviewCount > 0)
                        <a href="#ulasan" class="pd-rating-link">
                            {{ number_format($ratingAverage, 1) }}
                            <span>·</span>
                            {{ $reviewCount }} ulasan
                        </a>
                    @else
                        <span class="pd-rating-empty">Belum ada ulasan</span>
                    @endif
                </div>

                <div class="pd-price-block">
                    <span class="pd-price-label">Harga produk</span>

                    <div class="pd-price">
                        <span class="pd-price-currency">Rp</span>
                        <strong>{{ $price }}</strong>
                    </div>

                    <span class="pd-price-note">
                        Harga per produk
                    </span>
                </div>

                <div class="pd-detail-divider"></div>

                {{-- SHOP INFORMATION --}}
                <div class="pd-shop-card">

                    <div class="pd-shop-avatar">
                        @if ($shop && $shop->logo)
                            <img
                                src="{{ $imageUrl($shop->logo) }}"
                                alt="Logo {{ $shop->nama_toko }}"
                                loading="lazy"
                            >
                        @else
                            <span>
                                {{ \Illuminate\Support\Str::upper(
                                    \Illuminate\Support\Str::substr(
                                        (string) ($shop->nama_toko ?? 'UMKM'),
                                        0,
                                        1
                                    )
                                ) }}
                            </span>
                        @endif
                    </div>

                    <div class="pd-shop-info">
                        <span class="pd-shop-label">DIJUAL OLEH</span>

                        <strong>
                            {{ $shop->nama_toko ?? 'UMKM Lokal' }}
                        </strong>

                        <span class="pd-shop-subtitle">
                            Pelaku usaha lokal Indonesia
                        </span>
                    </div>

                    <span class="pd-shop-mark" aria-label="Toko lokal">✳</span>
                </div>

                {{-- STOCK --}}
                <div class="pd-stock-row">
                    <div class="pd-stock-label">
                        <span class="pd-stock-indicator {{ $stock > 0 ? 'is-available' : 'is-empty' }}"></span>

                        <span>
                            {{ $stock > 0 ? 'Stok tersedia' : 'Stok habis' }}
                        </span>
                    </div>

                    <strong>
                        {{ number_format($stock) }}
                        <span>unit</span>
                    </strong>
                </div>

                {{-- CART ACTION --}}
                <div class="pd-purchase">

                    @if ($stock > 0)

                        @auth
                            @if (auth()->user()->role === 'pembeli')

                                <form
                                    class="pd-cart-form"
                                    action="{{ route('cart.store', ['product' => $product->slug]) }}"
                                    method="POST"
                                    id="productCartForm"
                                >
                                    @csrf

                                    <label for="productQuantity" class="pd-quantity-label">
                                        Jumlah pembelian
                                    </label>

                                    <div class="pd-purchase-controls">

                                        <div class="pd-quantity">
                                            <button
                                                type="button"
                                                class="pd-quantity-button"
                                                data-quantity-step="-1"
                                                aria-label="Kurangi jumlah"
                                            >−</button>

                                            <input
                                                type="number"
                                                name="quantity"
                                                id="productQuantity"
                                                min="1"
                                                max="{{ $stock }}"
                                                value="1"
                                                inputmode="numeric"
                                                aria-label="Jumlah produk"
                                            >

                                            <button
                                                type="button"
                                                class="pd-quantity-button"
                                                data-quantity-step="1"
                                                aria-label="Tambah jumlah"
                                            >+</button>
                                        </div>

                                        <button
                                            type="submit"
                                            class="pd-add-cart"
                                            id="addToCartButton"
                                        >
                                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M3 4h2l2.1 10.1a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 1.9-1.4L21 8H6" />
                                                <circle cx="10" cy="20" r="1" />
                                                <circle cx="18" cy="20" r="1" />
                                            </svg>

                                            <span>Tambah ke keranjang</span>
                                            <span class="pd-add-arrow">↗</span>
                                        </button>

                                    </div>

                                    @error('quantity')
                                        <p class="pd-cart-error">{{ $message }}</p>
                                    @enderror
                                </form>

                            @else

                                <a class="pd-add-cart pd-disabled-action" href="{{ route('home') }}">
                                    <span>Kembali ke beranda</span>
                                    <span class="pd-add-arrow">↗</span>
                                </a>

                            @endif
                        @else

                            <a
                                class="pd-add-cart"
                                href="{{ route('login') }}"
                            >
                                <span>Masuk untuk berbelanja</span>
                                <span class="pd-add-arrow">↗</span>
                            </a>

                            <p class="pd-login-hint">
                                Masuk sebagai pembeli untuk menambahkan produk ke keranjang.
                            </p>

                        @endauth

                    @else

                        <button type="button" class="pd-add-cart is-unavailable" disabled>
                            Stok sedang habis
                        </button>

                    @endif

                    <button
                        type="button"
                        class="pd-share-button"
                        id="shareProduct"
                    >
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle cx="18" cy="5" r="3" />
                            <circle cx="6" cy="12" r="3" />
                            <circle cx="18" cy="19" r="3" />
                            <path d="m8.7 10.7 6.6-4.4M8.7 13.3l6.6 4.4" />
                        </svg>

                        Bagikan produk
                    </button>

                </div>

                <div class="pd-trust-note">
                    <span class="pd-trust-icon">✓</span>
                    <p>
                        Kamu turut mendukung pertumbuhan usaha lokal
                        melalui PasarLokal.
                    </p>
                </div>

            </div>

        </section>

        {{-- PRODUCT DESCRIPTION --}}
        <section class="pd-description-section" data-reveal>

            <div class="pd-section-heading">
                <div>
                    <span class="pd-section-kicker">KENALI PRODUKNYA</span>
                    <h2>Tentang produk ini</h2>
                </div>

                <span class="pd-heading-decoration">✳</span>
            </div>

            <div class="pd-description-content">
                @if ($description !== '')
                    <p>{{ $description }}</p>
                @else
                    <p>
                        Informasi lengkap mengenai produk ini belum tersedia.
                        Silakan hubungi penjual melalui informasi toko yang tersedia.
                    </p>
                @endif
            </div>

            <div class="pd-description-meta">
                <div>
                    <span>KATEGORI</span>
                    <strong>{{ $category->nama ?? 'Produk lokal' }}</strong>
                </div>

                <div>
                    <span>KETERSEDIAAN</span>
                    <strong>{{ $stock > 0 ? 'Tersedia' : 'Habis' }}</strong>
                </div>

                <div>
                    <span>ASAL PRODUK</span>
                    <strong>UMKM Indonesia</strong>
                </div>
            </div>

        </section>

        {{-- REVIEWS --}}
        <section class="pd-reviews-section" id="ulasan" data-reveal>

            <div class="pd-section-heading">
                <div>
                    <span class="pd-section-kicker">CERITA PEMBELI</span>
                    <h2>Ulasan produk</h2>
                </div>

                <div class="pd-reviews-summary">
                    <span>★</span>

                    <strong>
                        {{ $ratingAverage > 0 ? number_format($ratingAverage, 1, ',', '.') : '—' }}
                    </strong>

                    <small>{{ $reviewCount }} ulasan</small>
                </div>
            </div>

            @if ($reviewCount > 0)

                <div class="pd-review-grid">

                    @foreach ($reviews->take(6) as $review)
                        @php
                            $reviewComment = data_get($review, 'komentar')
                                ?? data_get($review, 'ulasan')
                                ?? data_get($review, 'comment')
                                ?? data_get($review, 'review')
                                ?? '';

                            $reviewUser = data_get($review, 'user.name')
                                ?? 'Pembeli PasarLokal';
                        @endphp

                        <article class="pd-review-card">

                            <div class="pd-review-top">
                                <div class="pd-review-avatar">
                                    {{ \Illuminate\Support\Str::upper(
                                        \Illuminate\Support\Str::substr($reviewUser, 0, 1)
                                    ) }}
                                </div>

                                <div class="pd-review-author">
                                    <strong>{{ $reviewUser }}</strong>

                                    @if ($review->created_at)
                                        <span>{{ $review->created_at->format('d M Y') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="pd-review-stars" aria-label="Rating {{ (int) ($review->rating ?? 0) }} dari 5">
                                @for ($i = 1; $i <= 5; $i++)
                                    <span class="{{ (int) ($review->rating ?? 0) >= $i ? 'is-filled' : '' }}">★</span>
                                @endfor
                            </div>

                            @if ($reviewComment !== '')
                                <p>{{ $reviewComment }}</p>
                            @else
                                <p>Pembeli telah memberikan penilaian untuk produk ini.</p>
                            @endif

                        </article>
                    @endforeach

                </div>

            @else

                <div class="pd-empty-reviews">
                    <span class="pd-empty-review-icon">✳</span>

                    <h3>Jadilah yang pertama memberi ulasan</h3>

                    <p>
                        Ulasan pembeli akan membantu orang lain
                        mengenal produk ini lebih baik.
                    </p>

                </div>

            @endif

        </section>

        {{-- BOTTOM CTA --}}
        <section class="pd-bottom-cta" data-reveal>

            <div class="pd-bottom-cta-copy">
                <span>DUKUNG PRODUK LOKAL</span>
                <h2>Setiap pilihan punya arti.</h2>
                <p>Jelajahi produk lokal lainnya dan temukan favoritmu.</p>
            </div>

            <a href="{{ route('home') }}" class="pd-explore-button">
                Jelajahi produk
                <span>↗</span>
            </a>

        </section>

    </div>

    <div id="productToast" class="pd-toast" role="status" aria-live="polite"></div>

</main>
@endsection
