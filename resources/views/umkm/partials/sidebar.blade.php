<div class="sidebar-inner">
    <a href="{{ route('umkm.dashboard') }}" class="portal-brand">
        <span class="portal-brand-mark">PL.</span>

        <span>
            PasarLokal
            <small>RUANG PENJUAL</small>
        </span>
    </a>

    <p class="sidebar-label">TOKO DAN USAHA</p>

    <nav class="sidebar-nav">
        <a
            href="{{ route('umkm.dashboard') }}"
            class="sidebar-link {{ request()->routeIs('umkm.dashboard') ? 'is-active' : '' }}"
        >
            <span class="nav-icon">01</span>
            Dashboard
        </a>
        
@if (auth()->user()->umkm?->status === 'aktif')
    <a
        href="{{ route('umkm.products.index') }}"
        @class([
            'sidebar-link',
            'is-active' => request()->routeIs('umkm.products.*'),
        ])
    >
        <span class="nav-icon">02</span>
        Produk & Stok
    </a>
@endif

    </nav>

    <div class="sidebar-note seller-note">
        <span class="note-mark">✳</span>

        <strong>Usahamu, peluangmu</strong>

        <p>
            Pengaturan toko, produk, stok, dan pesanan
            akan dikembangkan pada tahap berikutnya.
        </p>
    </div>

    <a href="{{ route('home') }}" class="sidebar-home">
        ↖ Lihat halaman depan
    </a>
</div>