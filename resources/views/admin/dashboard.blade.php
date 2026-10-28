
@php
    $admin = $adminUser ?? auth()->user();

    $routeFor = static function (array $names, string $fallback = '#') {
        foreach ($names as $name) {
            if (\Illuminate\Support\Facades\Route::has($name)) {
                return route($name);
            }
        }

        return $fallback;
    };

    $formatRupiah = static function ($value) {
        return 'Rp' . number_format((float) $value, 0, ',', '.');
    };

    $productUrl = $routeFor(['admin.products.index']);
    $userUrl = $routeFor(['admin.users.index', 'admin.user.index']);
    $umkmUrl = $routeFor([
        'admin.umkms.index',
        'admin.umkm.index',
        'admin.stores.index',
    ]);
    $categoryUrl = $routeFor(['admin.categories.index']);
    $orderUrl = $routeFor([
        'admin.orders.index',
        'admin.transactions.index',
    ]);

    $topSales = max(
        1,
        (float) collect($salesChart ?? [])->max('total')
    );
    $weeklyRevenue = (float) collect($salesChart ?? [])->sum('total');

    $imageUrl = static function ($path) {
        if (!$path) {
            return null;
        }

        if (preg_match('/^https?:\/\//i', (string) $path)) {
            return $path;
        }

        return asset('storage/' . ltrim((string) $path, '/'));
    };
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#193B2C">
    <meta name="description" content="Dashboard administrator PasarLokal.">

    <title>Dashboard Admin — PasarLokal</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])
</head>

<body class="admin-dashboard-page">

<div class="admin-shell" id="adminShell">

    <div class="admin-mobile-overlay" id="adminMobileOverlay"></div>

{{-- SIDEBAR BERSAMA --}}
@include('admin.partials.sidebar', [
    'activeMenu' => 'dashboard',
])
    {{-- MAIN --}}
    <main class="admin-main">

        {{-- TOPBAR --}}
        <header class="admin-topbar">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
            <div class="admin-topbar-left">
                <button class="admin-mobile-toggle"
                        type="button"
                        id="adminMobileToggle"
                        aria-label="Buka navigasi">
                    ☰
                </button>

                <div class="admin-breadcrumb">
                    <span>Workspace</span>
                    <b>/</b>
                    <strong>Dashboard</strong>
                </div>
            </div>

            <div class="admin-topbar-actions">
                <span class="admin-system-status">
                    <i></i> SYSTEM ONLINE
                </span>

                <span class="admin-topbar-divider"></span>

                <span class="admin-date">
                    {{ now()->translatedFormat('D, d M Y') }}
                </span>

                <button type="button"
                        id="adminRefreshButton"
                        class="admin-refresh-button"
                        title="Muat ulang dashboard"
                        aria-label="Muat ulang dashboard">
                    ↻
                </button>
            </div>

        </header>

        <div class="admin-content">

            {{-- PAGE INTRO --}}
            <section class="admin-page-intro">
                <div>
                    <div class="admin-eyebrow">
                        <span></span> PASARLOKAL / CONTROL CENTER
                    </div>

                    <h1>Dashboard <em>Admin.</em></h1>

                    <p>
                        Selamat datang kembali,
                        <strong>{{ $admin->name ?? 'Administrator' }}</strong>.
                        Berikut ringkasan aktivitas marketplace hari ini.
                    </p>
                </div>

                <a href="{{ $routeFor(['home'], '/') }}"
                   class="admin-outline-button">
                    Lihat website <span>↗</span>
                </a>
            </section>

            {{-- HERO --}}
            <section class="admin-hero">

                <div class="admin-hero-copy">
                    <span class="admin-hero-kicker">
                        ✳ YOUR MARKETPLACE AT A GLANCE
                    </span>

                    <h2>
                        Kelola lebih cerdas.<br>
                        <span>Tumbuhkan usaha lokal.</span>
                    </h2>

                    <p>
                        Semua yang penting untuk menjaga PasarLokal
                        berjalan dengan baik, dalam satu ruang kendali.
                    </p>

                    <div class="admin-hero-actions">
                        <a href="{{ $productUrl }}" class="admin-primary-button">
                            Kelola katalog <span>↗</span>
                        </a>

                        <a href="{{ $umkmUrl }}" class="admin-hero-link">
                            Tinjau toko UMKM <span>→</span>
                        </a>
                    </div>
                </div>

                <div class="admin-hero-art" aria-hidden="true">
                    <div class="admin-art-ring admin-ring-one"></div>
                    <div class="admin-art-ring admin-ring-two"></div>

                    <div class="admin-art-card admin-art-card-back">
                        <span>LOCAL</span>
                        <strong>GOOD<br>GOODS.</strong>
                    </div>

                    <div class="admin-art-card admin-art-card-front">
                        <div class="admin-art-card-top">
                            <span class="admin-art-mark">P.</span>
                            <span>MARKETPLACE</span>
                        </div>

                        <div class="admin-art-graph">
                            <i style="height: 28%"></i>
                            <i style="height: 48%"></i>
                            <i style="height: 38%"></i>
                            <i style="height: 65%"></i>
                            <i style="height: 52%"></i>
                            <i style="height: 82%"></i>
                            <i style="height: 100%"></i>
                        </div>

                        <div class="admin-art-card-foot">
                            <span>BUILT FOR LOCAL</span>
                            <b>✳</b>
                        </div>
                    </div>

                    <div class="admin-art-orbit-dot"></div>
                    <span class="admin-art-star">✳</span>
                    <span class="admin-art-plus">＋</span>
                </div>

                <div class="admin-hero-index">PL / ADMIN.01</div>
            </section>

            {{-- FLASH MESSAGES --}}
            @if (session('success'))
                <div class="admin-flash admin-flash-success">
                    <span>✓</span>
                    <p>{{ session('success') }}</p>
                    <button type="button" data-dismiss-flash>×</button>
                </div>
            @endif

            @if (session('error'))
                <div class="admin-flash admin-flash-error">
                    <span>!</span>
                    <p>{{ session('error') }}</p>
                    <button type="button" data-dismiss-flash>×</button>
                </div>
            @endif

            {{-- STATISTICS --}}
            <section class="admin-section">

                <div class="admin-section-heading">
                    <div>
                        <span class="admin-section-kicker">LIVE OVERVIEW</span>
                        <h2>Angka utama marketplace</h2>
                        <p>Statistik yang dihitung dari database.</p>
                    </div>

                    <span class="admin-data-pill">
                        <i></i> DATA TERKINI
                    </span>
                </div>

                <div class="admin-metrics-grid">

                    <article class="admin-metric-card metric-revenue">
                        <div class="admin-metric-top">
                            <span class="admin-metric-icon">↗</span>
                            <span class="admin-metric-label">TOTAL OMZET</span>
                        </div>

                        <strong class="admin-metric-value admin-currency">
                            {{ $formatRupiah($totalRevenue ?? 0) }}
                        </strong>

                        <p>
                            Akumulasi kolom nominal transaksi yang tersedia.
                        </p>

                        <span class="admin-metric-decoration">Rp</span>
                    </article>

                    <article class="admin-metric-card metric-orders">
                        <div class="admin-metric-top">
                            <span class="admin-metric-icon">▧</span>
                            <span class="admin-metric-label">TRANSAKSI</span>
                        </div>

                        <strong class="admin-metric-value">
                            {{ number_format($ordersCount ?? 0, 0, ',', '.') }}
                        </strong>

                        <p>
                            {{ number_format($pendingOrdersCount ?? 0, 0, ',', '.') }}
                            pesanan menunggu tindak lanjut.
                        </p>

                        <span class="admin-metric-decoration">#</span>
                    </article>

                    <article class="admin-metric-card metric-products">
                        <div class="admin-metric-top">
                            <span class="admin-metric-icon">▦</span>
                            <span class="admin-metric-label">PRODUK</span>
                        </div>

                        <strong class="admin-metric-value">
                            {{ number_format($productsCount ?? 0, 0, ',', '.') }}
                        </strong>

                        <p>
                            {{ number_format($activeProductsCount ?? 0, 0, ',', '.') }}
                            produk berstatus aktif.
                        </p>

                        <span class="admin-metric-decoration">✳</span>
                    </article>

                    <article class="admin-metric-card metric-stores">
                        <div class="admin-metric-top">
                            <span class="admin-metric-icon">⌂</span>
                            <span class="admin-metric-label">TOKO UMKM</span>
                        </div>

                        <strong class="admin-metric-value">
                            {{ number_format($umkmCount ?? 0, 0, ',', '.') }}
                        </strong>

                        <p>
                            {{ number_format($activeUmkmCount ?? 0, 0, ',', '.') }}
                            toko berstatus aktif.
                        </p>

                        <span class="admin-metric-decoration">⌂</span>
                    </article>

                    <article class="admin-metric-card metric-users">
                        <div class="admin-metric-top">
                            <span class="admin-metric-icon">♙</span>
                            <span class="admin-metric-label">PENGGUNA</span>
                        </div>

                        <strong class="admin-metric-value">
                            {{ number_format($usersCount ?? 0, 0, ',', '.') }}
                        </strong>

                        <p>
                            {{ number_format($buyersCount ?? 0, 0, ',', '.') }}
                            pengguna ber-role pembeli.
                        </p>

                        <span class="admin-metric-decoration">◎</span>
                    </article>

                </div>
            </section>

            {{-- CHART AND QUICK OVERVIEW --}}
            <section class="admin-analytics-grid">

                <article class="admin-panel admin-chart-panel">

                    <div class="admin-panel-heading">
                        <div>
                            <span class="admin-section-kicker">REVENUE ACTIVITY</span>
                            <h2>Aktivitas omzet</h2>
                            <p>Nilai transaksi per hari selama tujuh hari terakhir.</p>
                        </div>

                        <span class="admin-panel-chip">7 HARI</span>
                    </div>

                    <div class="admin-chart-summary">
                        <div>
                            <span>Total dari grafik</span>
                            <strong>{{ $formatRupiah($weeklyRevenue) }}</strong>
                        </div>

                        <span class="admin-chart-legend">
                            <i></i> Omzet harian
                        </span>
                    </div>

                    @if ($weeklyRevenue > 0)
                        <div class="admin-chart">

                            <div class="admin-chart-grid">
                                <span></span>
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>

                            <div class="admin-chart-bars">
                                @foreach ($salesChart as $point)
                                    @php
                                        $barHeight = (
                                            ((float) $point['total'] / $topSales) * 100
                                        );
                                    @endphp

                                    <div class="admin-chart-column"
                                         title="{{ $point['date'] }} · {{ $formatRupiah($point['total']) }}">

                                        <span class="admin-chart-value">
                                            {{ $formatRupiah($point['total']) }}
                                        </span>

                                        <div class="admin-chart-bar-space">
                                            <span class="admin-chart-bar"
                                                  style="height: {{ max(4, $barHeight) }}%">
                                            </span>
                                        </div>

                                        <small>{{ $point['label'] }}</small>
                                        <span class="admin-chart-date">
                                            {{ $point['date'] }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="admin-chart-empty">
                            <span>↗</span>
                            <strong>Grafik siap bertumbuh</strong>
                            <p>
                                Belum ada nominal transaksi pada periode ini,
                                atau kolom nominal belum tersedia di database.
                            </p>
                        </div>
                    @endif

                    <div class="admin-chart-footer">
                        <span>Data aktual · 7 hari terakhir</span>
                        <span>{{ $formatRupiah($monthlyRevenue ?? 0) }} bulan ini</span>
                    </div>

                </article>

                <article class="admin-panel admin-health-panel">

                    <div class="admin-panel-heading">
                        <div>
                            <span class="admin-section-kicker">MARKETPLACE HEALTH</span>
                            <h2>Perlu diperhatikan</h2>
                            <p>Ringkasan hal yang perlu dipantau.</p>
                        </div>

                        <span class="admin-panel-icon">✳</span>
                    </div>

                    <a href="{{ $umkmUrl }}" class="admin-health-row">
                        <span class="admin-health-icon health-orange">⌂</span>

                        <span class="admin-health-copy">
                            <strong>Verifikasi toko</strong>
                            <small>UMKM yang berstatus menunggu.</small>
                        </span>

                        <b>{{ number_format($pendingUmkmCount ?? 0, 0, ',', '.') }}</b>
                        <span class="admin-health-arrow">↗</span>
                    </a>

                    <a href="{{ $productUrl }}" class="admin-health-row">
                        <span class="admin-health-icon health-lime">▦</span>

                        <span class="admin-health-copy">
                            <strong>Stok rendah</strong>
                            <small>Produk dengan stok 5 unit atau kurang.</small>
                        </span>

                        <b>{{ number_format($lowStockCount ?? 0, 0, ',', '.') }}</b>
                        <span class="admin-health-arrow">↗</span>
                    </a>

                    <a href="{{ $orderUrl }}" class="admin-health-row">
                        <span class="admin-health-icon health-sage">▧</span>

                        <span class="admin-health-copy">
                            <strong>Pesanan tertunda</strong>
                            <small>Periksa status transaksi terbaru.</small>
                        </span>

                        <b>{{ number_format($pendingOrdersCount ?? 0, 0, ',', '.') }}</b>
                        <span class="admin-health-arrow">↗</span>
                    </a>

                    <div class="admin-health-footer">
                        <span class="admin-health-footer-icon">✓</span>
                        <span>
                            <strong>Monitoring aktif</strong>
                            <small>Ringkasan diperbarui setiap halaman dimuat.</small>
                        </span>
                    </div>
                </article>

            </section>

            {{-- RECENT ORDERS --}}
            <section class="admin-panel admin-orders-panel">

                <div class="admin-panel-heading admin-panel-heading-row">
                    <div>
                        <span class="admin-section-kicker">TRANSACTION LOG</span>
                        <h2>Pesanan terbaru</h2>
                        <p>Pantau aktivitas transaksi marketplace.</p>
                    </div>

                    <a href="{{ $orderUrl }}" class="admin-text-link">
                        Lihat semua <span>↗</span>
                    </a>
                </div>

                <div class="admin-table-tools">
                    <label class="admin-search-field">
                        <span>⌕</span>
                        <input type="search"
                               id="adminOrderSearch"
                               placeholder="Cari nomor pesanan atau pembeli..."
                               autocomplete="off">
                    </label>

                    <span class="admin-table-count">
                        {{ count($recentOrders ?? []) }} pesanan ditampilkan
                    </span>
                </div>

                <div class="admin-table-wrap">
                    <table class="admin-table">
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

                        <tbody id="adminOrdersBody">
                            @forelse ($recentOrders ?? [] as $order)
                                @php
                                    $orderStatus = strtolower(
                                        (string) data_get($order, 'status', '')
                                    );

                                    $orderBuyer = data_get(
                                        $order,
                                        'buyer_name',
                                        'Pembeli PasarLokal'
                                    );

                                    $orderId = data_get($order, 'id', 0);

                                    $orderCode = 'PL-' . str_pad(
                                        (string) $orderId,
                                        6,
                                        '0',
                                        STR_PAD_LEFT
                                    );

                                    $orderDate = data_get($order, 'created_at');

                                    $orderTotal = data_get($order, 'admin_total')
                                        ?? data_get($order, 'total_harga')
                                        ?? data_get($order, 'grand_total')
                                        ?? data_get($order, 'total_amount')
                                        ?? data_get($order, 'total')
                                        ?? 0;

                                    $statusLabels = [
                                        'menunggu_pembayaran' => 'Menunggu pembayaran',
                                        'menunggu_konfirmasi' => 'Menunggu konfirmasi',
                                        'pending' => 'Menunggu diproses',
                                        'baru' => 'Pesanan baru',
                                        'diproses' => 'Diproses',
                                        'dikirim' => 'Dikirim',
                                        'selesai' => 'Selesai',
                                        'dibatalkan' => 'Dibatalkan',
                                        'gagal' => 'Gagal',
                                    ];

                                    $statusLabel = $statusLabels[$orderStatus]
                                        ?? ($orderStatus !== ''
                                            ? ucfirst(str_replace('_', ' ', $orderStatus))
                                            : 'Belum tersedia');

                                    $statusTone = in_array(
                                        $orderStatus,
                                        ['selesai'],
                                        true
                                    ) ? 'success' : (
                                        in_array(
                                            $orderStatus,
                                            ['dibatalkan', 'gagal'],
                                            true
                                        ) ? 'danger' : 'pending'
                                    );
                                @endphp

                                <tr class="admin-order-row"
                                    data-search="{{ strtolower($orderCode . ' ' . $orderBuyer . ' ' . $statusLabel) }}">

                                    <td>
                                        <strong class="admin-order-code">
                                            {{ $orderCode }}
                                        </strong>
                                        <small class="admin-table-subtitle">
                                            Pesanan marketplace
                                        </small>
                                    </td>

                                    <td>
                                        <span class="admin-buyer-cell">
                                            <span class="admin-buyer-avatar">
                                                {{ mb_strtoupper(mb_substr((string) $orderBuyer, 0, 1)) }}
                                            </span>
                                            <strong>{{ $orderBuyer }}</strong>
                                        </span>
                                    </td>

                                    <td>
                                        {{ $orderDate
                                            ? \Illuminate\Support\Carbon::parse($orderDate)->format('d M Y')
                                            : '—' }}
                                    </td>

                                    <td>
                                        <strong class="admin-order-total">
                                            {{ $formatRupiah($orderTotal) }}
                                        </strong>
                                    </td>

                                    <td>
                                        <span class="admin-status-pill status-{{ $statusTone }}">
                                            <i></i>{{ $statusLabel }}
                                        </span>
                                    </td>

                                    <td>
                                        <a href="{{ $orderUrl }}"
                                           class="admin-row-action"
                                           title="Buka pengelolaan pesanan">↗</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="admin-table-empty">
                                            <span>▧</span>
                                            <strong>Belum ada transaksi</strong>
                                            <p>
                                                Pesanan akan tampil di sini
                                                setelah tersedia di database.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="admin-no-search-match" id="adminOrdersNoMatch" hidden>
                    Tidak ada pesanan yang cocok dengan pencarian.
                </div>

            </section>

            {{-- PRODUCTS AND RECENT USERS --}}
            <section class="admin-bottom-grid">

                <article class="admin-panel admin-recent-products">

                    <div class="admin-panel-heading admin-panel-heading-row">
                        <div>
                            <span class="admin-section-kicker">PRODUCT MONITOR</span>
                            <h2>Produk terbaru</h2>
                            <p>Produk yang terakhir ditambahkan.</p>
                        </div>

                        <a href="{{ $productUrl }}" class="admin-text-link">
                            Katalog <span>↗</span>
                        </a>
                    </div>

                    <label class="admin-search-field admin-product-search">
                        <span>⌕</span>
                        <input type="search"
                               id="adminProductSearch"
                               placeholder="Cari produk..."
                               autocomplete="off">
                    </label>

                    <div class="admin-product-list" id="adminProductList">
                        @forelse ($recentProducts ?? [] as $product)
                            @php
                                $productName = $product->nama ?? $product->name ?? 'Produk tanpa nama';
                                $productImage = $product->gambar ?? $product->image ?? null;
                                $productStore = data_get($product, 'umkm.nama_toko')
                                    ?? data_get($product, 'umkm.name')
                                    ?? 'UMKM lokal';
                                $productCategory = data_get($product, 'category.nama')
                                    ?? 'Produk lokal';
                            @endphp

                            <div class="admin-product-row"
                                 data-search="{{ strtolower($productName . ' ' . $productStore . ' ' . $productCategory) }}">

                                <div class="admin-product-thumb">
                                    @if ($imageUrl($productImage))
                                        <img src="{{ $imageUrl($productImage) }}"
                                             alt="{{ $productName }}"
                                             loading="lazy">
                                    @else
                                        <span>{{ ['✳', '◈', '✷', '❋'][$loop->index % 4] }}</span>
                                    @endif
                                </div>

                                <div class="admin-product-info">
                                    <strong>{{ $productName }}</strong>
                                    <small>{{ $productStore }} · {{ $productCategory }}</small>
                                </div>

                                <div class="admin-product-right">
                                    <strong>{{ $formatRupiah($product->harga ?? $product->price ?? 0) }}</strong>
                                    <small>
                                        Stok: {{ number_format((int) ($product->stok ?? $product->stock ?? 0), 0, ',', '.') }}
                                    </small>
                                </div>

                            </div>
                        @empty
                            <div class="admin-empty-small">
                                <span>▦</span>
                                <strong>Belum ada produk</strong>
                                <p>Produk baru akan tampil di bagian ini.</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="admin-no-search-match" id="adminProductsNoMatch" hidden>
                        Produk tidak ditemukan.
                    </div>

                </article>

                <article class="admin-panel admin-recent-users">

                    <div class="admin-panel-heading admin-panel-heading-row">
                        <div>
                            <span class="admin-section-kicker">COMMUNITY</span>
                            <h2>Pengguna terbaru</h2>
                            <p>Akun terakhir terdaftar.</p>
                        </div>

                        <a href="{{ $userUrl }}" class="admin-text-link">
                            Pengguna <span>↗</span>
                        </a>
                    </div>

                    <div class="admin-user-list">
                        @forelse ($recentUsers ?? [] as $person)
                            @php
                                $role = strtolower((string) ($person->role ?? 'pengguna'));

                                $roleLabels = [
                                    'admin' => 'Administrator',
                                    'umkm' => 'Penjual UMKM',
                                    'pembeli' => 'Pembeli',
                                ];

                                $roleLabel = $roleLabels[$role] ?? ucfirst($role);
                            @endphp

                            <div class="admin-user-row">
                                <div class="admin-user-avatar">
                                    {{ mb_strtoupper(mb_substr((string) $person->name, 0, 1)) }}
                                </div>

                                <div class="admin-user-info">
                                    <strong>{{ $person->name }}</strong>
                                    <small>{{ $person->email }}</small>
                                </div>

                                <span class="admin-role-tag">
                                    {{ $roleLabel }}
                                </span>
                            </div>
                        @empty
                            <div class="admin-empty-small">
                                <span>♙</span>
                                <strong>Belum ada pengguna</strong>
                                <p>Pengguna baru akan tampil di sini.</p>
                            </div>
                        @endforelse
                    </div>

                </article>

            </section>

            {{-- FOOTER --}}
            <footer class="admin-footer">
                <span>© {{ date('Y') }} PASARLOKAL / ADMIN STUDIO</span>
                <span>Dibangun untuk cerita usaha lokal. <b>✳</b></span>
            </footer>

        </div>
    </main>
</div>

</body>
</html>
