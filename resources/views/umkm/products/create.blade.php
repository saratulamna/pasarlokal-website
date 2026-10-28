
@php
    $sellerUser = auth()->user();

    $dashboardUrl = route('umkm.dashboard');
    $productsUrl = route('umkm.products.index');

    $ordersUrl = \Illuminate\Support\Facades\Route::has('umkm.orders.index')
        ? route('umkm.orders.index')
        : url('/umkm/dashboard#orders-panel');

    $storeUrl = \Illuminate\Support\Facades\Route::has('umkm.store.edit')
        ? route('umkm.store.edit')
        : url('/umkm/dashboard#store-card');
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#193B2C">
    <meta name="description" content="Tambah dan kelola produk lokal di PasarLokal Seller Workspace.">

    <title>Tambah Produk — PasarLokal</title>

    @vite([
        'resources/css/app.css',
        'resources/css/pages/umkm/dashboard.css',
        'resources/js/app.js',
        'resources/js/pages/umkm/dashboard.js',
    ])
</head>

<body class="seller-body upc-page">

<div class="seller-app upc-app" id="sellerApp">

    {{-- MOBILE OVERLAY --}}
    <button
        type="button"
        class="seller-mobile-overlay"
        id="sellerMobileOverlay"
        aria-label="Tutup navigasi"
        tabindex="-1"
    ></button>

    {{-- =====================================================
         SIDEBAR SELLER WORKSPACE
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

            <a href="{{ $dashboardUrl }}" class="seller-nav-link">
                <span class="seller-nav-icon">⌂</span>
                <span>Dashboard</span>
            </a>

            <a
                href="{{ $productsUrl }}"
                class="seller-nav-link is-active"
            >
                <span class="seller-nav-icon">▦</span>
                <span>Produk saya</span>
                <span class="seller-nav-current"></span>
            </a>

            <a href="{{ $ordersUrl }}" class="seller-nav-link">
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

            <a href="{{ $storeUrl }}" class="seller-nav-link">
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

                <span class="seller-account-dot" title="Akun masuk"></span>

            </div>
        </div>

        <a href="{{ route('home') }}" class="seller-sidebar-home">
            ↖ Lihat halaman depan
        </a>

    </aside>

    {{-- =====================================================
         MAIN CONTENT
    ====================================================== --}}
    <main class="seller-main">

        {{-- HEADER --}}
        <header class="seller-topbar">

            <div class="seller-topbar-left">
                <button
                    type="button"
                    class="seller-mobile-toggle"
                    id="sellerMobileToggle"
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

                <nav class="upc-breadcrumb" aria-label="Breadcrumb">
                    <a href="{{ $dashboardUrl }}">Dashboard</a>
                    <span>/</span>
                    <a href="{{ $productsUrl }}">Produk saya</a>
                    <span>/</span>
                    <strong>Tambah produk</strong>
                </nav>
            </div>

            <div class="seller-topbar-right">
                <div class="upc-topbar-status">
                    <span></span>
                    Seller Workspace
                </div>

                <div class="seller-account seller-header-account">
                    <span class="seller-account-avatar">
                        {{ mb_strtoupper(mb_substr((string) ($sellerUser->name ?? 'P'), 0, 1)) }}
                    </span>

                    <span class="seller-account-info">
                        <strong>{{ $sellerUser->name ?? 'Penjual PasarLokal' }}</strong>
                        <small>Merchant / Penjual</small>
                    </span>
                </div>
            </div>

        </header>

        <div class="seller-content upc-content">

            {{-- HERO --}}
            <section class="upc-hero">

                <div class="upc-hero-copy">

                    <div class="upc-eyebrow">
                        <span class="upc-eyebrow-dot"></span>
                        RUANG KREATIF UMKM
                    </div>

                    <h1>
                        Produk bagus,<br>
                        <span>cerita baru.</span>
                    </h1>

                    <p>
                        Ceritakan produk lokal terbaikmu. Lengkapi detail,
                        tampilkan foto yang menarik, dan mulai jangkau
                        lebih banyak pelanggan.
                    </p>

                    <div class="upc-hero-foot">
                        <span class="upc-hero-number">01</span>
                        <span class="upc-hero-line"></span>
                        <span class="upc-hero-foot-text">FORM PRODUK BARU</span>
                    </div>

                </div>

                <div class="upc-hero-art" aria-hidden="true">

                    <div class="upc-art-circle upc-art-circle-one"></div>
                    <div class="upc-art-circle upc-art-circle-two"></div>

                    <div class="upc-art-card">

                        <span class="upc-art-spark">✳</span>

                        <div class="upc-art-bag">
                            <svg viewBox="0 0 96 96" fill="none">
                                <path
                                    d="M17 35H79L73 80H23L17 35Z"
                                    fill="#D9F36A"
                                    stroke="#193B2C"
                                    stroke-width="3.5"
                                    stroke-linejoin="round"
                                />
                                <path
                                    d="M33 36V28C33 19.7 39.7 13 48 13C56.3 13 63 19.7 63 28V36"
                                    stroke="#193B2C"
                                    stroke-width="3.5"
                                    stroke-linecap="round"
                                />
                                <path
                                    d="M35 50C41 57 55 57 61 50"
                                    stroke="#193B2C"
                                    stroke-width="3"
                                    stroke-linecap="round"
                                />
                                <circle cx="39" cy="45" r="2.8" fill="#193B2C"/>
                                <circle cx="57" cy="45" r="2.8" fill="#193B2C"/>
                            </svg>
                        </div>

                        <strong>Dari lokal,</strong>
                        <strong>untuk semua.</strong>

                        <span>MADE WITH CARE ♡</span>

                    </div>

                    <div class="upc-art-sticker upc-art-sticker-top">
                        <span>✳</span> PRODUK LOKAL
                    </div>

                    <div class="upc-art-sticker upc-art-sticker-bottom">
                        GOOD THINGS GROW HERE
                    </div>

                </div>

            </section>

            {{-- SECTION HEADING --}}
            <div class="upc-section-heading">
                <div>
                    <span class="upc-section-kicker">LET'S GET STARTED</span>
                    <h2>Siapkan produkmu <span>✳</span></h2>
                    <p>
                        Lengkapi informasi agar pelanggan mengenal produkmu
                        dengan lebih baik.
                    </p>
                </div>

                <div class="upc-required-note">
                    <span>*</span> Wajib diisi
                </div>
            </div>

            {{-- VALIDATION ERRORS --}}
            @if ($errors->any())
                <div class="upc-alert upc-alert-error" role="alert">
                    <span class="upc-alert-icon">!</span>

                    <div>
                        <strong>Periksa kembali formulirmu.</strong>
                        <p>
                            Ada isian yang belum sesuai. Pesan kesalahan
                            ditampilkan di bawah kolom terkait.
                        </p>
                    </div>
                </div>
            @endif

            @if (session('success'))
                <div class="upc-alert upc-alert-success" role="status">
                    <span class="upc-alert-icon">✓</span>
                    <div>
                        <strong>Berhasil!</strong>
                        <p>{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            {{-- =================================================
                 PRODUCT FORM
            ================================================== --}}
            <form
                id="upcForm"
                action="{{ route('umkm.products.store') }}"
                method="POST"
                enctype="multipart/form-data"
            >
                @csrf

                <div class="upc-layout">

                    {{-- MAIN FORM COLUMN --}}
                    <div class="upc-form-column">

                        {{-- GENERAL INFORMATION --}}
                        <section class="upc-panel">

                            <div class="upc-panel-heading">

                                <div class="upc-panel-icon upc-icon-green">
                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <rect
                                            x="3" y="3" width="18" height="18" rx="4"
                                            stroke="currentColor" stroke-width="1.6"
                                        />
                                        <path
                                            d="M8 8H16M8 12H16M8 16H13"
                                            stroke="currentColor" stroke-width="1.8"
                                            stroke-linecap="round"
                                        />
                                    </svg>
                                </div>

                                <div>
                                    <h3>Kenalan dengan produkmu</h3>
                                    <p>Informasi dasar yang akan dilihat pembeli.</p>
                                </div>

                                <span class="upc-panel-number">01</span>

                            </div>

                            {{-- NAME --}}
                            <div class="upc-field">

                                <label for="upcName">
                                    Nama produk <span>*</span>
                                </label>

                                <input
                                    id="upcName"
                                    type="text"
                                    name="nama"
                                    value="{{ old('nama', $product->nama ?? '') }}"
                                    placeholder="Contoh: Keripik Pisang Cokelat"
                                    maxlength="180"
                                    autocomplete="off"
                                    required
                                    class="@error('nama') is-invalid @enderror"
                                >

                                @error('nama')
                                    <span class="upc-error">{{ $message }}</span>
                                @else
                                    <div class="upc-field-footer">
                                        <span>Gunakan nama yang jelas dan mudah diingat.</span>
                                        <span class="upc-char-count">
                                            <b id="upcNameCount">{{ mb_strlen(old('nama', '')) }}</b>/180
                                        </span>
                                    </div>
                                @enderror

                            </div>

                            {{-- CATEGORY --}}
                            <div class="upc-field">

                                <label for="upcCategory">
                                    Kategori produk
                                    <span class="upc-optional">(opsional)</span>
                                </label>

                                <div class="upc-select-wrap">
                                    <select
                                        id="upcCategory"
                                        name="category_id"
                                        class="@error('category_id') is-invalid @enderror"
                                    >
                                        <option value="">
                                            Pilih kategori jika tersedia
                                        </option>

                                        @foreach ($categories as $category)
                                            <option
                                                value="{{ $category->id }}"
                                                @selected(
                                                    (string) old('category_id', $product->category_id ?? '')
                                                    === (string) $category->id
                                                )
                                            >
                                                {{ $category->nama }}
                                            </option>
                                        @endforeach

                                    </select>

                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path
                                            d="M7 10L12 15L17 10"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </div>

                                @error('category_id')
                                    <span class="upc-error">{{ $message }}</span>
                                @else
                                    <span class="upc-field-help">
                                        Kategori membantu pembeli menemukan produk yang sesuai.
                                    </span>
                                @enderror

                            </div>

                            {{-- DESCRIPTION --}}
                            <div class="upc-field">

                                <label for="upcDescription">
                                    Cerita tentang produkmu
                                    <span class="upc-optional">(opsional)</span>
                                </label>

                                <textarea
                                    id="upcDescription"
                                    name="deskripsi"
                                    rows="5"
                                    maxlength="20000"
                                    placeholder="Ceritakan keunikan, bahan, cara pembuatan, atau hal menarik lainnya..."
                                    class="@error('deskripsi') is-invalid @enderror"
                                >{{ old('deskripsi', $product->deskripsi ?? '') }}</textarea>

                                @error('deskripsi')
                                    <span class="upc-error">{{ $message }}</span>
                                @else
                                    <div class="upc-field-footer">
                                        <span>Deskripsi yang jelas membuat pembeli lebih mengenal produkmu.</span>
                                        <span class="upc-char-count">
                                            <b id="upcDescriptionCount">{{ mb_strlen(old('deskripsi', '')) }}</b>/20000
                                        </span>
                                    </div>
                                @enderror

                            </div>

                        </section>

                        {{-- PRICE & STOCK --}}
                        <section class="upc-panel">

                            <div class="upc-panel-heading">

                                <div class="upc-panel-icon upc-icon-orange">
                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <rect
                                            x="3" y="5" width="18" height="14" rx="3"
                                            stroke="currentColor" stroke-width="1.7"
                                        />
                                        <circle
                                            cx="12" cy="12" r="3"
                                            stroke="currentColor" stroke-width="1.7"
                                        />
                                        <path
                                            d="M6.5 9H6.51M17.5 15H17.51"
                                            stroke="currentColor" stroke-width="2.3"
                                            stroke-linecap="round"
                                        />
                                    </svg>
                                </div>

                                <div>
                                    <h3>Atur harga & persediaan</h3>
                                    <p>Tentukan harga jual dan jumlah unit yang tersedia.</p>
                                </div>

                                <span class="upc-panel-number">02</span>

                            </div>

                            <div class="upc-two-fields">

                                {{-- PRICE --}}
                                <div class="upc-field">

                                    <label for="upcPrice">
                                        Harga jual <span>*</span>
                                    </label>

                                    <div class="upc-input-prefix">
                                        <span>Rp</span>

                                        <input
                                            id="upcPrice"
                                            type="number"
                                            name="harga"
                                            min="1"
                                            max="9999999999999"
                                            step="1"
                                            value="{{ old('harga', $product->harga ?? '') }}"
                                            placeholder="25000"
                                            required
                                            class="@error('harga') is-invalid @enderror"
                                        >
                                    </div>

                                    @error('harga')
                                        <span class="upc-error">{{ $message }}</span>
                                    @else
                                        <span class="upc-field-help">
                                            Harga minimal Rp1.
                                        </span>
                                    @enderror

                                </div>

                                {{-- STOCK --}}
                                <div class="upc-field">

                                    <label for="upcStock">
                                        Stok tersedia <span>*</span>
                                    </label>

                                    <div class="upc-input-suffix">

                                        <input
                                            id="upcStock"
                                            type="number"
                                            name="stok"
                                            min="0"
                                            step="1"
                                            value="{{ old('stok', $product->stok ?? 0) }}"
                                            placeholder="50"
                                            required
                                            class="@error('stok') is-invalid @enderror"
                                        >

                                        <span>unit</span>

                                    </div>

                                    @error('stok')
                                        <span class="upc-error">{{ $message }}</span>
                                    @else
                                        <span class="upc-field-help">
                                            Stok bisa dimulai dari angka 0.
                                        </span>
                                    @enderror

                                </div>

                            </div>

                            <div class="upc-price-insight">

                                <div class="upc-price-insight-icon">✳</div>

                                <div>
                                    <strong>Hitung harga dengan cermat</strong>
                                    <p>
                                        Pertimbangkan biaya produksi, bahan,
                                        kemasan, dan operasional.
                                    </p>
                                </div>

                                <div class="upc-live-price" id="upcLivePrice">
                                    Rp0
                                </div>

                            </div>

                        </section>

                        {{-- PRODUCT STATUS --}}
                        <section class="upc-panel">

                            <div class="upc-panel-heading">

                                <div class="upc-panel-icon upc-icon-lime">
                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <circle
                                            cx="12" cy="12" r="9"
                                            stroke="currentColor" stroke-width="1.6"
                                        />
                                        <path
                                            d="M7.5 12L10.5 15L16.5 9"
                                            stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </div>

                                <div>
                                    <h3>Status produk</h3>
                                    <p>Pilih status awal produk yang akan disimpan.</p>
                                </div>

                                <span class="upc-panel-number">03</span>

                            </div>

                            <div class="upc-status-options">

                                <label class="upc-status-choice">

                                    <input
                                        type="radio"
                                        name="status"
                                        value="draft"
                                        @checked(old('status', 'draft') === 'draft')
                                    >

                                    <span class="upc-status-card">

                                        <span class="upc-status-symbol upc-status-symbol-draft">
                                            ◷
                                        </span>

                                        <span class="upc-status-copy">
                                            <strong>Simpan sebagai draft</strong>
                                            <small>
                                                Simpan produk tanpa mengaktifkannya.
                                            </small>
                                        </span>

                                        <span class="upc-radio-dot"></span>

                                    </span>

                                </label>

                                <label class="upc-status-choice">

                                    <input
                                        type="radio"
                                        name="status"
                                        value="aktif"
                                        @checked(old('status') === 'aktif')
                                    >

                                    <span class="upc-status-card">

                                        <span class="upc-status-symbol upc-status-symbol-active">
                                            ↗
                                        </span>

                                        <span class="upc-status-copy">
                                            <strong>Aktifkan produk</strong>
                                            <small>
                                                Tandai produk sebagai aktif.
                                            </small>
                                        </span>

                                        <span class="upc-radio-dot"></span>

                                    </span>

                                </label>

                            </div>

                            @error('status')
                                <span class="upc-error">{{ $message }}</span>
                            @enderror

                        </section>

                    </div>

                    {{-- =================================================
                         RIGHT COLUMN
                    ================================================== --}}
                    <aside class="upc-side-column">

                        {{-- IMAGE UPLOAD --}}
                        <section class="upc-panel upc-media-panel">

                            <div class="upc-panel-heading upc-panel-heading-compact">

                                <div class="upc-panel-icon upc-icon-purple">
                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <rect
                                            x="3" y="3" width="18" height="18" rx="4"
                                            stroke="currentColor" stroke-width="1.7"
                                        />
                                        <circle
                                            cx="8.5" cy="8.5" r="1.8"
                                            stroke="currentColor" stroke-width="1.5"
                                        />
                                        <path
                                            d="M21 15L16 10L5 21"
                                            stroke="currentColor" stroke-width="1.7"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </div>

                                <div>
                                    <h3>Foto produk</h3>
                                    <p>Tampilkan produk dengan foto yang jelas.</p>
                                </div>

                            </div>

                            <label
                                class="upc-upload-zone"
                                id="upcDropzone"
                                for="upcImage"
                            >

                                <input
                                    id="upcImage"
                                    type="file"
                                    name="gambar"
                                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                    class="upc-file-input @error('gambar') is-invalid @enderror"
                                >

                                <img
                                    src=""
                                    alt="Preview foto produk"
                                    id="upcPreviewImage"
                                    class="upc-upload-preview"
                                    hidden
                                >

                                <span
                                    class="upc-upload-placeholder"
                                    id="upcUploadPlaceholder"
                                >

                                    <span class="upc-upload-icon">
                                        <svg viewBox="0 0 48 48" fill="none" aria-hidden="true">
                                            <rect
                                                x="5" y="7" width="38" height="34" rx="8"
                                                stroke="currentColor" stroke-width="2"
                                            />
                                            <circle
                                                cx="17" cy="18" r="3"
                                                stroke="currentColor" stroke-width="2"
                                            />
                                            <path
                                                d="M7 34L19 23L27 30L33 24L41 31"
                                                stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>

                                        <span>+</span>
                                    </span>

                                    <strong>Upload foto produk</strong>
                                    <span>Pilih gambar dari perangkatmu</span>
                                    <small>JPG, PNG, WEBP · Maks. 2 MB</small>

                                </span>

                                <span
                                    class="upc-upload-change"
                                    id="upcUploadChange"
                                    hidden
                                >
                                    Klik untuk mengganti foto
                                </span>

                            </label>

                            @error('gambar')
                                <span class="upc-error">{{ $message }}</span>
                            @else
                                <div class="upc-media-tip">
                                    <span>✦</span>
                                    Gunakan foto terang dan fokus pada produk.
                                </div>
                            @enderror

                        </section>

                        {{-- LIVE PRODUCT PREVIEW --}}
                        <section class="upc-preview-panel">

                            <div class="upc-preview-top">

                                <div>
                                    <span class="upc-preview-kicker">LIVE PREVIEW</span>
                                    <h3>Tampilan produkmu</h3>
                                </div>

                                <span class="upc-live-badge">
                                    <span></span> LIVE
                                </span>

                            </div>

                            <div class="upc-product-preview">

                                <div class="upc-preview-image-wrap">

                                    <span class="upc-preview-badge">
                                        PRODUK LOKAL
                                    </span>

                                    <div
                                        class="upc-preview-image-placeholder"
                                        id="upcCardPlaceholder"
                                    >
                                        <svg viewBox="0 0 100 100" fill="none" aria-hidden="true">
                                            <rect x="9" y="10" width="82" height="80" rx="17" fill="#E8E6D9"/>
                                            <circle cx="35" cy="35" r="8" fill="#D9F36A" stroke="#193B2C" stroke-width="2"/>
                                            <path d="M15 75L39 51L54 65L67 52L86 70V78H15V75Z" fill="#C5D7B8" stroke="#193B2C" stroke-width="2" stroke-linejoin="round"/>
                                        </svg>
                                        <span>Foto produkmu<br>akan muncul di sini</span>
                                    </div>

                                    <img
                                        src=""
                                        alt="Preview produk"
                                        id="upcPreviewCardImage"
                                        class="upc-preview-card-image"
                                        hidden
                                    >

                                </div>

                                <div class="upc-preview-product-content">

                                    <span
                                        class="upc-preview-category"
                                        id="upcPreviewCategory"
                                    >
                                        KATEGORI PRODUK
                                    </span>

                                    <h4 id="upcPreviewName">
                                        Nama produkmu
                                    </h4>

                                    <p
                                        class="upc-preview-description"
                                        id="upcPreviewDescription"
                                    >
                                        Deskripsi singkat produk akan tampil di sini.
                                    </p>

                                    <div class="upc-preview-divider"></div>

                                    <div class="upc-preview-bottom">

                                        <div>
                                            <small>Harga produk</small>
                                            <strong id="upcPreviewPrice">Rp0</strong>
                                        </div>

                                        <span class="upc-preview-cart" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none">
                                                <path
                                                    d="M3 4H5L7.2 14.5C7.4 15.4 8.2 16 9.1 16H17.5C18.4 16 19.2 15.4 19.4 14.5L21 8H6"
                                                    stroke="currentColor" stroke-width="1.7"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                />
                                                <circle cx="10" cy="20" r="1.4" fill="currentColor"/>
                                                <circle cx="18" cy="20" r="1.4" fill="currentColor"/>
                                            </svg>
                                        </span>

                                    </div>

                                </div>

                            </div>

                            <div class="upc-preview-note">
                                <span>✳</span>
                                <p>
                                    Pratinjau membantu mengecek informasi
                                    sebelum produk disimpan.
                                </p>
                            </div>

                        </section>

                        {{-- CHECKLIST --}}
                        <section class="upc-checklist">

                            <div class="upc-checklist-heading">
                                <span>✳</span>
                                <div>
                                    <strong>Persiapan produk</strong>
                                    <small>Cek sebelum menyimpan.</small>
                                </div>
                            </div>

                            <div class="upc-check-item" id="upcCheckName">
                                <span class="upc-check-circle">✓</span>
                                <span>Nama produk sudah diisi</span>
                            </div>

                            <div class="upc-check-item" id="upcCheckPrice">
                                <span class="upc-check-circle">✓</span>
                                <span>Harga sudah ditentukan</span>
                            </div>

                            <div class="upc-check-item" id="upcCheckImage">
                                <span class="upc-check-circle">✓</span>
                                <span>Foto produk dipilih (opsional)</span>
                            </div>

                        </section>

                    </aside>

                </div>

                {{-- =================================================
                     BOTTOM ACTION BAR
                ================================================== --}}
                <div class="upc-bottom-bar">

                    <div class="upc-bottom-note">
                        <span class="upc-bottom-check">✓</span>

                        <div>
                            <strong>Siap menyimpan produk?</strong>
                            <small>Pastikan informasi dan harga sudah benar.</small>
                        </div>
                    </div>

                    <div class="upc-actions">

                        <a href="{{ $productsUrl }}" class="upc-button upc-button-cancel">
                            Batal
                        </a>

                        <button
                            type="submit"
                            class="upc-button upc-button-submit"
                            id="upcSubmit"
                        >
                            <span class="upc-submit-label">Simpan produk</span>

                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path
                                    d="M5 12H19M13 6L19 12L13 18"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </button>

                    </div>

                </div>

            </form>

            {{-- FOOTER --}}
            <footer class="seller-footer upc-footer">
                <span>© {{ date('Y') }} PASARLOKAL / SELLER STUDIO</span>
                <span>Dibangun untuk cerita usaha lokal. <b>✳</b></span>
            </footer>

        </div>

    </main>

</div>

</body>
</html>
