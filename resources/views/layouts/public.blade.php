
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#F7F4EA">

    <title>@yield('title', 'PasarLokal — Produk Lokal Indonesia')</title>

    <meta
        name="description"
        content="@yield('meta_description', 'Jelajahi produk lokal dan dukung UMKM Indonesia bersama PasarLokal.')"
    >

    @vite('resources/css/public/base.css')
    @stack('page-styles')
</head>

<body class="@yield('body_class', 'public-body')">

<header class="public-header">
    <div class="public-header-inner">

        <a href="{{ route('home') }}" class="public-brand">
            <img
                src="{{ asset('images/Logo-pasarlokal.png') }}"
                alt="Logo PasarLokal"
                class="public-brand-logo"
            >

            <span class="public-brand-name">
                Pasar<span>Lokal.</span>
            </span>
        </a>

        <nav class="public-navigation" aria-label="Navigasi utama">
            <a href="{{ route('home') }}">Beranda</a>
            <a href="{{ route('home') }}#kategori">Kategori</a>
            <a href="{{ route('home') }}#produk">Jelajahi produk</a>
        </nav>

        <div class="public-header-actions">
            @auth
                @if (auth()->user()->role === 'pembeli')
                    <a href="{{ route('cart.index') }}" class="public-cart-link">
                        <span aria-hidden="true">🛒</span>
                        Keranjang
                    </a>

                    <a href="{{ route('pembeli.dashboard') }}" class="public-button public-button-light">
                        Dashboard
                    </a>
                @elseif (auth()->user()->role === 'umkm')
                    <a href="{{ route('umkm.dashboard') }}" class="public-button public-button-light">
                        Dashboard Penjual
                    </a>
                @else
                    <a href="{{ route('admin.dashboard') }}" class="public-button public-button-light">
                        Dashboard Admin
                    </a>
                @endif
            @else
                <a href="{{ route('login') }}" class="public-login-link">
                    Masuk
                </a>

                <a href="{{ route('register') }}" class="public-button public-button-primary">
                    Mulai sekarang <span>↗</span>
                </a>
            @endauth
        </div>

    </div>
</header>

<main id="main-content">
    @yield('content')
</main>

<footer class="public-footer">
    <div class="public-footer-inner">
        <a href="{{ route('home') }}" class="public-footer-brand">
            PasarLokal<span>.</span>
        </a>

        <p>
            Belanja produk lokal, dukung UMKM Indonesia.
        </p>

        <a href="{{ route('home') }}" class="public-footer-top">
            Kembali ke atas ↑
        </a>
    </div>

    <div class="public-footer-bottom">
        © {{ date('Y') }} PasarLokal. Dibuat untuk mendukung usaha lokal Indonesia.
    </div>
</footer>
    
@stack('page-scripts')

</body>
</html>
