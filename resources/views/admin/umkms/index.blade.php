
@extends('layouts.admin')

@section('title', 'Verifikasi UMKM')

@section('breadcrumb', 'UMKM & Toko')

@section('body-class', 'admin-umkms-page')

@section('content')

<div class="admin-umkm-page">

    {{-- =====================================================
         HEADER HALAMAN
    ====================================================== --}}

    <section class="page-intro">
        <div class="page-intro-content">
            <p class="section-kicker">ADMIN CONSOLE / UMKM</p>

            <h1>
                Kelola <span>usaha lokal.</span>
            </h1>

            <p class="page-description">
                Tinjau pendaftaran toko, verifikasi pemilik usaha,
                dan kelola UMKM yang ingin bergabung dengan PasarLokal.
            </p>
        </div>

        <span class="status-pill">
            <span></span>
            VERIFIKASI TOKO
        </span>
    </section>


    {{-- =====================================================
         NOTIFIKASI
    ====================================================== --}}

    @if (session('success'))
        <div class="portal-alert" role="status">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="portal-alert portal-alert-error" role="alert">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="portal-alert portal-alert-error" role="alert">
            {{ $errors->first() }}
        </div>
    @endif


    {{-- =====================================================
         STATISTIK UMKM
    ====================================================== --}}

    <section class="umkm-stat-grid" aria-label="Statistik UMKM">

        <article class="umkm-stat-card">
            <span class="umkm-stat-label">SELURUH TOKO</span>

            <strong>{{ number_format($stats['total']) }}</strong>

            <span class="umkm-stat-foot">
                Total toko terdaftar
            </span>
        </article>

        <article class="umkm-stat-card pending-stat">
            <span class="umkm-stat-label">MENUNGGU VERIFIKASI</span>

            <strong>{{ number_format($stats['pending']) }}</strong>

            <span class="umkm-stat-foot">
                Perlu ditinjau admin
            </span>
        </article>

        <article class="umkm-stat-card active-stat">
            <span class="umkm-stat-label">TOKO AKTIF</span>

            <strong>{{ number_format($stats['active']) }}</strong>

            <span class="umkm-stat-foot">
                Telah disetujui
            </span>
        </article>

        <article class="umkm-stat-card rejected-stat">
            <span class="umkm-stat-label">TOKO DITOLAK</span>

            <strong>{{ number_format($stats['rejected']) }}</strong>

            <span class="umkm-stat-foot">
                Pendaftaran tidak disetujui
            </span>
        </article>

    </section>


    {{-- =====================================================
         PANEL DAFTAR TOKO
    ====================================================== --}}

    <section class="umkm-management-panel">

        <div class="umkm-panel-heading">
            <div>
                <p class="section-kicker">DATABASE TOKO</p>
                <h2>Daftar UMKM</h2>
            </div>

            <span class="umkm-total">
                {{ number_format($umkms->total()) }} toko
            </span>
        </div>


        {{-- FILTER STATUS --}}

        @php
            $filters = [
                'menunggu_verifikasi' => 'Menunggu',
                'aktif' => 'Aktif',
                'ditolak' => 'Ditolak',
                'semua' => 'Semua toko',
            ];
        @endphp

        <nav class="umkm-filters" aria-label="Filter status toko">

            @foreach ($filters as $key => $label)
                <a
                    href="{{ route('admin.umkms.index', ['status' => $key]) }}"
                    @class([
                        'umkm-filter',
                        'is-active' => $status === $key,
                    ])
                    @if ($status === $key)
                        aria-current="page"
                    @endif
                >
                    {{ $label }}
                </a>
            @endforeach

        </nav>


        {{-- =================================================
             TABEL UMKM
        ================================================== --}}

        @if ($umkms->isNotEmpty())

            <div class="umkm-table-wrap">

                <table class="umkm-table">

                    <thead>
                        <tr>
                            <th scope="col">TOKO / PEMILIK</th>
                            <th scope="col">PRODUK</th>
                            <th scope="col">STATUS</th>
                            <th scope="col">TANGGAL DAFTAR</th>
                            <th scope="col" class="umkm-action-heading">
                                AKSI
                            </th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($umkms as $umkm)

                            @php
                                $storeName = $umkm->nama_toko ?: 'Toko tanpa nama';

                                $initial = mb_strtoupper(
                                    mb_substr($storeName, 0, 1)
                                );
                            @endphp

                            <tr>

                                {{-- IDENTITAS TOKO --}}

                                <td>
                                    <div class="umkm-owner-cell">

                                        <span
                                            class="umkm-avatar"
                                            aria-hidden="true"
                                        >
                                            {{ $initial }}
                                        </span>

                                        <div class="umkm-owner-info">
                                            <strong>{{ $storeName }}</strong>

                                            <small>
                                                {{ $umkm->user?->name ?? 'Pemilik tidak tersedia' }}
                                            </small>

                                            <small>
                                                {{ $umkm->user?->email ?? '-' }}
                                            </small>
                                        </div>

                                    </div>
                                </td>


                                {{-- JUMLAH PRODUK --}}

                                <td>
                                    {{ number_format($umkm->products_count ?? 0) }}
                                </td>


                                {{-- STATUS TOKO --}}

                                <td>
                                    @if ($umkm->status === 'aktif')

                                        <span class="umkm-status status-active">
                                            Aktif
                                        </span>

                                    @elseif ($umkm->status === 'ditolak')

                                        <span class="umkm-status status-rejected">
                                            Ditolak
                                        </span>

                                    @else

                                        <span class="umkm-status status-pending">
                                            Menunggu
                                        </span>

                                    @endif
                                </td>


                                {{-- TANGGAL PENDAFTARAN --}}

                                <td>
                                    <span class="umkm-date">
                                        {{ $umkm->created_at?->format('d M Y') ?? '—' }}
                                    </span>
                                </td>


                                {{-- AKSI VERIFIKASI --}}

                                <td>

                                    @if ($umkm->status === 'menunggu_verifikasi')

                                        <div class="umkm-action-group">

                                            <form
                                                action="{{ route('admin.umkms.approve', $umkm) }}"
                                                method="POST"
                                                data-confirm="Setujui toko {{ $storeName }}?"
                                            >
                                                @csrf
                                                @method('PATCH')

                                                <button
                                                    type="submit"
                                                    class="umkm-approve-button"
                                                >
                                                    Setujui
                                                </button>
                                            </form>

                                            <form
                                                action="{{ route('admin.umkms.reject', $umkm) }}"
                                                method="POST"
                                                data-confirm="Tolak pendaftaran toko {{ $storeName }}?"
                                            >
                                                @csrf
                                                @method('PATCH')

                                                <button
                                                    type="submit"
                                                    class="umkm-reject-button"
                                                >
                                                    Tolak
                                                </button>
                                            </form>

                                        </div>

                                    @else

                                        <span class="umkm-no-action">—</span>

                                    @endif

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>


            {{-- PAGINATION --}}

            @if ($umkms->hasPages())
                <div class="umkm-pagination">
                    {{ $umkms->links() }}
                </div>
            @endif


        @else

            {{-- EMPTY STATE --}}

            <div class="umkm-empty-state">

                <span class="umkm-empty-icon" aria-hidden="true">
                    ✳
                </span>

                <h3>
                    @if ($status === 'menunggu_verifikasi')
                        Tidak ada pendaftaran yang menunggu.
                    @elseif ($status === 'aktif')
                        Belum ada toko aktif.
                    @elseif ($status === 'ditolak')
                        Belum ada toko yang ditolak.
                    @else
                        Belum ada UMKM terdaftar.
                    @endif
                </h3>

                <p>
                    Data toko yang sesuai dengan filter ini akan muncul
                    di sini setelah tersedia.
                </p>

                @if ($status !== 'semua')
                    <a
                        href="{{ route('admin.umkms.index', ['status' => 'semua']) }}"
                        class="umkm-filter is-active"
                        style="margin-top: 16px;"
                    >
                        Lihat semua toko
                    </a>
                @endif

            </div>

        @endif

    </section>

</div>

@endsection
