
@extends('layouts.admin')

@section('title', 'Detail Pesanan')
@section('breadcrumb', 'Detail Pesanan')
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

    $rawStatus = strtolower((string) $order->status);
    $rawPaymentStatus = strtolower((string) $order->payment_status);

    $orderStatusText = $orderLabels[$rawStatus]
        ?? ($rawStatus !== '' ? ucfirst(str_replace('_', ' ', $rawStatus)) : 'Belum diatur');

    $paymentStatusText = $paymentLabels[$rawPaymentStatus]
        ?? ($rawPaymentStatus !== '' ? ucfirst(str_replace('_', ' ', $rawPaymentStatus)) : 'Belum diatur');

    $money = static fn ($value) =>
        'Rp' . number_format((float) $value, 0, ',', '.');

    $statusTone = in_array($rawStatus, ['selesai', 'completed'], true)
        ? 'success'
        : (in_array($rawStatus, ['dibatalkan', 'cancelled', 'gagal', 'failed'], true)
            ? 'danger'
            : (in_array($rawStatus, ['dikirim', 'shipped', 'diproses', 'processing'], true)
                ? 'info'
                : 'pending'));

    // Tentukan warna badge pembayaran berdasarkan statusnya.
    // Closure ini dipakai untuk status pembayaran pesanan dan setiap riwayat pembayaran.
    $paymentToneFor = static function ($status): string {
        $status = strtolower(trim((string) $status));

        if (in_array($status, [
            'paid', 'dibayar', 'lunas', 'verified', 'berhasil', 'sukses',
        ], true)) {
            return 'success';
        }

        if (in_array($status, [
            'ditolak', 'rejected', 'gagal', 'failed', 'refunded', 'dikembalikan',
        ], true)) {
            return 'danger';
        }

        if (in_array($status, [
            'pending', 'menunggu_pembayaran', 'belum_dibayar', 'unpaid',
            'menunggu_verifikasi', 'menunggu_konfirmasi',
        ], true)) {
            return 'pending';
        }

        return 'neutral';
    };

    $paymentTone = $paymentToneFor($rawPaymentStatus);
@endphp


<div class="ao-page">

    {{-- BACK AND TITLE --}}

    <div class="ao-detail-navigation">
        <a href="{{ route('admin.orders.index') }}" class="ao-back-button">
            <span>←</span>
            Kembali ke daftar pesanan
        </a>

        <span class="ao-record-id">
            ORDER #{{ str_pad((string) $order->id, 6, '0', STR_PAD_LEFT) }}
        </span>
    </div>


    {{-- ORDER HEADER --}}

    <section class="ao-detail-header">

        <div>
            <p class="ao-eyebrow">TRANSACTION DETAIL</p>

            <h1>
                {{ $order->order_number ?: 'ORD-' . $order->id }}
            </h1>

            <p class="ao-description">
                Dibuat pada
                {{ $order->created_at
                    ? \Illuminate\Support\Carbon::parse($order->created_at)->translatedFormat('l, d F Y \p\u\k\u\l H:i')
                    : 'tanggal tidak tersedia' }}
            </p>
        </div>

        <div class="ao-detail-statuses">
            <span class="ao-status ao-order-{{ $statusTone }}">
                <i></i>
                {{ $orderStatusText }}
            </span>

            <span class="ao-status ao-payment-{{ $paymentTone }}">
                <i></i>
                {{ $paymentStatusText }}
            </span>
        </div>

    </section>


    {{-- TIMELINE --}}

    <section class="ao-timeline-panel">

        <p class="ao-eyebrow">ORDER TIMELINE</p>

        <div class="ao-timeline">

            <div class="ao-timeline-step is-complete">
                <span>✓</span>
                <div>
                    <strong>Pesanan dibuat</strong>
                    <small>
                        {{ $order->created_at
                            ? \Illuminate\Support\Carbon::parse($order->created_at)->format('d M Y, H:i')
                            : '—' }}
                    </small>
                </div>
            </div>

            <div class="ao-timeline-step {{ $order->paid_at ? 'is-complete' : '' }}">
                <span>{{ $order->paid_at ? '✓' : '2' }}</span>
                <div>
                    <strong>Pembayaran</strong>
                    <small>
                        {{ $order->paid_at
                            ? \Illuminate\Support\Carbon::parse($order->paid_at)->format('d M Y, H:i')
                            : 'Belum ada waktu pembayaran pada pesanan' }}
                    </small>
                </div>
            </div>

            <div class="ao-timeline-step {{ $order->shipped_at ? 'is-complete' : '' }}">
                <span>{{ $order->shipped_at ? '✓' : '3' }}</span>
                <div>
                    <strong>Dikirim</strong>
                    <small>
                        {{ $order->shipped_at
                            ? \Illuminate\Support\Carbon::parse($order->shipped_at)->format('d M Y, H:i')
                            : 'Belum ada waktu pengiriman' }}
                    </small>
                </div>
            </div>

            <div class="ao-timeline-step {{ $order->completed_at ? 'is-complete' : '' }}">
                <span>{{ $order->completed_at ? '✓' : '4' }}</span>
                <div>
                    <strong>Selesai</strong>
                    <small>
                        {{ $order->completed_at
                            ? \Illuminate\Support\Carbon::parse($order->completed_at)->format('d M Y, H:i')
                            : 'Pesanan belum selesai' }}
                    </small>
                </div>
            </div>

            @if ($order->cancelled_at)
                <div class="ao-cancel-note">
                    Pesanan dibatalkan pada
                    {{ \Illuminate\Support\Carbon::parse($order->cancelled_at)->format('d M Y, H:i') }}.
                </div>
            @endif

        </div>

    </section>


    {{-- MAIN DETAIL GRID --}}

    <div class="ao-detail-grid">

        <div class="ao-detail-main">


            {{-- PURCHASE ITEMS --}}

            <section class="ao-detail-panel">

                <div class="ao-detail-panel-heading">
                    <div>
                        <p class="ao-eyebrow">PURCHASE SUMMARY</p>
                        <h2>Item pesanan</h2>
                    </div>

                    <span class="ao-detail-count">
                        {{ number_format($items->sum('quantity')) }} unit
                    </span>
                </div>


                <div class="ao-detail-items">

                    @forelse ($items as $item)

                        @php
                            $productName = $item->product_name ?: 'Produk tidak tersedia';
                            $imagePath = $item->product_image;

                            $imageUrl = $imagePath
                                ? (
                                    \Illuminate\Support\Str::startsWith(
                                        $imagePath,
                                        ['http://', 'https://']
                                    )
                                        ? $imagePath
                                        : asset('storage/' . ltrim($imagePath, '/'))
                                )
                                : null;
                        @endphp

                        <div class="ao-detail-item">

                            <div class="ao-detail-product-image">
                                @if ($imageUrl)
                                    <img
                                        src="{{ $imageUrl }}"
                                        alt="{{ $productName }}"
                                        loading="lazy"
                                    >
                                @else
                                    <span>✳</span>
                                @endif
                            </div>

                            <div class="ao-detail-product-info">
                                <strong>{{ $productName }}</strong>

                                <small>
                                    {{ $money($item->harga_satuan) }} per unit
                                </small>

                                <span>
                                    Kuantitas: {{ number_format((int) $item->quantity) }}
                                </span>
                            </div>

                            <div class="ao-detail-product-total">
                                <small>Subtotal item</small>
                                <strong>{{ $money($item->subtotal) }}</strong>
                            </div>

                        </div>

                    @empty

                        <div class="ao-empty">
                            <span>▧</span>
                            <h3>Belum ada item</h3>
                            <p>Detail item untuk pesanan ini tidak ditemukan.</p>
                        </div>

                    @endforelse

                </div>


                {{-- TOTAL BREAKDOWN --}}

                <div class="ao-total-breakdown">

                    <div>
                        <span>Subtotal produk</span>
                        <strong>{{ $money($order->subtotal) }}</strong>
                    </div>

                    <div>
                        <span>Biaya pengiriman</span>
                        <strong>{{ $money($order->shipping_cost) }}</strong>
                    </div>

                    <div>
                        <span>Diskon</span>
                        <strong class="ao-discount">
                            −{{ $money($order->discount) }}
                        </strong>
                    </div>

                    <div class="ao-grand-total">
                        <span>Total pesanan</span>
                        <strong>{{ $money($order->total) }}</strong>
                    </div>

                </div>

            </section>


            {{-- PAYMENT HISTORY --}}

            <section class="ao-detail-panel">

                <div class="ao-detail-panel-heading">
                    <div>
                        <p class="ao-eyebrow">PAYMENT RECORDS</p>
                        <h2>Riwayat pembayaran</h2>
                    </div>

                    <span class="ao-detail-count">
                        {{ $payments->count() }} catatan
                    </span>
                </div>


                @if ($payments->isNotEmpty())

                    <div class="ao-payment-history">

                        @foreach ($payments as $payment)

                            @php
                                $rawPaymentRecordStatus = strtolower((string) $payment->status);

                                $paymentRecordLabel = $paymentLabels[$rawPaymentRecordStatus]
                                    ?? ($rawPaymentRecordStatus !== ''
                                        ? ucfirst(str_replace('_', ' ', $rawPaymentRecordStatus))
                                        : 'Belum diatur');

                                $proofPath = $payment->payment_proof;

                                $proofUrl = $proofPath
                                    ? (
                                        \Illuminate\Support\Str::startsWith(
                                            $proofPath,
                                            ['http://', 'https://']
                                        )
                                            ? $proofPath
                                            : asset('storage/' . ltrim($proofPath, '/'))
                                    )
                                    : null;
                            @endphp

                            <article class="ao-payment-record">

                                <div class="ao-payment-record-top">

                                    <div>
                                        <strong>
                                            {{ $payment->payment_code ?: 'PAY-' . $payment->id }}
                                        </strong>

                                        <small>
                                            {{ $payment->created_at
                                                ? \Illuminate\Support\Carbon::parse($payment->created_at)->format('d M Y, H:i')
                                                : 'Tanggal tidak tersedia' }}
                                        </small>
                                    </div>

                                    <span class="ao-status ao-payment-{{ $paymentToneFor($rawPaymentRecordStatus) }}">
                                        <i></i>
                                        {{ $paymentRecordLabel }}
                                    </span>

                                </div>


                                <div class="ao-payment-record-details">

                                    <div>
                                        <span>Metode</span>
                                        <strong>
                                            {{ $payment->metode
                                                ? ucfirst(str_replace('_', ' ', $payment->metode))
                                                : '—' }}
                                        </strong>
                                    </div>

                                    <div>
                                        <span>Jumlah</span>
                                        <strong>{{ $money($payment->jumlah) }}</strong>
                                    </div>

                                    <div>
                                        <span>Referensi provider</span>
                                        <strong>{{ $payment->provider_reference ?: '—' }}</strong>
                                    </div>

                                    <div>
                                        <span>Waktu dibayar</span>
                                        <strong>
                                            {{ $payment->paid_at
                                                ? \Illuminate\Support\Carbon::parse($payment->paid_at)->format('d M Y, H:i')
                                                : '—' }}
                                        </strong>
                                    </div>

                                </div>


                                @if ($payment->catatan)
                                    <div class="ao-payment-note">
                                        <strong>Catatan</strong>
                                        <p>{{ $payment->catatan }}</p>
                                    </div>
                                @endif


                                @if ($proofUrl)
                                    <a
                                        href="{{ $proofUrl }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="ao-proof-link"
                                    >
                                        Lihat bukti pembayaran ↗
                                    </a>
                                @endif

                            </article>

                        @endforeach

                    </div>

                @else

                    <div class="ao-no-payment">
                        <span>◷</span>
                        <strong>Belum ada catatan pembayaran</strong>
                        <p>Data pembayaran untuk pesanan ini belum tersedia.</p>
                    </div>

                @endif

            </section>

        </div>


        {{-- RIGHT COLUMN --}}

        <aside class="ao-detail-aside">


            {{-- BUYER --}}

            <section class="ao-detail-panel">

                <div class="ao-detail-panel-heading">
                    <div>
                        <p class="ao-eyebrow">CUSTOMER</p>
                        <h2>Informasi pembeli</h2>
                    </div>
                </div>

                <div class="ao-customer-card">

                    @php
                        $customerName = $order->recipient_name
                            ?: ($order->buyer_account_name ?: 'Pembeli');

                        $customerInitial = mb_strtoupper(
                            mb_substr($customerName, 0, 1)
                        );
                    @endphp

                    <div class="ao-customer-avatar">
                        {{ $customerInitial }}
                    </div>

                    <strong>{{ $customerName }}</strong>

                    <span>
                        {{ $order->buyer_email ?: 'Email akun tidak tersedia' }}
                    </span>

                </div>

                <div class="ao-info-list">

                    <div>
                        <span>Nama akun</span>
                        <strong>{{ $order->buyer_account_name ?: '—' }}</strong>
                    </div>

                    <div>
                        <span>Telepon akun</span>
                        <strong>{{ $order->buyer_phone ?: '—' }}</strong>
                    </div>

                    <div>
                        <span>Nama penerima</span>
                        <strong>{{ $order->recipient_name ?: '—' }}</strong>
                    </div>

                    <div>
                        <span>Telepon penerima</span>
                        <strong>{{ $order->recipient_phone ?: '—' }}</strong>
                    </div>

                </div>

            </section>


            {{-- SHIPPING --}}

            <section class="ao-detail-panel">

                <div class="ao-detail-panel-heading">
                    <div>
                        <p class="ao-eyebrow">DELIVERY</p>
                        <h2>Alamat pengiriman</h2>
                    </div>
                </div>

                <div class="ao-address-card">

                    <span class="ao-address-icon">⌖</span>

                    <div>
                        <strong>{{ $order->recipient_name ?: 'Penerima' }}</strong>

                        <p>
                            {{ $order->shipping_address ?: 'Alamat belum tersedia.' }}
                        </p>

                        @if ($order->shipping_postal_code)
                            <small>
                                Kode pos: {{ $order->shipping_postal_code }}
                            </small>
                        @endif
                    </div>

                </div>

                @if ($order->shipping_notes)
                    <div class="ao-detail-note">
                        <strong>Catatan pengiriman</strong>
                        <p>{{ $order->shipping_notes }}</p>
                    </div>
                @endif

                @if ($order->notes)
                    <div class="ao-detail-note">
                        <strong>Catatan pesanan</strong>
                        <p>{{ $order->notes }}</p>
                    </div>
                @endif

            </section>


            {{-- STORE AND PAYMENT METHOD --}}

            <section class="ao-detail-panel">

                <div class="ao-detail-panel-heading">
                    <div>
                        <p class="ao-eyebrow">ORDER INFORMATION</p>
                        <h2>Informasi transaksi</h2>
                    </div>
                </div>

                <div class="ao-info-list">

                    <div>
                        <span>Nomor pesanan</span>
                        <strong>{{ $order->order_number ?: 'ORD-' . $order->id }}</strong>
                    </div>

                    <div>
                        <span>Toko UMKM</span>
                        <strong>{{ $order->store_name ?: '—' }}</strong>
                    </div>

                    <div>
                        <span>Metode pembayaran</span>
                        <strong>
                            {{ $order->payment_method
                                ? ucfirst(str_replace('_', ' ', $order->payment_method))
                                : '—' }}
                        </strong>
                    </div>

                    <div>
                        <span>Waktu pembayaran</span>
                        <strong>
                            {{ $order->paid_at
                                ? \Illuminate\Support\Carbon::parse($order->paid_at)->format('d M Y, H:i')
                                : '—' }}
                        </strong>
                    </div>

                </div>

            </section>

        </aside>

    </div>


    <footer class="ao-footer">
        <span>© {{ date('Y') }} PASARLOKAL / ADMIN STUDIO</span>
        <span>Detail dan riwayat transaksi. <b>✳</b></span>
    </footer>

</div>

@endsection
