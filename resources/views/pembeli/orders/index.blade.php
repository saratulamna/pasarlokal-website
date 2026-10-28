@extends('layouts.buyer')

@push('page-styles')
    @vite('resources/css/pages/pembeli/order-history.css')
@endpush

@section('page-title', 'Riwayat Pesanan')

@section('content')
@php
$orderSource = $orders ?? collect();

if (is_object($orderSource) && method_exists($orderSource, 'items')) {
    // Ambil model pesanan asli dari paginator.
    $orderList = collect($orderSource->items());

    $totalOrders = method_exists($orderSource, 'total')
        ? $orderSource->total()
        : $orderList->count();
} else {
    // Jika controller mengirim collection biasa.
    $orderList = collect($orderSource);
    $totalOrders = $orderList->count();
}

    $getStatus = function ($status) {
        $key = strtolower((string) $status);

        return match ($key) {
            'menunggu_pembayaran' => [
                'label' => 'Menunggu pembayaran',
                'class' => 'oh-status-payment',
            ],
            'menunggu_verifikasi' => [
                'label' => 'Menunggu verifikasi',
                'class' => 'oh-status-review',
            ],
            'menunggu_konfirmasi' => [
                'label' => 'Menunggu konfirmasi',
                'class' => 'oh-status-review',
            ],
            'diproses' => [
                'label' => 'Sedang diproses',
                'class' => 'oh-status-process',
            ],
            'dikirim' => [
                'label' => 'Dalam pengiriman',
                'class' => 'oh-status-shipping',
            ],
            'selesai' => [
                'label' => 'Selesai',
                'class' => 'oh-status-success',
            ],
            'dibatalkan' => [
                'label' => 'Dibatalkan',
                'class' => 'oh-status-cancelled',
            ],
            default => [
                'label' => $key !== ''
                    ? ucwords(str_replace('_', ' ', $key))
                    : 'Belum tersedia',
                'class' => 'oh-status-default',
            ],
        };
    };

    $getPaymentLabel = function ($method) {
        return match (strtolower((string) $method)) {
            'transfer_bank' => 'Transfer bank',
            'cod' => 'Bayar di tempat (COD)',
            default => $method
                ? ucwords(str_replace('_', ' ', (string) $method))
                : 'Belum ditentukan',
        };
    };

    $getOrderTotal = function ($order) {
        return (float) (
            $order->total_harga
            ?? $order->total
            ?? $order->grand_total
            ?? 0
        );
    };

    $totalOrders = is_object($orderSource)
        && method_exists($orderSource, 'total')
        ? $orderSource->total()
        : $orderList->count();

    $pendingOrders = $orderList->filter(
        fn ($order) => in_array(
            strtolower((string) ($order->status ?? '')),
            ['menunggu_pembayaran', 'menunggu_konfirmasi'],
            true
        )
    )->count();

    $processingOrders = $orderList->filter(
        fn ($order) => in_array(
            strtolower((string) ($order->status ?? '')),
            ['diproses', 'dikirim'],
            true
        )
    )->count();

    $completedOrders = $orderList->filter(
        fn ($order) => strtolower((string) ($order->status ?? '')) === 'selesai'
    )->count();
@endphp

<div class="oh-page">

    {{-- =====================================================
         HEADER
    ====================================================== --}}
    <section class="oh-hero">
        <div class="oh-hero-copy">
            <span class="oh-eyebrow">
                <span class="oh-eyebrow-dot"></span>
                PERSONAL ORDER CENTER
            </span>

            <h1>
                Semua pesanan,<br>
                <span>satu tempat.</span>
            </h1>

            <p>
                Pantau perjalanan belanja produk lokalmu dengan mudah.
                Cari pesanan, cek status, dan lanjutkan pembayaran langsung
                dari halaman ini.
            </p>

            <div class="oh-hero-meta">
                <span class="oh-hero-meta-icon">✓</span>
                <span>Informasi pesanan tersusun rapi.</span>
            </div>
        </div>

        <div class="oh-hero-art" aria-hidden="true">
            <div class="oh-hero-ring oh-ring-one"></div>
            <div class="oh-hero-ring oh-ring-two"></div>

            <div class="oh-art-sheet">
                <div class="oh-art-sheet-top">
                    <span class="oh-art-brand">PL.</span>
                    <span class="oh-art-check">✓</span>
                </div>

                <div class="oh-art-row">
                    <span></span>
                    <span></span>
                </div>

                <div class="oh-art-row">
                    <span></span>
                    <span></span>
                </div>

                <div class="oh-art-row">
                    <span></span>
                    <span></span>
                </div>

                <div class="oh-art-sheet-footer">
                    <span></span>
                    <strong>ORDER TRACKER</strong>
                </div>
            </div>

            <div class="oh-art-sticker">
                <span>LOCAL</span>
                <strong>LOVE ✳</strong>
            </div>
        </div>
    </section>

    {{-- =====================================================
         BREADCRUMB
    ====================================================== --}}
    <nav class="oh-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('home') }}">Beranda</a>
        <span>/</span>
        <a href="{{ route('pembeli.dashboard') }}">Dashboard</a>
        <span>/</span>
        <strong>Riwayat pesanan</strong>
    </nav>

    {{-- =====================================================
         FLASH MESSAGES
    ====================================================== --}}
    @if (session('success'))
        <div class="oh-alert oh-alert-success" role="status">
            <span class="oh-alert-icon">✓</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="oh-alert oh-alert-error" role="alert">
            <span class="oh-alert-icon">!</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- =====================================================
         STATISTICS
    ====================================================== --}}
    <section class="oh-stats" aria-label="Ringkasan pesanan">
        <article class="oh-stat-card oh-stat-total">
            <div class="oh-stat-top">
                <span class="oh-stat-label">TOTAL PESANAN</span>
                <span class="oh-stat-icon">▤</span>
            </div>

            <div class="oh-stat-value">{{ $totalOrders }}</div>

            <span class="oh-stat-foot">Pesanan dalam riwayatmu</span>
        </article>

        <article class="oh-stat-card">
            <div class="oh-stat-top">
                <span class="oh-stat-label">PERLU DICEK</span>
                <span class="oh-stat-icon oh-stat-icon-orange">◷</span>
            </div>

            <div class="oh-stat-value">{{ $pendingOrders }}</div>

            <span class="oh-stat-foot">Menunggu pembayaran atau konfirmasi</span>
        </article>

        <article class="oh-stat-card">
            <div class="oh-stat-top">
                <span class="oh-stat-label">DALAM PROSES</span>
                <span class="oh-stat-icon oh-stat-icon-sage">↗</span>
            </div>

            <div class="oh-stat-value">{{ $processingOrders }}</div>

            <span class="oh-stat-foot">Diproses atau sedang dikirim</span>
        </article>

        <article class="oh-stat-card">
            <div class="oh-stat-top">
                <span class="oh-stat-label">SELESAI</span>
                <span class="oh-stat-icon oh-stat-icon-lime">✓</span>
            </div>

            <div class="oh-stat-value">{{ $completedOrders }}</div>

            <span class="oh-stat-foot">Pesanan berstatus selesai</span>
        </article>
    </section>

    {{-- =====================================================
         ORDER HISTORY
    ====================================================== --}}
    <section class="oh-orders-section">
        <div class="oh-section-heading">
            <div>
                <span class="oh-section-kicker">YOUR PURCHASES</span>
                <h2>Riwayat <span>belanja.</span></h2>
                <p>Semua pesananmu, dari yang terbaru hingga sebelumnya.</p>
            </div>

            <a href="{{ route('home') }}" class="oh-shop-button">
                <span>+</span>
                Jelajahi produk
            </a>
        </div>

        {{-- TOOLBAR --}}
        <div class="oh-toolbar">
            <label class="oh-search">
                <span class="oh-search-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <circle
                            cx="10.8" cy="10.8" r="6.8"
                            stroke="currentColor" stroke-width="1.8"
                        />
                        <path
                            d="m16 16 4.2 4.2"
                            stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round"
                        />
                    </svg>
                </span>

                <input
                    type="search"
                    id="ohOrderSearch"
                    placeholder="Cari nomor pesanan atau pembayaran..."
                    autocomplete="off"
                    aria-label="Cari pesanan"
                >

                <span class="oh-search-shortcut">⌕</span>
            </label>

            <div class="oh-toolbar-note">
                <span class="oh-toolbar-dot"></span>
                <span>{{ $orderList->count() }} pesanan ditampilkan</span>
            </div>
        </div>

        {{-- FILTER STATUS --}}
        <div class="oh-filter-bar" role="group" aria-label="Filter status pesanan">
            <span class="oh-filter-label">FILTER</span>

            <button
                type="button"
                class="oh-filter is-active"
                data-order-filter="all"
                aria-pressed="true"
            >
                Semua
            </button>

            <button
                type="button"
                class="oh-filter"
                data-order-filter="menunggu_pembayaran"
                aria-pressed="false"
            >
                Menunggu pembayaran
            </button>

            <button
                type="button"
                class="oh-filter"
                data-order-filter="menunggu_konfirmasi"
                aria-pressed="false"
            >
                Menunggu konfirmasi
            </button>

            <button
                type="button"
                class="oh-filter"
                data-order-filter="diproses"
                aria-pressed="false"
            >
                Diproses
            </button>

            <button
                type="button"
                class="oh-filter"
                data-order-filter="dikirim"
                aria-pressed="false"
            >
                Dikirim
            </button>

            <button
                type="button"
                class="oh-filter"
                data-order-filter="selesai"
                aria-pressed="false"
            >
                Selesai
            </button>

            <button
                type="button"
                class="oh-filter"
                data-order-filter="dibatalkan"
                aria-pressed="false"
            >
                Dibatalkan
            </button>
        </div>

        {{-- ORDER LIST --}}
        @if ($orderList->isNotEmpty())
            <div class="oh-order-list" id="ohOrderList">
                @foreach ($orderList as $order)
                    @php
                        $statusKey = strtolower((string) ($order->status ?? ''));
                        $statusInfo = $getStatus($statusKey);

                        $paymentStatusKey = strtolower(
                            (string) ($order->payment_status ?? '')
                        );

                        $paymentStatusInfo = $getStatus($paymentStatusKey);
                        $paymentMethod = $order->payment_method ?? null;

                        $orderTotal = $getOrderTotal($order);

                        $displayOrderNumber = 'PL-' . str_pad(
                            (string) ($order->id ?? ''),
                            6,
                            '0',
                            STR_PAD_LEFT
                        );

                        $searchableText = strtolower(
                            $displayOrderNumber . ' '
                            . $getPaymentLabel($paymentMethod) . ' '
                            . $statusInfo['label'] . ' '
                            . $paymentStatusInfo['label']
                        );

                        $isTransferOrder = strtolower((string) $paymentMethod) === 'transfer_bank';

                        $needsPayment = in_array(
                            $paymentStatusKey,
                            ['belum_bayar', 'menunggu_pembayaran', 'ditolak'],
                            true
                        );

                        $canContinuePayment = $isTransferOrder
                            && $needsPayment
                            && $statusKey !== 'dibatalkan';

                        $createdAt = $order->created_at ?? null;
                    @endphp

                    <article
                        class="oh-order-card"
                        data-order-card
                        data-status="{{ $statusKey }}"
                        data-search="{{ $searchableText }}"
                    >
                        <div class="oh-order-card-top">
                            <div class="oh-order-identity">
                                <div class="oh-order-mark">PL.</div>

                                <div class="oh-order-identity-copy">
                                    <span class="oh-order-ref-label">NOMOR PESANAN</span>

                                    <strong class="oh-order-ref">
                                        #{{ $displayOrderNumber }}
                                    </strong>

                                    <span class="oh-order-date">
                                        {{ $createdAt ? $createdAt->format('d M Y · H:i') : 'Tanggal belum tersedia' }}
                                    </span>
                                </div>
                            </div>

                            <span class="oh-status {{ $statusInfo['class'] }}">
                                <span class="oh-status-dot"></span>
                                {{ $statusInfo['label'] }}
                            </span>
                        </div>

                        <div class="oh-order-divider"></div>

                        <div class="oh-order-card-body">
                            <div class="oh-order-payment">
                                <span class="oh-detail-label">METODE PEMBAYARAN</span>

                                <div class="oh-payment-value">
                                    <span class="oh-payment-icon">
                                        @if ($isTransferOrder)
                                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path
                                                    d="M3 9.5h18M5 9.5V19m4-9.5V19m6-9.5V19m4-9.5V19M3 19h18M12 3 2.8 8h18.4L12 3Z"
                                                    stroke="currentColor"
                                                    stroke-width="1.7"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                />
                                            </svg>
                                        @else
                                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <rect
                                                    x="3" y="5" width="18" height="14" rx="2"
                                                    stroke="currentColor" stroke-width="1.7"
                                                />
                                                <circle
                                                    cx="12" cy="12" r="3"
                                                    stroke="currentColor" stroke-width="1.7"
                                                />
                                            </svg>
                                        @endif
                                    </span>

                                    <strong>{{ $getPaymentLabel($paymentMethod) }}</strong>
                                </div>

                                @if ($paymentStatusKey !== '')
                                    <div class="oh-payment-status">
                                        <span class="oh-payment-status-dot"></span>
                                        Pembayaran:
                                        {{ $paymentStatusInfo['label'] }}
                                    </div>
                                @endif
                            </div>

                            <div class="oh-order-total">
                                <span class="oh-detail-label">TOTAL PESANAN</span>
                                <strong>Rp{{ number_format($orderTotal, 0, ',', '.') }}</strong>
                                <small>Termasuk komponen biaya yang tercatat</small>
                            </div>
                        </div>

                        <div class="oh-order-card-bottom">
                            <span class="oh-order-thanks">
                                <span>✳</span>
                                Terima kasih sudah mendukung UMKM lokal.
                            </span>

                            @if ($canContinuePayment)
                                <a
                                    href="{{ route('pembeli.payments.show', ['order' => $order->id]) }}"
                                    class="oh-order-action oh-action-primary"
                                >
                                    Lanjut pembayaran
                                    <span>↗</span>
                                </a>
                            @else
                                <span class="oh-order-action-label">
                                    {{ $statusKey === 'selesai' ? 'Pesanan selesai' : 'Status diperbarui sesuai pesanan' }}
                                </span>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- EMPTY FILTER RESULTS --}}
            <div class="oh-filter-empty" id="ohFilterEmpty" hidden>
                <div class="oh-filter-empty-icon">⌕</div>
                <h3>Pesanan tidak ditemukan.</h3>
                <p>Coba kata kunci lain atau pilih filter status yang berbeda.</p>

                <button type="button" id="ohResetFilters" class="oh-reset-button">
                    Reset pencarian
                </button>
            </div>

            {{-- PAGINATION --}}
            @if (is_object($orderSource)
                && method_exists($orderSource, 'links')
                && $orderSource->hasPages())
                <div class="oh-pagination">
                    <div class="oh-pagination-info">
                        Menampilkan
                        {{ $orderSource->firstItem() ?? 0 }}–{{ $orderSource->lastItem() ?? 0 }}
                        dari {{ $orderSource->total() }} pesanan
                    </div>

                    <div class="oh-pagination-links">
                        {{ $orderSource->links() }}
                    </div>
                </div>
            @endif

        @else
            {{-- EMPTY STATE --}}
            <div class="oh-empty">
                <div class="oh-empty-art" aria-hidden="true">
                    <div class="oh-empty-sun"></div>

                    <div class="oh-empty-document">
                        <span class="oh-empty-document-brand">PL.</span>
                        <span class="oh-empty-line"></span>
                        <span class="oh-empty-line short"></span>
                        <span class="oh-empty-check">✓</span>
                    </div>

                    <span class="oh-empty-spark oh-spark-one">✳</span>
                    <span class="oh-empty-spark oh-spark-two">✦</span>
                </div>

                <span class="oh-empty-kicker">YOUR STORY STARTS HERE</span>

                <h3>Belum ada pesanan.</h3>

                <p>
                    Saat kamu menemukan produk lokal yang disukai dan membuat pesanan,
                    riwayatnya akan tampil di halaman ini.
                </p>

                <a href="{{ route('home') }}" class="oh-empty-button">
                    <span>Temukan produk lokal</span>
                    <span>↗</span>
                </a>
            </div>
        @endif
    </section>

    {{-- =====================================================
         FOOTER NOTE
    ====================================================== --}}
    <section class="oh-bottom-note">
        <div class="oh-bottom-note-icon">✳</div>

        <div>
            <strong>Belanja lokal, dampak nyata.</strong>
            <p>
                Setiap pesanan mendukung produk dan usaha lokal untuk terus berkembang.
            </p>
        </div>

        <a href="{{ route('home') }}" class="oh-bottom-link">
            Belanja lagi <span>↗</span>
        </a>
    </section>

    <footer class="oh-footer">
        <span class="oh-footer-brand">PL.</span>
        <span>PasarLokal · Cerita lokal, pilihan istimewa.</span>
        <span class="oh-footer-flower">✳</span>
    </footer>
</div>
@endsection