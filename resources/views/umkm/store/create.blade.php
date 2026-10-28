
@php
    use Illuminate\Support\Facades\Route;

    $seller = auth()->user();

    $postRouteName = collect([
        'umkm.toko.store',
        'umkm.store.store',
        'umkm.toko.simpan',
        'umkm.store',
    ])->first(fn ($name) => Route::has($name));

    $storePostUrl = $postRouteName
        ? route($postRouteName)
        : url('/umkm/toko/buat');

    $categoryOptions = [
        'Makanan & Minuman',
        'Fashion',
        'Kerajinan',
        'Kecantikan',
        'Pertanian',
        'Aksesoris',
        'Perlengkapan Rumah',
        'Lainnya',
    ];
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#193B2C">
    <meta name="description" content="Buat toko UMKM di PasarLokal dan mulai perkenalkan produk lokalmu.">

    <title>Buat Toko — PasarLokal</title>

    @vite([
        'resources/css/app.css',
        'resources/css/pages/umkm/store-create.css',
        'resources/js/pages/umkm/store-create.js',
    ])
</head>

<body class="store-create-body">

<div class="stc-page">

    {{-- TOP NAVIGATION --}}
    <header class="stc-topbar">

        <a href="{{ route('umkm.dashboard') }}" class="stc-brand">
            <span class="stc-brand-mark">P<span>.</span></span>

            <span class="stc-brand-name">
                Pasar<span>Lokal</span>
                <small>SELLER STUDIO</small>
            </span>
        </a>

        <div class="stc-topbar-right">
            <span class="stc-secure-label">
                <span>✓</span> AREA PENJUAL
            </span>

            <a href="{{ route('umkm.dashboard') }}" class="stc-back-link">
                <span>←</span> Dashboard
            </a>
        </div>

    </header>

    <main class="stc-main">

        {{-- HERO --}}
        <section class="stc-hero">

            <div class="stc-hero-copy">

                <div class="stc-eyebrow">
                    <span></span>
                    YOUR BUSINESS STARTS HERE
                </div>

                <h1>
                    Saatnya usahamu<br>
                    punya <span>rumah sendiri.</span>
                </h1>

                <p>
                    Ceritakan tentang tokomu, perkenalkan karya terbaikmu,
                    dan mulailah membangun kepercayaan pelanggan bersama PasarLokal.
                </p>

                <div class="stc-hero-points">
                    <span><i>✓</i> Profil usaha</span>
                    <span><i>✓</i> Identitas toko</span>
                    <span><i>✓</i> Siap berkembang</span>
                </div>

            </div>

            <div class="stc-hero-visual" aria-hidden="true">

                <div class="stc-orbit stc-orbit-one"></div>
                <div class="stc-orbit stc-orbit-two"></div>

                <div class="stc-visual-note stc-note-top">
                    <span>✳</span>
                    MADE WITH HEART
                </div>

                <div class="stc-visual-card">
                    <span class="stc-visual-card-label">YOUR LOCAL BRAND</span>
                    <strong>LOCAL<br>IS SPECIAL<span>.</span></strong>
                    <div class="stc-visual-card-bottom">
                        <span>PL. / YOUR STORE</span>
                        <span>↗</span>
                    </div>
                </div>

                <div class="stc-visual-sticker">
                    <span>GROW</span>
                    <strong>LOCAL!</strong>
                </div>

                <span class="stc-visual-star stc-star-one">✦</span>
                <span class="stc-visual-star stc-star-two">✳</span>

            </div>

            <span class="stc-hero-index">01 — THE BEGINNING</span>

        </section>

        {{-- FLASH MESSAGES --}}
        @if (session('success'))
            <div class="stc-alert stc-alert-success" role="status">
                <span class="stc-alert-icon">✓</span>

                <div>
                    <strong>Berhasil!</strong>
                    <p>{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="stc-alert stc-alert-danger" role="alert">
                <span class="stc-alert-icon">!</span>

                <div>
                    <strong>Terjadi kendala</strong>
                    <p>{{ session('error') }}</p>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="stc-alert stc-alert-danger" role="alert">
                <span class="stc-alert-icon">!</span>

                <div>
                    <strong>Yuk, periksa kembali datanya.</strong>

                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- MAIN CONTENT --}}
        <div class="stc-content-grid">

            {{-- FORM --}}
            <section class="stc-form-column">

                <div class="stc-form-intro">

                    <div>
                        <span class="stc-section-kicker">LET'S BUILD YOUR STORE</span>
                        <h2>Kenalan dulu dengan tokomu.</h2>
                        <p>
                            Isi informasi di bawah ini supaya calon pembeli
                            lebih mengenal usaha dan produk yang kamu tawarkan.
                        </p>
                    </div>

                    <span class="stc-step-indicator">
                        <strong>01</strong>
                        <span>/</span>
                        <small>BUAT TOKO</small>
                    </span>

                </div>

                <form
                    action="{{ $storePostUrl }}"
                    method="POST"
                    enctype="multipart/form-data"
                    id="stcStoreForm"
                    class="stc-form"
                >
                    @csrf

                    {{-- SECTION 01: IDENTITY --}}
                    <section class="stc-form-section">

                        <div class="stc-form-section-heading">
                            <span class="stc-section-number">01</span>

                            <div>
                                <h3>Identitas toko</h3>
                                <p>Nama dan identitas utama usaha kamu.</p>
                            </div>
                        </div>

                        {{-- STORE NAME --}}
                        <div class="stc-field">
                            <label for="nama_toko">
                                Nama toko
                                <span class="stc-required">*</span>
                            </label>

                            <div class="stc-input-wrap">
                                <span class="stc-input-prefix">✳</span>

                                <input
                                    type="text"
                                    id="nama_toko"
                                    name="nama_toko"
                                    value="{{ old('nama_toko', old('nama_usaha')) }}"
                                    placeholder="Contoh: Kopi Gayo Kita"
                                    maxlength="100"
                                    autocomplete="organization"
                                    data-store-name
                                    required
                                >
                            </div>

                            <span class="stc-field-hint">
                                Gunakan nama yang mudah diingat dan mencerminkan usahamu.
                            </span>

                            @error('nama_toko')
                                <span class="stc-field-error">{{ $message }}</span>
                            @enderror

                            @error('nama_usaha')
                                <span class="stc-field-error">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- COMPATIBILITY ALIAS --}}
                        <input
                            type="hidden"
                            name="nama_usaha"
                            id="stcNamaUsahaAlias"
                            value="{{ old('nama_usaha', old('nama_toko')) }}"
                        >

                        <div class="stc-field-grid">

                            <div class="stc-field">
                                <label for="jenis_usaha">
                                    Bidang usaha
                                    <span class="stc-required">*</span>
                                </label>

                                <div class="stc-select-wrap">
                                    <select
                                        id="jenis_usaha"
                                        name="jenis_usaha"
                                        data-store-category
                                        required
                                    >
                                        <option value="">Pilih bidang usaha</option>

                                        @foreach ($categoryOptions as $category)
                                            <option
                                                value="{{ $category }}"
                                                @selected(
                                                    old('jenis_usaha', old('kategori')) === $category
                                                )
                                            >
                                                {{ $category }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <span>⌄</span>
                                </div>

                                @error('jenis_usaha')
                                    <span class="stc-field-error">{{ $message }}</span>
                                @enderror

                                @error('kategori')
                                    <span class="stc-field-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <input
                                type="hidden"
                                name="kategori"
                                id="stcKategoriAlias"
                                value="{{ old('kategori', old('jenis_usaha')) }}"
                            >

                            <div class="stc-field">
                                <label for="telepon">
                                    Nomor telepon
                                    <span class="stc-required">*</span>
                                </label>

                                <div class="stc-input-wrap">
                                    <span class="stc-input-prefix">☎</span>

                                    <input
                                        type="tel"
                                        id="telepon"
                                        name="telepon"
                                        value="{{ old('telepon') }}"
                                        placeholder="08xxxxxxxxxx"
                                        autocomplete="tel"
                                        inputmode="tel"
                                        maxlength="20"
                                        required
                                    >
                                </div>

                                <span class="stc-field-hint">
                                    Gunakan nomor yang dapat dihubungi.
                                </span>

                                @error('telepon')
                                    <span class="stc-field-error">{{ $message }}</span>
                                @enderror
                            </div>

                        </div>

                    </section>

                    {{-- SECTION 02: ABOUT --}}
                    <section class="stc-form-section">

                        <div class="stc-form-section-heading">
                            <span class="stc-section-number">02</span>

                            <div>
                                <h3>Cerita di balik usaha</h3>
                                <p>Bantu pembeli mengenal produk dan nilai tokomu.</p>
                            </div>
                        </div>

                        <div class="stc-field">
                            <div class="stc-label-row">
                                <label for="deskripsi">
                                    Cerita toko
                                    <span class="stc-required">*</span>
                                </label>

                                <span class="stc-character-count">
                                    <b id="stcDescriptionCount">0</b>/500
                                </span>
                            </div>

                            <textarea
                                id="deskripsi"
                                name="deskripsi"
                                rows="5"
                                maxlength="500"
                                placeholder="Ceritakan produk yang kamu jual, keunikan usaha, bahan yang digunakan, atau nilai lokal yang kamu bawa..."
                                data-store-description
                                required
                            >{{ old('deskripsi') }}</textarea>

                            <span class="stc-field-hint">
                                Tips: ceritakan keunikan usahamu dengan jujur dan jelas.
                            </span>

                            @error('deskripsi')
                                <span class="stc-field-error">{{ $message }}</span>
                            @enderror
                        </div>

                    </section>

                    {{-- SECTION 03: CONTACT --}}
                    <section class="stc-form-section">

                        <div class="stc-form-section-heading">
                            <span class="stc-section-number">03</span>

                            <div>
                                <h3>Informasi dan lokasi</h3>
                                <p>Lengkapi informasi agar tokomu lebih terpercaya.</p>
                            </div>
                        </div>

                        <div class="stc-field">
                            <label for="email">
                                Email usaha
                            </label>

                            <div class="stc-input-wrap">
                                <span class="stc-input-prefix">@</span>

                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="{{ old('email', $seller->email ?? '') }}"
                                    placeholder="usaha@email.com"
                                    autocomplete="email"
                                >
                            </div>

                            <span class="stc-field-hint">
                                Opsional, jika controller mengizinkan email kosong.
                            </span>

                            @error('email')
                                <span class="stc-field-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="stc-field">
                            <label for="alamat">
                                Alamat usaha
                                <span class="stc-required">*</span>
                            </label>

                            <div class="stc-textarea-wrap">
                                <span class="stc-input-prefix">⌖</span>

                                <textarea
                                    id="alamat"
                                    name="alamat"
                                    rows="3"
                                    placeholder="Nama jalan, desa/kelurahan, kecamatan, kota..."
                                    autocomplete="street-address"
                                    required
                                >{{ old('alamat') }}</textarea>
                            </div>

                            <span class="stc-field-hint">
                                Tulis alamat dengan jelas agar identitas usahamu mudah dikenali.
                            </span>

                            @error('alamat')
                                <span class="stc-field-error">{{ $message }}</span>
                            @enderror
                        </div>

                    </section>

                    {{-- SECTION 04: LOGO --}}
                    <section class="stc-form-section">

                        <div class="stc-form-section-heading">
                            <span class="stc-section-number">04</span>

                            <div>
                                <h3>Logo atau foto toko</h3>
                                <p>Tambahkan visual yang mewakili brand lokalmu.</p>
                            </div>
                        </div>

                        <div class="stc-logo-upload">

                            <div class="stc-logo-preview" id="stcLogoPreview">
                                <span id="stcLogoInitial">
                                    {{ mb_strtoupper(mb_substr(old('nama_toko', $seller->name ?? 'P'), 0, 1)) }}
                                </span>

                                <img
                                    id="stcLogoImage"
                                    src=""
                                    alt="Preview logo toko"
                                    hidden
                                >
                            </div>

                            <div class="stc-logo-upload-content">

                                <label for="logo" class="stc-upload-button">
                                    <span>↑</span>
                                    Pilih gambar
                                </label>

                                <input
                                    type="file"
                                    name="logo"
                                    id="logo"
                                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                    class="stc-file-input"
                                >

                                <strong id="stcLogoFileName">
                                    Belum ada gambar dipilih
                                </strong>

                                <small>
                                    JPG, PNG, WEBP. Maksimal 2 MB jika sesuai aturan server.
                                </small>

                                @error('logo')
                                    <span class="stc-field-error">{{ $message }}</span>
                                @enderror

                                @error('foto_toko')
                                    <span class="stc-field-error">{{ $message }}</span>
                                @enderror

                            </div>

                        </div>

                    </section>

                    {{-- SUBMISSION --}}
                    <div class="stc-form-submit">

                        <div class="stc-submit-note">
                            <span>✳</span>

                            <p>
                                Pastikan data yang kamu isi benar.
                                Status toko mengikuti proses verifikasi di sistem.
                            </p>
                        </div>

                        <button type="submit" class="stc-submit-button" id="stcSubmitButton">
                            <span>Buat toko saya</span>
                            <b>↗</b>
                        </button>

                        <a href="{{ route('umkm.dashboard') }}" class="stc-cancel-link">
                            Kembali ke dashboard
                        </a>

                    </div>

                </form>

            </section>

            {{-- LIVE PREVIEW --}}
            <aside class="stc-preview-column">

                <div class="stc-preview-sticky">

                    <div class="stc-preview-heading">
                        <span class="stc-section-kicker">A LITTLE PREVIEW</span>
                        <h2>Intip calon tokomu.</h2>
                        <p>Perubahan nama, kategori, dan logo terlihat langsung di sini.</p>
                    </div>

                    <article class="stc-store-preview">

                        <div class="stc-preview-cover">

                            <span class="stc-preview-cover-label">
                                LOCAL GOODS / INDONESIA
                            </span>

                            <span class="stc-preview-cover-big">PL.</span>

                            <span class="stc-preview-cover-star">✳</span>

                        </div>

                        <div class="stc-preview-body">

                            <div class="stc-preview-avatar" id="stcPreviewAvatar">
                                <span id="stcPreviewInitial">
                                    {{ mb_strtoupper(mb_substr(old('nama_toko', $seller->name ?? 'P'), 0, 1)) }}
                                </span>

                                <img
                                    id="stcPreviewLogo"
                                    src=""
                                    alt=""
                                    hidden
                                >
                            </div>

                            <span class="stc-preview-status">
                                <i></i> PROFIL TOKO
                            </span>

                            <h3 id="stcPreviewName">
                                {{ old('nama_toko') ?: 'Nama tokomu di sini' }}
                            </h3>

                            <div class="stc-preview-category" id="stcPreviewCategory">
                                {{ old('jenis_usaha') ?: 'Bidang usaha' }}
                            </div>

                            <p id="stcPreviewDescription">
                                {{ old('deskripsi') ?: 'Cerita unik dan karya lokalmu akan tampil di sini.' }}
                            </p>

                            <div class="stc-preview-divider"></div>

                            <div class="stc-preview-footer">
                                <span><i>✳</i> LOCAL BRAND</span>
                                <span>PL. / 001</span>
                            </div>

                        </div>

                    </article>

                    {{-- CHECKLIST --}}
                    <div class="stc-preview-checklist">

                        <div class="stc-checklist-heading">
                            <span>BEFORE YOU START</span>
                            <b>✦</b>
                        </div>

                        <div class="stc-checklist-item">
                            <span class="stc-check-icon">01</span>
                            <span>
                                <strong>Nama yang berkesan</strong>
                                <small>Identitas toko mudah dikenali.</small>
                            </span>
                        </div>

                        <div class="stc-checklist-item">
                            <span class="stc-check-icon">02</span>
                            <span>
                                <strong>Cerita yang autentik</strong>
                                <small>Jelaskan nilai unik usaha lokalmu.</small>
                            </span>
                        </div>

                        <div class="stc-checklist-item">
                            <span class="stc-check-icon">03</span>
                            <span>
                                <strong>Kontak yang benar</strong>
                                <small>Pastikan informasi bisa dihubungi.</small>
                            </span>
                        </div>

                    </div>

                    <div class="stc-preview-quote">
                        <span>“</span>

                        <p>
                            Usaha kecil punya cerita besar.
                            Mulai ceritakan dari tokomu sendiri.
                        </p>

                        <small>PASARLOKAL / SELLER STUDIO</small>
                    </div>

                </div>

            </aside>

        </div>

        <footer class="stc-footer">
            <span>© {{ date('Y') }} PASARLOKAL / SELLER STUDIO</span>
            <span>Bangga produk lokal Indonesia. <b>✳</b></span>
        </footer>

    </main>

</div>

</body>
</html>
