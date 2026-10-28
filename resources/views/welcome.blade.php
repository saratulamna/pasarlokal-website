
@extends('layouts.public')

@section('title', 'PasarLokal — Temukan Produk Lokal Favoritmu')
@section('meta_description', 'Jelajahi produk UMKM lokal, temukan toko favorit, dan dukung pertumbuhan usaha Indonesia.')

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

    $categoryIcons = [
        'makanan-minuman' => '🍜',
        'fashion-lokal' => '🧵',
        'kerajinan' => '🧺',
        'kecantikan' => '🌿',
        'rumah-dekorasi' => '🏡',
        'aksesori' => '🪡',
        'pertanian' => '🌾',
        'oleh-oleh-daerah' => '🎁',
    ];
@endphp

@push('page-styles')
    @vite('resources/css/public/home.css')
@endpush

@push('page-scripts')
    @vite('resources/js/public/home.js')
@endpush

@section('content')

{{-- HERO --}}
<section class="home-hero">
    <div class="home-shell home-hero-grid">

        <div class="home-hero-content" data-reveal>
            <div class="home-eyebrow">
                <span class="home-eyebrow-dot"></span>
                MARKETPLACE PRODUK LOKAL INDONESIA
            </div>

            <h1>
                Temukan yang
                <span>lokal.</span>
                Dukung yang
                <span>hebat.</span>
            </h1>

            <p class="home-hero-description">
                Dari dapur rumahan hingga karya pengrajin,
                temukan produk istimewa langsung dari UMKM Indonesia.
            </p>

            <form
                action="{{ route('home') }}"
                method="GET"
                class="home-search"
                role="search"
            >
                <span class="home-search-icon" aria-hidden="true">⌕</span>

                <input
                    type="search"
                    name="q"
                    value="{{ $search ?? request('q') }}"
                    placeholder="Cari kopi, kerajinan, makanan..."
                    aria-label="Cari produk atau nama toko"
                    maxlength="100"
                >

                @if (request('kategori'))
                    <input
                        type="hidden"
                        name="kategori"
                        value="{{ request('kategori') }}"
                    >
                @endif

                <button type="submit">
                    Cari produk <span>↗</span>
                </button>
            </form>

            <div class="home-hero-quicklinks">
                <span>Populer:</span>

                <a href="{{ route('home', ['q' => 'kopi']) }}#produk">Kopi</a>
                <a href="{{ route('home', ['q' => 'makanan']) }}#produk">Makanan</a>
                <a href="{{ route('home', ['q' => 'kerajinan']) }}#produk">Kerajinan</a>
            </div>
        </div>

        <div class="home-hero-art" data-reveal>
            <div class="home-art-background"></div>

            <div class="home-art-sticker home-art-sticker-top">
                <span>✳</span>
                Bangga produk lokal
            </div>

            <div class="home-art-main-card">
                <div class="home-art-label">LOCAL FINDS</div>

                <div class="home-art-illustration">
                    <div class="home-art-circle"></div>
                    <div class="home-art-bag">🛍️</div>
                    <div class="home-art-leaf">🌿</div>
                    <div class="home-art-sparkle">✦</div>
                </div>

                <div class="home-art-bottom">
                    <div>
                        <small>Dari lokal untuk semua</small>
                        <strong>Produk pilihan, cerita istimewa.</strong>
                    </div>
                    <span>↗</span>
                </div>
            </div>

            <div class="home-art-sticker home-art-sticker-bottom">
                <span>♡</span>
                Dukung UMKM
            </div>
        </div>

    </div>

    <div class="home-hero-decoration home-hero-decoration-one"></div>
    <div class="home-hero-decoration home-hero-decoration-two"></div>
</section>

{{-- STATISTIK --}}
<section class="home-stat-strip">
    <div class="home-shell home-stat-grid">

        <div class="home-stat-intro">
            <span class="home-stat-star">✳</span>
            <p>Gerakan kecil, dampak nyata untuk usaha lokal.</p>
        </div>

        <div class="home-stat-item">
            <strong>{{ number_format($stats['products'] ?? 0) }}<span>+</span></strong>
            <small>Produk tersedia</small>
        </div>

        <div class="home-stat-item">
            <strong>{{ number_format($stats['umkms'] ?? 0) }}<span>+</span></strong>
            <small>Toko UMKM aktif</small>
        </div>

        <div class="home-stat-item">
            <strong>{{ number_format($stats['categories'] ?? 0) }}</strong>
            <small>Kategori pilihan</small>
        </div>

    </div>
</section>

{{-- KATEGORI --}}
<section class="home-section home-categories-section" id="kategori">
    <div class="home-shell">

        <div class="home-section-heading" data-reveal>
            <div>
                <p class="home-section-eyebrow">JELAJAHI PILIHANMU</p>
                <h2>Temukan berdasarkan <span>kategori.</span></h2>
                <p class="home-section-description">
                    Cari produk sesuai kebutuhanmu, dari camilan hingga karya tangan.
                </p>
            </div>

            <a href="{{ route('home') }}#produk" class="home-text-link">
                Lihat semua produk <span>↗</span>
            </a>
        </div>

        <div class="home-category-grid">
            @forelse ($categories as $category)
                <a
                    href="{{ route('home', ['kategori' => $category->slug]) }}#produk"
                    class="home-category-card"
                    data-reveal
                >
                    <div class="home-category-icon">
                        {{ $categoryIcons[$category->slug] ?? '✳' }}
                    </div>

                    <div class="home-category-copy">
                        <strong>{{ $category->nama }}</strong>

                        <span>
                            {{ (int) ($category->active_products_count ?? 0) }}
                            produk
                        </span>
                    </div>

                    <span class="home-category-arrow">↗</span>
                </a>
            @empty
                <div class="home-empty-state">
                    <span>✳</span>
                    <strong>Kategori sedang disiapkan.</strong>
                    <p>Kategori akan muncul setelah tersedia dan aktif.</p>
                </div>
            @endforelse
        </div>

    </div>
</section>

{{-- PRODUK UNGGULAN --}}
<section class="home-section home-products-section" id="produk">
    <div class="home-shell">

        <div class="home-section-heading" data-reveal>
            <div>
                <p class="home-section-eyebrow">PILIHAN KOMUNITAS LOKAL</p>
                <h2>Temuan yang <span>menarik.</span></h2>

                <p class="home-section-description">
                    @if (!empty($search) || !empty($selectedCategoryName))
                        Hasil produk sesuai pilihan pencarianmu.
                    @else
                        Lihat produk aktif yang siap kamu jelajahi.
                    @endif
                </p>
            </div>
        </div>

        @if (!empty($selectedCategoryName) || !empty($search))
            <div class="home-active-filter">
                <span>Hasil pencarian</span>

                @if (!empty($search))
                    <strong>“{{ $search }}”</strong>
                @endif

                @if (!empty($selectedCategoryName))
                    <strong>{{ $selectedCategoryName }}</strong>
                @endif

                <a href="{{ route('home') }}#produk">Reset ↗</a>
            </div>
        @endif

        <div class="home-product-grid">
            @forelse ($featuredProducts as $product)
                <article class="home-product-card" data-reveal>
                    <a
                        href="{{ route('products.show', $product->slug) }}"
                        class="home-product-image"
                        aria-label="Lihat {{ $product->nama }}"
                    >
                        @if ($product->gambar)
                            <img
                                src="{{ $imageUrl($product->gambar) }}"
                                alt="{{ $product->nama }}"
                                loading="lazy"
                            >
                        @else
                            <div class="home-product-no-image">
                                <span>📦</span>
                                <small>Foto segera hadir</small>
                            </div>
                        @endif

                        <span class="home-product-badge">Pilihan lokal</span>
                    </a>

                    <div class="home-product-info">
                        <div class="home-product-category">
                            {{ $product->category?->nama ?? 'Produk lokal' }}
                        </div>

                        <h3>
                            <a href="{{ route('products.show', $product->slug) }}">
                                {{ $product->nama }}
                            </a>
                        </h3>

                        <a
                            href="{{ route('products.show', $product->slug) }}"
                            class="home-product-store"
                        >
                            {{ $product->umkm?->nama_toko ?? 'UMKM lokal' }} <span>↗</span>
                        </a>

                        <div class="home-product-meta">
                            <span class="home-product-rating">
                                <span>★</span>

                                @if (($product->reviews_count ?? 0) > 0)
                                    {{ number_format((float) $product->reviews_avg_rating, 1) }}
                                @else
                                    Baru
                                @endif
                            </span>

                            <span>{{ number_format((int) ($product->terjual ?? 0)) }} terjual</span>
                        </div>

                        <div class="home-product-bottom">
                            <strong>
                                Rp{{ number_format((float) $product->harga, 0, ',', '.') }}
                            </strong>

                            <a
                                href="{{ route('products.show', $product->slug) }}"
                                class="home-product-buy"
                            >
                                Lihat <span>↗</span>
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="home-empty-state home-empty-products">
                    <span>⌕</span>
                    <strong>Belum ada produk yang cocok.</strong>
                    <p>
                        Coba kata kunci lain atau jelajahi kategori yang tersedia.
                    </p>
                    <a href="{{ route('home') }}">Tampilkan semua produk ↗</a>
                </div>
            @endforelse
        </div>

    </div>
</section>

{{-- PRODUK TERBARU --}}
<section class="home-section home-latest-section">
    <div class="home-shell">

        <div class="home-section-heading" data-reveal>
            <div>
                <p class="home-section-eyebrow">BARU DI PASARLOKAL</p>
                <h2>Baru datang, <span>baru ditemukan.</span></h2>
                <p class="home-section-description">
                    Jelajahi produk yang baru ditambahkan oleh para penjual lokal.
                </p>
            </div>
        </div>

        <div class="home-latest-grid">
            @forelse ($latestProducts as $product)
                <a
                    href="{{ route('products.show', $product->slug) }}"
                    class="home-latest-card"
                    data-reveal
                >
                    <div class="home-latest-image">
                        @if ($product->gambar)
                            <img
                                src="{{ $imageUrl($product->gambar) }}"
                                alt="{{ $product->nama }}"
                                loading="lazy"
                            >
                        @else
                            <span>📦</span>
                        @endif
                    </div>

                    <div class="home-latest-info">
                        <small>{{ $product->category?->nama ?? 'Produk lokal' }}</small>
                        <strong>{{ $product->nama }}</strong>
                        <span>{{ $product->umkm?->nama_toko ?? 'UMKM lokal' }}</span>
                        <b>Rp{{ number_format((float) $product->harga, 0, ',', '.') }}</b>
                    </div>

                    <span class="home-latest-arrow">↗</span>
                </a>
            @empty
                <div class="home-empty-state">
                    <span>✳</span>
                    <strong>Produk terbaru akan tampil di sini.</strong>
                </div>
            @endforelse
        </div>

    </div>
</section>

{{-- CTA PENJUAL --}}
<section class="home-seller-cta">
    <div class="home-shell home-seller-card" data-reveal>
        <div class="home-seller-copy">
            <p class="home-section-eyebrow">PUNYA USAHA LOKAL?</p>

            <h2>
                Ceritakan produkmu.
                <span>Temukan pelanggan baru.</span>
            </h2>

            <p>
                Bergabung di PasarLokal dan bawa produk UMKM-mu 
                lebih dekat dengan pembeli.
            </p>

            @guest
                <a href="{{ route('register') }}" class="home-seller-button">
                    Mulai sebagai penjual <span>↗</span>
                </a>
            @else
                @if (auth()->user()->role === 'umkm')
                    <a href="{{ route('umkm.dashboard') }}" class="home-seller-button">
                        Buka ruang penjual <span>↗</span>
                    </a>
                @else
                    <a href="{{ route('home') }}" class="home-seller-button">
                        Jelajahi produk <span>↗</span>
                    </a>
                @endif
            @endguest
        </div>

        <div class="home-seller-art" aria-hidden="true">
            <div class="home-seller-circle"></div>
            <span class="home-seller-box">📦</span>
            <span class="home-seller-sparkle">✳</span>
            <span class="home-seller-heart">♡</span>
        </div>
    </div>
</section>

@endsection
