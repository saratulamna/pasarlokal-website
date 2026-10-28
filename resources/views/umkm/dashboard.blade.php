
@php
    $sellerUser = auth()->user();
    $storeProfile = $umkm ?? ($vendorStore ?? data_get($sellerUser, 'umkm'));

    // Deklarasikan helper route sebelum dipakai di bagian mana pun.
    $routeFor = static function (array $names, string $fallback = '#') {
        foreach ($names as $name) {
            if (\Illuminate\Support\Facades\Route::has($name)) {
                return route($name);
            }
        }

        return $fallback;
    };

    // Mengambil item paginator dengan benar.
    $toItems = static function ($source) {
        if (is_object($source) && method_exists($source, 'items')) {
            return collect($source->items());
        }

        return collect($source ?? []);
    };

    $productSource = $products ?? ($myProducts ?? collect());
    $productItems = $toItems($productSource);

    $orderSource = $recentOrders ?? ($orders ?? collect());
    $orderItems = $toItems($orderSource);

    $topProductSource = $topProducts ?? ($bestSellingProducts ?? $productItems);
    $topProductItems = $toItems($topProductSource)->take(4)->values();

    $productCountValue = $totalProducts
        ?? ($productsCount ?? data_get($stats ?? [], 'products'))
        ?? (
            is_object($productSource) && method_exists($productSource, 'total')
                ? $productSource->total()
                : $productItems->count()
        );

    $orderCountValue = $totalOrders
        ?? ($ordersCount ?? data_get($stats ?? [], 'orders'))
        ?? (
            is_object($orderSource) && method_exists($orderSource, 'total')
                ? $orderSource->total()
                : $orderItems->count()
        );

    $readOrderTotal = static function ($order) {
        return (float) (
            data_get($order, 'total_harga')
            ?? data_get($order, 'grand_total')
            ?? data_get($order, 'total')
            ?? data_get($order, 'total_amount')
            ?? 0
        );
    };

    $directRevenue = $totalRevenue
        ?? ($revenue ?? data_get($stats ?? [], 'revenue'))
        ?? data_get($stats ?? [], 'total_revenue');

    $hasDirectRevenue = $directRevenue !== null;

    $revenueValue = $hasDirectRevenue
        ? (float) $directRevenue
        : $orderItems->reject(function ($order) {
            return in_array(
                strtolower((string) (data_get($order, 'status') ?? '')),
                [
                    'dibatalkan',
                    'cancelled',
                    'canceled',
                    'gagal',
                    'failed',
                    'ditolak',
                ],
                true
            );
        })->sum($readOrderTotal);

    $revenueCaption = $hasDirectRevenue
        ? 'Sesuai ringkasan penjualan yang tersedia'
        : 'Akumulasi nilai pesanan yang ditampilkan';

    $normalStatus = static function ($status) {
        $status = strtolower(trim((string) ($status ?? '')));

        $labels = [
            'menunggu_pembayaran' => 'Menunggu pembayaran',
            'belum_bayar' => 'Belum dibayar',
            'menunggu_konfirmasi' => 'Menunggu konfirmasi',
            'menunggu_verifikasi' => 'Menunggu verifikasi',
            'pending' => 'Menunggu diproses',
            'baru' => 'Pesanan baru',
            'diproses' => 'Sedang diproses',
            'diproses_penjual' => 'Sedang diproses',
            'dikirim' => 'Dalam pengiriman',
            'dalam_pengiriman' => 'Dalam pengiriman',
            'selesai' => 'Selesai',
            'berhasil' => 'Berhasil',
            'lunas' => 'Lunas',
            'dibatalkan' => 'Dibatalkan',
            'cancelled' => 'Dibatalkan',
            'canceled' => 'Dibatalkan',
            'ditolak' => 'Ditolak',
            'gagal' => 'Gagal',
        ];

        return $labels[$status]
            ?? ($status !== ''
                ? ucfirst(str_replace('_', ' ', $status))
                : 'Status belum tersedia');
    };

    $statusTone = static function ($status) {
        $status = strtolower(trim((string) ($status ?? '')));

        if (in_array($status, ['selesai', 'berhasil', 'lunas'], true)) {
            return 'success';
        }

        if (in_array(
            $status,
            ['dibatalkan', 'cancelled', 'canceled', 'ditolak', 'gagal'],
            true
        )) {
            return 'danger';
        }

        if (in_array(
            $status,
            ['dikirim', 'dalam_pengiriman', 'diproses', 'diproses_penjual'],
            true
        )) {
            return 'info';
        }

        return 'pending';
    };

    $pendingOrderCount = $orderItems->filter(function ($order) {
        return in_array(
            strtolower((string) (data_get($order, 'status') ?? '')),
            [
                'menunggu_pembayaran',
                'belum_bayar',
                'menunggu_konfirmasi',
                'menunggu_verifikasi',
                'pending',
                'baru',
            ],
            true
        );
    })->count();

    $providedLowStock = $lowStockProducts ?? null;

    $lowStockItems = $providedLowStock !== null
        ? $toItems($providedLowStock)
        : $productItems->filter(function ($product) {
            $stock = data_get($product, 'stok')
                ?? data_get($product, 'stock');

            return $stock !== null && (int) $stock <= 5;
        })->values();

    $lowStockCount = $lowStockCount
        ?? data_get($stats ?? [], 'low_stock')
        ?? $lowStockItems->count();

    $salesSeriesSource = $weeklySales ?? ($salesChart ?? []);

    $salesSeries = collect($salesSeriesSource)
        ->take(7)
        ->values()
        ->map(function ($point, $index) {
            if (is_numeric($point)) {
                return [
                    'label' => 'Hari ' . ($index + 1),
                    'value' => (float) $point,
                ];
            }

            return [
                'label' => data_get($point, 'label')
                    ?? data_get($point, 'day')
                    ?? data_get($point, 'date')
                    ?? ('Hari ' . ($index + 1)),

                'value' => (float) (
                    data_get($point, 'total')
                    ?? data_get($point, 'value')
                    ?? data_get($point, 'revenue')
                    ?? data_get($point, 'omzet')
                    ?? 0
                ),
            ];
        });

    $salesMax = max(1, (float) $salesSeries->max('value'));

    $storeName = data_get($storeProfile, 'nama_toko')
        ?? data_get($storeProfile, 'nama_usaha')
        ?? data_get($storeProfile, 'name')
        ?? data_get($sellerUser, 'name')
        ?? 'Toko UMKM Saya';

    $storeStatus = strtolower((string) (
        data_get($storeProfile, 'status')
        ?? data_get($storeProfile, 'status_verifikasi')
        ?? 'belum_terverifikasi'
    ));

    $storeIsActive = in_array(
        $storeStatus,
        [
            'aktif',
            'active',
            'terverifikasi',
            'disetujui',
            'approved',
            'verified',
        ],
        true
    );

    $storeStatusLabel = !$storeProfile
        ? 'Profil toko belum ada'
        : (
            $storeIsActive
                ? 'Toko aktif'
                : (
                    in_array($storeStatus, ['ditolak', 'rejected'], true)
                        ? 'Perlu perbaikan'
                        : 'Menunggu verifikasi'
                )
        );

    $storeStatusTone = $storeIsActive
        ? 'success'
        : (
            in_array($storeStatus, ['ditolak', 'rejected'], true)
                ? 'danger'
                : 'pending'
        );

    $storeImage = data_get($storeProfile, 'logo')
        ?? data_get($storeProfile, 'gambar')
        ?? data_get($storeProfile, 'foto_toko')
        ?? null;

    $imageUrl = static function ($path) {
        if (!$path) {
            return null;
        }

        if (preg_match('/^https?:\/\//i', (string) $path)) {
            return $path;
        }

        return asset('storage/' . ltrim((string) $path, '/'));
    };

    // Nama route diperiksa terlebih dahulu agar tidak memanggil
    // route yang belum terdaftar.
    $productIndexUrl = $routeFor(
        ['umkm.products.index', 'umkm.product.index', 'umkm.produk.index'],
        '#products-panel'
    );

    $productCreateUrl = $routeFor(
        ['umkm.products.create', 'umkm.product.create', 'umkm.produk.create'],
        '#products-panel'
    );

    $ordersIndexUrl = $routeFor(
        ['umkm.orders.index', 'umkm.order.index', 'umkm.pesanan.index'],
        '#orders-panel'
    );

    $storeEditUrl = $routeFor(
        ['umkm.store.edit', 'umkm.profile.edit', 'umkm.toko.edit', 'umkm.store.index'],
        '#store-card'
    );
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#193B2C">
    <meta name="description" content="Dashboard penjual PasarLokal — kelola toko, produk, dan pesanan UMKM.">

    <title>Dashboard Penjual — PasarLokal</title>

    @vite([
        'resources/css/app.css',
        'resources/css/pages/umkm/dashboard.css',
        'resources/js/app.js',
        'resources/js/pages/umkm/dashboard.js',
    ])
</head>

<body class="seller-body">

<div class="seller-app" id="sellerApp">
    <div class="seller-mobile-overlay" id="sellerMobileOverlay"></div>

    {{-- SIDEBAR --}}
    <aside class="seller-sidebar" id="sellerSidebar">

        <a href="{{ $routeFor(['umkm.dashboard'], '/umkm/dashboard') }}"
           class="seller-brand"
           aria-label="Dashboard PasarLokal">

            <span class="seller-brand-mark">P<span>.</span></span>
            <span class="seller-brand-word">Pasar<span>Lokal</span></span>
        </a>

        <div class="seller-workspace-label">SELLER WORKSPACE</div>

        <nav class="seller-navigation" aria-label="Navigasi penjual">

            <a href="{{ $routeFor(['umkm.dashboard'], '/umkm/dashboard') }}"
               class="seller-nav-link is-active"
               aria-current="page">

                <span class="seller-nav-icon">⌂</span>
                <span>Dashboard</span>
                <span class="seller-nav-current"></span>
            </a>

            <a href="{{ $productIndexUrl }}" class="seller-nav-link">
                <span class="seller-nav-icon">▦</span>
                <span>Produk saya</span>
            </a>

            <a href="{{ $ordersIndexUrl }}" class="seller-nav-link">
                <span class="seller-nav-icon">▤</span>
                <span>Pesanan</span>

                @if ($pendingOrderCount > 0)
                    <span class="seller-nav-count">
                        {{ $pendingOrderCount > 99 ? '99+' : $pendingOrderCount }}
                    </span>
                @endif
            </a>

            <a href="#inventory-panel" class="seller-nav-link">
                <span class="seller-nav-icon">▧</span>
                <span>Stok & inventori</span>

                @if ((int) $lowStockCount > 0)
                    <span class="seller-nav-warning">
                        {{ $lowStockCount > 99 ? '99+' : $lowStockCount }}
                    </span>
                @endif
            </a>

            <a href="{{ $storeEditUrl }}" class="seller-nav-link">
                <span class="seller-nav-icon">▣</span>
                <span>Profil toko</span>
            </a>

        </nav>

        <div class="seller-sidebar-tip">
            <p>Kelola toko dengan rapi agar pembeli makin percaya.</p>
            <a href="#seller-guide">Lihat tips toko <span>↗</span></a>
        </div>

        <div class="seller-sidebar-bottom">

            <div class="seller-account">
                <span class="seller-account-avatar">
                    {{ mb_strtoupper(mb_substr((string) ($sellerUser->name ?? 'P'), 0, 1)) }}
                </span>

                <span class="seller-account-info">
                    <strong>{{ $sellerUser->name ?? 'Penjual PasarLokal' }}</strong>
                    <small>Merchant / Penjual</small>
                </span>

                <span class="seller-account-dot" title="Akun masuk"></span>
            </div>

            @if (\Illuminate\Support\Facades\Route::has('logout'))
                <form action="{{ route('logout') }}" method="POST" class="seller-logout-form">
                    @csrf
                    <button type="submit" class="seller-logout-button">
                        <span>↪</span> Keluar dari akun
                    </button>
                </form>
            @endif

        </div>
    </aside>

    {{-- MAIN CONTENT --}}
    <main class="seller-main">

        <header class="seller-topbar">
            <div class="seller-topbar-left">
                <button type="button"
                        class="seller-mobile-toggle"
                        id="sellerMobileToggle"
                        aria-label="Buka navigasi"
                        aria-expanded="false">☰</button>

                <div class="seller-breadcrumb">
                    <span>Workspace</span>
                    <span class="seller-breadcrumb-slash">/</span>
                    <strong>Dashboard</strong>
                </div>
            </div>

            <div class="seller-topbar-actions">
                <span class="seller-live-indicator"><i></i> SISTEM AKTIF</span>
                <span class="seller-topbar-divider"></span>
                <span class="seller-topbar-date">{{ now()->translatedFormat('D, d M Y') }}</span>

                <button type="button"
                        class="seller-refresh-button"
                        id="sellerRefreshButton"
                        aria-label="Muat ulang dashboard"
                        title="Muat ulang">↻</button>
            </div>
        </header>

        <div class="seller-content">

            {{-- FLASH MESSAGES --}}
            @if (session('success'))
                <div class="seller-flash seller-flash-success" role="status">
                    <span>✓</span>
                    <div>
                        <strong>Berhasil!</strong>
                        <p>{{ session('success') }}</p>
                    </div>
                    <button type="button" data-dismiss-flash aria-label="Tutup pesan">×</button>
                </div>
            @endif

            @if (session('error'))
                <div class="seller-flash seller-flash-error" role="alert">
                    <span>!</span>
                    <div>
                        <strong>Ada kendala</strong>
                        <p>{{ session('error') }}</p>
                    </div>
                    <button type="button" data-dismiss-flash aria-label="Tutup pesan">×</button>
                </div>
            @endif

            @if ($errors->any())
                <div class="seller-flash seller-flash-error" role="alert">
                    <span>!</span>
                    <div>
                        <strong>Periksa data kembali</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" data-dismiss-flash aria-label="Tutup pesan">×</button>
                </div>
            @endif

            {{-- STORE WELCOME --}}
            <section class="seller-welcome-grid">

                <div class="seller-welcome-panel">
                    <div class="seller-welcome-copy">

                        <div class="seller-eyebrow">
                            <span></span> YOUR BUSINESS, YOUR STORY
                        </div>

                        <h1>
                            Halo,
                            {{ \Illuminate\Support\Str::of($sellerUser->name ?? 'Penjual')->explode(' ')->first() }}
                            <span class="seller-wave">✳</span>
                        </h1>

                        <p>
                            Setiap produk punya cerita. Yuk, kembangkan tokomu hari ini
                            dan buat lebih banyak pelanggan jatuh hati pada karya lokalmu.
                        </p>

                        <div class="seller-welcome-meta">
                            <span class="seller-status-pill seller-status-{{ $storeStatusTone }}">
                                <i></i>{{ $storeStatusLabel }}
                            </span>

                            <span class="seller-meta-divider"></span>
                            <span class="seller-welcome-store">{{ $storeName }}</span>
                        </div>

                        <div class="seller-welcome-actions">
                            <a href="{{ $productCreateUrl }}" class="seller-btn seller-btn-lime">
                                <span>＋</span> Tambah produk baru <b>↗</b>
                            </a>

                            <a href="#orders-panel" class="seller-btn seller-btn-ghost">
                                Pantau pesanan <span>→</span>
                            </a>
                        </div>

                    </div>

                    <div class="seller-welcome-art" aria-hidden="true">
                        <div class="seller-art-orbit seller-orbit-one"></div>
                        <div class="seller-art-orbit seller-orbit-two"></div>
                        <div class="seller-art-block seller-art-block-back"></div>

                        <div class="seller-art-box">
                            <span>✳</span>
                            <strong>LOCAL<br>GOODS</strong>
                            <small>MADE WITH HEART</small>
                        </div>

                        <div class="seller-art-chip seller-art-chip-top">
                            <span>↗</span> Peluang baru
                        </div>

                        <div class="seller-art-chip seller-art-chip-bottom">
                            <span>♥</span> Dukung lokal
                        </div>

                        <span class="seller-art-spark seller-spark-one">✦</span>
                        <span class="seller-art-spark seller-spark-two">✳</span>
                    </div>

                    <span class="seller-welcome-index" aria-hidden="true">PL. / SELLER STUDIO</span>
                </div>

                {{-- STORE PROFILE CARD --}}
                <aside class="seller-store-card" id="store-card">

                    <div class="seller-store-cover">
                        <span class="seller-store-cover-label">YOUR LOCAL BRAND</span>
                        <span class="seller-store-cover-mark">PL.</span>
                        <span class="seller-store-cover-spark">✳</span>
                    </div>

                    <div class="seller-store-profile">

                        <div class="seller-store-avatar">
                            @if ($imageUrl($storeImage))
                                <img src="{{ $imageUrl($storeImage) }}"
                                     alt="Logo {{ $storeName }}">
                            @else
                                <span>
                                    {{ mb_strtoupper(mb_substr((string) $storeName, 0, 1)) }}
                                </span>
                            @endif
                        </div>

                        <div class="seller-store-status seller-status-{{ $storeStatusTone }}">
                            <i></i>{{ $storeStatusLabel }}
                        </div>

                        <h2>{{ $storeName }}</h2>

                        <p>
                            {{ data_get($storeProfile, 'kategori')
                                ?? data_get($storeProfile, 'jenis_usaha')
                                ?? 'Pelaku usaha lokal Indonesia' }}
                        </p>

                        @if (data_get($storeProfile, 'alamat') || data_get($storeProfile, 'kota') || data_get($storeProfile, 'provinsi'))
                            <div class="seller-store-location">
                                <span>⌖</span>
                                {{ collect([
                                    data_get($storeProfile, 'kota'),
                                    data_get($storeProfile, 'provinsi'),
                                    data_get($storeProfile, 'alamat'),
                                ])->filter()->join(', ') }}
                            </div>
                        @else
                            <div class="seller-store-location">
                                <span>⌖</span>Informasi lokasi belum dilengkapi
                            </div>
                        @endif

                        <a href="{{ $storeEditUrl }}" class="seller-store-edit">
                            Kelola profil toko <span>↗</span>
                        </a>

                    </div>
                </aside>

            </section>

            {{-- STORE SETUP NOTICE --}}
            @if (!$storeProfile)
                <section class="seller-setup-banner">
                    <span class="seller-setup-icon">✳</span>

                    <div>
                        <strong>Lengkapi identitas tokomu</strong>
                        <p>
                            Tambahkan informasi usaha, alamat, dan logo agar
                            tokomu terlihat lebih terpercaya bagi pembeli.
                        </p>
                    </div>

                    <a href="{{ $routeFor(['umkm.store.create', 'umkm.profile.create', 'umkm.toko.create'], '#store-card') }}">
                        Mulai lengkapi <span>↗</span>
                    </a>
                </section>
            @endif

            {{-- BUSINESS METRICS --}}
            <section class="seller-section seller-metrics-section" aria-label="Ringkasan performa toko">

                <div class="seller-section-heading">
                    <div>
                        <span class="seller-section-kicker">AT A GLANCE</span>
                        <h2>Ringkasan performa</h2>
                        <p>Gambaran singkat aktivitas tokomu.</p>
                    </div>

                    <span class="seller-heading-period"><i></i> DATA TERSEDIA</span>
                </div>

                <div class="seller-metrics-grid">

                    <article class="seller-metric seller-metric-revenue">
                        <div class="seller-metric-top">
                            <span class="seller-metric-icon">↗</span>
                            <span class="seller-metric-tag">PENJUALAN</span>
                        </div>

                        <span class="seller-metric-label">
                            {{ $hasDirectRevenue ? 'Omzet toko' : 'Nilai pesanan terdata' }}
                        </span>

                        <strong class="seller-metric-value">
                            Rp{{ number_format($revenueValue, 0, ',', '.') }}
                        </strong>

                        <span class="seller-metric-note">{{ $revenueCaption }}</span>
                        <span class="seller-metric-decoration">Rp</span>
                    </article>

                    <article class="seller-metric seller-metric-orders">
                        <div class="seller-metric-top">
                            <span class="seller-metric-icon">▤</span>
                            <span class="seller-metric-tag">TRANSAKSI</span>
                        </div>

                        <span class="seller-metric-label">Total pesanan</span>

                        <strong class="seller-metric-value">
                            {{ number_format((int) $orderCountValue, 0, ',', '.') }}
                        </strong>

                        <span class="seller-metric-note">
                            <b>{{ number_format((int) $pendingOrderCount, 0, ',', '.') }}</b>
                            pesanan menunggu tindak lanjut dari data yang dimuat
                        </span>

                        <span class="seller-metric-decoration">#</span>
                    </article>

                    <article class="seller-metric seller-metric-products">
                        <div class="seller-metric-top">
                            <span class="seller-metric-icon">▦</span>
                            <span class="seller-metric-tag">KATALOG</span>
                        </div>

                        <span class="seller-metric-label">Produk terdaftar</span>

                        <strong class="seller-metric-value">
                            {{ number_format((int) $productCountValue, 0, ',', '.') }}
                        </strong>

                        <span class="seller-metric-note">
                            Jumlah berdasarkan ringkasan atau data produk yang tersedia
                        </span>

                        <span class="seller-metric-decoration">✳</span>
                    </article>

                    <article class="seller-metric seller-metric-stock">
                        <div class="seller-metric-top">
                            <span class="seller-metric-icon">▧</span>
                            <span class="seller-metric-tag">INVENTORI</span>
                        </div>

                        <span class="seller-metric-label">Stok perlu perhatian</span>

                        <strong class="seller-metric-value">
                            {{ number_format((int) $lowStockCount, 0, ',', '.') }}
                        </strong>

                        <span class="seller-metric-note">
                            Produk dengan stok 5 atau kurang, bila datanya tersedia
                        </span>

                        <span class="seller-metric-decoration">!</span>
                    </article>

                </div>
            </section>

            {{-- SALES CHART AND QUICK ACTIONS --}}
            <section class="seller-analytics-grid">

                <article class="seller-panel seller-sales-panel">

                    <div class="seller-panel-heading">
                        <div>
                            <span class="seller-section-kicker">SALES PERFORMANCE</span>
                            <h2>Perjalanan penjualan</h2>
                            <p>Visualisasi dari data penjualan yang diberikan dashboard.</p>
                        </div>

                        <span class="seller-panel-icon">⌁</span>
                    </div>

                    @if ($salesSeries->isNotEmpty())

                        <div class="seller-chart-summary">
                            <div>
                                <span>Total pada grafik</span>
                                <strong>
                                    Rp{{ number_format((float) $salesSeries->sum('value'), 0, ',', '.') }}
                                </strong>
                            </div>

                            <span class="seller-chart-legend"><i></i> Penjualan</span>
                        </div>

                        <div class="seller-chart"
                             role="img"
                             aria-label="Grafik penjualan berdasarkan data dashboard">

                            <div class="seller-chart-y-labels">
                                <span>Rp{{ number_format($salesMax, 0, ',', '.') }}</span>
                                <span>Rp{{ number_format($salesMax * .66, 0, ',', '.') }}</span>
                                <span>Rp{{ number_format($salesMax * .33, 0, ',', '.') }}</span>
                                <span>Rp0</span>
                            </div>

                            <div class="seller-chart-plot">

                                <div class="seller-chart-gridline seller-chart-gridline-one"></div>
                                <div class="seller-chart-gridline seller-chart-gridline-two"></div>
                                <div class="seller-chart-gridline seller-chart-gridline-three"></div>
                                <div class="seller-chart-gridline seller-chart-gridline-four"></div>

                                <div class="seller-chart-bars">
                                    @foreach ($salesSeries as $point)
                                        <div class="seller-chart-column"
                                             title="{{ $point['label'] }}: Rp{{ number_format($point['value'], 0, ',', '.') }}">

                                            <span class="seller-chart-value">
                                                {{ number_format($point['value'], 0, ',', '.') }}
                                            </span>

                                            <div class="seller-chart-bar-wrap">
                                                <span class="seller-chart-bar"
                                                      style="height: {{ max(3, ($point['value'] / $salesMax) * 100) }}%"></span>
                                            </div>

                                            <small>{{ \Illuminate\Support\Str::limit((string) $point['label'], 8, '') }}</small>
                                        </div>
                                    @endforeach
                                </div>

                            </div>
                        </div>

                    @else

                        <div class="seller-chart-empty">
                            <div class="seller-chart-empty-art">
                                <span>↗</span>
                                <i></i><i></i><i></i><i></i><i></i>
                            </div>

                            <strong>Grafik siap berkembang bersamamu</strong>

                            <p>
                                Data grafik mingguan belum dikirim ke halaman ini.
                                Grafik akan terisi saat controller menyediakan seri penjualan.
                            </p>
                        </div>

                    @endif

                    <div class="seller-chart-footer">
                        <span><i></i> Data ditampilkan sesuai sumber yang tersedia</span>
                        <a href="{{ $ordersIndexUrl }}">Tinjau transaksi <span>↗</span></a>
                    </div>

                </article>

                <article class="seller-panel seller-action-panel">

                    <div class="seller-panel-heading">
                        <div>
                            <span class="seller-section-kicker">WORK SMARTER</span>
                            <h2>Aksi cepat</h2>
                            <p>Jalan pintas untuk operasional harian.</p>
                        </div>

                        <span class="seller-panel-icon">✳</span>
                    </div>

                    <div class="seller-quick-actions">

                        <a href="{{ $productCreateUrl }}" class="seller-quick-action seller-quick-product">
                            <span class="seller-quick-icon">＋</span>
                            <span>
                                <strong>Tambah produk</strong>
                                <small>Perluas katalog tokomu</small>
                            </span>
                            <b>↗</b>
                        </a>

                        <a href="{{ $productIndexUrl }}" class="seller-quick-action seller-quick-catalog">
                            <span class="seller-quick-icon">▦</span>
                            <span>
                                <strong>Kelola produk</strong>
                                <small>Edit harga, deskripsi, dan stok</small>
                            </span>
                            <b>↗</b>
                        </a>

                        <a href="{{ $ordersIndexUrl }}" class="seller-quick-action seller-quick-orders">
                            <span class="seller-quick-icon">▤</span>
                            <span>
                                <strong>Kelola pesanan</strong>
                                <small>Lihat pesanan dan status transaksi</small>
                            </span>
                            <b>↗</b>
                        </a>

                        <a href="{{ $storeEditUrl }}" class="seller-quick-action seller-quick-store">
                            <span class="seller-quick-icon">▣</span>
                            <span>
                                <strong>Perbarui toko</strong>
                                <small>Jaga profil usaha tetap lengkap</small>
                            </span>
                            <b>↗</b>
                        </a>

                    </div>

                    <div class="seller-action-footnote">
                        <span>✦</span> Saran: cek pesanan masuk dan stok secara berkala.
                    </div>

                </article>

            </section>

            {{-- RECENT ORDERS --}}
            <section class="seller-panel seller-orders-panel" id="orders-panel">

                <div class="seller-panel-heading seller-panel-heading-row">
                    <div>
                        <span class="seller-section-kicker">ORDER MANAGEMENT</span>
                        <h2>Pesanan terbaru</h2>
                        <p>Pantau status pesanan yang tersedia untuk tokomu.</p>
                    </div>

                    <a href="{{ $ordersIndexUrl }}" class="seller-text-link">
                        Lihat semua pesanan <span>↗</span>
                    </a>
                </div>

                <div class="seller-table-tools">
                    <label class="seller-search">
                        <span>⌕</span>
                        <input type="search"
                               id="sellerOrderSearch"
                               placeholder="Cari nomor pesanan atau pembeli..."
                               autocomplete="off">
                    </label>

                    <span class="seller-table-count">
                        {{ $orderItems->count() }} item ditampilkan
                    </span>
                </div>

                @if ($orderItems->isNotEmpty())

                    <div class="seller-table-wrap">
                        <table class="seller-orders-table">

                            <thead>
                                <tr>
                                    <th>NO. PESANAN</th>
                                    <th>PEMBELI</th>
                                    <th>TANGGAL</th>
                                    <th>TOTAL</th>
                                    <th>STATUS</th>
                                    <th></th>
                                </tr>
                            </thead>

                            <tbody id="sellerOrdersBody">

                                @foreach ($orderItems->take(8) as $order)

                                    @php
                                        $orderStatus = data_get($order, 'status')
                                            ?? data_get($order, 'order_status');

                                        $orderBuyer = data_get($order, 'user.name')
                                            ?? data_get($order, 'buyer.name')
                                            ?? data_get($order, 'customer.name')
                                            ?? data_get($order, 'nama_pembeli')
                                            ?? 'Pembeli PasarLokal';

                                        $orderDate = data_get($order, 'created_at');

                                        $orderDateText = $orderDate
                                            ? \Illuminate\Support\Carbon::parse($orderDate)->translatedFormat('d M Y')
                                            : '—';

                                        $orderCode = 'PL-' . str_pad(
                                            (string) data_get($order, 'id', '0'),
                                            6,
                                            '0',
                                            STR_PAD_LEFT
                                        );
                                    @endphp

                                    <tr class="seller-order-row"
                                        data-search="{{ strtolower($orderCode . ' ' . $orderBuyer . ' ' . $normalStatus($orderStatus)) }}">

                                        <td>
                                            <span class="seller-order-code">{{ $orderCode }}</span>
                                            <small class="seller-order-source">Pesanan marketplace</small>
                                        </td>

                                        <td>
                                            <span class="seller-buyer-cell">
                                                <span class="seller-buyer-avatar">
                                                    {{ mb_strtoupper(mb_substr((string) $orderBuyer, 0, 1)) }}
                                                </span>
                                                <strong>{{ $orderBuyer }}</strong>
                                            </span>
                                        </td>

                                        <td><span class="seller-date-cell">{{ $orderDateText }}</span></td>

                                        <td>
                                            <strong class="seller-order-total">
                                                Rp{{ number_format($readOrderTotal($order), 0, ',', '.') }}
                                            </strong>
                                        </td>

                                        <td>
                                            <span class="seller-order-status seller-order-status-{{ $statusTone($orderStatus) }}">
                                                <i></i>{{ $normalStatus($orderStatus) }}
                                            </span>
                                        </td>

                                        <td>
                                            <a href="{{ $ordersIndexUrl }}"
                                               class="seller-row-action"
                                               aria-label="Lihat pesanan {{ $orderCode }}">↗</a>
                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>
                        </table>
                    </div>

                    <div class="seller-table-empty-filter" id="sellerOrdersNoMatch" hidden>
                        <span>⌕</span>
                        <strong>Pesanan tidak ditemukan</strong>
                        <p>Coba kata kunci lain untuk mencari pesanan.</p>
                    </div>

                @else

                    <div class="seller-empty-state seller-orders-empty">
                        <span class="seller-empty-illustration">▤</span>
                        <strong>Belum ada pesanan untuk ditampilkan</strong>
                        <p>
                            Pesanan yang masuk akan muncul di bagian ini sehingga
                            kamu bisa memantau statusnya.
                        </p>
                        <a href="{{ $productIndexUrl }}">
                            Periksa katalog produk <span>↗</span>
                        </a>
                    </div>

                @endif

            </section>

            {{-- PRODUCTS AND INVENTORY --}}
            <section class="seller-products-grid">

                <article class="seller-panel seller-products-panel" id="products-panel">

                    <div class="seller-panel-heading seller-panel-heading-row">
                        <div>
                            <span class="seller-section-kicker">PRODUCT SPOTLIGHT</span>
                            <h2>Produk di tokomu</h2>
                            <p>Ringkasan katalog yang tersedia.</p>
                        </div>

                        <a href="{{ $productIndexUrl }}" class="seller-text-link">
                            Kelola produk <span>↗</span>
                        </a>
                    </div>

                    <label class="seller-search seller-product-search">
                        <span>⌕</span>
                        <input type="search"
                               id="sellerProductSearch"
                               placeholder="Cari produk..."
                               autocomplete="off">
                    </label>

                    @if ($topProductItems->isNotEmpty())

                        <div class="seller-product-list" id="sellerProductList">

                            @foreach ($topProductItems as $product)

                                @php
                                    $productImage = data_get($product, 'gambar')
                                        ?? data_get($product, 'image')
                                        ?? data_get($product, 'foto');

                                    $productStock = data_get($product, 'stok')
                                        ?? data_get($product, 'stock');

                                    $productStatus = strtolower((string) (data_get($product, 'status') ?? ''));

                                    $productName = data_get($product, 'nama')
                                        ?? data_get($product, 'name')
                                        ?? 'Produk tanpa nama';

                                    $productPrice = (float) (
                                        data_get($product, 'harga')
                                        ?? data_get($product, 'price')
                                        ?? 0
                                    );
                                @endphp

                                <div class="seller-product-row" data-search="{{ strtolower($productName) }}">

                                    <div class="seller-product-thumb">
                                        @if ($imageUrl($productImage))
                                            <img src="{{ $imageUrl($productImage) }}"
                                                 alt="{{ $productName }}"
                                                 loading="lazy">
                                        @else
                                            <span>{{ ['✳', '◈', '✷', '❋'][$loop->index % 4] }}</span>
                                        @endif
                                    </div>

                                    <div class="seller-product-detail">
                                        <strong>{{ $productName }}</strong>

                                        <small>
                                            {{ data_get($product, 'category.nama')
                                                ?? data_get($product, 'kategori')
                                                ?? 'Produk lokal' }}
                                        </small>

                                        <div class="seller-product-row-meta">
                                            <span>
                                                Stok:
                                                <b>
                                                    {{ $productStock !== null
                                                        ? number_format((int) $productStock, 0, ',', '.')
                                                        : '—' }}
                                                </b>
                                            </span>

                                            <span class="seller-product-status seller-product-status-{{ in_array($productStatus, ['aktif', 'active', 'published'], true) ? 'active' : 'draft' }}">
                                                {{ $productStatus !== ''
                                                    ? ucfirst(str_replace('_', ' ', $productStatus))
                                                    : 'Status belum tersedia' }}
                                            </span>
                                        </div>
                                    </div>

                                    <strong class="seller-product-price">
                                        Rp{{ number_format($productPrice, 0, ',', '.') }}
                                    </strong>

                                </div>

                            @endforeach

                        </div>

                        <div class="seller-product-no-match" id="sellerProductsNoMatch" hidden>
                            Produk tidak ditemukan dengan kata kunci itu.
                        </div>

                    @else

                        <div class="seller-empty-state seller-products-empty">
                            <span class="seller-empty-illustration">▦</span>
                            <strong>Koleksi pertamamu dimulai di sini</strong>
                            <p>
                                Tambahkan produk dengan foto, harga, deskripsi,
                                dan stok agar pembeli dapat menemukannya.
                            </p>
                            <a href="{{ $productCreateUrl }}">＋ Tambah produk pertama</a>
                        </div>

                    @endif

                </article>

                {{-- LOW STOCK --}}
                <article class="seller-panel seller-inventory-panel" id="inventory-panel">

                    <div class="seller-panel-heading seller-panel-heading-row">
                        <div>
                            <span class="seller-section-kicker">STOCK WATCH</span>
                            <h2>Perhatian stok</h2>
                            <p>Item yang mungkin perlu dicek.</p>
                        </div>

                        <span class="seller-stock-count">
                            {{ number_format((int) $lowStockCount, 0, ',', '.') }}
                        </span>
                    </div>

                    @if ($lowStockItems->isNotEmpty())

                        <div class="seller-inventory-list">

                            @foreach ($lowStockItems->take(5) as $stockProduct)

                                @php
                                    $stockName = data_get($stockProduct, 'nama')
                                        ?? data_get($stockProduct, 'name')
                                        ?? 'Produk tanpa nama';

                                    $stockValue = data_get($stockProduct, 'stok')
                                        ?? data_get($stockProduct, 'stock');

                                    $stockIsEmpty = $stockValue !== null && (int) $stockValue <= 0;
                                @endphp

                                <div class="seller-inventory-item">

                                    <span class="seller-inventory-icon {{ $stockIsEmpty ? 'is-empty' : '' }}">
                                        {{ $stockIsEmpty ? '!' : '▧' }}
                                    </span>

                                    <span class="seller-inventory-info">
                                        <strong>{{ $stockName }}</strong>
                                        <small>
                                            {{ $stockIsEmpty
                                                ? 'Stok habis'
                                                : 'Persediaan hampir habis' }}
                                        </small>
                                    </span>

                                    <span class="seller-inventory-quantity {{ $stockIsEmpty ? 'is-empty' : '' }}">
                                        {{ $stockValue !== null ? (int) $stockValue : '—' }}
                                        <small>unit</small>
                                    </span>

                                </div>

                            @endforeach

                        </div>

                        <a href="{{ $productIndexUrl }}" class="seller-inventory-link">
                            Periksa katalog <span>↗</span>
                        </a>

                    @else


@php
    $stockWarnings = $lowStockProducts ?? collect();
@endphp

@forelse ($stockWarnings as $stockProduct)
    <div class="seller-stock-empty">
        <span>!</span>

        <strong>{{ $stockProduct->nama }}</strong>

        <p>
            Stok tersisa {{ $stockProduct->stok }} unit.
            Segera periksa persediaan produkmu.
        </p>

        <a href="{{ route('umkm.products.edit', $stockProduct) }}">
            Periksa produk ↗
        </a>
    </div>
@empty
    <div class="seller-stock-empty">
        <span>✓</span>

        <strong>Belum ada peringatan stok</strong>

        <p>
            Semua produk memiliki stok di atas batas minimum.
            Peringatan akan muncul otomatis saat stok 5 unit
            atau kurang.
        </p>

        <a href="{{ $productIndexUrl }}">
            Lihat semua produk ↗
        </a>
    </div>
@endforelse


                    @endif

                </article>

            </section>

            {{-- SELLER GUIDE --}}
            <section class="seller-guide-banner" id="seller-guide">

                <div class="seller-guide-icon">✳</div>

                <div class="seller-guide-copy">
                    <span class="seller-section-kicker">SMALL STEPS, REAL IMPACT</span>
                    <h2>Buat tokomu makin dipercaya.</h2>
                    <p>
                        Foto produk yang jelas, stok yang akurat, deskripsi jujur,
                        dan respons pesanan yang sigap membantu pelanggan
                        berbelanja dengan lebih nyaman.
                    </p>
                </div>

                <div class="seller-guide-checklist">
                    <span><i>✓</i> Foto produk jelas</span>
                    <span><i>✓</i> Stok selalu akurat</span>
                    <span><i>✓</i> Pesanan ditangani</span>
                </div>

                <span class="seller-guide-decoration" aria-hidden="true">LOCAL.</span>

            </section>

            <footer class="seller-footer">
                <span>© {{ date('Y') }} PASARLOKAL / SELLER STUDIO</span>
                <span>Dibangun untuk cerita usaha lokal. <b>✳</b></span>
            </footer>

        </div>
    </main>
</div>

</body>
</html>
