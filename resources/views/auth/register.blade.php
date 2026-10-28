
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#F7F4EA">
    <meta
        name="description"
        content="Buat akun PasarLokal dan mulai menemukan produk UMKM Indonesia."
    >

    <title>Buat Akun — PasarLokal</title>

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
    'resources/css/auth/register.css',
    'resources/css/auth/transitions.css',
    'resources/js/auth/register.js',
    'resources/js/auth/transitions.js'
])
</head>

<body class="register-page">

    <main class="register-layout">

        <!-- ==========================================
             LEFT: REGISTRATION FORM
        =========================================== -->
        <section class="register-panel">

            <header class="register-header">
                <x-brand-logo class="register-brand" height="40" />

                <div class="register-header-login">
                    <span>Sudah punya akun?</span>
                    <a href="{{ route('login') }}">Masuk</a>
                </div>
            </header>

            <div class="register-content">

                <div class="register-heading">

                    <div class="register-eyebrow">
                        <span class="register-eyebrow-icon">✳</span>
                        BERGABUNG DENGAN KAMI
                    </div>

                    <h1>
                        Mulai perjalanan<br>
                        <span>lokalmu.</span>
                    </h1>

                    <p>
                        Buat akun PasarLokal untuk menemukan produk
                        menarik dan mendukung UMKM Indonesia.
                    </p>

                </div>

                @if (session('status'))
                    <div class="register-status" role="status">
                        <span class="status-icon">✓</span>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <form
                    id="registerForm"
                    class="register-form"
                    action="{{ route('register.store') }}"
                    method="POST"
                    novalidate
                >
                    @csrf

                    <!-- FULL NAME -->
                    <div class="register-field">

                        <label for="name">Nama lengkap</label>

                        <div class="register-input-wrap @error('name') has-error @enderror">

                            <span class="register-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <circle cx="12" cy="8" r="4" />
                                    <path d="M5 20v-1a7 7 0 0 1 14 0v1" />
                                </svg>
                            </span>

                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="{{ old('name') }}"
                                placeholder="Masukkan nama lengkap"
                                autocomplete="name"
                                maxlength="255"
                                required
                                autofocus
                                aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                                @error('name') aria-describedby="name-error" @enderror
                            >

                            <span class="input-valid-icon" aria-hidden="true">✓</span>
                        </div>

                        @error('name')
                            <p class="register-error" id="name-error">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    <!-- EMAIL -->
                    <div class="register-field">

                        <label for="email">Alamat email</label>

                        <div class="register-input-wrap @error('email') has-error @enderror">

                            <span class="register-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <rect x="3.5" y="5" width="17" height="14" rx="3" />
                                    <path d="m5 7 7 5 7-5" />
                                </svg>
                            </span>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="nama@email.com"
                                autocomplete="email"
                                autocapitalize="none"
                                spellcheck="false"
                                maxlength="255"
                                required
                                aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                                @error('email') aria-describedby="email-error" @enderror
                            >

                            <span class="input-valid-icon" aria-hidden="true">✓</span>
                        </div>

                        @error('email')
                            <p class="register-error" id="email-error">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    <!-- PASSWORD -->
                    <div class="register-field">

                        <label for="password">Kata sandi</label>

                        <div class="register-input-wrap @error('password') has-error @enderror">

                            <span class="register-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <rect x="4.5" y="10" width="15" height="11" rx="2.5" />
                                    <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                                    <path d="M12 14v2" />
                                </svg>
                            </span>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Minimal 8 karakter"
                                autocomplete="new-password"
                                minlength="8"
                                required
                                aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                                @error('password') aria-describedby="password-error" @enderror
                            >

                            <button
                                type="button"
                                class="register-password-toggle"
                                data-password-target="password"
                                aria-label="Tampilkan kata sandi"
                                aria-pressed="false"
                            >
                                Lihat
                            </button>

                        </div>

                        <div class="password-strength" id="passwordStrength" aria-live="polite">
                            <div class="strength-bars" aria-hidden="true">
                                <span></span>
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>

                            <span class="strength-text">
                                Gunakan minimal 8 karakter
                            </span>
                        </div>

                        @error('password')
                            <p class="register-error" id="password-error">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    <!-- PASSWORD CONFIRMATION -->
                    <div class="register-field">

                        <label for="password_confirmation">
                            Konfirmasi kata sandi
                        </label>

                        <div class="register-input-wrap" id="confirmationWrap">

                            <span class="register-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M7 12.5 10.2 16 17.5 8" />
                                    <path d="M12 3 20 6v5c0 5-3.4 8.4-8 10-4.6-1.6-8-5-8-10V6l8-3Z" />
                                </svg>
                            </span>

                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                placeholder="Ulangi kata sandi"
                                autocomplete="new-password"
                                minlength="8"
                                required
                                aria-invalid="false"
                            >

                            <button
                                type="button"
                                class="register-password-toggle"
                                data-password-target="password_confirmation"
                                aria-label="Tampilkan konfirmasi kata sandi"
                                aria-pressed="false"
                            >
                                Lihat
                            </button>

                        </div>

                        <p
                            class="register-error confirmation-error"
                            id="confirmation-error"
                            hidden
                        ></p>

                    </div>

                    <!-- TERMS -->
                    <div class="register-terms">
                        <span class="terms-icon" aria-hidden="true">✳</span>

                        <p>
                            Pastikan data yang kamu masukkan benar agar
                            akun dapat digunakan dengan baik.
                        </p>
                    </div>

                    <!-- SUBMIT -->
                    <button
                        type="submit"
                        class="register-submit"
                        id="registerSubmit"
                    >
                        <span class="register-submit-text">
                            Buat akun sekarang
                        </span>

                        <span class="register-submit-arrow" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M5 12h14" />
                                <path d="m13 5 7 7-7 7" />
                            </svg>
                        </span>
                    </button>

                </form>

                <div class="register-divider">
                    <span></span>
                    <span>LANGKAH PERTAMAMU</span>
                    <span></span>
                </div>

                <p class="register-bottom-text">
                    Sudah menjadi bagian dari PasarLokal?
                    <a href="{{ route('login') }}" data-auth-transition="login">
    Masuk
</a>
                </p>

            </div>

            <footer class="register-footer">
                <span>© {{ date('Y') }} PasarLokal</span>
                <span class="register-footer-dot">✳</span>
                <span>Bangga Produk Lokal</span>
            </footer>

        </section>

        <!-- ==========================================
             RIGHT: BRAND EXPERIENCE
        =========================================== -->
        <aside class="register-showcase">

            <div class="showcase-top">
                <span class="showcase-tag">
                    <span></span>
                    LOCAL GOODS, REAL STORIES
                </span>

                <span class="showcase-number">01 / 03</span>
            </div>

            <div class="showcase-main">

                <p class="showcase-kicker">LEBIH DARI SEKADAR BELANJA</p>

                <h2>
                    Hal baik<br>
                    dimulai dari<br>
                    <span>pilihan kecil.</span>
                </h2>

                <p class="showcase-description">
                    Kenali produk lokal, temukan karya unik,
                    dan jadi bagian dari perjalanan UMKM Indonesia.
                </p>

                <!-- Decorative editorial illustration -->
                <div class="register-art" aria-hidden="true">

                    <div class="art-ring art-ring-one"></div>
                    <div class="art-ring art-ring-two"></div>

                    <div class="art-sun"></div>

                    <div class="art-main-card">

                        <div class="art-card-header">
                            <span>LOCAL COLLECTION</span>
                            <span class="art-star">✳</span>
                        </div>

                        <div class="art-scene">

                            <div class="art-arch"></div>
                            <div class="art-pot">
                                <span class="art-pot-line"></span>
                            </div>

                            <div class="art-plant">
                                <span class="plant-stem"></span>
                                <span class="plant-leaf leaf-a"></span>
                                <span class="plant-leaf leaf-b"></span>
                                <span class="plant-leaf leaf-c"></span>
                                <span class="plant-leaf leaf-d"></span>
                            </div>

                        </div>

                        <div class="art-card-footer">
                            <div>
                                <strong>Made with purpose.</strong>
                                <span>Crafted by local makers</span>
                            </div>

                            <span class="art-card-arrow">↗</span>
                        </div>

                    </div>

                    <div class="art-sticker">
                        <span>♡</span>
                    </div>

                    <div class="art-label">
                        <span class="art-label-symbol">✳</span>
                        <div>
                            <strong>Support local</strong>
                            <small>Grow together.</small>
                        </div>
                    </div>

                </div>

                <div class="showcase-benefits">

                    <div class="benefit-item">
                        <span class="benefit-icon">01</span>
                        <div>
                            <strong>Temukan</strong>
                            <small>Produk unik pilihan</small>
                        </div>
                    </div>

                    <div class="benefit-divider"></div>

                    <div class="benefit-item">
                        <span class="benefit-icon">02</span>
                        <div>
                            <strong>Dukung</strong>
                            <small>Perjalanan UMKM</small>
                        </div>
                    </div>

                </div>

            </div>

            <footer class="showcase-footer">
                <span>BUILT AROUND LOCAL STORIES</span>
                <span>✳</span>
            </footer>

        </aside>

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
