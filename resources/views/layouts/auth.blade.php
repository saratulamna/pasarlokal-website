<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#F7F4EA">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Akun') — PasarLokal</title>

    @vite([
        'resources/css/auth.css',
        'resources/js/auth.js'
    ])
</head>

<body class="auth-page">
    <main class="auth-shell">

<a href="{{ route('home') }}" class="brand" aria-label="PasarLokal">
    <img
        src="{{ asset('images/Logo-pasarlokal.png') }}"
        alt="PasarLokal"
        class="brand-logo-img"
    >
</a>


        <div class="auth-frame">
            <aside class="auth-aside">
                <div class="story-top">
                    <span class="story-badge">
                        <span class="status-dot"></span>
                        DUKUNG PRODUK LOKAL
                    </span>

                    <span class="story-number">ID / UMKM</span>
                </div>

                <div class="story-copy">
                    <p class="story-kicker">BELANJA DENGAN MAKNA.</p>

                    <h1>
                        Cerita lokal,
                        <span>tumbuh lebih besar.</span>
                    </h1>

                    <p class="story-description">
                        Temukan produk pilihan dari usaha lokal Indonesia.
                        Dukung para pelaku UMKM lewat setiap pembelian.
                    </p>
                </div>

                <div class="story-art" aria-hidden="true">
                    <div class="art-card art-card-back">
                        <span class="art-sun"></span>
                        <span class="art-line"></span>
                        <span class="art-line short"></span>
                    </div>

                    <div class="art-card art-card-front">
                        <div class="art-illustration">
                            <span class="art-leaf leaf-one"></span>
                            <span class="art-leaf leaf-two"></span>
                            <span class="art-pot"></span>
                        </div>

                        <div class="art-caption">
                            <span class="art-caption-label">PILIHAN LOKAL</span>
                            <strong>Dibuat dengan hati.</strong>
                        </div>

                        <span class="art-star">✳</span>
                    </div>

                    <span class="art-orbit orbit-one"></span>
                    <span class="art-orbit orbit-two"></span>
                </div>

                <div class="story-bottom">
                    <span>BAIK UNTUK USAHA LOKAL.</span>
                    <span>© PASARLOKAL</span>
                </div>
            </aside>

            <section class="auth-panel">
                @yield('content')
            </section>
        </div>

        <footer class="auth-footer">
            <span>PasarLokal — Marketplace Produk Lokal dan UMKM</span>
            <a href="{{ route('home') }}">Kembali ke beranda ↗</a>
        </footer>
    </main>
</body>
</html>