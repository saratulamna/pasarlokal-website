
@extends('layouts.admin')

@section('title', 'Manajemen Pengguna')

@section('breadcrumb', 'Manajemen Pengguna')

@section('body-class', 'admin-users-page')

@section('content')

@php
    $roleLabels = [
        'admin' => 'Administrator',
        'umkm' => 'Pemilik UMKM',
        'pembeli' => 'Pembeli',
    ];
@endphp

<div class="au-content">

    {{-- =====================================================
         FLASH MESSAGES
    ====================================================== --}}

    @if (session('success'))
        <div class="au-flash au-flash-success" role="status">
            <span class="au-flash-icon">✓</span>
            <span>{{ session('success') }}</span>

            <button
                type="button"
                data-dismiss-flash
                aria-label="Tutup notifikasi"
            >
                ×
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="au-flash au-flash-error" role="alert">
            <span class="au-flash-icon">!</span>
            <span>{{ session('error') }}</span>

            <button
                type="button"
                data-dismiss-flash
                aria-label="Tutup notifikasi"
            >
                ×
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="au-flash au-flash-error" role="alert">
            <span class="au-flash-icon">!</span>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif


    {{-- =====================================================
         PAGE HEADING
    ====================================================== --}}

    <section class="au-page-heading">

        <div class="au-heading-copy">

            <div class="au-eyebrow">
                <span></span>
                PEOPLE &amp; ACCESS
            </div>

            <h1>
                Manajemen <span>Pengguna.</span>
            </h1>

            <p>
                Kenali, kelola, dan atur akses seluruh pengguna
                di ekosistem PasarLokal.
            </p>

        </div>

        <a
            href="{{ route('admin.users.create') }}"
            class="au-primary-button"
        >
            <span class="au-button-plus">+</span>
            Tambah Pengguna
            <span class="au-button-arrow">↗</span>
        </a>

    </section>


    {{-- =====================================================
         SUMMARY CARDS
    ====================================================== --}}

    <section class="au-stats" aria-label="Statistik pengguna">

        <article class="au-stat-card au-stat-total">

            <div class="au-stat-top">
                <span class="au-stat-icon">♙</span>
                <span class="au-stat-label">TOTAL PENGGUNA</span>
            </div>

            <div class="au-stat-number">
                {{ number_format($usersCount, 0, ',', '.') }}
            </div>

            <div class="au-stat-foot">
                Semua akun terdaftar
            </div>

            <span class="au-stat-decoration" aria-hidden="true">01</span>

        </article>


        <article class="au-stat-card au-stat-buyer">

            <div class="au-stat-top">
                <span class="au-stat-icon">♡</span>
                <span class="au-stat-label">PEMBELI</span>
            </div>

            <div class="au-stat-number">
                {{ number_format($roleCounts['pembeli'], 0, ',', '.') }}
            </div>

            <div class="au-stat-foot">
                Akun pelanggan marketplace
            </div>

            <span class="au-stat-decoration" aria-hidden="true">02</span>

        </article>


        <article class="au-stat-card au-stat-seller">

            <div class="au-stat-top">
                <span class="au-stat-icon">⌂</span>
                <span class="au-stat-label">PEMILIK UMKM</span>
            </div>

            <div class="au-stat-number">
                {{ number_format($roleCounts['umkm'], 0, ',', '.') }}
            </div>

            <div class="au-stat-foot">
                Akun pengelola toko
            </div>

            <span class="au-stat-decoration" aria-hidden="true">03</span>

        </article>


        <article class="au-stat-card au-stat-verified">

            <div class="au-stat-top">
                <span class="au-stat-icon">✓</span>
                <span class="au-stat-label">EMAIL TERVERIFIKASI</span>
            </div>

            <div class="au-stat-number">
                {{ number_format($verifiedCount, 0, ',', '.') }}
            </div>

            <div class="au-stat-foot">
                {{ number_format($unverifiedCount, 0, ',', '.') }}
                email belum diverifikasi
            </div>

            <span class="au-stat-decoration" aria-hidden="true">04</span>

        </article>

    </section>


    {{-- =====================================================
         USER DIRECTORY PANEL
    ====================================================== --}}

    <section class="au-panel">

        {{-- Panel heading --}}
        <div class="au-panel-heading">

            <div>
                <div class="au-panel-overline">DIRECTORY</div>

                <h2>Daftar Pengguna</h2>

                <p>
                    Kelola identitas dan hak akses akun yang terdaftar.
                </p>
            </div>

            <div class="au-record-count">
                <span class="au-record-dot"></span>
                {{ number_format($users->total(), 0, ',', '.') }}
                akun ditemukan
            </div>

        </div>


        {{-- =================================================
             SEARCH AND FILTER
        ================================================== --}}

        <form
            method="GET"
            action="{{ route('admin.users.index') }}"
            class="au-filter-form"
            id="auFilterForm"
        >

            <label class="au-search-box">

                <span class="au-search-icon" aria-hidden="true">⌕</span>

                <input
                    type="search"
                    name="q"
                    value="{{ $search }}"
                    placeholder="Cari nama atau alamat email..."
                    autocomplete="off"
                    aria-label="Cari pengguna"
                >

                @if ($search !== '')
                    <a
                        href="{{ route('admin.users.index', array_filter([
                            'role' => $role,
                            'verification' => $verification,
                        ])) }}"
                        class="au-search-clear"
                        aria-label="Hapus pencarian"
                    >
                        ×
                    </a>
                @endif

            </label>


            <select
                name="role"
                class="au-filter-select"
                aria-label="Filter berdasarkan role"
            >
                <option value="">Semua role</option>

                <option
                    value="admin"
                    @selected($role === 'admin')
                >
                    Administrator
                </option>

                <option
                    value="umkm"
                    @selected($role === 'umkm')
                >
                    Pemilik UMKM
                </option>

                <option
                    value="pembeli"
                    @selected($role === 'pembeli')
                >
                    Pembeli
                </option>
            </select>


            <select
                name="verification"
                class="au-filter-select"
                aria-label="Filter verifikasi email"
            >
                <option value="">Semua status email</option>

                <option
                    value="verified"
                    @selected($verification === 'verified')
                >
                    Terverifikasi
                </option>

                <option
                    value="unverified"
                    @selected($verification === 'unverified')
                >
                    Belum diverifikasi
                </option>
            </select>


            <button type="submit" class="au-filter-button">
                Terapkan
            </button>


            @if ($search !== '' || $role !== '' || $verification !== '')
                <a
                    href="{{ route('admin.users.index') }}"
                    class="au-reset-button"
                >
                    Reset
                </a>
            @endif

        </form>


        {{-- =================================================
             USERS TABLE
        ================================================== --}}

        <div class="au-table-wrap">

            <table class="au-table">

                <thead>
                    <tr>
                        <th scope="col">PENGGUNA</th>
                        <th scope="col">ROLE</th>
                        <th scope="col">STATUS EMAIL</th>
                        <th scope="col">TANGGAL BERGABUNG</th>
                        <th scope="col" class="au-actions-heading">AKSI</th>
                    </tr>
                </thead>


                <tbody>

                    @forelse ($users as $item)

                        @php
                            $itemRole = $item->role ?? 'pembeli';

                            $roleClass = match ($itemRole) {
                                'admin' => 'admin',
                                'umkm' => 'umkm',
                                default => 'pembeli',
                            };

                            $itemInitial = mb_strtoupper(
                                mb_substr($item->name ?? '?', 0, 1)
                            );
                        @endphp

                        <tr>

                            {{-- USER IDENTITY --}}
                            <td>
                                <div class="au-user-cell">

                                    <div class="au-user-avatar au-avatar-{{ $roleClass }}">
                                        {{ $itemInitial }}
                                    </div>

                                    <div class="au-user-detail">
                                        <strong>{{ $item->name }}</strong>
                                        <span>{{ $item->email }}</span>
                                    </div>

                                </div>
                            </td>


                            {{-- ROLE --}}
                            <td>
                                <span class="au-role au-role-{{ $roleClass }}">
                                    <span class="au-role-dot"></span>

                                    {{ $roleLabels[$itemRole] ?? ucfirst($itemRole) }}
                                </span>
                            </td>


                            {{-- EMAIL VERIFICATION --}}
                            <td>
                                @if ($item->email_verified_at)

                                    <span class="au-status au-status-verified">
                                        <span></span>
                                        Terverifikasi
                                    </span>

                                @else

                                    <span class="au-status au-status-pending">
                                        <span></span>
                                        Belum diverifikasi
                                    </span>

                                @endif
                            </td>


                            {{-- JOIN DATE --}}
                            <td>
                                <div class="au-date-cell">

                                    <strong>
                                        {{ $item->created_at?->translatedFormat('d M Y') ?? '—' }}
                                    </strong>

                                    <span>
                                        {{ $item->created_at?->format('H:i') ?? '' }}
                                    </span>

                                </div>
                            </td>


                            {{-- ACTIONS --}}
                            <td>
                                <div class="au-row-actions">

                                    <a
                                        href="{{ route('admin.users.edit', $item) }}"
                                        class="au-action au-action-edit"
                                        title="Edit pengguna"
                                        aria-label="Edit {{ $item->name }}"
                                    >
                                        ✎
                                    </a>


                                    @if ((int) $item->id !== (int) auth()->id())

                                        <form
                                            action="{{ route('admin.users.destroy', $item) }}"
                                            method="POST"
                                            data-delete-form
                                            data-user-name="{{ $item->name }}"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="au-action au-action-delete"
                                                title="Hapus pengguna"
                                                aria-label="Hapus {{ $item->name }}"
                                            >
                                                ×
                                            </button>

                                        </form>

                                    @else

                                        <span
                                            class="au-action-disabled"
                                            title="Akun yang sedang digunakan tidak bisa dihapus"
                                        >
                                            ×
                                        </span>

                                    @endif

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5">

                                <div class="au-empty-state">

                                    <div class="au-empty-icon">♙</div>

                                    <h3>
                                        {{ $search || $role || $verification
                                            ? 'Pengguna tidak ditemukan'
                                            : 'Belum ada pengguna' }}
                                    </h3>

                                    <p>
                                        {{ $search || $role || $verification
                                            ? 'Coba ubah kata kunci atau filter pencarianmu.'
                                            : 'Akun yang terdaftar akan muncul di halaman ini.' }}
                                    </p>

                                    @if ($search || $role || $verification)
                                        <a
                                            href="{{ route('admin.users.index') }}"
                                            class="au-empty-link"
                                        >
                                            Hapus semua filter
                                        </a>
                                    @endif

                                </div>

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- =================================================
             PAGINATION
        ================================================== --}}

        @if ($users->hasPages())

            <div class="au-pagination">

                <div class="au-pagination-info">
                    Menampilkan
                    <strong>{{ $users->firstItem() }}</strong>–<strong>{{ $users->lastItem() }}</strong>
                    dari
                    <strong>{{ $users->total() }}</strong>
                    pengguna
                </div>

                <div class="au-pagination-links">
                    {{ $users->links() }}
                </div>

            </div>

        @endif

    </section>


    {{-- =====================================================
         FOOTER
    ====================================================== --}}

    <footer class="au-footer">
        <span>
            © {{ date('Y') }} PASARLOKAL / ADMIN STUDIO
        </span>

        <span>
            Mengembangkan ekonomi lokal, bersama.
            <b>✳</b>
        </span>
    </footer>

</div>

@endsection
