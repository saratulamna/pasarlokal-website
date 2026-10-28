@extends('layouts.buyer')

@push('page-styles')
    @vite('resources/css/pages/pembeli/cart.css')
@endpush

@section('page-title', 'Keranjang Belanja')

@section('content')
@php
    /*
    |--------------------------------------------------------------------------
    | Ambil item keranjang dari data yang dikirim controller
    |--------------------------------------------------------------------------
    */
    $rawCartItems = collect();

    if (isset($cartItems)) {
        $rawCartItems = collect($cartItems);
    } elseif (isset($cart) && $cart) {
        $rawCartItems = collect($cart->items ?? []);
    } elseif (isset($items)) {
        $rawCartItems = collect($items);
    }

    $cartItems = $rawCartItems
        ->filter(fn ($item) => isset($item->product) && $item->product)
        ->values();

    $totalItems = $cartItems->sum(
        fn ($item) => (int) ($item->quantity ?? $item->jumlah ?? 1)
    );

    $subtotal = $cartItems->sum(function ($item) {
        $quantity = (int) ($item->quantity ?? $item->jumlah ?? 1);
        $price = (int) ($item->product->harga ?? 0);

        return $quantity * $price;
    });

    $shopCount = $cartItems
        ->pluck('product.umkm_id')
        ->filter()
        ->unique()
        ->count();

    $canCheckout = $cartItems->isNotEmpty()
        && $cartItems->every(
            fn ($item) => (int) ($item->product->stok ?? 0) > 0
        );
@endphp

<div class="cart-page">

    {{-- HEADER HALAMAN --}}
    <section class="ct-hero">
        <div class="ct-hero-copy">
            <div class="ct-eyebrow">
                <span class="ct-eyebrow-dot"></span>
                BELANJA PRODUK LOKAL
            </div>

            <h1>
                Keranjang<br>
                <span>belanjamu.</span>
            </h1>

            <p>
                Sedikit lagi produk lokal pilihanmu siap menemani hari.
                Cek kembali belanjaanmu sebelum checkout, ya!
            </p>

            <div class="ct-hero-meta">
                <span class="ct-meta-icon">✳</span>
                Mendukung UMKM, mulai dari keranjang.
            </div>
        </div>

        <div class="ct-hero-art" aria-hidden="true">
            <div class="ct-art-circle ct-art-circle-one"></div>
            <div class="ct-art-circle ct-art-circle-two"></div>

            <div class="ct-art-note">
                <span class="ct-note-spark">✳</span>
                <span>LOCAL<br>IS LOVELY</span>
            </div>

            <div class="ct-art-bag">
                <div class="ct-bag-handle"></div>
                <div class="ct-bag-body">
                    <span class="ct-bag-brand">PL.</span>
                    <span class="ct-bag-line"></span>
                    <span class="ct-bag-line ct-bag-line-short"></span>
                    <span class="ct-bag-flower">✳</span>
                </div>
            </div>

            <div class="ct-art-sticker">
                <span>GOOD</span>
                <strong>CHOICE!</strong>
            </div>
        </div>
    </section>

    {{-- BREADCRUMB --}}
    <nav class="ct-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('home') }}">Beranda</a>
        <span>/</span>
        <span>Keranjang</span>
    </nav>

    @if (session('success'))
        <div class="ct-alert ct-alert-success" role="status">
            <span class="ct-alert-icon">✓</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="ct-alert ct-alert-error" role="alert">
            <span class="ct-alert-icon">!</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="ct-alert ct-alert-error" role="alert">
            <span class="ct-alert-icon">!</span>
            <div>
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    @if ($cartItems->isEmpty())

        {{-- KERANJANG KOSONG --}}
        <section class="ct-empty">
            <div class="ct-empty-illustration" aria-hidden="true">
                <div class="ct-empty-sun"></div>
                <div class="ct-empty-bag">
                    <div class="ct-empty-handle"></div>
                    <div class="ct-empty-bag-face">
                        <span>PL.</span>
                        <span class="ct-empty-flower">✳</span>
                    </div>
                </div>
                <span class="ct-empty-spark ct-spark-one">✳</span>
                <span class="ct-empty-spark ct-spark-two">✦</span>
                <span class="ct-empty-spark ct-spark-three">·</span>
            </div>

            <span class="ct-empty-label">BELUM ADA BELANJAAN</span>

            <h2>Wah, keranjangmu<br>masih kosong!</h2>

            <p>
                Banyak produk lokal menarik yang menunggu untuk kamu temukan.
                Yuk, mulai jelajahi karya UMKM Indonesia.
            </p>

            <a href="{{ route('home') }}" class="ct-primary-button">
                <span>Mulai belanja</span>
                <span class="ct-button-arrow">↗</span>
            </a>

            <div class="ct-empty-footnote">
                <span>✳</span>
                Temukan favoritmu, dukung usaha lokal.
            </div>
        </section>

    @else

        {{-- KERANJANG BERISI PRODUK --}}
        <div class="ct-section-heading">
            <div>
                <span class="ct-section-kicker">PESANANMU</span>
                <h2>Siap masuk keranjang? <span>✳</span></h2>
            </div>

            <div class="ct-item-count">
                <span class="ct-count-number">{{ $totalItems }}</span>
                <span>{{ $totalItems === 1 ? 'item' : 'item' }} dipilih</span>
            </div>
        </div>

        <div class="ct-layout">

            {{-- DAFTAR ITEM --}}
            <section class="ct-items-column">
                <div class="ct-list-topline">
                    <span>PRODUK PILIHANMU</span>
                    <span>{{ $cartItems->count() }} PRODUK</span>
                </div>

                <div class="ct-items-list">
                    @foreach ($cartItems as $item)
                        @php
                            $product = $item->product;
                            $quantity = (int) ($item->quantity ?? $item->jumlah ?? 1);
                            $price = (int) ($product->harga ?? 0);
                            $stock = (int) ($product->stok ?? 0);
                            $lineTotal = $price * $quantity;

                            $productImage = $product->gambar ?? null;

                            if ($productImage && !\Illuminate\Support\Str::startsWith($productImage, ['http://', 'https://'])) {
                                $productImage = asset('storage/' . ltrim($productImage, '/'));
                            }

                            $storeName = $product->umkm?->nama_toko
                                ?? $product->umkm?->nama_usaha
                                ?? $product->umkm?->nama
                                ?? 'UMKM Lokal';
                        @endphp

                        <article class="ct-item-card">
                            <a
                                href="{{ route('products.show', ['product' => $product->slug]) }}"
                                class="ct-product-image"
                                aria-label="Lihat {{ $product->nama }}"
                            >
                                @if ($productImage)
                                    <img
                                        src="{{ $productImage }}"
                                        alt="{{ $product->nama }}"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="ct-image-placeholder">
                                        <span>PL.</span>
                                        <span class="ct-placeholder-flower">✳</span>
                                    </div>
                                @endif
                            </a>

                            <div class="ct-item-info">
                                <div class="ct-item-store">
                                    <span class="ct-store-dot"></span>
                                    {{ $storeName }}
                                </div>

                                <a
                                    href="{{ route('products.show', ['product' => $product->slug]) }}"
                                    class="ct-product-name"
                                >
                                    {{ $product->nama }}
                                </a>

                                <div class="ct-product-unit">
                                    <span class="ct-unit-dot"></span>
                                    Produk lokal pilihan
                                </div>

                                <div class="ct-item-mobile-price">
                                    Rp{{ number_format($price, 0, ',', '.') }}
                                </div>

                                @if ($stock < 1)
                                    <span class="ct-stock-label ct-stock-empty">
                                        Stok habis
                                    </span>
                                @elseif ($stock <= 5)
                                    <span class="ct-stock-label ct-stock-low">
                                        Sisa {{ $stock }} stok
                                    </span>
                                @else
                                    <span class="ct-stock-label ct-stock-ready">
                                        <span>✓</span> Tersedia
                                    </span>
                                @endif
                            </div>

                            <div class="ct-item-actions">
                                <div class="ct-item-price">
                                    <span class="ct-price-caption">HARGA</span>
                                    <strong>Rp{{ number_format($price, 0, ',', '.') }}</strong>
                                </div>

                                @if ($stock > 0)
                                    <form
                                        action="{{ route('cart.update', ['cartItem' => $item->id]) }}"
                                        method="POST"
                                        class="ct-quantity-form"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <label
                                            for="quantity-{{ $item->id }}"
                                            class="ct-quantity-label"
                                        >
                                            JUMLAH
                                        </label>

                                        <div class="ct-quantity-control">
                                            <input
                                                id="quantity-{{ $item->id }}"
                                                type="number"
                                                name="quantity"
                                                value="{{ $quantity }}"
                                                min="1"
                                                max="{{ $stock }}"
                                                inputmode="numeric"
                                                required
                                                aria-label="Jumlah {{ $product->nama }}"
                                            >
                                            <button type="submit" class="ct-update-button">
                                                Simpan
                                            </button>
                                        </div>
                                    </form>
                                @else
                                    <div class="ct-unavailable-note">
                                        Tidak tersedia
                                    </div>
                                @endif

                                <div class="ct-line-total">
                                    <span>Subtotal</span>
                                    <strong>Rp{{ number_format($lineTotal, 0, ',', '.') }}</strong>
                                </div>

                                <form
                                    action="{{ route('cart.destroy', ['cartItem' => $item->id]) }}"
                                    method="POST"
                                    class="ct-remove-form"
                                    onsubmit="return confirm('Hapus {{ addslashes($product->nama) }} dari keranjang?')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="ct-remove-button">
                                        <span aria-hidden="true">×</span>
                                        Hapus produk
                                    </button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>

                <a href="{{ route('home') }}" class="ct-continue-link">
                    <span>←</span>
                    Lanjut menjelajahi produk
                </a>
            </section>

            {{-- RINGKASAN BELANJA --}}
            <aside class="ct-summary-column">
                <div class="ct-summary-card">
                    <div class="ct-summary-header">
                        <div>
                            <span class="ct-summary-kicker">SEDIKIT LAGI</span>
                            <h2>Ringkasan<br>belanja.</h2>
                        </div>

                        <div class="ct-summary-icon" aria-hidden="true">↗</div>
                    </div>

                    <div class="ct-summary-items">
                        <div class="ct-summary-row">
                            <span>Jumlah produk</span>
                            <strong>{{ $totalItems }} item</strong>
                        </div>

                        <div class="ct-summary-row">
                            <span>Produk berbeda</span>
                            <strong>{{ $cartItems->count() }} produk</strong>
                        </div>

                        <div class="ct-summary-row">
                            <span>Asal UMKM</span>
                            <strong>{{ max(1, $shopCount) }} toko</strong>
                        </div>
                    </div>

                    <div class="ct-summary-divider">
                        <span></span>
                        <span>✳</span>
                        <span></span>
                    </div>

                    <div class="ct-total-row">
                        <span>Subtotal produk</span>
                        <strong>Rp{{ number_format($subtotal, 0, ',', '.') }}</strong>
                    </div>

                    <div class="ct-shipping-note">
                        <span class="ct-shipping-icon">✦</span>
                        <p>
                            Ongkir dihitung saat checkout berdasarkan pesanan dan toko.
                        </p>
                    </div>

                    @if ($canCheckout)
                        <a href="{{ route('checkout.index') }}" class="ct-checkout-button">
                            <span>Lanjut ke checkout</span>
                            <span class="ct-checkout-arrow">↗</span>
                        </a>
                    @else
                        <button type="button" class="ct-checkout-button ct-checkout-disabled" disabled>
                            <span>Periksa stok produk</span>
                            <span class="ct-checkout-arrow">!</span>
                        </button>
                        <p class="ct-checkout-hint">
                            Hapus produk yang stoknya habis sebelum melanjutkan.
                        </p>
                    @endif

                    <div class="ct-secure-note">
                        <span>✓</span>
                        Periksa kembali pesanan sebelum membayar.
                    </div>
                </div>

                <div class="ct-local-note">
                    <div class="ct-local-note-icon">✳</div>
                    <div>
                        <strong>Belanja punya arti.</strong>
                        <p>Setiap pesanan ikut mendukung perjalanan UMKM lokal.</p>
                    </div>
                </div>
            </aside>
        </div>
    @endif

    <footer class="ct-footer-note">
        <span class="ct-footer-mark">PL.</span>
        <span>Dibuat dengan semangat untuk produk lokal Indonesia.</span>
        <span class="ct-footer-flower">✳</span>
    </footer>
</div>
@endsection