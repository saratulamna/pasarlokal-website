
@extends('layouts.buyer')

@section('title', 'Dashboard Pembeli — PasarLokal')
@section('breadcrumb', 'Dashboard')
@section('eyebrow', 'YOUR PERSONAL SHOPPING SPACE')
@section('page-title', 'Halo, ' . (auth()->user()->name ?? 'Pembeli') . '!')
@section('page-description', 'Semua aktivitas belanjamu, lebih rapi dan mudah dipantau.')

@php
    $buyerId = auth()->id();

    $ordersQuery = \App\Models\Order::query()
        ->where('user_id', $buyerId);

    $totalOrders = (clone $ordersQuery)->count();

    $activeOrders = (clone $ordersQuery)
        ->whereNotIn('status', ['selesai', 'dibatalkan'])
        ->count();

    $completedOrders = (clone $ordersQuery)
        ->where('status', 'selesai')
        ->count();

    $recentOrders = (clone $ordersQuery)
        ->latest()
        ->limit(5)
        ->get();

    $statusLabels = [
        'menunggu_pembayaran' => 'Menunggu pembayaran',
        'menunggu_konfirmasi' => 'Menunggu konfirmasi',
        'diproses' => 'Sedang diproses',
        'dikirim' => 'Dalam pengiriman',
        'selesai' => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
    ];

    $formatOrderStatus = function ($status) {
        return match ($status) {
            'selesai' => 'buyer-status-success',
            'dikirim', 'diproses' => 'buyer-status-info',
            'dibatalkan' => 'buyer-status-danger',
            'menunggu_pembayaran', 'menunggu_konfirmasi' => 'buyer-status-pending',
            default => 'buyer-status-neutral',
        };
    };
@endphp

@push('page-styles')
    @vite('resources/css/pages/pembeli/dashboard.css')
@endpush

@section('page-actions')
<a href="{{ route('pembeli.products.index') }}" class="bd-btn bd-btn-primary">
    <span>Jelajahi produk</span>
    <span class="bd-btn-arrow" aria-hidden="true">↗</span>
</a>
@endsection

@section('content')

<div class="bd-dashboard">

    {{-- WELCOME HERO --}}
    <section class="bd-hero">

        <div class="bd-hero-content">
            <div class="bd-hero-eyebrow">
                <span class="bd-live-dot"></span>
                SELAMAT DATANG DI PASARLOKAL
            </div>

            <h2>
                Temukan yang kamu suka.
                <span>Dukung yang lokal.</span>
            </h2>

            <p>
                Produk istimewa, cerita di balik usaha lokal,
                dan pengalaman belanja yang lebih bermakna.
                Semuanya dimulai dari sini.
            </p>

            <div class="bd-hero-actions">
</a>


                <a href="{{ route('pembeli.orders.index') }}" class="bd-hero-secondary">
                    Lihat pesanan
                </a>
                
            </div>
        </div>

        <div class="bd-hero-visual" aria-hidden="true">
            <div class="bd-hero-orbit bd-hero-orbit-one"></div>
            <div class="bd-hero-orbit bd-hero-orbit-two"></div>

            <div class="bd-hero-spark bd-hero-spark-one">✳</div>
            <div class="bd-hero-spark bd-hero-spark-two">✦</div>

            <div class="bd-hero-product">
                <div class="bd-hero-product-top">
                    <span>LOCAL GOODS</span>
                    <span>01 / PL</span>
                </div>

                <div class="bd-hero-product-art">
                    <div class="bd-art-sun"></div>
                    <div class="bd-art-leaf bd-art-leaf-one"></div>
                    <div class="bd-art-leaf bd-art-leaf-two"></div>
                    <div class="bd-art-leaf bd-art-leaf-three"></div>
                    <div class="bd-art-basket"></div>
                </div>

                <div class="bd-hero-product-bottom">
                    <div>
                        <strong>Made with purpose.</strong>
                        <span>Bangga produk lokal.</span>
                    </div>
                    <span class="bd-product-star">✳</span>
                </div>
            </div>

            <div class="bd-floating-note">
                <span class="bd-floating-note-icon">♡</span>
                <span>
                    <strong>Support local</strong>
                    <small>Grow together.</small>
                </span>
            </div>
        </div>

        <div class="bd-hero-index" aria-hidden="true">
            LOCAL MARKETPLACE · INDONESIA
        </div>

    </section>

    {{-- STATISTICS --}}
    <section class="bd-stats" aria-label="Ringkasan aktivitas belanja">

        <article class="bd-stat-card">
            <div class="bd-stat-top">
                <span class="bd-stat-label">Total pesanan</span>
                <span class="bd-stat-icon bd-stat-icon-forest" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M7 3.5h8l4 4V20a1.5 1.5 0 0 1-1.5 1.5h-11A1.5 1.5 0 0 1 5 20V5A1.5 1.5 0 0 1 6.5 3.5Z"/>
                        <path d="M14.5 3.5V8H19M8.5 12h7M8.5 16h7"/>
                    </svg>
                </span>
            </div>

            <div class="bd-stat-value">{{ number_format($totalOrders) }}</div>

            <div class="bd-stat-footer">
                <span class="bd-stat-dot"></span>
                Seluruh riwayat transaksi
            </div>
        </article>

        <article class="bd-stat-card">
            <div class="bd-stat-top">
                <span class="bd-stat-label">Pesanan aktif</span>
                <span class="bd-stat-icon bd-stat-icon-lime" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="8.5"/>
                        <path d="M12 7v5l3.5 2"/>
                    </svg>
                </span>
            </div>

            <div class="bd-stat-value">{{ number_format($activeOrders) }}</div>

            <div class="bd-stat-footer">
                <span class="bd-stat-dot bd-dot-lime"></span>
                Belum selesai
            </div>
        </article>

        <article class="bd-stat-card">
            <div class="bd-stat-top">
                <span class="bd-stat-label">Pesanan selesai</span>
                <span class="bd-stat-icon bd-stat-icon-sage" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="m5 12 4.5 4.5L19 7"/>
                    </svg>
                </span>
            </div>

            <div class="bd-stat-value">{{ number_format($completedOrders) }}</div>

            <div class="bd-stat-footer">
                <span class="bd-stat-dot bd-dot-success"></span>
                Transaksi selesai
            </div>
        </article>

    </section>

    {{-- QUICK ACTIONS --}}
    <section class="bd-section">

        <div class="bd-section-heading">
            <div>
                <span class="bd-section-kicker">YOUR NEXT STEP</span>
                <h2>Apa yang ingin kamu lakukan?</h2>
                <p>Pilih aktivitas dan lanjutkan belanjamu.</p>
            </div>
        </div>

        <div class="bd-shortcuts">

            <a href="{{ route('home') }}" class="bd-shortcut-card">
                <div class="bd-shortcut-top">
                    <span class="bd-shortcut-icon bd-shortcut-icon-green">
                        <svg viewBox="0 0 24 24" fill="none">
                            <circle cx="10.5" cy="10.5" r="6.5"/>
                            <path d="m16 16 4.5 4.5"/>
                        </svg>
                    </span>
                    <span class="bd-shortcut-arrow">↗</span>
                </div>

                <div>
                    <h3>Temukan produk</h3>
                    <p>Jelajahi koleksi produk lokal pilihan.</p>
                </div>
            </a>

            <a href="{{ route('cart.index') }}" class="bd-shortcut-card">
                <div class="bd-shortcut-top">
                    <span class="bd-shortcut-icon bd-shortcut-icon-lime">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M3 4h2l2.2 10.1a2 2 0 0 0 2 1.6h7.6a2 2 0 0 0 1.9-1.4L21 8H6"/>
                            <circle cx="10" cy="20" r="1"/>
                            <circle cx="18" cy="20" r="1"/>
                        </svg>
                    </span>
                    <span class="bd-shortcut-arrow">↗</span>
                </div>

                <div>
                    <h3>Keranjang belanja</h3>
                    <p>Periksa produk sebelum checkout.</p>
                </div>
            </a>

            <a href="{{ route('pembeli.orders.index') }}" class="bd-shortcut-card">
                <div class="bd-shortcut-top">
                    <span class="bd-shortcut-icon bd-shortcut-icon-peach">
                        <svg viewBox="0 0 24 24" fill="none">
                            <rect x="4" y="5" width="16" height="15" rx="2"/>
                            <path d="M8 3v4M16 3v4M4 10h16M8 14h3M8 17h6"/>
                        </svg>
                    </span>
                    <span class="bd-shortcut-arrow">↗</span>
                </div>

                <div>
                    <h3>Riwayat pesanan</h3>
                    <p>Pantau status transaksi terbarumu.</p>
                </div>
            </a>

        </div>

    </section>

    {{-- RECENT ORDERS --}}
    <section class="bd-orders-panel">

        <div class="bd-orders-heading">
            <div>
                <span class="bd-section-kicker">ORDER OVERVIEW</span>
                <h2>Pesanan terbaru</h2>
                <p>Ringkasan transaksi terakhir akunmu.</p>
            </div>

            <a href="{{ route('pembeli.orders.index') }}" class="bd-view-all">
                Lihat semua
                <span aria-hidden="true">↗</span>
            </a>
        </div>

        @if ($recentOrders->isNotEmpty())

            <div class="bd-orders-table-wrap">
                <table class="bd-orders-table">
                    <thead>
                        <tr>
                            <th>ORDER</th>
                            <th>TANGGAL</th>
                            <th>TOTAL</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($recentOrders as $order)
                            @php
                                $status = $order->status ?? 'menunggu_konfirmasi';

                                $statusText = $statusLabels[$status]
                                    ?? ucfirst(str_replace('_', ' ', $status));

                                $orderNumber = $order->kode_pesanan
                                    ?? $order->order_number
                                    ?? ('PL-' . str_pad((string) $order->id, 5, '0', STR_PAD_LEFT));

                                $orderTotal = $order->total_harga
                                    ?? $order->total
                                    ?? $order->grand_total
                                    ?? 0;
                            @endphp

                            <tr>
                                <td>
                                    <span class="bd-order-number">
                                        {{ $orderNumber }}
                                    </span>
                                    <span class="bd-order-caption">
                                        ID transaksi #{{ $order->id }}
                                    </span>
                                </td>

                                <td>
                                    <span class="bd-order-date">
                                        {{ $order->created_at?->format('d M Y') ?? '—' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="bd-order-price">
                                        Rp{{ number_format((float) $orderTotal, 0, ',', '.') }}
                                    </span>
                                </td>

                                <td>
                                    <span class="bd-order-status {{ $formatOrderStatus($status) }}">
                                        <span></span>
                                        {{ $statusText }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        @else

            <div class="bd-empty">
                <div class="bd-empty-illustration">
                    <span>✳</span>
                </div>

                <h3>Belum ada pesanan</h3>

                <p>
                    Produk favoritmu mungkin tinggal selangkah lagi.
                    Mulai jelajahi produk lokal dan buat pesanan pertamamu.
                </p>

                <a href="{{ route('home') }}" class="bd-btn bd-btn-primary">
                    Jelajahi produk
                    <span class="bd-btn-arrow" aria-hidden="true">↗</span>
                </a>
            </div>

        @endif

        <div class="bd-orders-footer">
            <span>Data pesanan berdasarkan akun yang sedang masuk.</span>
            <a href="{{ route('pembeli.orders.index') }}">Buka riwayat ↗</a>
        </div>

    </section>

    {{-- BRAND CLOSING --}}
    <section class="bd-closing">
        <div class="bd-closing-mark">✳</div>

        <div>
            <strong>Belanja lokal, tumbuhkan peluang.</strong>
            <p>Setiap pilihanmu ikut mendukung perjalanan UMKM Indonesia.</p>
        </div>

        <a href="{{ route('home') }}" aria-label="Kembali ke PasarLokal">
            ↗
        </a>
    </section>

</div>

@endsection
