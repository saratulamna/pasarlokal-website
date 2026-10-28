
@php
    $sellerUser = auth()->user();

    $routeFor = static function (
        array $names,
        array $parameters = [],
        string $fallback = '/'
    ): string {
        foreach ($names as $name) {
            if (\Illuminate\Support\Facades\Route::has($name)) {
                return route($name, $parameters);
            }
        }

        return url($fallback);
    };

    $productsPage = $products ?? collect();

    $productRows = is_object($productsPage)
        && method_exists($productsPage, 'getCollection')
            ? $productsPage->getCollection()
            : collect($productsPage);

    $categoryRows = collect($categories ?? []);

    $productsUrl = route('umkm.products.index');
    $createUrl = route('umkm.products.create');
    $dashboardUrl = route('umkm.dashboard');

    $ordersUrl = $routeFor(
        ['umkm.orders.index', 'umkm.order.index', 'umkm.pesanan.index'],
        [],
        '/umkm/dashboard#orders-panel'
    );

    $storeUrl = $routeFor(
        ['umkm.store.edit', 'umkm.profile.edit', 'umkm.toko.edit', 'umkm.store.index'],
        [],
        '/umkm/dashboard#store-card'
    );

    $totalProducts = is_object($productsPage)
        && method_exists($productsPage, 'total')
            ? $productsPage->total()
            : $productRows->count();

    $activeCount = $productRows->filter(function ($product) {
        return in_array(
            strtolower((string) data_get($product, 'status', '')),
            ['aktif', 'active'],
            true
        );
    })->count();

    $lowStockCount = $productRows->filter(function ($product) {
        $stock = (int) data_get($product, 'stok', 0);

        return $stock > 0 && $stock <= 5;
    })->count();

    $emptyStockCount = $productRows->filter(function ($product) {
        return (int) data_get($product, 'stok', 0) <= 0;
    })->count();

    $productImageUrl = static function ($path): ?string {
        if (!$path) {
            return null;
        }

        $path = trim((string) $path);

        if (\Illuminate\Support\Str::startsWith($path, [
            'https://',
            'http://',
            'data:image/',
            '/',
        ])) {
            return $path;
        }

        return \Illuminate\Support\Facades\Storage::url($path);
    };

    $searchValue = request('search', '');
    $categoryValue = request('category', 'all');
    $statusValue = request('status', 'all');
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#193B2C">
    <meta name="description" content="Kelola katalog produk dan persediaan toko di PasarLokal Seller Workspace.">

    <title>Produk Saya — PasarLokal Seller Workspace</title>

    @vite([
        'resources/css/app.css',
        'resources/css/pages/umkm/dashboard.css',
        'resources/css/pages/umkm/products-index.css',
        'resources/js/app.js',
        'resources/js/pages/umkm/products-index.js',
    ])
</head>

<body class="seller-body">

<div class="seller-app upi-app" id="upiApp">

    {{-- MOBILE OVERLAY --}}
    <button
        type="button"
        class="seller-mobile-overlay upi-sidebar-overlay"
        id="sellerMobileOverlay"
        aria-label="Tutup menu navigasi"
        tabindex="-1"
    ></button>

    {{-- =====================================================
         SIDEBAR: SAMA DENGAN DASHBOARD UMKM
    ====================================================== --}}
    <aside class="seller-sidebar" id="sellerSidebar">

        <a
            href="{{ $dashboardUrl }}"
            class="seller-brand"
            aria-label="Dashboard PasarLokal"
        >
            <span class="seller-brand-mark">P<span>.</span></span>
            <span class="seller-brand-word">Pasar<span>Lokal</span></span>
        </a>

        <div class="seller-workspace-label">
            SELLER WORKSPACE
        </div>

        <nav class="seller-navigation" aria-label="Navigasi penjual">

            <a
                href="{{ $dashboardUrl }}"
                class="seller-nav-link"
            >
                <span class="seller-nav-icon">⌂</span>
                <span>Dashboard</span>
            </a>

            <a
                href="{{ $productsUrl }}"
                class="seller-nav-link is-active"
                aria-current="page"
            >
                <span class="seller-nav-icon">▦</span>
                <span>Produk saya</span>
                <span class="seller-nav-current"></span>
            </a>

            <a
                href="{{ $ordersUrl }}"
                class="seller-nav-link"
            >
                <span class="seller-nav-icon">▤</span>
                <span>Pesanan</span>
            </a>

            <a
                href="{{ $dashboardUrl }}#inventory-panel"
                class="seller-nav-link"
            >
                <span class="seller-nav-icon">▧</span>
                <span>Stok & inventori</span>
            </a>

            <a
                href="{{ $storeUrl }}"
                class="seller-nav-link"
            >
                <span class="seller-nav-icon">▣</span>
                <span>Profil toko</span>
            </a>

        </nav>

        <div class="seller-sidebar-tip">
            <p>Kelola toko dengan rapi agar pembeli makin percaya.</p>

            <a href="{{ $dashboardUrl }}#seller-guide">
                Lihat tips toko <span>↗</span>
            </a>
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

                <span
                    class="seller-account-dot"
                    title="Akun masuk"
                ></span>

            </div>
        </div>

        <a href="{{ route('home') }}" class="seller-sidebar-home">
            ↖ Lihat halaman depan
        </a>

    </aside>

    {{-- =====================================================
         MAIN CONTENT
    ====================================================== --}}
    <main class="seller-main upi-main">

        {{-- HEADER --}}
        <header class="seller-topbar upi-topbar">

            <div class="upi-topbar-left">

                <button
                    type="button"
                    class="upi-mobile-toggle seller-mobile-toggle"
                    id="sellerMobileToggle"
                    data-sidebar-toggle
                    aria-label="Buka navigasi"
                    aria-expanded="false"
                >
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path
                            d="M4 7H20M4 12H20M4 17H20"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                        />
                    </svg>
                </button>

                <nav class="upi-breadcrumb" aria-label="Breadcrumb">
                    <a href="{{ $dashboardUrl }}">Dashboard</a>
                    <span>/</span>
                    <strong>Produk saya</strong>
                </nav>

            </div>

            <div class="upi-topbar-right">

                <div class="upi-topbar-status">
                    <span></span>
                    Seller Workspace
                </div>

                <div class="upi-user">

                    <span class="upi-user-avatar">
                        {{ mb_strtoupper(mb_substr((string) ($sellerUser->name ?? 'P'), 0, 1)) }}
                    </span>

                    <span class="upi-user-info">
                        <strong>{{ $sellerUser->name ?? 'Penjual PasarLokal' }}</strong>
                        <span>Merchant / Penjual</span>
                    </span>

                </div>

            </div>
        </header>

        <div class="seller-content upi-content">

            {{-- =================================================
                 HERO
            ================================================== --}}
            <section class="upi-hero">

                <div class="upi-hero-copy">

                    <div class="upi-eyebrow">
                        <span class="upi-eyebrow-dot"></span>
                        RUANG PRODUK UMKM
                    </div>

                    <h1>
                        Produkmu,<br>
                        <span>pusat perhatian.</span>
                    </h1>

                    <p>
                        Kelola koleksi produk lokalmu dalam satu tempat.
                        Perbarui informasi, pantau persediaan, dan
                        siapkan produk terbaik untuk pelanggan.
                    </p>

                    <div class="upi-hero-actions">

                        <a href="{{ $createUrl }}" class="upi-primary-button">
                            <span class="upi-button-plus">+</span>
                            Tambah produk

                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path
                                    d="M5 12H19M13 6L19 12L13 18"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </a>

                        <span class="upi-hero-caption">
                            <span>✳</span>
                            Dibuat untuk produk lokal
                        </span>

                    </div>
                </div>

                <div class="upi-hero-art" aria-hidden="true">

                    <div class="upi-hero-orbit upi-hero-orbit-one"></div>
                    <div class="upi-hero-orbit upi-hero-orbit-two"></div>

                    <div class="upi-hero-product">

                        <div class="upi-hero-product-top">
                            <span>YOUR COLLECTION</span>
                            <span>✳</span>
                        </div>

                        <div class="upi-hero-bag">
                            <svg viewBox="0 0 100 100" fill="none">
                                <path
                                    d="M18 37H82L75 83H25L18 37Z"
                                    fill="#D9F36A"
                                    stroke="#193B2C"
                                    stroke-width="3.5"
                                    stroke-linejoin="round"
                                />
                                <path
                                    d="M34 38V29C34 20.2 41.2 13 50 13C58.8 13 66 20.2 66 29V38"
                                    stroke="#193B2C"
                                    stroke-width="3.5"
                                    stroke-linecap="round"
                                />
                                <circle cx="41" cy="51" r="3" fill="#193B2C"/>
                                <circle cx="59" cy="51" r="3" fill="#193B2C"/>
                                <path
                                    d="M40 62C45 67 55 67 60 62"
                                    stroke="#193B2C"
                                    stroke-width="3"
                                    stroke-linecap="round"
                                />
                            </svg>
                        </div>

                        <div class="upi-hero-product-bottom">
                            <strong>Made with care.</strong>
                            <span>LOCAL GOODS, BIG STORIES.</span>
                        </div>

                    </div>

                    <div class="upi-floating-tag upi-floating-tag-top">
                        <span>✦</span> PRODUK LOKAL
                    </div>

                    <div class="upi-floating-tag upi-floating-tag-bottom">
                        GROW YOUR BUSINESS ↗
                    </div>

                </div>

            </section>

            {{-- =================================================
                 SECTION TITLE
            ================================================== --}}
            <section class="upi-section-heading">

                <div>
                    <span class="upi-section-kicker">PRODUCT MANAGEMENT</span>

                    <h2>
                        Semua produk <span>✳</span>
                    </h2>

                    <p>
                        Kelola katalog dan pastikan produkmu selalu siap dijual.
                    </p>
                </div>

                <a href="{{ $createUrl }}" class="upi-secondary-button">
                    <span>+</span>
                    Produk baru
                </a>

            </section>

            {{-- =================================================
                 STATISTICS
            ================================================== --}}
            <section class="upi-stats-grid">

                <article class="upi-stat-card upi-stat-card-total">

                    <div class="upi-stat-top">
                        <div class="upi-stat-icon">▦</div>
                        <span class="upi-stat-label">KOLEKSI</span>
                    </div>

                    <div class="upi-stat-value">
                        {{ number_format($totalProducts, 0, ',', '.') }}
                    </div>

                    <div class="upi-stat-description">
                        Total produk dalam katalog
                    </div>

                    <div class="upi-stat-bottom">
                        <span>01</span>
                        <div></div>
                        <span>Semua produk</span>
                    </div>

                </article>

                <article class="upi-stat-card upi-stat-card-active">

                    <div class="upi-stat-top">
                        <div class="upi-stat-icon">✓</div>
                        <span class="upi-stat-label">STATUS</span>
                    </div>

                    <div class="upi-stat-value">
                        {{ number_format($activeCount, 0, ',', '.') }}
                    </div>

                    <div class="upi-stat-description">
                        Produk aktif pada halaman ini
                    </div>

                    <div class="upi-stat-bottom">
                        <span>02</span>
                        <div></div>
                        <span>Siap ditampilkan</span>
                    </div>

                </article>

                <article class="upi-stat-card upi-stat-card-low">

                    <div class="upi-stat-top">
                        <div class="upi-stat-icon">!</div>
                        <span class="upi-stat-label">PERSEDIAAN</span>
                    </div>

                    <div class="upi-stat-value">
                        {{ number_format($lowStockCount, 0, ',', '.') }}
                    </div>

                    <div class="upi-stat-description">
                        Produk dengan stok 1–5 unit
                    </div>

                    <div class="upi-stat-bottom">
                        <span>03</span>
                        <div></div>
                        <span>Periksa stok</span>
                    </div>

                </article>

                <article class="upi-stat-card upi-stat-card-empty">

                    <div class="upi-stat-top">
                        <div class="upi-stat-icon">−</div>
                        <span class="upi-stat-label">STOK HABIS</span>
                    </div>

                    <div class="upi-stat-value">
                        {{ number_format($emptyStockCount, 0, ',', '.') }}
                    </div>

                    <div class="upi-stat-description">
                        Produk tanpa persediaan pada halaman ini
                    </div>

                    <div class="upi-stat-bottom">
                        <span>04</span>
                        <div></div>
                        <span>Perlu restok</span>
                    </div>

                </article>

            </section>

            {{-- =================================================
                 PRODUCT CATALOG
            ================================================== --}}
            <section class="upi-catalog-panel">

                <div class="upi-catalog-heading">

                    <div>
                        <span class="upi-section-kicker">YOUR INVENTORY</span>

                        <h2>Katalog produk</h2>

                        <p>
                            Menampilkan
                            <strong data-result-count>
                                {{ $productRows->count() }}
                            </strong>
                            produk pada halaman ini.
                        </p>
                    </div>

                    <a href="{{ $createUrl }}" class="upi-catalog-add">
                        <span>+</span>
                        Tambah produk
                    </a>

                </div>

                {{-- FILTER FORM --}}
                <form
                    action="{{ $productsUrl }}"
                    method="GET"
                    id="upiFilterForm"
                    class="upi-filter-form"
                >

                    <div class="upi-search-field">

                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle
                                cx="10.8"
                                cy="10.8"
                                r="6.8"
                                stroke="currentColor"
                                stroke-width="1.7"
                            />
                            <path
                                d="M16 16L21 21"
                                stroke="currentColor"
                                stroke-width="1.7"
                                stroke-linecap="round"
                            />
                        </svg>

                        <input
                            id="upiSearch"
                            data-search-input
                            type="search"
                            name="search"
                            value="{{ $searchValue }}"
                            placeholder="Cari nama atau deskripsi produk..."
                            autocomplete="off"
                        >

                    </div>

                    <div class="upi-filter-select">

                        <select
                            id="upiCategory"
                            name="category"
                            data-category-filter
                            aria-label="Filter kategori"
                        >
                            <option value="all">Semua kategori</option>

                            @foreach ($categoryRows as $category)
                                @php
                                    $categoryName = data_get(
                                        $category,
                                        'nama',
                                        data_get($category, 'name', 'Kategori')
                                    );
                                @endphp

                                <option
                                    value="{{ $categoryName }}"
                                    @selected(
                                        mb_strtolower((string) $categoryValue)
                                        === mb_strtolower((string) $categoryName)
                                    )
                                >
                                    {{ $categoryName }}
                                </option>
                            @endforeach

                        </select>

                        <span class="upi-select-arrow">⌄</span>

                    </div>

                    <div class="upi-filter-select">

                        <select
                            id="upiStatus"
                            name="status"
                            data-status-filter
                            aria-label="Filter status"
                        >
                            <option value="all" @selected($statusValue === 'all')>
                                Semua status
                            </option>
                            <option value="aktif" @selected($statusValue === 'aktif')>
                                Aktif
                            </option>
                            <option value="nonaktif" @selected($statusValue === 'nonaktif')>
                                Nonaktif
                            </option>
                        </select>

                        <span class="upi-select-arrow">⌄</span>

                    </div>

                    <button type="submit" class="upi-filter-submit">
                        Terapkan
                    </button>

                    <button
                        type="button"
                        class="upi-filter-reset"
                        data-reset-filters
                    >
                        Reset
                    </button>

                </form>

                {{-- QUICK FILTER --}}
                <div class="upi-quick-filter-row">

                    <div class="upi-quick-filters">

                        <button
                            type="button"
                            class="upi-quick-filter is-active"
                            data-quick-filter="all"
                            aria-pressed="true"
                        >
                            <span class="upi-quick-dot upi-dot-all"></span>
                            Semua
                        </button>

                        <button
                            type="button"
                            class="upi-quick-filter"
                            data-quick-filter="active"
                            aria-pressed="false"
                        >
                            <span class="upi-quick-dot upi-dot-active"></span>
                            Aktif
                        </button>

                        <button
                            type="button"
                            class="upi-quick-filter"
                            data-quick-filter="inactive"
                            aria-pressed="false"
                        >
                            <span class="upi-quick-dot upi-dot-inactive"></span>
                            Nonaktif
                        </button>

                        <button
                            type="button"
                            class="upi-quick-filter"
                            data-quick-filter="low-stock"
                            aria-pressed="false"
                        >
                            <span class="upi-quick-dot upi-dot-low"></span>
                            Stok menipis
                        </button>

                        <button
                            type="button"
                            class="upi-quick-filter"
                            data-quick-filter="empty-stock"
                            aria-pressed="false"
                        >
                            <span class="upi-quick-dot upi-dot-empty"></span>
                            Stok habis
                        </button>

                    </div>

                    <div class="upi-view-options">

                        <label for="upiSort">Urutkan</label>

                        <select id="upiSort" data-sort-select>
                            <option value="latest">Terbaru</option>
                            <option value="oldest">Terlama</option>
                            <option value="name">Nama A–Z</option>
                            <option value="name-desc">Nama Z–A</option>
                            <option value="price-asc">Harga terendah</option>
                            <option value="price-desc">Harga tertinggi</option>
                            <option value="stock-asc">Stok terendah</option>
                            <option value="stock-desc">Stok tertinggi</option>
                        </select>

                        <div class="upi-view-toggle" aria-label="Mode tampilan">

                            <button
                                type="button"
                                class="upi-view-button is-active"
                                data-view-mode="grid"
                                aria-label="Tampilan kartu"
                                aria-pressed="true"
                            >
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <rect x="4" y="4" width="6" height="6" rx="1"
                                          stroke="currentColor" stroke-width="1.7"/>
                                    <rect x="14" y="4" width="6" height="6" rx="1"
                                          stroke="currentColor" stroke-width="1.7"/>
                                    <rect x="4" y="14" width="6" height="6" rx="1"
                                          stroke="currentColor" stroke-width="1.7"/>
                                    <rect x="14" y="14" width="6" height="6" rx="1"
                                          stroke="currentColor" stroke-width="1.7"/>
                                </svg>
                            </button>

                            <button
                                type="button"
                                class="upi-view-button"
                                data-view-mode="list"
                                aria-label="Tampilan daftar"
                                aria-pressed="false"
                            >
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path
                                        d="M9 6H20M9 12H20M9 18H20"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                    />
                                    <circle cx="4.5" cy="6" r="1" fill="currentColor"/>
                                    <circle cx="4.5" cy="12" r="1" fill="currentColor"/>
                                    <circle cx="4.5" cy="18" r="1" fill="currentColor"/>
                                </svg>
                            </button>

                        </div>
                    </div>

                </div>

                {{-- NOTIFIKASI --}}
                @if (session('success'))
                    <div class="upi-alert upi-alert-success" role="alert" data-alert>
                        <span class="upi-alert-icon">✓</span>
                        <div>
                            <strong>Berhasil!</strong>
                            <p>{{ session('success') }}</p>
                        </div>
                        <button type="button" data-dismiss-alert aria-label="Tutup">×</button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="upi-alert upi-alert-error" role="alert" data-alert>
                        <span class="upi-alert-icon">!</span>
                        <div>
                            <strong>Ada kendala.</strong>
                            <p>{{ session('error') }}</p>
                        </div>
                        <button type="button" data-dismiss-alert aria-label="Tutup">×</button>
                    </div>
                @endif

                {{-- PRODUCT GRID --}}
                <div
                    class="upi-product-grid is-grid-view"
                    id="upiProductGrid"
                    data-product-grid
                >

                    @forelse ($productRows as $product)

                        @php
                            $productId = data_get($product, 'id');

                            $productName = data_get(
                                $product,
                                'nama',
                                data_get($product, 'name', 'Produk tanpa nama')
                            );

                            $productPrice = (float) data_get($product, 'harga', 0);
                            $productStock = (int) data_get($product, 'stok', 0);

                            $productStatus = mb_strtolower(
                                (string) data_get($product, 'status', 'nonaktif')
                            );

                            $productCategory = data_get(
                                $product,
                                'category.nama',
                                data_get(
                                    $product,
                                    'category.name',
                                    'Tanpa kategori'
                                )
                            );

                            $productDescription = data_get(
                                $product,
                                'deskripsi',
                                data_get($product, 'description', '')
                            );

                            $imageUrl = $productImageUrl(
                                data_get($product, 'gambar')
                            );

                            $editUrl = route('umkm.products.edit', $product);

                            $deleteUrl = route('umkm.products.destroy', $product);

                            $stockState = $productStock <= 0
                                ? 'empty'
                                : ($productStock <= 5 ? 'low' : 'normal');

                            $statusLabel = match ($productStatus) {
                                'aktif', 'active' => 'Aktif',
                                'nonaktif', 'non-aktif', 'inactive' => 'Nonaktif',
                                'menunggu', 'pending', 'menunggu_verifikasi' => 'Menunggu',
                                default => ucfirst(str_replace('_', ' ', $productStatus)),
                            };

                            $statusTone = in_array(
                                $productStatus,
                                ['aktif', 'active'],
                                true
                            ) ? 'active' : (
                                in_array(
                                    $productStatus,
                                    ['nonaktif', 'non-aktif', 'inactive'],
                                    true
                                ) ? 'inactive' : 'pending'
                            );
                        @endphp

                        <article
                            class="upi-product-card"
                            data-product-card
                            data-id="{{ $productId }}"
                            data-name="{{ $productName }}"
                            data-price="{{ $productPrice }}"
                            data-stock="{{ $productStock }}"
                            data-category="{{ $productCategory }}"
                            data-status="{{ $productStatus }}"
                            data-description="{{ $productDescription }}"
                        >

                            {{-- IMAGE --}}
                            <div class="upi-product-image-wrap">

                                @if ($imageUrl)
                                    <img
                                        class="upi-product-image"
                                        src="{{ $imageUrl }}"
                                        alt="{{ $productName }}"
                                        loading="lazy"
                                        onerror="this.style.display='none';this.nextElementSibling.hidden=false;"
                                    >

                                    <div
                                        class="upi-product-image-fallback"
                                        hidden
                                    >
                                        <svg viewBox="0 0 80 80" fill="none" aria-hidden="true">
                                            <rect x="8" y="8" width="64" height="64" rx="14" fill="#E8E6D9"/>
                                            <circle cx="30" cy="29" r="6" fill="#D9F36A" stroke="#193B2C" stroke-width="2"/>
                                            <path d="M14 61L33 42L44 52L54 42L66 54V65H14V61Z" fill="#C5D7B8" stroke="#193B2C" stroke-width="2" stroke-linejoin="round"/>
                                        </svg>
                                        <span>Foto tidak tersedia</span>
                                    </div>
                                @else
                                    <div class="upi-product-image-fallback">
                                        <svg viewBox="0 0 80 80" fill="none" aria-hidden="true">
                                            <rect x="8" y="8" width="64" height="64" rx="14" fill="#E8E6D9"/>
                                            <circle cx="30" cy="29" r="6" fill="#D9F36A" stroke="#193B2C" stroke-width="2"/>
                                            <path d="M14 61L33 42L44 52L54 42L66 54V65H14V61Z" fill="#C5D7B8" stroke="#193B2C" stroke-width="2" stroke-linejoin="round"/>
                                        </svg>
                                        <span>Belum ada foto</span>
                                    </div>
                                @endif

                                <span class="upi-product-category-badge">
                                    {{ $productCategory }}
                                </span>

                                <span class="upi-product-status upi-status-{{ $stockState }}">
                                    @if ($stockState === 'empty')
                                        Stok habis
                                    @elseif ($stockState === 'low')
                                        Stok menipis
                                    @else
                                        Persediaan aman
                                    @endif
                                </span>

                                {{-- ACTION MENU --}}
                                <div class="upi-product-menu-wrap">

                                    <button
                                        type="button"
                                        class="upi-product-menu-toggle"
                                        data-product-menu-toggle
                                        data-menu-target="upiMenu{{ $productId }}"
                                        aria-label="Aksi produk {{ $productName }}"
                                        aria-expanded="false"
                                    >
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <circle cx="5" cy="12" r="1.5" fill="currentColor"/>
                                            <circle cx="12" cy="12" r="1.5" fill="currentColor"/>
                                            <circle cx="19" cy="12" r="1.5" fill="currentColor"/>
                                        </svg>
                                    </button>

                                    <div
                                        class="upi-product-menu"
                                        id="upiMenu{{ $productId }}"
                                        data-product-menu
                                        hidden
                                    >

                                        <a href="{{ $editUrl }}">
                                            <span>✎</span>
                                            Edit produk
                                        </a>

                                        <form
                                            id="upiDeleteForm{{ $productId }}"
                                            action="{{ $deleteUrl }}"
                                            method="POST"
                                            data-delete-form
                                            data-product-name="{{ $productName }}"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit">
                                                <span>⌫</span>
                                                Hapus produk
                                            </button>
                                        </form>

                                    </div>
                                </div>

                            </div>

                            {{-- PRODUCT DETAILS --}}
                            <div class="upi-product-body">

                                <div class="upi-product-title-row">

                                    <div class="upi-product-title-copy">

                                        <h3 class="upi-product-name" data-product-name>
                                            {{ $productName }}
                                        </h3>

                                        <span class="upi-product-id">
                                            PRODUK #{{ str_pad((string) $productId, 4, '0', STR_PAD_LEFT) }}
                                        </span>

                                    </div>

                                    <span
                                        class="upi-status-chip upi-status-chip-{{ $statusTone }}"
                                        data-product-status
                                    >
                                        <span></span>
                                        {{ $statusLabel }}
                                    </span>

                                </div>

                                <p class="upi-product-description" data-product-description>
                                    {{ $productDescription ?: 'Belum ada deskripsi produk. Tambahkan cerita dan keunggulannya agar lebih menarik.' }}
                                </p>

                                <div class="upi-product-divider"></div>

                                <div class="upi-product-metrics">

                                    <div class="upi-product-price">
                                        <span>HARGA JUAL</span>

                                        <strong>
                                            Rp{{ number_format($productPrice, 0, ',', '.') }}
                                        </strong>
                                    </div>

                                    <div class="upi-product-stock">
                                        <span>STOK TERSEDIA</span>

                                        <strong
                                            class="{{ $stockState === 'empty' ? 'is-empty' : ($stockState === 'low' ? 'is-low' : '') }}"
                                            data-product-stock
                                        >
                                            {{ number_format($productStock, 0, ',', '.') }}
                                            <small>unit</small>
                                        </strong>
                                    </div>

                                </div>

                                <div class="upi-product-card-actions">

                                    <a
                                        href="{{ $editUrl }}"
                                        class="upi-product-edit-button"
                                    >
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M12 20H21" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            <path d="M16.5 3.5A2.12 2.12 0 0 1 19.5 6.5L8 18L4 19L5 15L16.5 3.5Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                                        </svg>

                                        Kelola produk
                                    </a>

                                    <span class="upi-product-action-decoration" aria-hidden="true">
                                        ↗
                                    </span>

                                </div>

                            </div>

                        </article>

                    @empty

                        <div class="upi-empty-state" data-empty-state>

                            <div class="upi-empty-art">
                                <span class="upi-empty-spark">✳</span>

                                <svg viewBox="0 0 100 100" fill="none" aria-hidden="true">
                                    <path d="M20 37H80L73 82H27L20 37Z" fill="#D9F36A" stroke="#193B2C" stroke-width="3" stroke-linejoin="round"/>
                                    <path d="M36 37V29C36 21.3 42.3 15 50 15C57.7 15 64 21.3 64 29V37" stroke="#193B2C" stroke-width="3" stroke-linecap="round"/>
                                    <path d="M39 55H61" stroke="#193B2C" stroke-width="3" stroke-linecap="round"/>
                                </svg>
                            </div>

                            <span class="upi-section-kicker">YOUR NEXT BIG THING</span>

                            <h3>Belum ada produk di sini.</h3>

                            <p>
                                Mulai isi katalog tokomu dengan produk lokal terbaik.
                                Produk pertamamu bisa dimulai dari sini.
                            </p>

                            <a href="{{ $createUrl }}" class="upi-primary-button">
                                <span class="upi-button-plus">+</span>
                                Tambah produk pertama
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M5 12H19M13 6L19 12L13 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>

                        </div>

                    @endforelse

                    {{-- FILTER EMPTY STATE --}}
                    @if ($productRows->isNotEmpty())
                        <div class="upi-filter-empty" data-empty-state hidden>
                            <div class="upi-filter-empty-icon">⌕</div>

                            <h3>Produk tidak ditemukan.</h3>

                            <p>
                                Coba ubah kata pencarian atau pilih filter lainnya.
                            </p>

                            <button
                                type="button"
                                class="upi-secondary-button"
                                data-reset-filters
                            >
                                Reset filter
                            </button>
                        </div>
                    @endif

                </div>

                {{-- PAGINATION --}}
                @if (is_object($productsPage) && method_exists($productsPage, 'links'))

                    <div class="upi-pagination">

                        <div class="upi-pagination-info">
                            Menampilkan
                            <strong>{{ $productsPage->firstItem() ?? 0 }}</strong>
                            sampai
                            <strong>{{ $productsPage->lastItem() ?? 0 }}</strong>
                            dari
                            <strong>{{ $productsPage->total() }}</strong>
                            produk
                        </div>

                        <div class="upi-pagination-links">
                            {{ $productsPage->withQueryString()->links() }}
                        </div>

                    </div>

                @endif

            </section>

            {{-- FOOTER --}}
            <footer class="seller-footer upi-footer">

                <div>
                    <span class="upi-footer-mark">✳</span>
                    <strong>PasarLokal</strong>
                    <span>Produk lokal, peluang tanpa batas.</span>
                </div>

                <span class="upi-footer-copy">
                    © {{ date('Y') }} PASARLOKAL / SELLER STUDIO
                </span>

            </footer>

        </div>

    </main>

</div>

</body>
</html>
