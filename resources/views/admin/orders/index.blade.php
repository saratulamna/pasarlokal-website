
@extends('layouts.admin')

@section('title', 'Pesanan')
@section('breadcrumb', 'Pesanan')
@section('body-class', 'admin-orders-page')

@section('content')

@php
    $orderLabels = [
        'menunggu_pembayaran' => 'Menunggu pembayaran',
        'menunggu_konfirmasi' => 'Menunggu konfirmasi',
        'pending' => 'Menunggu diproses',
        'baru' => 'Pesanan baru',
        'diproses' => 'Diproses',
        'processing' => 'Diproses',
        'dikirim' => 'Dikirim',
        'shipped' => 'Dikirim',
        'selesai' => 'Selesai',
        'completed' => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
        'cancelled' => 'Dibatalkan',
        'gagal' => 'Gagal',
        'failed' => 'Gagal',
    ];

    $paymentLabels = [
        'menunggu_pembayaran' => 'Menunggu pembayaran',
        'belum_dibayar' => 'Belum dibayar',
        'unpaid' => 'Belum dibayar',
        'pending' => 'Menunggu',
        'menunggu_verifikasi' => 'Menunggu verifikasi',
        'paid' => 'Dibayar',
        'dibayar' => 'Dibayar',
        'lunas' => 'Lunas',
        'verified' => 'Terverifikasi',
        'berhasil' => 'Berhasil',
        'sukses' => 'Berhasil',
        'ditolak' => 'Ditolak',
        'rejected' => 'Ditolak',
        'gagal' => 'Gagal',
        'failed' => 'Gagal',
        'refunded' => 'Dikembalikan',
        'dikembalikan' => 'Dikembalikan',
    ];

    $orderTone = static function ($value) {
        $value = strtolower((string) $value);

        if (in_array($value, ['selesai', 'completed'], true)) {
            return 'success';
        }

        if (in_array($value, ['dibatalkan', 'cancelled', 'gagal', 'failed'], true)) {
            return 'danger';
        }

        if (in_array($value, ['dikirim', 'shipped', 'diproses', 'processing'], true)) {
            return 'info';
        }

        return 'pending';
    };

    $paymentTone = static function ($value) {
        $value = strtolower((string) $value);

        if (in_array($value, [
            'paid', 'dibayar', 'lunas', 'verified', 'berhasil', 'sukses',
        ], true)) {
            return 'success';
        }

        if (in_array($value, [
            'ditolak', 'rejected', 'gagal', 'failed', 'refunded',
        ], true)) {
            return 'danger';
        }

        if (in_array($value, [
            'menunggu_pembayaran',
            'belum_dibayar',
            'unpaid',
            'pending',
            'menunggu_verifikasi',
        ], true)) {
            return 'pending';
        }

        return 'neutral';
    };

    $money = static fn ($value) =>
        'Rp' . number_format((float) $value, 0, ',', '.');
@endphp


<div class="ao-page">

    {{-- PAGE HEADER --}}

    <section class="ao-intro">
        <div>
            <p class="ao-eyebrow">ADMIN CONSOLE / TRANSAKSI</p>

            <h1>
                Kelola <span>pesanan.</span>
            </h1>

            <p class="ao-description">
                Pantau proses belanja, informasi pembayaran,
                rincian item, dan status pemenuhan pesanan di PasarLokal.
            </p>
        </div>

        <div class="ao-live-pill">
            <span></span>
            ORDER MANAGEMENT
        </div>
    </section>


    {{-- FLASH MESSAGE --}}

    @if (session('success'))
        <div class="ao-alert ao-alert-success" role="status">
            <span>✓</span>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="ao-alert ao-alert-error" role="alert">
            <span>!</span>
            {{ session('error') }}
        </div>
    @endif


    {{-- SUMMARY --}}

    <section class="ao-stats" aria-label="Ringkasan pesanan">

        <article class="ao-stat ao-stat-total">
            <div class="ao-stat-top">
                <span class="ao-stat-icon">▧</span>
                <span class="ao-stat-label">TOTAL PESANAN</span>
            </div>

            <strong>{{ number_format($stats['total'], 0, ',', '.') }}</strong>
            <small>Seluruh transaksi tercatat</small>
        </article>

        <article class="ao-stat ao-stat-waiting">
            <div class="ao-stat-top">
                <span class="ao-stat-icon">◷</span>
                <span class="ao-stat-label">BELUM DIBAYAR</span>
            </div>

            <strong>{{ number_format($stats['awaiting_payment'], 0, ',', '.') }}</strong>
            <small>Perlu pemantauan pembayaran</small>
        </article>

        <article class="ao-stat ao-stat-processing">
            <div class="ao-stat-top">
                <span class="ao-stat-icon">⚙</span>
                <span class="ao-stat-label">DIPROSES</span>
            </div>

            <strong>{{ number_format($stats['processing'], 0, ',', '.') }}</strong>
            <small>Pesanan dalam penanganan</small>
        </article>

        <article class="ao-stat ao-stat-shipped">
            <div class="ao-stat-top">
                <span class="ao-stat-icon">↗</span>
                <span class="ao-stat-label">DIKIRIM</span>
            </div>

            <strong>{{ number_format($stats['shipped'], 0, ',', '.') }}</strong>
            <small>Dalam proses pengiriman</small>
        </article>

        <article class="ao-stat ao-stat-completed">
            <div class="ao-stat-top">
                <span class="ao-stat-icon">✓</span>
                <span class="ao-stat-label">SELESAI</span>
            </div>

            <strong>{{ number_format($stats['completed'], 0, ',', '.') }}</strong>
            <small>Transaksi selesai</small>
        </article>

        <article class="ao-stat ao-stat-cancelled">
            <div class="ao-stat-top">
                <span class="ao-stat-icon">×</span>
                <span class="ao-stat-label">DIBATALKAN / GAGAL</span>
            </div>

            <strong>{{ number_format($stats['cancelled'], 0, ',', '.') }}</strong>
            <small>Pesanan tidak dilanjutkan</small>
        </article>

    </section>


    {{-- ORDERS PANEL --}}

    <section class="ao-panel">

        <div class="ao-panel-heading">
            <div>
                <p class="ao-eyebrow">TRANSACTION DIRECTORY</p>
                <h2>Daftar pesanan</h2>
                <p>
                    Lihat transaksi berdasarkan pembeli, status,
                    tanggal, dan nilai pembayaran.
                </p>
            </div>

            <span class="ao-total-badge">
                {{ number_format($orders->total(), 0, ',', '.') }}
                hasil
            </span>
        </div>


        {{-- SEARCH AND FILTERS --}}

        <form
            action="{{ route('admin.orders.index') }}"
            method="GET"
            class="ao-filters"
        >

            <label class="ao-search">
                <span aria-hidden="true">⌕</span>

                <input
                    type="search"
                    name="q"
                    value="{{ $search }}"
                    placeholder="Nomor pesanan, pembeli, toko..."
                    aria-label="Cari pesanan"
                >
            </label>


            <select name="status" class="ao-select" aria-label="Status pesanan">
                <option value="">Semua status pesanan</option>

                @foreach ($orderStatuses as $itemStatus)
                    <option
                        value="{{ $itemStatus }}"
                        @selected($status === $itemStatus)
                    >
                        {{ $orderLabels[strtolower($itemStatus)] ?? ucfirst(str_replace('_', ' ', $itemStatus)) }}
                    </option>
                @endforeach
            </select>


            <select
                name="payment_status"
                class="ao-select"
                aria-label="Status pembayaran"
            >
                <option value="">Semua pembayaran</option>

                @foreach ($paymentStatuses as $itemStatus)
                    <option
                        value="{{ $itemStatus }}"
                        @selected($paymentStatus === $itemStatus)
                    >
                        {{ $paymentLabels[strtolower($itemStatus)] ?? ucfirst(str_replace('_', ' ', $itemStatus)) }}
                    </option>
                @endforeach
            </select>


            <label class="ao-date-filter">
                <span>Dari</span>
                <input type="date" name="from" value="{{ $from }}">
            </label>

            <label class="ao-date-filter">
                <span>Sampai</span>
                <input type="date" name="to" value="{{ $to }}">
            </label>


            <select name="per_page" class="ao-select ao-per-page" aria-label="Jumlah hasil per halaman">
                <option value="10" @selected($perPage === 10)>10 / halaman</option>
                <option value="25" @selected($perPage === 25)>25 / halaman</option>
                <option value="50" @selected($perPage === 50)>50 / halaman</option>
            </select>


            <button type="submit" class="ao-filter-button">
                Terapkan filter
            </button>


            @if ($search !== '' || $status !== '' || $paymentStatus !== '' || $from !== '' || $to !== '')
                <a href="{{ route('admin.orders.index') }}" class="ao-reset-button">
                    Reset
                </a>
            @endif

        </form>


        {{-- TABLE --}}

        <div class="ao-table-wrap">

            <table class="ao-table">

                <thead>
                    <tr>
                        <th>PESANAN</th>
                        <th>PEMBELI</th>
                        <th>UMKM / TOKO</th>
                        <th>ITEM</th>
                        <th>TOTAL</th>
                        <th>PEMBAYARAN</th>
                        <th>STATUS PESANAN</th>
                        <th>AKSI</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($orders as $item)

                        @php
                            $rawOrderStatus = strtolower((string) $item->status);
                            $rawPaymentStatus = strtolower((string) $item->payment_status);

                            $orderStatusText = $orderLabels[$rawOrderStatus]
                                ?? ($rawOrderStatus !== ''
                                    ? ucfirst(str_replace('_', ' ', $rawOrderStatus))
                                    : 'Belum diatur');

                            $paymentStatusText = $paymentLabels[$rawPaymentStatus]
                                ?? ($rawPaymentStatus !== ''
                                    ? ucfirst(str_replace('_', ' ', $rawPaymentStatus))
                                    : 'Belum diatur');

                            $buyerName = $item->recipient_name
                                ?: ($item->buyer_account_name ?: 'Pembeli');

                            $quantity = (int) ($item->item_quantity ?? 0);
                        @endphp

                        <tr>

                            <td>
                                <div class="ao-order-cell">
                                    <strong>
                                        {{ $item->order_number ?: 'ORD-' . $item->id }}
                                    </strong>

                                    <small>
                                        #{{ str_pad((string) $item->id, 6, '0', STR_PAD_LEFT) }}
                                    </small>

                                    <small>
                                        {{ $item->created_at
                                            ? \Illuminate\Support\Carbon::parse($item->created_at)->format('d M Y, H:i')
                                            : '—' }}
                                    </small>
                                </div>
                            </td>


                            <td>
                                <div class="ao-buyer-cell">
                                    <span class="ao-avatar">
                                        {{ mb_strtoupper(mb_substr($buyerName, 0, 1)) }}
                                    </span>

                                    <div>
                                        <strong>{{ $buyerName }}</strong>
                                        <small>{{ $item->buyer_email ?: 'Email tidak tersedia' }}</small>
                                    </div>
                                </div>
                            </td>


                            <td>
                                <span class="ao-store-name">
                                    {{ $item->store_name ?: 'Toko tidak tersedia' }}
                                </span>
                            </td>


                            <td>
                                <span class="ao-item-count">
                                    {{ number_format($quantity, 0, ',', '.') }}
                                    unit
                                </span>

                                <small class="ao-item-lines">
                                    {{ number_format((int) ($item->item_lines ?? 0)) }}
                                    baris produk
                                </small>
                            </td>


                            <td>
                                <strong class="ao-money">
                                    {{ $money($item->total) }}
                                </strong>

                                <small class="ao-payment-method">
                                    {{ $item->payment_method
                                        ? ucfirst(str_replace('_', ' ', $item->payment_method))
                                        : 'Metode belum tersedia' }}
                                </small>
                            </td>


                            <td>
                                <span class="ao-status ao-payment-{{ $paymentTone($rawPaymentStatus) }}">
                                    <i></i>
                                    {{ $paymentStatusText }}
                                </span>
                            </td>


                            <td>
                                <span class="ao-status ao-order-{{ $orderTone($rawOrderStatus) }}">
                                    <i></i>
                                    {{ $orderStatusText }}
                                </span>
                            </td>


                            <td>
                                <a
                                    href="{{ route('admin.orders.show', $item->id) }}"
                                    class="ao-detail-button"
                                    aria-label="Lihat detail pesanan {{ $item->order_number }}"
                                    title="Lihat detail pesanan"
                                >
                                    Detail <span>↗</span>
                                </a>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8">

                                <div class="ao-empty">
                                    <span>▧</span>

                                    <h3>
                                        @if ($search || $status || $paymentStatus || $from || $to)
                                            Pesanan tidak ditemukan
                                        @else
                                            Belum ada pesanan
                                        @endif
                                    </h3>

                                    <p>
                                        @if ($search || $status || $paymentStatus || $from || $to)
                                            Ubah kata kunci atau filter untuk melihat hasil lain.
                                        @else
                                            Transaksi akan muncul di sini setelah pembeli melakukan pemesanan.
                                        @endif
                                    </p>

                                    @if ($search || $status || $paymentStatus || $from || $to)
                                        <a href="{{ route('admin.orders.index') }}" class="ao-filter-button">
                                            Reset filter
                                        </a>
                                    @endif
                                </div>

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- PAGINATION --}}

        @if ($orders->hasPages())
            <div class="ao-pagination">

                <span>
                    Menampilkan {{ $orders->firstItem() }}–{{ $orders->lastItem() }}
                    dari {{ $orders->total() }} pesanan
                </span>

                {{ $orders->links() }}

            </div>
        @endif

    </section>


    <footer class="ao-footer">
        <span>© {{ date('Y') }} PASARLOKAL / ADMIN STUDIO</span>
        <span>Data transaksi marketplace. <b>✳</b></span>
    </footer>

</div>

@endsection
