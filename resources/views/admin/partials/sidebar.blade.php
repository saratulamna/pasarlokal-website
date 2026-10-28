
@php
    /*
    |--------------------------------------------------------------------------
    | ADMIN SIDEBAR — PASARLOKAL
    |--------------------------------------------------------------------------
    | Satu sidebar bersama untuk seluruh halaman administrator.
    */

    $admin = $adminUser ?? auth()->user();

    /*
    |--------------------------------------------------------------------------
    | ROUTE HELPER
    |--------------------------------------------------------------------------
    */

    $routeFor = static function (array $names, string $fallback = '#') {
        foreach ($names as $name) {
            if (\Illuminate\Support\Facades\Route::has($name)) {
                return route($name);
            }
        }

        return $fallback;
    };

    /*
    |--------------------------------------------------------------------------
    | ACTIVE MENU
    |--------------------------------------------------------------------------
    */

    $activeMenu = $activeMenu ?? '';

    if ($activeMenu === '') {
        if (request()->routeIs('admin.dashboard')) {
            $activeMenu = 'dashboard';
        } elseif (request()->routeIs('admin.users.*', 'admin.user.*')) {
            $activeMenu = 'users';
        } elseif (request()->routeIs(
            'admin.umkms.*',
            'admin.umkm.*',
            'admin.stores.*'
        )) {
            $activeMenu = 'umkms';
        } elseif (request()->routeIs('admin.payments.*')) {
            $activeMenu = 'payments';
        } elseif (request()->routeIs('admin.products.*')) {
            $activeMenu = 'products';
        } elseif (request()->routeIs('admin.categories.*')) {
            $activeMenu = 'categories';
        } elseif (request()->routeIs(
            'admin.orders.*',
            'admin.transactions.*'
        )) {
            $activeMenu = 'orders';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | MENU URL
    |--------------------------------------------------------------------------
    */

    $dashboardUrl = $routeFor([
        'admin.dashboard',
    ], '/admin/dashboard');

    $userUrl = $routeFor([
        'admin.users.index',
        'admin.user.index',
    ]);

    $umkmUrl = $routeFor([
        'admin.umkms.index',
        'admin.umkm.index',
        'admin.stores.index',
    ]);

    $productUrl = $routeFor([
        'admin.products.index',
    ]);

    $categoryUrl = $routeFor([
        'admin.categories.index',
    ]);

    $orderUrl = $routeFor([
        'admin.orders.index',
        'admin.transactions.index',
    ]);

    $paymentUrl = $routeFor([
        'admin.payments.index',
    ]);

    $homeUrl = $routeFor([
        'home',
    ], '/');
@endphp


{{-- =========================================================
     PASARLOKAL — ADMIN SIDEBAR
========================================================= --}}

<aside class="admin-sidebar" id="adminSidebar">

    {{-- BRAND --}}
    <a href="{{ $dashboardUrl }}" class="admin-brand">

        <span class="admin-brand-mark">
            P<span>.</span>
        </span>

        <span class="admin-brand-copy">
            Pasar<span>Lokal</span>

            <small>ADMIN STUDIO</small>
        </span>

    </a>


    {{-- WORKSPACE LABEL --}}
    <div class="admin-sidebar-label">
        WORKSPACE
    </div>


    {{-- NAVIGATION --}}
    <nav class="admin-nav" aria-label="Navigasi admin">

        {{-- DASHBOARD --}}
        <a
            href="{{ $dashboardUrl }}"
            @class([
                'admin-nav-link',
                'is-active' => $activeMenu === 'dashboard',
            ])
            @if ($activeMenu === 'dashboard')
                aria-current="page"
            @endif
        >
            <span class="admin-nav-icon">⌂</span>

            <span>Dashboard</span>

            @if ($activeMenu === 'dashboard')
                <i aria-hidden="true"></i>
            @endif
        </a>


        {{-- SECTION LABEL --}}
        <div class="admin-nav-caption">
            MANAJEMEN
        </div>


        {{-- PENGGUNA --}}
        <a
            href="{{ $userUrl }}"
            @class([
                'admin-nav-link',
                'is-active' => $activeMenu === 'users',
            ])
            @if ($activeMenu === 'users')
                aria-current="page"
            @endif
        >
            <span class="admin-nav-icon">♙</span>

            <span>Pengguna</span>

            @if ($activeMenu === 'users')
                <i aria-hidden="true"></i>
            @endif
        </a>


        {{-- UMKM --}}
        <a
            href="{{ $umkmUrl }}"
            @class([
                'admin-nav-link',
                'is-active' => $activeMenu === 'umkms',
            ])
            @if ($activeMenu === 'umkms')
                aria-current="page"
            @endif
        >
            <span class="admin-nav-icon">⌂</span>

            <span>UMKM &amp; toko</span>

            @if (($pendingUmkmCount ?? 0) > 0)
                <span class="admin-nav-badge">
                    {{ $pendingUmkmCount }}
                </span>
            @endif

            @if ($activeMenu === 'umkms')
                <i aria-hidden="true"></i>
            @endif
        </a>


        {{-- PRODUK --}}
        <a
            href="{{ $productUrl }}"
            @class([
                'admin-nav-link',
                'is-active' => $activeMenu === 'products',
            ])
            @if ($activeMenu === 'products')
                aria-current="page"
            @endif
        >
            <span class="admin-nav-icon">▦</span>

            <span>Produk</span>

            @if ($activeMenu === 'products')
                <i aria-hidden="true"></i>
            @endif
        </a>


        {{-- KATEGORI --}}
        <a
            href="{{ $categoryUrl }}"
            @class([
                'admin-nav-link',
                'is-active' => $activeMenu === 'categories',
            ])
            @if ($activeMenu === 'categories')
                aria-current="page"
            @endif
        >
            <span class="admin-nav-icon">▤</span>

            <span>Kategori</span>

            @if ($activeMenu === 'categories')
                <i aria-hidden="true"></i>
            @endif
        </a>


        {{-- PESANAN --}}
        <a
            href="{{ $orderUrl }}"
            @class([
                'admin-nav-link',
                'is-active' => $activeMenu === 'orders',
            ])
            @if ($activeMenu === 'orders')
                aria-current="page"
            @endif
        >
            <span class="admin-nav-icon">▧</span>

            <span>Pesanan</span>

            @if (($pendingOrdersCount ?? 0) > 0)
                <span class="admin-nav-badge admin-badge-orange">
                    {{ $pendingOrdersCount }}
                </span>
            @endif

            @if ($activeMenu === 'orders')
                <i aria-hidden="true"></i>
            @endif
        </a>


        {{-- VERIFIKASI PEMBAYARAN --}}
        <a
            href="{{ $paymentUrl }}"
            @class([
                'admin-nav-link',
                'is-active' => $activeMenu === 'payments',
            ])
            @if ($activeMenu === 'payments')
                aria-current="page"
            @endif
        >
            <span class="admin-nav-icon">↗</span>

            <span>Verifikasi pembayaran</span>

            @if ($activeMenu === 'payments')
                <i aria-hidden="true"></i>
            @endif
        </a>

    </nav>


    {{-- ADMIN ACCOUNT --}}
    <div class="admin-sidebar-bottom">

        <div class="admin-profile">

            <div class="admin-avatar">
                {{ mb_strtoupper(
                    mb_substr(
                        (string) ($admin->name ?? 'A'),
                        0,
                        1
                    )
                ) }}
            </div>

            <div class="admin-profile-info">
                <strong>
                    {{ $admin->name ?? 'Administrator' }}
                </strong>

                <small>
                    Super Administrator
                </small>
            </div>

            <span
                class="admin-online-dot"
                aria-label="Status akun"
            ></span>

        </div>


        {{-- LOGOUT --}}
        @if (\Illuminate\Support\Facades\Route::has('logout'))

            <form
                method="POST"
                action="{{ route('logout') }}"
                class="admin-logout-form"
            >
                @csrf

                <button
                    type="submit"
                    class="admin-logout"
                >
                    <span aria-hidden="true">↪</span>

                    Keluar dari akun
                </button>

            </form>

        @endif

    </div>

</aside>
