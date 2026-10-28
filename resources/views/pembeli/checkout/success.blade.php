@extends('layouts.buyer')

@push('page-styles')
    @vite('resources/css/pages/pembeli/checkout-success.css')
@endpush

@section('page-title', 'Checkout Berhasil')

@section('content')
@php
    /*
    |--------------------------------------------------------------------------
    | Data pesanan bersifat opsional.
    | Halaman tetap dapat ditampilkan apabila controller hanya melakukan redirect.
    |--------------------------------------------------------------------------
    */
    $successOrders = collect();

    if (isset($orders)) {
        $successOrders = collect($orders);
    } elseif (isset($order) && $order) {
        $successOrders = collect([$order]);
    }

    $successOrders = $successOrders
        ->filter(fn ($item) => is_object($item))
        ->values();

    $successOrderTotal = $successOrders->sum(function ($item) {
        return (float) (
            $item->total_harga
            ?? $item->total
            ?? $item->grand_total
            ?? 0
        );
    });

    $successItemCount = $successOrders->count();

    $paymentLabel = function ($method) {
        return match ($method) {
            'transfer_bank' => 'Transfer bank',
            'cod' => 'Bayar di tempat (COD)',
            default => 'Lihat detail pesanan',
        };
    };
@endphp

<div class="cs-page">

    {{-- =====================================================
         SUCCESS HERO
    ====================================================== --}}
    <section class="cs-hero">
        <div class="cs-hero-decoration cs-decoration-one" aria-hidden="true"></div>
        <div class="cs-hero-decoration cs-decoration-two" aria-hidden="true"></div>

        <div class="cs-hero-copy">
            <div class="cs-status-label">
                <span class="cs-status-dot"></span>
                CHECKOUT SELESAI
            </div>

            <div class="cs-success-mark" aria-hidden="true">
                <svg viewBox="0 0 48 48" fill="none">
                    <path
                        d="M12.5 24.5L20.5 32L36 15.5"
                        stroke="currentColor"
                        stroke-width="4"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </div>

            <span class="cs-overline">TERIMA KASIH SUDAH BELANJA LOKAL</span>

            <h1>
                Yeay, pesananmu<br>
                <span>berhasil dibuat!</span>
            </h1>

            <p>
                Terima kasih sudah mempercayai PasarLokal.
                Pesananmu telah memasuki tahap berikutnya.
                Simpan halaman ini dan cek detail pesanan untuk informasi selanjutnya.
            </p>

            <div class="cs-hero-actions">
                <a href="{{ route('pembeli.orders.index') }}" class="cs-button-primary">
                    <span>Lihat pesanan saya</span>
                    <span class="cs-button-arrow">↗</span>
                </a>

                <a href="{{ route('home') }}" class="cs-button-secondary">
                    Lanjut belanja
                </a>
            </div>
        </div>

        <div class="cs-hero-art" aria-hidden="true">
            <div class="cs-art-orbit cs-orbit-one"></div>
            <div class="cs-art-orbit cs-orbit-two"></div>
            <div class="cs-art-orbit cs-orbit-three"></div>

            <div class="cs-art-confetti cs-confetti-one">✳</div>
            <div class="cs-art-confetti cs-confetti-two">✦</div>
            <div class="cs-art-confetti cs-confetti-three">✳</div>

            <div class="cs-art-card">
                <div class="cs-art-card-top">
                    <span class="cs-art-brand">PL.</span>
                    <span class="cs-art-check">✓</span>
                </div>

                <div class="cs-art-lines">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>

                <div class="cs-art-card-bottom">
                    <span>MADE WITH CARE</span>
                    <span>INDONESIA ✳</span>
                </div>
            </div>

            <div class="cs-art-sticker">
                <span>LOCAL</span>
                <strong>LOVE!</strong>
            </div>

            <div class="cs-art-caption">
                <span class="cs-caption-star">✳</span>
                <div>
                    <strong>Terima kasih!</strong>
                    <small>Sudah mendukung UMKM</small>
                </div>
            </div>
        </div>
    </section>

    {{-- =====================================================
         ORDER CONFIRMATION STRIP
    ====================================================== --}}
    <section class="cs-confirmation-strip">
        <div class="cs-confirmation-icon">✓</div>

        <div class="cs-confirmation-copy">
            <strong>Pesanan tersimpan di PasarLokal.</strong>
            <span>
                Lihat riwayat pesanan untuk mengecek status, pembayaran,
                dan informasi pesananmu.
            </span>
        </div>

        <a href="{{ route('pembeli.orders.index') }}" class="cs-confirmation-link">
            Buka riwayat <span>↗</span>
        </a>
    </section>

    {{-- =====================================================
         OPTIONAL ORDER DETAILS
    ====================================================== --}}
    @if ($successOrders->isNotEmpty())
        <section class="cs-order-section">
            <div class="cs-section-heading">
                <div>
                    <span class="cs-section-kicker">ORDER OVERVIEW</span>
                    <h2>Ringkasan <span>pesanan.</span></h2>
                </div>

                <span class="cs-order-count">
                    {{ $successOrderTotal > 0 ? 'TOTAL PESANAN' : 'PESANAN TERCATAT' }}
                </span>
            </div>

            <div class="cs-order-card">
                <div class="cs-order-card-heading">
                    <div>
                        <span class="cs-order-heading-label">KONFIRMASI PESANAN</span>
                        <h3>
                            {{ $successItemCount }}
                            {{ $successItemCount === 1 ? 'pesanan' : 'pesanan' }}
                            berhasil dibuat
                        </h3>
                    </div>

                    <span class="cs-order-status">
                        <span></span>
                        Tercatat
                    </span>
                </div>

                <div class="cs-order-list">
                    @foreach ($successOrders as $successOrder)
                        @php
                            $orderAmount = (float) (
                                $successOrder->total_harga
                                ?? $successOrder->total
                                ?? $successOrder->grand_total
                                ?? 0
                            );

                            $orderPaymentMethod = $successOrder->payment_method ?? null;
                            $orderPaymentStatus = $successOrder->payment_status ?? null;
                        @endphp

                        <article class="cs-order-row">
                            <div class="cs-order-symbol">PL.</div>

                            <div class="cs-order-information">
                                <strong>
                                    Pesanan #{{ $successOrder->id ?? '—' }}
                                </strong>

                                <span>
                                    {{ $paymentLabel($orderPaymentMethod) }}
                                </span>

                                @if ($orderPaymentStatus)
                                    <small>
                                        Status pembayaran:
                                        {{ str_replace('_', ' ', ucfirst($orderPaymentStatus)) }}
                                    </small>
                                @endif
                            </div>

                            <div class="cs-order-amount">
                                <span>TOTAL</span>
                                <strong>
                                    Rp{{ number_format($orderAmount, 0, ',', '.') }}
                                </strong>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if ($successItemCount > 1)
                    <div class="cs-order-total">
                        <span>Total dari pesanan yang ditampilkan</span>
                        <strong>
                            Rp{{ number_format($successOrderTotal, 0, ',', '.') }}
                        </strong>
                    </div>
                @endif

                <div class="cs-order-card-footer">
                    <span>✳</span>
                    Detail lengkap tersedia di halaman riwayat pesanan.
                </div>
            </div>
        </section>
    @endif

    {{-- =====================================================
         WHAT HAPPENS NEXT
    ====================================================== --}}
    <section class="cs-next-section">
        <div class="cs-section-heading">
            <div>
                <span class="cs-section-kicker">WHAT HAPPENS NEXT?</span>
                <h2>Selanjutnya, <span>apa ya?</span></h2>
                <p>
                    Ikuti tiga langkah ini supaya proses pesananmu tetap lancar.
                </p>
            </div>
        </div>

        <div class="cs-next-grid">
            <article class="cs-next-card cs-next-card-one">
                <div class="cs-next-card-top">
                    <span class="cs-next-number">01</span>
                    <span class="cs-next-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <rect
                                x="4" y="3" width="16" height="18" rx="2"
                                stroke="currentColor" stroke-width="1.7"
                            />
                            <path
                                d="M8 8h8M8 12h8M8 16h4"
                                stroke="currentColor" stroke-width="1.7"
                                stroke-linecap="round"
                            />
                        </svg>
                    </span>
                </div>

                <span class="cs-next-label">LANGKAH PERTAMA</span>
                <h3>Cek detail pesanan</h3>
                <p>
                    Buka riwayat pesanan untuk melihat nomor, rincian, dan status
                    pesanan yang sudah dibuat.
                </p>

                <a href="{{ route('pembeli.orders.index') }}" class="cs-next-link">
                    Lihat pesanan <span>↗</span>
                </a>
            </article>

            <article class="cs-next-card cs-next-card-two">
                <div class="cs-next-card-top">
                    <span class="cs-next-number">02</span>
                    <span class="cs-next-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <rect
                                x="3" y="5" width="18" height="14" rx="2"
                                stroke="currentColor" stroke-width="1.7"
                            />
                            <path
                                d="M3 10h18M7 15h3"
                                stroke="currentColor" stroke-width="1.7"
                                stroke-linecap="round"
                            />
                        </svg>
                    </span>
                </div>

                <span class="cs-next-label">LANGKAH KEDUA</span>
                <h3>Periksa pembayaran</h3>
                <p>
                    Bila memilih transfer bank, buka detail pembayaran untuk
                    membaca instruksi dan mengirim bukti bila diperlukan.
                </p>

                <a href="{{ route('pembeli.orders.index') }}" class="cs-next-link">
                    Cek status pembayaran <span>↗</span>
                </a>
            </article>

            <article class="cs-next-card cs-next-card-three">
                <div class="cs-next-card-top">
                    <span class="cs-next-number">03</span>
                    <span class="cs-next-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path
                                d="M3 7h11v10H3zM14 10h4l3 3v4h-7z"
                                stroke="currentColor" stroke-width="1.7"
                                stroke-linecap="round" stroke-linejoin="round"
                            />
                            <circle
                                cx="7.5" cy="18" r="2"
                                stroke="currentColor" stroke-width="1.7"
                            />
                            <circle
                                cx="17.5" cy="18" r="2"
                                stroke="currentColor" stroke-width="1.7"
                            />
                        </svg>
                    </span>
                </div>

                <span class="cs-next-label">LANGKAH KETIGA</span>
                <h3>Pantau perkembangan</h3>
                <p>
                    Kunjungi riwayat pesanan secara berkala untuk melihat
                    perkembangan status pesananmu.
                </p>

                <a href="{{ route('pembeli.orders.index') }}" class="cs-next-link">
                    Pantau pesanan <span>↗</span>
                </a>
            </article>
        </div>
    </section>

    {{-- =====================================================
         PAYMENT REMINDER
    ====================================================== --}}
    <section class="cs-payment-reminder">
        <div class="cs-reminder-icon">i</div>

        <div class="cs-reminder-copy">
            <strong>Ingat, periksa instruksi pembayaran terlebih dahulu.</strong>
            <p>
                Gunakan hanya informasi pembayaran yang tampil di aplikasi.
                Bila detail rekening menyebut simulasi atau demo, jangan mengirim
                uang sungguhan ke rekening tersebut.
            </p>
        </div>

        <a href="{{ route('pembeli.orders.index') }}" class="cs-reminder-link">
            Cek sekarang <span>↗</span>
        </a>
    </section>

    {{-- =====================================================
         CLOSING BANNER
    ====================================================== --}}
    <section class="cs-closing">
        <div class="cs-closing-copy">
            <span class="cs-closing-kicker">THANK YOU FOR SHOPPING LOCAL</span>

            <h2>
                Belanja kecil,<br>
                <span>dampak yang berarti.</span>
            </h2>

            <p>
                Terima kasih sudah ikut memberi ruang bagi produk lokal
                untuk tumbuh dan dikenal lebih luas.
            </p>

            <a href="{{ route('home') }}" class="cs-closing-button">
                Temukan produk lainnya <span>↗</span>
            </a>
        </div>

        <div class="cs-closing-art" aria-hidden="true">
            <div class="cs-closing-circle"></div>
            <div class="cs-closing-star">✳</div>

            <div class="cs-closing-note">
                <strong>LOCAL</strong>
                <span>IS SPECIAL.</span>
            </div>

            <div class="cs-closing-badge">PL.</div>
        </div>
    </section>

    <footer class="cs-footer">
        <span class="cs-footer-brand">PL.</span>
        <span>PasarLokal · Cerita lokal, pilihan istimewa.</span>
        <span class="cs-footer-flower">✳</span>
    </footer>
</div>
@endsection