
<div class="sidebar-inner">

    {{-- BRAND --}}
    <a href="{{ route('pembeli.dashboard') }}" class="portal-brand">
        <span class="portal-brand-mark">PL.</span>

        <span>
            PasarLokal
            <small>RUANG PEMBELI</small>
        </span>
    </a>

    {{-- MENU UTAMA --}}
    <p class="sidebar-label">AKTIVITASMU</p>

    <nav class="sidebar-nav">


{{-- Dashboard --}}
<a
    href="{{ route('pembeli.dashboard') }}"
    @class([
        'sidebar-link',
        'is-active' => request()->routeIs('pembeli.dashboard'),
    ])
>
    <span class="nav-icon">01</span>
    <span>Dashboard</span>
</a>

{{-- Keranjang --}}
<a
    href="{{ route('cart.index') }}"
    @class([
        'sidebar-link',
        'is-active' => request()->routeIs('cart.*'),
    ])
>
    <span class="nav-icon">02</span>
    <span>Keranjang Belanja</span>
</a>

{{-- Riwayat Pesanan --}}
<a
    href="{{ route('pembeli.orders.index') }}"
    @class([
        'sidebar-link',
        'is-active' => request()->routeIs('pembeli.orders.*'),
    ])
>
    <span class="nav-icon">03</span>
    <span>Riwayat Pesanan</span>
</a>


    </nav>

    {{-- INFORMASI --}}
    <div class="sidebar-note buyer-note">
        <span class="note-mark">↗</span>

        <strong>Jelajahi produk lokal</strong>

        <p>
            Temukan produk UMKM favoritmu, simpan pilihan belanja,
            dan pantau perjalanan pesananmu.
        </p>
    </div>

    {{-- KEMBALI KE HALAMAN DEPAN --}}
    <a href="{{ route('home') }}" class="sidebar-home">
        ↖ Lihat halaman depan
    </a>

</div>
