
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#F7F6F0">

    <title>@yield('title', 'Area Pembeli — PasarLokal')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

@vite([
    'resources/css/buyer/base.css',
    'resources/js/buyer/buyer.js'
])

    @stack('page-styles')
</head>

<body class="buyer-body">

<div class="buyer-app">

    <div class="buyer-sidebar-backdrop" id="buyerBackdrop"></div>

    <!-- SIDEBAR -->
    <aside class="buyer-sidebar" id="buyerSidebar">

        <div class="buyer-sidebar-brand">
            <x-brand-logo height="37" class="buyer-brand-logo" />

            <button
                type="button"
                class="buyer-sidebar-close"
                id="buyerSidebarClose"
                aria-label="Tutup menu"
            >×</button>
        </div>

        <div class="buyer-sidebar-caption">
            RUANG BELANJA KAMU
        </div>

        <nav class="buyer-navigation" aria-label="Navigasi pembeli">

            <a
                href="{{ route('pembeli.dashboard') }}"
                class="buyer-nav-link {{ request()->routeIs('pembeli.dashboard') ? 'is-active' : '' }}"
                @if(request()->routeIs('pembeli.dashboard')) aria-current="page" @endif
            >
                <span class="buyer-nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <rect x="3" y="3" width="7" height="8" rx="1.5"/>
                        <rect x="14" y="3" width="7" height="5" rx="1.5"/>
                        <rect x="14" y="12" width="7" height="9" rx="1.5"/>
                        <rect x="3" y="15" width="7" height="6" rx="1.5"/>
                    </svg>
                </span>
                <span>Dashboard</span>
            </a>

            <div class="buyer-nav-section-label">
                AKTIVITAS BELANJA
            </div>

            <a
                href="{{ route('cart.index') }}"
                class="buyer-nav-link {{ request()->routeIs('cart.*') ? 'is-active' : '' }}"
                @if(request()->routeIs('cart.*')) aria-current="page" @endif
            >
                <span class="buyer-nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M3 4h2l2.2 10.1a2 2 0 0 0 2 1.6h7.6a2 2 0 0 0 1.9-1.4L21 8H6"/>
                        <circle cx="10" cy="20" r="1"/>
                        <circle cx="18" cy="20" r="1"/>
                    </svg>
                </span>
                <span>Keranjang</span>
            </a>

            <a
                href="{{ route('checkout.index') }}"
                class="buyer-nav-link {{ request()->routeIs('checkout.*') ? 'is-active' : '' }}"
                @if(request()->routeIs('checkout.*')) aria-current="page" @endif
            >
                <span class="buyer-nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <rect x="3.5" y="5" width="17" height="14" rx="2.5"/>
                        <path d="M3.5 10h17"/>
                        <path d="M7 15h4"/>
                    </svg>
                </span>
                <span>Checkout</span>
            </a>

            <a
                href="{{ route('pembeli.orders.index') }}"
                class="buyer-nav-link {{ request()->routeIs('pembeli.orders.*') || request()->routeIs('pembeli.payments.*') ? 'is-active' : '' }}"
                @if(request()->routeIs('pembeli.orders.*') || request()->routeIs('pembeli.payments.*')) aria-current="page" @endif
            >
                <span class="buyer-nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M6 3.5h9l4 4V20a1.5 1.5 0 0 1-1.5 1.5h-11A1.5 1.5 0 0 1 5 20V5a1.5 1.5 0 0 1 1-1.5Z"/>
                        <path d="M14.5 3.5V8h4.5"/>
                        <path d="M8.5 12h7M8.5 16h7"/>
                    </svg>
                </span>
                <span>Pesanan saya</span>
            </a>

        </nav>

        <div class="buyer-sidebar-bottom">

            <a href="{{ route('home') }}" class="buyer-back-store">
                <span class="buyer-back-store-icon">↗</span>
                <span>
                    <strong>Jelajahi PasarLokal</strong>
                    <small>Temukan produk favoritmu</small>
                </span>
            </a>

            <div class="buyer-profile">

                <div class="buyer-profile-avatar">
                    {{ \Illuminate\Support\Str::upper(
                        \Illuminate\Support\Str::substr(
                            auth()->user()->name ?? 'P',
                            0,
                            1
                        )
                    ) }}
                </div>

                <div class="buyer-profile-info">
                    <strong>{{ auth()->user()->name ?? 'Pembeli' }}</strong>
                    <span>Akun pembeli</span>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="buyer-logout"
                        aria-label="Keluar dari akun"
                        title="Keluar"
                    >
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M10 17l5-5-5-5"/>
                            <path d="M15 12H3"/>
                            <path d="M12 3h6.5A2.5 2.5 0 0 1 21 5.5v13a2.5 2.5 0 0 1-2.5 2.5H12"/>
                        </svg>
                    </button>
                </form>

            </div>

        </div>

    </aside>

    <!-- MAIN AREA -->
    <div class="buyer-main-wrap">

        <header class="buyer-topbar">

            <div class="buyer-topbar-left">

                <button
                    type="button"
                    class="buyer-menu-toggle"
                    id="buyerMenuToggle"
                    aria-label="Buka menu"
                    aria-expanded="false"
                    aria-controls="buyerSidebar"
                >
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M4 7h16M4 12h16M4 17h16"/>
                    </svg>
                </button>

                <div class="buyer-topbar-label">
                    <span>PasarLokal</span>
                    <span class="buyer-topbar-separator">/</span>
                    <span>@yield('breadcrumb', 'Area Pembeli')</span>
                </div>

            </div>

            <a href="{{ route('home') }}" class="buyer-topbar-store">
                <span>Ke toko</span>
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M7 17 17 7M7 7h10v10"/>
                </svg>
            </a>

        </header>

        <main class="buyer-main">

            @if (session('success'))
                <div class="buyer-alert buyer-alert-success" role="status" data-flash>
                    <span class="buyer-alert-symbol">✓</span>
                    <span>{{ session('success') }}</span>
                    <button type="button" class="buyer-alert-close" aria-label="Tutup notifikasi">×</button>
                </div>
            @endif

            @if (session('error'))
                <div class="buyer-alert buyer-alert-error" role="alert" data-flash>
                    <span class="buyer-alert-symbol">!</span>
                    <span>{{ session('error') }}</span>
                    <button type="button" class="buyer-alert-close" aria-label="Tutup notifikasi">×</button>
                </div>
            @endif

            @if ($errors->any())
                <div class="buyer-alert buyer-alert-error" role="alert">
                    <span class="buyer-alert-symbol">!</span>
                    <div>
                        <strong>Periksa kembali data kamu.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @hasSection('page-title')
                <div class="buyer-page-heading">
                    <div class="buyer-page-heading-copy">
                        <div class="buyer-eyebrow">
                            @yield('eyebrow', 'AREA PEMBELI')
                        </div>

                        <h1>@yield('page-title')</h1>

                        @hasSection('page-description')
                            <p>@yield('page-description')</p>
                        @endif
                    </div>

                    @hasSection('page-actions')
                        <div class="buyer-page-actions">
                            @yield('page-actions')
                        </div>
                    @endif
                </div>
            @endif

            @yield('content')

        </main>

        <footer class="buyer-footer">
            <span>© {{ date('Y') }} PasarLokal</span>
            <span class="buyer-footer-dot">✳</span>
            <span>Bangga produk lokal Indonesia.</span>
        </footer>

    </div>

</div>

@stack('page-scripts')

</body>
</html>
