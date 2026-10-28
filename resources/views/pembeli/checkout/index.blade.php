@extends('layouts.buyer')

@push('page-styles')
    @vite('resources/css/pages/pembeli/checkout.css')
@endpush

@section('page-title', 'Checkout Pesanan')

@section('content')
@php
    $checkoutItems = collect(
        $cartItems ?? (($cart ?? null)?->items ?? [])
    )->filter(fn ($item) => isset($item->product) && $item->product);

    $itemCount = $checkoutItems->sum(
        fn ($item) => (int) ($item->quantity ?? $item->jumlah ?? 1)
    );

    $calculatedSubtotal = $checkoutItems->sum(function ($item) {
        return (int) ($item->product->harga ?? 0)
            * (int) ($item->quantity ?? $item->jumlah ?? 1);
    });

    $checkoutSubtotal = isset($subtotal)
        ? (float) $subtotal
        : $calculatedSubtotal;

    $checkoutShopCount = $checkoutItems
        ->pluck('product.umkm_id')
        ->filter()
        ->unique()
        ->count();

    $checkoutShopCount = $checkoutItems->isEmpty()
        ? 0
        : max(1, $checkoutShopCount);

    $checkoutShipping = isset($shipping)
        ? (float) $shipping
        : $checkoutShopCount * 10000;

    $checkoutGrandTotal = isset($total)
        ? (float) $total
        : $checkoutSubtotal + $checkoutShipping;

    $buyer = auth()->user();

    $recipientName = old('nama_penerima', $buyer->name ?? '');
    $recipientPhone = old('nomor_telepon', $buyer->phone ?? '');
    $recipientAddress = old('alamat_pengiriman', '');

    $selectedPayment = old('payment_method', 'transfer_bank');

    $imageUrl = function ($path) {
        if (!$path) {
            return null;
        }

        if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return asset('storage/' . ltrim($path, '/'));
    };
@endphp

<div class="co-page">

    {{-- HEADER --}}
    <section class="co-hero">
        <div class="co-hero-copy">
            <div class="co-eyebrow">
                <span></span>
                SATU LANGKAH LAGI
            </div>

            <h1>
                Saatnya<br>
                <span>jadi milikmu.</span>
            </h1>

            <p>
                Periksa detail pengiriman dan pilihan pembayaranmu.
                Kami bantu membuat proses belanja produk lokal terasa sederhana.
            </p>

            <div class="co-hero-foot">
                <span class="co-hero-star">✳</span>
                <span>Belanja lokal, dampak nyata.</span>
            </div>
        </div>

        <div class="co-hero-art" aria-hidden="true">
            <div class="co-art-ring co-art-ring-one"></div>
            <div class="co-art-ring co-art-ring-two"></div>

            <div class="co-art-card">
                <span class="co-art-card-mark">PL.</span>
                <span class="co-art-card-line"></span>
                <span class="co-art-card-line short"></span>
                <span class="co-art-card-star">✳</span>
            </div>

            <div class="co-art-sticker">
                <span>MADE</span>
                <strong>WITH CARE</strong>
                <span>✳</span>
            </div>

            <div class="co-art-orb"></div>
        </div>
    </section>

    {{-- STEP INDICATOR --}}
    <nav class="co-steps" aria-label="Tahapan pesanan">
        <a href="{{ route('cart.index') }}" class="co-step co-step-complete">
            <span class="co-step-number">✓</span>
            <span class="co-step-copy">
                <small>LANGKAH 01</small>
                <strong>Keranjang</strong>
            </span>
        </a>

        <span class="co-step-line co-step-line-active"></span>

        <div class="co-step co-step-current" aria-current="step">
            <span class="co-step-number">02</span>
            <span class="co-step-copy">
                <small>LANGKAH 02</small>
                <strong>Checkout</strong>
            </span>
        </div>

        <span class="co-step-line"></span>

        <div class="co-step co-step-future">
            <span class="co-step-number">03</span>
            <span class="co-step-copy">
                <small>LANGKAH 03</small>
                <strong>Selesai</strong>
            </span>
        </div>
    </nav>

    {{-- BREADCRUMB --}}
    <nav class="co-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('home') }}">Beranda</a>
        <span>/</span>
        <a href="{{ route('cart.index') }}">Keranjang</a>
        <span>/</span>
        <strong>Checkout</strong>
    </nav>

    {{-- FLASH MESSAGE --}}
    @if (session('success'))
        <div class="co-alert co-alert-success" role="status">
            <span class="co-alert-icon">✓</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="co-alert co-alert-error" role="alert">
            <span class="co-alert-icon">!</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="co-alert co-alert-error" role="alert">
            <span class="co-alert-icon">!</span>
            <div>
                <strong>Periksa kembali data checkout.</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if ($checkoutItems->isEmpty())

        {{-- EMPTY CHECKOUT --}}
        <section class="co-empty">
            <div class="co-empty-art" aria-hidden="true">
                <div class="co-empty-sun"></div>
                <div class="co-empty-bag">
                    <span>PL.</span>
                    <span>✳</span>
                </div>
            </div>

            <span class="co-empty-kicker">BELUM ADA PESANAN</span>

            <h2>Keranjangmu masih kosong.</h2>

            <p>
                Tambahkan produk lokal ke keranjang sebelum melanjutkan
                ke proses checkout.
            </p>

            <a href="{{ route('cart.index') }}" class="co-primary-button">
                <span>Kembali ke keranjang</span>
                <span>↗</span>
            </a>
        </section>

    @else

        <div class="co-heading">
            <div>
                <span class="co-section-kicker">REVIEW YOUR ORDER</span>
                <h2>Pastikan semuanya <span>pas.</span></h2>
                <p>Isi informasi di bawah ini sebelum membuat pesanan.</p>
            </div>

            <a href="{{ route('cart.index') }}" class="co-edit-cart">
                <span>←</span>
                Ubah keranjang
            </a>
        </div>

        <form
            action="{{ route('checkout.store') }}"
            method="POST"
            id="checkoutForm"
            class="co-layout"
        >
            @csrf

            {{-- KOLOM KIRI: FORM --}}
            <div class="co-form-column">

                {{-- ALAMAT PENGIRIMAN --}}
                <section class="co-panel">
                    <div class="co-panel-heading">
                        <div class="co-panel-heading-left">
                            <span class="co-panel-number">01</span>
                            <div>
                                <span class="co-panel-eyebrow">DELIVERY DETAILS</span>
                                <h3>Alamat pengiriman</h3>
                                <p>Ke mana pesananmu akan dikirim?</p>
                            </div>
                        </div>

                        <span class="co-heading-symbol" aria-hidden="true">⌖</span>
                    </div>

                    <div class="co-fields-grid">
                        <div class="co-field">
                            <label for="nama_penerima">
                                Nama penerima <span>*</span>
                            </label>

                            <input
                                id="nama_penerima"
                                type="text"
                                name="nama_penerima"
                                value="{{ $recipientName }}"
                                placeholder="Nama lengkap penerima"
                                maxlength="100"
                                autocomplete="name"
                                required
                            >

                            @error('nama_penerima')
                                <small class="co-field-error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="co-field">
                            <label for="nomor_telepon">
                                Nomor telepon <span>*</span>
                            </label>

                            <input
                                id="nomor_telepon"
                                type="tel"
                                name="nomor_telepon"
                                value="{{ $recipientPhone }}"
                                placeholder="Contoh: 081234567890"
                                maxlength="20"
                                autocomplete="tel"
                                inputmode="tel"
                                required
                            >

                            @error('nomor_telepon')
                                <small class="co-field-error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="co-field co-field-full">
                            <label for="alamat_pengiriman">
                                Alamat lengkap <span>*</span>
                            </label>

                            <textarea
                                id="alamat_pengiriman"
                                name="alamat_pengiriman"
                                rows="4"
                                placeholder="Nama jalan, nomor rumah, desa/kelurahan, kecamatan, kota/kabupaten, dan patokan alamat."
                                maxlength="1000"
                                autocomplete="street-address"
                                required
                            >{{ $recipientAddress }}</textarea>

                            <div class="co-field-foot">
                                <span>Pastikan alamat dapat ditemukan kurir.</span>
                                <span class="co-char-count" data-count-for="alamat_pengiriman">0/1000</span>
                            </div>

                            @error('alamat_pengiriman')
                                <small class="co-field-error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="co-field co-field-full">
                            <label for="catatan">
                                Catatan untuk penjual
                                <span class="co-optional">(opsional)</span>
                            </label>

                            <textarea
                                id="catatan"
                                name="catatan"
                                rows="2"
                                placeholder="Contoh: Tolong kemas dengan rapi."
                                maxlength="500"
                            >{{ old('catatan') }}</textarea>

                            <div class="co-field-foot">
                                <span>Catatan tambahan untuk pesananmu.</span>
                                <span class="co-char-count" data-count-for="catatan">0/500</span>
                            </div>

                            @error('catatan')
                                <small class="co-field-error">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </section>

                {{-- METODE PEMBAYARAN --}}
                <section class="co-panel">
                    <div class="co-panel-heading">
                        <div class="co-panel-heading-left">
                            <span class="co-panel-number">02</span>
                            <div>
                                <span class="co-panel-eyebrow">PAYMENT METHOD</span>
                                <h3>Pilih pembayaran</h3>
                                <p>Pilih metode yang sesuai untukmu.</p>
                            </div>
                        </div>

                        <span class="co-heading-symbol" aria-hidden="true">↗</span>
                    </div>

                    <div class="co-payment-options">

                        <label class="co-payment-option {{ $selectedPayment === 'transfer_bank' ? 'is-selected' : '' }}">
                            <input
                                type="radio"
                                name="payment_method"
                                value="transfer_bank"
                                {{ $selectedPayment === 'transfer_bank' ? 'checked' : '' }}
                                required
                            >

                            <span class="co-payment-radio"></span>

                            <span class="co-payment-icon co-payment-bank" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M3 9.5h18M5 9.5V19m4-9.5V19m6-9.5V19m4-9.5V19M3 19h18M12 3 2.8 8h18.4L12 3Z"
                                        stroke="currentColor" stroke-width="1.7"
                                        stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>

                            <span class="co-payment-copy">
                                <strong>Transfer bank</strong>
                                <span>Instruksi pembayaran tersedia setelah pesanan dibuat.</span>
                                <small>Verifikasi pembayaran dilakukan secara manual.</small>
                            </span>

                            <span class="co-payment-tag">TRANSFER</span>
                        </label>

                        <label class="co-payment-option {{ $selectedPayment === 'cod' ? 'is-selected' : '' }}">
                            <input
                                type="radio"
                                name="payment_method"
                                value="cod"
                                {{ $selectedPayment === 'cod' ? 'checked' : '' }}
                            >

                            <span class="co-payment-radio"></span>

                            <span class="co-payment-icon co-payment-cod" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <rect x="3" y="5" width="18" height="14" rx="2"
                                        stroke="currentColor" stroke-width="1.7"/>
                                    <circle cx="12" cy="12" r="3"
                                        stroke="currentColor" stroke-width="1.7"/>
                                    <path d="M6.5 8.5h.01M17.5 15.5h.01"
                                        stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
                                </svg>
                            </span>

                            <span class="co-payment-copy">
                                <strong>Bayar di tempat (COD)</strong>
                                <span>Bayar ketika pesanan diterima, jika tersedia.</span>
                                <small>Ikuti konfirmasi pesanan dari sistem.</small>
                            </span>

                            <span class="co-payment-tag co-payment-tag-cod">COD</span>
                        </label>
                    </div>

                    <div class="co-payment-notice">
                        <span class="co-notice-icon">i</span>
                        <p>
                            Jangan melakukan transfer sebelum membaca instruksi
                            pembayaran setelah pesanan dibuat. Jika rekening ditandai
                            sebagai simulasi, jangan mengirim uang sungguhan.
                        </p>
                    </div>

                    @error('payment_method')
                        <small class="co-field-error">{{ $message }}</small>
                    @enderror
                </section>

                <div class="co-bottom-note">
                    <span class="co-bottom-note-icon">✳</span>
                    <p>
                        Dengan membuat pesanan, kamu membantu mendukung usaha dan
                        produk lokal Indonesia.
                    </p>
                </div>
            </div>

            {{-- KOLOM KANAN: RINGKASAN --}}
            <aside class="co-summary-column">
                <section class="co-summary-card">
                    <div class="co-summary-heading">
                        <div>
                            <span class="co-summary-kicker">ORDER SUMMARY</span>
                            <h3>Ringkasan<br>pesanan.</h3>
                        </div>

                        <span class="co-summary-icon" aria-hidden="true">✳</span>
                    </div>

                    <div class="co-summary-product-heading">
                        <span>PRODUK PILIHANMU</span>
                        <span>{{ $itemCount }} ITEM</span>
                    </div>

                    <div class="co-summary-products">
                        @foreach ($checkoutItems as $item)
                            @php
                                $product = $item->product;
                                $quantity = (int) ($item->quantity ?? $item->jumlah ?? 1);
                                $price = (float) ($product->harga ?? 0);
                                $lineTotal = $price * $quantity;

                                $productImage = $imageUrl($product->gambar ?? null);

                                $storeName = $product->umkm?->nama_toko
                                    ?? $product->umkm?->nama_usaha
                                    ?? $product->umkm?->nama
                                    ?? 'UMKM Lokal';
                            @endphp

                            <div class="co-summary-product">
                                <div class="co-summary-product-image">
                                    @if ($productImage)
                                        <img
                                            src="{{ $productImage }}"
                                            alt="{{ $product->nama }}"
                                            loading="lazy"
                                        >
                                    @else
                                        <span>PL.</span>
                                        <span class="co-summary-placeholder-star">✳</span>
                                    @endif

                                    <span class="co-product-quantity">{{ $quantity }}</span>
                                </div>

                                <div class="co-summary-product-info">
                                    <strong>{{ $product->nama }}</strong>
                                    <span>{{ $storeName }}</span>
                                    <small>
                                        {{ $quantity }} × Rp{{ number_format($price, 0, ',', '.') }}
                                    </small>
                                </div>

                                <strong class="co-summary-line-total">
                                    Rp{{ number_format($lineTotal, 0, ',', '.') }}
                                </strong>
                            </div>
                        @endforeach
                    </div>

                    <div class="co-summary-divider">
                        <span></span>
                        <span>✳</span>
                        <span></span>
                    </div>

                    <div class="co-total-lines">
                        <div class="co-total-line">
                            <span>Subtotal produk</span>
                            <strong>Rp{{ number_format($checkoutSubtotal, 0, ',', '.') }}</strong>
                        </div>

                        <div class="co-total-line">
                            <span>
                                Ongkir
                                <small>{{ $checkoutShopCount }} toko × Rp10.000</small>
                            </span>
                            <strong>Rp{{ number_format($checkoutShipping, 0, ',', '.') }}</strong>
                        </div>
                    </div>

                    <div class="co-grand-total">
                        <div>
                            <span>TOTAL PEMBAYARAN</span>
                            <small>Termasuk ongkir yang ditampilkan</small>
                        </div>

                        <strong>Rp{{ number_format($checkoutGrandTotal, 0, ',', '.') }}</strong>
                    </div>

                    <button type="submit" class="co-submit-button" id="coSubmitButton">
                        <span class="co-submit-copy">
                            <strong>Buat pesanan</strong>
                            <small>Periksa datamu sebelum melanjutkan</small>
                        </span>
                        <span class="co-submit-arrow">↗</span>
                    </button>

                    <div class="co-summary-safe">
                        <span>✓</span>
                        <span>Pastikan alamat dan metode pembayaran sudah benar.</span>
                    </div>

                    <a href="{{ route('cart.index') }}" class="co-summary-back">
                        ← Kembali dan ubah keranjang
                    </a>
                </section>

                <section class="co-summary-promise">
                    <div class="co-promise-mark">PL.</div>
                    <div>
                        <strong>Setiap pesanan punya arti.</strong>
                        <p>Terima kasih telah mendukung perjalanan UMKM lokal.</p>
                    </div>
                </section>
            </aside>
        </form>
    @endif

    <footer class="co-footer">
        <span class="co-footer-logo">PL.</span>
        <span>PasarLokal — Cerita lokal, pilihan istimewa.</span>
        <span class="co-footer-flower">✳</span>
    </footer>
</div>
@endsection
