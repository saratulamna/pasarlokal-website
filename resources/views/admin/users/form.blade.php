@php
    $isEdit = $user !== null;

    $adminName = auth()->user()->name ?? 'Administrator';
    $initial = strtoupper(mb_substr($adminName, 0, 1));

    $routeFor = function (array $candidates, string $fallback = '#') {
        foreach ($candidates as $candidate) {
            if (\Illuminate\Support\Facades\Route::has($candidate)) {
                return route($candidate);
            }
        }

        return $fallback;
    };
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="theme-color" content="#193B2C">

    <title>
        {{ $isEdit ? 'Edit Pengguna' : 'Tambah Pengguna' }} — PasarLokal
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])
</head>

<body class="admin-users-page">

<div class="au-shell" id="auShell">

    <aside class="au-sidebar" id="auSidebar">

        <a href="{{ route('admin.dashboard') }}" class="au-brand">
            <span class="au-brand-mark">P.</span>

            <span class="au-brand-name">
                Pasar<span>Lokal</span>
            </span>
        </a>

        <div class="au-sidebar-label">WORKSPACE</div>

        <nav class="au-navigation">

            <a href="{{ route('admin.dashboard') }}" class="au-nav-link">
                <span class="au-nav-icon">◫</span>
                <span>Dashboard</span>
            </a>

            <a
                href="{{ route('admin.users.index') }}"
                class="au-nav-link is-active"
            >
                <span class="au-nav-icon">♙</span>
                <span>Pengguna</span>
                <span class="au-active-dot"></span>
            </a>

            <a
                href="{{ $routeFor(['admin.umkms.index', 'admin.umkm.index']) }}"
                class="au-nav-link"
            >
                <span class="au-nav-icon">⌂</span>
                <span>UMKM</span>
            </a>

            <a
                href="{{ $routeFor(['admin.products.index']) }}"
                class="au-nav-link"
            >
                <span class="au-nav-icon">▧</span>
                <span>Produk</span>
            </a>

            <a
                href="{{ $routeFor(['admin.categories.index']) }}"
                class="au-nav-link"
            >
                <span class="au-nav-icon">▤</span>
                <span>Kategori</span>
            </a>

            <a
                href="{{ $routeFor(['admin.orders.index', 'admin.transactions.index']) }}"
                class="au-nav-link"
            >
                <span class="au-nav-icon">▣</span>
                <span>Pesanan</span>
            </a>

        </nav>

        <div class="au-sidebar-bottom">

            <div class="au-sidebar-caption">SIGNED IN AS</div>

            <div class="au-admin-profile">
                <div class="au-admin-avatar">{{ $initial }}</div>

                <div class="au-admin-info">
                    <strong>{{ $adminName }}</strong>
                    <span>Administrator</span>
                </div>

                <span class="au-online-dot"></span>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit" class="au-logout">
                    <span>↪</span>
                    Keluar dari akun
                </button>
            </form>

        </div>
    </aside>

    <button
        type="button"
        class="au-overlay"
        id="auOverlay"
        aria-label="Tutup navigasi"
        tabindex="-1"
    ></button>

    <main class="au-main">

        <header class="au-topbar">

            <div class="au-topbar-left">
                <button
                    type="button"
                    id="auMenuToggle"
                    class="au-menu-toggle"
                    aria-label="Buka menu"
                >
                    ☰
                </button>

                <div class="au-breadcrumb">
                    <a href="{{ route('admin.users.index') }}">Pengguna</a>
                    <span class="au-breadcrumb-separator">/</span>

                    <strong>{{ $isEdit ? 'Edit pengguna' : 'Tambah pengguna' }}</strong>
                </div>
            </div>

            <div class="au-topbar-right">
                <span class="au-topbar-date">
                    {{ now()->translatedFormat('l, d F Y') }}
                </span>

                <div class="au-topbar-avatar">{{ $initial }}</div>
            </div>

        </header>

        <div class="au-content au-form-content">

            @if (session('error'))
                <div class="au-flash au-flash-error">
                    <span class="au-flash-icon">!</span>
                    {{ session('error') }}
                </div>
            @endif

            <section class="au-page-heading au-form-heading">

                <div class="au-heading-copy">

                    <div class="au-eyebrow">
                        <span></span>
                        ACCOUNT MANAGEMENT
                    </div>

                    <h1>
                        {{ $isEdit ? 'Perbarui' : 'Tambah' }}
                        <span>Pengguna.</span>
                    </h1>

                    <p>
                        {{ $isEdit
                            ? 'Perbarui informasi akun dan hak akses pengguna.'
                            : 'Buat akun baru untuk menjadi bagian dari PasarLokal.' }}
                    </p>

                </div>

                <a
                    href="{{ route('admin.users.index') }}"
                    class="au-secondary-button"
                >
                    <span>←</span>
                    Kembali
                </a>

            </section>

            <section class="au-form-layout">

                <div class="au-form-panel">

                    <div class="au-form-panel-heading">
                        <span class="au-form-heading-icon">♙</span>

                        <div>
                            <h2>Informasi Akun</h2>
                            <p>Lengkapi setiap informasi yang diperlukan.</p>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="au-form-errors">
                            <strong>Ada informasi yang perlu diperbaiki.</strong>

                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form
                        action="{{ $isEdit
                            ? route('admin.users.update', $user)
                            : route('admin.users.store') }}"
                        method="POST"
                        class="au-user-form"
                    >
                        @csrf

                        @if ($isEdit)
                            @method('PUT')
                        @endif

                        <div class="au-field">
                            <label for="name">Nama lengkap <span>*</span></label>

                            <input
                                id="name"
                                name="name"
                                type="text"
                                value="{{ old('name', $user?->name ?? '') }}"
                                placeholder="Contoh: Andi Pratama"
                                maxlength="255"
                                required
                                autofocus
                            >

                            @error('name')
                                <small class="au-field-error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="au-field">
                            <label for="email">Alamat email <span>*</span></label>

                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email', $user?->email ?? '') }}"
                                placeholder="nama@email.com"
                                maxlength="255"
                                autocomplete="email"
                                required
                            >

                            @error('email')
                                <small class="au-field-error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="au-field">
                            <label for="role">Hak akses / role <span>*</span></label>

                            <select id="role" name="role" required>
                                <option
                                    value="pembeli"
                                    @selected(old('role', $user?->role ?? 'pembeli') === 'pembeli')
                                >
                                    Pembeli
                                </option>

                                <option
                                    value="umkm"
                                    @selected(old('role', $user?->role ?? 'pembeli') === 'umkm')
                                >
                                    Pemilik UMKM
                                </option>

                                <option
                                    value="admin"
                                    @selected(old('role', $user?->role ?? 'pembeli') === 'admin')
                                >
                                    Administrator
                                </option>
                            </select>

                            <small class="au-field-hint">
                                Pilih role sesuai tanggung jawab akun.
                            </small>

                            @if ($isEdit && (int) $user->id === (int) auth()->id())
                                <small class="au-field-hint">
                                    Role akun yang sedang digunakan tidak akan diubah.
                                </small>
                            @endif

                            @error('role')
                                <small class="au-field-error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="au-form-divider"></div>

                        <div class="au-form-section-title">
                            <span>KEAMANAN AKUN</span>

                            @if ($isEdit)
                                <span class="au-optional-label">Opsional</span>
                            @endif
                        </div>

                        <div class="au-field">
                            <label for="password">
                                Password
                                @if (!$isEdit)
                                    <span>*</span>
                                @endif
                            </label>

                            <input
                                id="password"
                                name="password"
                                type="password"
                                placeholder="{{ $isEdit
                                    ? 'Kosongkan jika tidak ingin mengganti'
                                    : 'Minimal 8 karakter' }}"
                                minlength="8"
                                autocomplete="new-password"
                                {{ $isEdit ? '' : 'required' }}
                            >

                            @error('password')
                                <small class="au-field-error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="au-field">
                            <label for="password_confirmation">
                                Konfirmasi password
                                @if (!$isEdit)
                                    <span>*</span>
                                @endif
                            </label>

                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                placeholder="Masukkan ulang password"
                                autocomplete="new-password"
                                {{ $isEdit ? '' : 'required' }}
                            >
                        </div>

                        <div class="au-form-note">
                            <span>✳</span>

                            <p>
                                Password disimpan dalam bentuk hash.
                                Jangan memasukkan password pengguna lain
                                atau membagikan kredensial akun.
                            </p>
                        </div>

                        <div class="au-form-actions">

                            <a
                                href="{{ route('admin.users.index') }}"
                                class="au-cancel-button"
                            >
                                Batal
                            </a>

                            <button type="submit" class="au-primary-button">
                                <span>{{ $isEdit ? '✓' : '+' }}</span>

                                {{ $isEdit ? 'Simpan Perubahan' : 'Buat Pengguna' }}

                                <span class="au-button-arrow">↗</span>
                            </button>

                        </div>

                    </form>

                </div>

                <aside class="au-form-aside">

                    <div class="au-access-card">

                        <div class="au-access-decoration">PL.</div>

                        <span class="au-access-tag">ACCESS GUIDE</span>

                        <h3>Setiap role punya peran.</h3>

                        <p>
                            Berikan hak akses sesuai kebutuhan akun
                            agar setiap bagian marketplace tetap teratur.
                        </p>

                        <div class="au-access-item">
                            <span class="au-access-icon au-access-admin">A</span>

                            <div>
                                <strong>Administrator</strong>
                                <p>Mengelola sistem marketplace.</p>
                            </div>
                        </div>

                        <div class="au-access-item">
                            <span class="au-access-icon au-access-umkm">U</span>

                            <div>
                                <strong>Pemilik UMKM</strong>
                                <p>Mengelola toko dan produk.</p>
                            </div>
                        </div>

                        <div class="au-access-item">
                            <span class="au-access-icon au-access-buyer">P</span>

                            <div>
                                <strong>Pembeli</strong>
                                <p>Menjelajahi dan membeli produk lokal.</p>
                            </div>
                        </div>

                    </div>

                    <div class="au-security-card">
                        <span class="au-security-icon">✦</span>

                        <strong>Keamanan tetap utama</strong>

                        <p>
                            Periksa role sebelum menyimpan perubahan.
                            Hak administrator memberikan akses pengelolaan
                            yang lebih luas.
                        </p>
                    </div>

                </aside>

            </section>

            <footer class="au-footer">
                <span>© {{ date('Y') }} PASARLOKAL / ADMIN STUDIO</span>
                <span>Mengembangkan ekonomi lokal, bersama. <b>✳</b></span>
            </footer>

        </div>

    </main>

</div>

</body>
</html>