
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#F7F4EA">
    <meta
        name="description"
        content="Masuk ke PasarLokal untuk menemukan produk lokal dan mendukung UMKM Indonesia."
    >

    <title>Masuk ke Akun — PasarLokal</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >
<script>
    try {
        const normalizePath = path => path.replace(/\/+$/, '') || '/';
        const target = sessionStorage.getItem('pl-auth-transition-target');

        if (target === normalizePath(window.location.pathname)) {
            document.documentElement.classList.add('auth-transition-pending');
        }
    } catch (error) {
        // Halaman tetap berfungsi jika sessionStorage tidak tersedia.
    }
</script>

@vite([
    'resources/css/auth/login.css',
    'resources/css/auth/transitions.css',
    'resources/js/auth/login.js',
    'resources/js/auth/transitions.js'
])
</head>

<body class="login-page">
    <main class="login-shell">

        <!-- PANEL BRAND -->
        <section class="login-showcase" aria-label="Tentang PasarLokal">

            <div class="showcase-header">
                <x-brand-logo class="login-brand" height="43" />
            </div>

            <div class="showcase-content">

                <div class="showcase-eyebrow">
                    <span class="eyebrow-indicator"></span>
                    MARKETPLACE PRODUK LOKAL
                </div>

                <h1>
                    Temukan yang lokal.
                    <span>Dukung yang berarti.</span>
                </h1>

                <p class="showcase-description">
                    Ruang untuk menemukan produk istimewa dari UMKM
                    Indonesia. Setiap produk punya cerita, setiap
                    pilihan punya arti.
                </p>

                <!-- Ilustrasi dekoratif -->
                <div class="showcase-visual" aria-hidden="true">

                    <div class="visual-halo visual-halo-one"></div>
                    <div class="visual-halo visual-halo-two"></div>

                    <div class="visual-orbit">
                        <span class="orbit-point"></span>
                    </div>

                    <div class="showcase-product-card">

                        <div class="product-card-header">
                            <span class="product-card-label">
                                CERITA DARI UMKM
                            </span>

                            <span class="product-card-symbol">✳</span>
                        </div>

                        <div class="product-art">
                            <div class="product-art-sun"></div>

                            <div class="product-leaf leaf-left"></div>
                            <div class="product-leaf leaf-middle"></div>
                            <div class="product-leaf leaf-right"></div>

                            <div class="product-basket">
                                <span></span>
                            </div>
                        </div>

                        <div class="product-card-footer">
                            <div>
                                <strong>Dibuat dengan hati.</strong>
                                <span>Dari tangan kreatif lokal</span>
                            </div>

                            <span class="product-card-arrow">↗</span>
                        </div>

                    </div>

                    <div class="floating-label floating-label-local">
                        <span>✳</span>
                        <div>
                            <strong>Produk lokal</strong>
                            <small>Karya anak bangsa</small>
                        </div>
                    </div>

                    <div class="floating-label floating-label-heart">
                        <span>♡</span>
                    </div>

                </div>

                <div class="showcase-note">
                    <span></span>
                    Belanja lokal, tumbuhkan peluang.
                </div>

            </div>

            <footer class="showcase-footer">
                <span>UMKM INDONESIA</span>
                <span class="footer-dot">✳</span>
                <span>MADE WITH PURPOSE</span>
            </footer>

        </section>

        <!-- PANEL LOGIN -->
        <section class="login-panel">

            <div class="login-mobile-header">
                <x-brand-logo class="login-brand" height="40" />
            </div>

            <div class="login-content">

                <header class="login-heading">

                    <div class="login-eyebrow">
                        <span class="heading-mark">✳</span>
                        SENANG MELIHATMU LAGI
                    </div>

                    <h2>
                        Selamat datang<br>
                        kembali<span>.</span>
                    </h2>

                    <p>
                        Masukkan detail akunmu untuk melanjutkan
                        perjalanan bersama PasarLokal.
                    </p>

                </header>

                @if (session('status'))
                    <div class="login-status" role="status">
                        <span class="status-check">✓</span>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <form
                    id="loginForm"
                    class="login-form"
                    action="{{ route('login.store') }}"
                    method="POST"
                >
                    @csrf

                    <!-- EMAIL -->
                    <div class="form-field">

                        <label for="email">
                            Alamat email
                        </label>

                        <div class="input-container @error('email') has-error @enderror">

                            <span class="field-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <rect
                                        x="3.5"
                                        y="5"
                                        width="17"
                                        height="14"
                                        rx="3"
                                    />
                                    <path d="m5 7 7 5 7-5" />
                                </svg>
                            </span>

                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                placeholder="nama@email.com"
                                autocomplete="username"
                                autocapitalize="none"
                                spellcheck="false"
                                required
                                autofocus
                                aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                                @error('email') aria-describedby="email-error" @enderror
                            >

                        </div>

                        @error('email')
                            <p class="field-error" id="email-error">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    <!-- PASSWORD -->
                    <div class="form-field">

                        <div class="field-label-row">
                            <label for="password">
                                Kata sandi
                            </label>
                        </div>

                        <div class="input-container @error('password') has-error @enderror">

                            <span class="field-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <rect
                                        x="4.5"
                                        y="10"
                                        width="15"
                                        height="11"
                                        rx="2.5"
                                    />
                                    <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                                    <path d="M12 14v2" />
                                </svg>
                            </span>

                            <input
                                id="password"
                                name="password"
                                type="password"
                                placeholder="Masukkan kata sandi"
                                autocomplete="current-password"
                                required
                                aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                                @error('password') aria-describedby="password-error" @enderror
                            >

                            <button
                                id="passwordToggle"
                                class="password-toggle"
                                type="button"
                                aria-label="Tampilkan kata sandi"
                                aria-pressed="false"
                            >
                                Lihat
                            </button>

                        </div>

                        @error('password')
                            <p class="field-error" id="password-error">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    <!-- OPTIONS -->
                    <div class="login-options">

                        <label class="remember-option" for="remember">

                            <input
                                id="remember"
                                name="remember"
                                type="checkbox"
                                value="1"
                                @checked(old('remember'))
                            >

                            <span class="remember-box" aria-hidden="true">
                                <svg viewBox="0 0 16 16" fill="none">
                                    <path d="m3 8 3 3 7-7" />
                                </svg>
                            </span>

                            <span>Ingat saya</span>

                        </label>

                    </div>

                    <!-- SUBMIT -->
                    <button
                        id="loginSubmit"
                        class="login-submit"
                        type="submit"
                    >
                        <span class="submit-text">Masuk ke akun</span>

                        <span class="submit-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M5 12h14" />
                                <path d="m13 5 7 7-7 7" />
                            </svg>
                        </span>
                    </button>

                </form>

                <div class="login-separator">
                    <span></span>
                    <span>LANJUTKAN PERJALANANMU</span>
                    <span></span>
                </div>

                <p class="register-prompt">
                    Belum punya akun?
            <a
                href="{{ route('register') }}"
                data-auth-transition="register"
            >
                Daftar sekarang <span aria-hidden="true">↗</span>
            </a>
                </p>

                <div class="login-security">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 3 20 6v5c0 5-3.4 8.4-8 10-4.6-1.6-8-5-8-10V6l8-3Z" />
                        <path d="m9 12 2 2 4-4" />
                    </svg>

                    <span>Masuk dengan aman ke akun PasarLokal</span>
                </div>

            </div>

            <footer class="login-footer">
                <span>© {{ date('Y') }} PasarLokal</span>
                <span class="login-footer-dot">✳</span>
                <span>Bangga Produk Lokal</span>
            </footer>

        </section>

    </main>

    
<div class="auth-transition-overlay" id="authTransitionOverlay" aria-hidden="true">
    <div class="auth-transition-orb auth-transition-orb-one"></div>
    <div class="auth-transition-orb auth-transition-orb-two"></div>
    <div class="auth-transition-orb auth-transition-orb-three"></div>

    <div class="auth-transition-content">
        <div class="auth-transition-logo-wrap">
            <img
                class="auth-transition-logo"
                src="{{ asset('images/Logo-pasarlokal.png') }}"
                alt=""
            >
        </div>

        <span class="auth-transition-progress"></span>

        <p class="auth-transition-title">Setiap pilihan punya arti.</p>

        <span class="auth-transition-caption">
            PASARLOKAL · LOCAL GOODS, REAL STORIES
        </span>
    </div>

    <span class="auth-transition-corner">
        MADE FOR LOCAL LOVERS
    </span>
</div>

</body>
</html>
