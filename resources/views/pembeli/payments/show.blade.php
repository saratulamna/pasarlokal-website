
@extends('layouts.buyer')

@push('page-styles')
    @vite('resources/css/pages/pembeli/payment-detail.css')
@endpush

@push('page-scripts')
    @vite('resources/js/pages/pembeli/payment-detail.js')
@endpush

@section('page-title', 'Detail Pembayaran')

@section('content')

@php
    $payment = data_get($order, 'payment');

    $orderId = data_get($order, 'id');

    $orderCode = 'PL-' . str_pad((string) $orderId, 6, '0', STR_PAD_LEFT);

    $orderTotal = data_get($order, 'total_harga')
        ?? data_get($order, 'grand_total')
        ?? data_get($order, 'total')
        ?? data_get($payment, 'amount')
        ?? data_get($payment, 'jumlah')
        ?? 0;

    $rawMethod = data_get($order, 'payment_method')
        ?? data_get($payment, 'payment_method')
        ?? data_get($payment, 'metode')
        ?? data_get($payment, 'method')
        ?? '';

    $methodKey = strtolower(str_replace(
        [' ', '-'],
        '_',
        trim((string) $rawMethod)
    ));

    $isCod = in_array($methodKey, [
        'cod',
        'cash_on_delivery',
        'bayar_di_tempat',
        'tunai',
        'cash',
    ], true);

    $methodLabel = $isCod
        ? 'Bayar di tempat (COD)'
        : (in_array($methodKey, [
            'transfer_bank',
            'bank_transfer',
            'transfer',
            'bca',
            'bri',
            'bni',
            'mandiri',
        ], true)
            ? 'Transfer bank'
            : ($rawMethod !== '' ? ucwords(str_replace('_', ' ', $rawMethod)) : 'Belum ditentukan'));

    $rawStatus = data_get($payment, 'status')
        ?? data_get($order, 'payment_status')
        ?? data_get($order, 'status')
        ?? 'belum_bayar';

    $paymentStatus = strtolower(trim((string) $rawStatus));

    $statusLabels = [
        'belum_bayar'          => 'Menunggu pembayaran',
        'pending'              => 'Menunggu pembayaran',
        'menunggu_pembayaran'  => 'Menunggu pembayaran',
        'menunggu_verifikasi'  => 'Sedang diverifikasi',
        'menunggu_konfirmasi'  => 'Menunggu konfirmasi',
        'berhasil'             => 'Pembayaran berhasil',
        'lunas'                => 'Pembayaran lunas',
        'paid'                 => 'Pembayaran berhasil',
        'terverifikasi'        => 'Pembayaran terverifikasi',
        'ditolak'              => 'Bukti pembayaran ditolak',
        'gagal'                => 'Pembayaran gagal',
        'failed'               => 'Pembayaran gagal',
        'dibatalkan'           => 'Pesanan dibatalkan',
        'cancelled'             => 'Pesanan dibatalkan',
        'canceled'              => 'Pesanan dibatalkan',
        'diproses'              => 'Pesanan diproses',
        'dikirim'               => 'Pesanan dikirim',
        'selesai'               => 'Pesanan selesai',
    ];

    $statusLabel = $statusLabels[$paymentStatus]
        ?? ucwords(str_replace('_', ' ', $paymentStatus));

    $isPaid = in_array($paymentStatus, [
        'berhasil',
        'lunas',
        'paid',
        'terverifikasi',
    ], true);

    $isWaitingVerification = $paymentStatus === 'menunggu_verifikasi';

    $isRejected = in_array($paymentStatus, [
        'ditolak',
        'gagal',
        'failed',
    ], true);

    $isCancelled = in_array($paymentStatus, [
        'dibatalkan',
        'cancelled',
        'canceled',
    ], true);

    $statusTone = $isPaid
        ? 'success'
        : ($isWaitingVerification
            ? 'review'
            : ($isRejected || $isCancelled ? 'danger' : 'pending'));

    $canUpload = !$isCod
        && !$isPaid
        && !$isWaitingVerification
        && !$isCancelled;

    $createdAt = data_get($order, 'created_at');

    $dateText = $createdAt
        ? \Illuminate\Support\Carbon::parse($createdAt)->translatedFormat('d M Y, H:i')
        : 'Tanggal belum tersedia';

    $recipientName = data_get($order, 'nama_penerima')
        ?? data_get($order, 'recipient_name')
        ?? data_get($order, 'shipping_name')
        ?? null;

    $recipientPhone = data_get($order, 'nomor_telepon')
        ?? data_get($order, 'no_telepon')
        ?? data_get($order, 'phone')
        ?? null;

    $shippingAddress = data_get($order, 'alamat_pengiriman')
        ?? data_get($order, 'shipping_address')
        ?? data_get($order, 'alamat')
        ?? null;

    $proofPath = data_get($payment, 'bukti_transfer')
        ?? data_get($payment, 'bukti_pembayaran')
        ?? data_get($payment, 'proof_path')
        ?? data_get($order, 'bukti_transfer')
        ?? data_get($order, 'bukti_pembayaran')
        ?? null;

    $proofName = $proofPath
        ? basename(str_replace('\\', '/', $proofPath))
        : null;
@endphp

<div class="pd-page">

    {{-- BREADCRUMB --}}
    <nav class="pd-breadcrumb" aria-label="Navigasi halaman">
        <a href="{{ route('pembeli.orders.index') }}">
            <span aria-hidden="true">←</span>
            Riwayat pesanan
        </a>

        <span class="pd-breadcrumb-divider">/</span>
        <span>Detail pembayaran</span>
    </nav>

    {{-- HERO --}}
    <header class="pd-hero">

        <div class="pd-hero-pattern" aria-hidden="true"></div>

        <div class="pd-hero-content">

            <div class="pd-eyebrow">
                <span class="pd-eyebrow-dot"></span>
                PASARLOKAL PAYMENT CENTER
            </div>

            <h1>
                Pembayaran yang<br>
                <span>lebih mudah.</span>
            </h1>

            <p class="pd-hero-description">
                Semua informasi pembayaran dan pesananmu,
                dirangkum dalam satu tempat.
            </p>

            <div class="pd-order-reference">
                <div>
                    <span class="pd-reference-label">NOMOR PESANAN</span>
                    <strong>{{ $orderCode }}</strong>
                </div>

                <button
                    type="button"
                    class="pd-copy-button"
                    data-copy-order="{{ $orderCode }}"
                    aria-label="Salin nomor pesanan"
                >
                    <span aria-hidden="true">▣</span>
                    Salin
                </button>
            </div>

        </div>

        <div class="pd-hero-total">
            <span class="pd-total-label">TOTAL PEMBAYARAN</span>

            <strong>
                Rp{{ number_format((float) $orderTotal, 0, ',', '.') }}
            </strong>

            <span class="pd-total-caption">
                <span class="pd-total-sparkle">✳</span>
                Satu transaksi, satu langkah lebih dekat.
            </span>
        </div>

        <span class="pd-hero-stamp" aria-hidden="true">PL.</span>

    </header>

    {{-- FLASH MESSAGE --}}
    @if (session('success'))
        <div class="pd-alert pd-alert--success" role="status">
            <span class="pd-alert-icon">✓</span>
            <div>
                <strong>Berhasil!</strong>
                <p>{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="pd-alert pd-alert--danger" role="alert">
            <span class="pd-alert-icon">!</span>
            <div>
                <strong>Terjadi kendala</strong>
                <p>{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="pd-alert pd-alert--danger" role="alert">
            <span class="pd-alert-icon">!</span>
            <div>
                <strong>Periksa kembali data pembayaranmu.</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- MAIN GRID --}}
    <div class="pd-content-grid">

        <main class="pd-main-column">

            {{-- STATUS --}}
            <section class="pd-card pd-status-card">

                <div class="pd-section-heading">
                    <div class="pd-heading-icon">◷</div>

                    <div>
                        <span class="pd-overline">PAYMENT STATUS</span>
                        <h2>Status pembayaran</h2>
                    </div>
                </div>

                <div class="pd-status-content">

                    <div class="pd-status-symbol pd-status-symbol--{{ $statusTone }}">
                        @if ($isPaid)
                            ✓
                        @elseif ($isWaitingVerification)
                            ◷
                        @elseif ($isRejected || $isCancelled)
                            !
                        @else
                            ↗
                        @endif
                    </div>

                    <div class="pd-status-copy">
                        <span class="pd-status-badge pd-status-badge--{{ $statusTone }}">
                            {{ $statusLabel }}
                        </span>

                        @if ($isPaid)
                            <h3>Pembayaranmu sudah terkonfirmasi!</h3>
                            <p>Terima kasih sudah mendukung produk dan usaha lokal Indonesia.</p>

                        @elseif ($isWaitingVerification)
                            <h3>Bukti pembayaran sudah diterima.</h3>
                            <p>Silakan tunggu proses pemeriksaan oleh admin PasarLokal.</p>

                        @elseif ($isRejected)
                            <h3>Pembayaran perlu diperiksa kembali.</h3>
                            <p>Periksa catatan penolakan dari admin jika tersedia, kemudian unggah bukti yang sesuai.</p>

                        @elseif ($isCancelled)
                            <h3>Pesanan ini telah dibatalkan.</h3>
                            <p>Pesanan yang dibatalkan tidak dapat melanjutkan pembayaran melalui halaman ini.</p>

                        @elseif ($isCod)
                            <h3>Bayar saat pesanan diterima.</h3>
                            <p>Kamu memilih metode COD. Tidak perlu mengunggah bukti transfer.</p>

                        @else
                            <h3>Satu langkah lagi untuk menyelesaikan pesanan.</h3>
                            <p>Periksa detail transaksi sebelum melanjutkan proses pembayaran.</p>
                        @endif
                    </div>

                </div>

                <div class="pd-status-meta">
                    <div>
                        <span>Nomor pesanan</span>
                        <strong>{{ $orderCode }}</strong>
                    </div>

                    <div>
                        <span>Tanggal pesanan</span>
                        <strong>{{ $dateText }}</strong>
                    </div>
                </div>

            </section>

            {{-- PAYMENT INSTRUCTIONS --}}
            <section class="pd-card pd-instruction-card">

                <div class="pd-section-heading">
                    <div class="pd-heading-icon pd-heading-icon--lime">↗</div>

                    <div>
                        <span class="pd-overline">HOW IT WORKS</span>
                        <h2>
                            {{ $isCod ? 'Cara pembayaran COD' : 'Panduan pembayaran' }}
                        </h2>
                    </div>
                </div>

                @if ($isCod)

                    <div class="pd-cod-panel">
                        <div class="pd-cod-icon">📦</div>

                        <div>
                            <h3>Bayar saat pesanan diterima</h3>
                            <p>
                                Siapkan pembayaran sesuai total pesanan ketika
                                pesanan diterima. Ikuti konfirmasi dari penjual
                                untuk proses selanjutnya.
                            </p>
                        </div>
                    </div>

                @else

                    <div class="pd-demo-banner">
                        <div class="pd-demo-icon">i</div>

                        <div>
                            <strong>Mode simulasi pembayaran</strong>
                            <p>
                                PasarLokal menggunakan alur demonstrasi.
                                <b>Jangan melakukan transfer uang sungguhan.</b>
                                Tidak ada nomor rekening nyata yang ditampilkan di halaman ini.
                            </p>
                        </div>
                    </div>

                    <div class="pd-steps">

                        <div class="pd-step">
                            <div class="pd-step-number">01</div>

                            <div class="pd-step-copy">
                                <h3>Periksa total pesanan</h3>
                                <p>
                                    Pastikan nomor pesanan dan nominal
                                    pembayaran sesuai dengan detail di samping.
                                </p>
                            </div>

                            <span class="pd-step-check">✓</span>
                        </div>

                        <div class="pd-step">
                            <div class="pd-step-number">02</div>

                            <div class="pd-step-copy">
                                <h3>Siapkan bukti demonstrasi</h3>
                                <p>
                                    Untuk pengujian aplikasi, pilih file contoh
                                    yang sesuai dengan format yang diizinkan.
                                </p>
                            </div>

                            <span class="pd-step-check">02</span>
                        </div>

                        <div class="pd-step">
                            <div class="pd-step-number">03</div>

                            <div class="pd-step-copy">
                                <h3>Unggah dan tunggu verifikasi</h3>
                                <p>
                                    Setelah formulir dikirim, ikuti status
                                    pembayaran dan informasi yang ditampilkan sistem.
                                </p>
                            </div>

                            <span class="pd-step-check">03</span>
                        </div>

                    </div>

                @endif

            </section>

            {{-- UPLOAD PROOF --}}
            @if ($canUpload)

                <section class="pd-card pd-upload-card" id="pdUploadSection">

                    <div class="pd-section-heading">
                        <div class="pd-heading-icon pd-heading-icon--orange">↑</div>

                        <div>
                            <span class="pd-overline">PAYMENT DOCUMENT</span>
                            <h2>Unggah bukti pembayaran</h2>
                        </div>
                    </div>

                    <p class="pd-upload-intro">
                        Pilih berkas dari perangkatmu. Pastikan file terbaca
                        dengan jelas dan sesuai ketentuan.
                    </p>

                    <form
                        action="{{ route('pembeli.payments.show', ['order' => $orderId]) }}"
                        method="POST"
                        enctype="multipart/form-data"
                        class="pd-upload-form"
                        id="pdPaymentForm"
                    >
                        @csrf

                        <label
                            for="pdProofFile"
                            class="pd-upload-zone"
                            id="pdUploadZone"
                        >
                            <input
                                type="file"
                                id="pdProofFile"
                                name="bukti_transfer"
                                accept=".jpg,.jpeg,.png,.webp,.pdf,image/jpeg,image/png,image/webp,application/pdf"
                                required
                            >

                            <span class="pd-upload-icon" aria-hidden="true">↑</span>

                            <span class="pd-upload-title" id="pdUploadTitle">
                                Pilih file atau klik di sini
                            </span>

                            <span class="pd-upload-description" id="pdUploadDescription">
                                JPG, JPEG, PNG, WEBP, atau PDF
                            </span>

                            <span class="pd-upload-limit">MAKSIMAL 4 MB</span>
                        </label>

                        <div class="pd-file-preview" id="pdFilePreview" hidden>
                            <div class="pd-file-preview-icon" id="pdFileIcon">
                                FILE
                            </div>

                            <div class="pd-file-preview-info">
                                <strong id="pdFileName"></strong>
                                <span id="pdFileSize"></span>
                            </div>

                            <button
                                type="button"
                                class="pd-remove-file"
                                id="pdRemoveFile"
                                aria-label="Hapus file yang dipilih"
                            >
                                ×
                            </button>
                        </div>

                        <div class="pd-upload-consent">
                            <span>✓</span>
                            <p>
                                Pastikan file yang dipilih merupakan berkas
                                pengujian dan tidak berisi informasi pribadi
                                yang tidak diperlukan.
                            </p>
                        </div>

                        <button type="submit" class="pd-submit-button">
                            <span>Kirim bukti pembayaran</span>
                            <span class="pd-submit-arrow">↗</span>
                        </button>

                    </form>

                </section>

            @elseif ($isWaitingVerification)

                <section class="pd-card pd-review-card">

                    <div class="pd-review-icon">◷</div>

                    <div class="pd-review-copy">
                        <span class="pd-overline">VERIFICATION IN PROGRESS</span>
                        <h2>Bukti sedang diperiksa</h2>
                        <p>
                            Kamu tidak perlu mengunggah ulang saat pemeriksaan
                            sedang berlangsung. Silakan cek kembali statusnya nanti.
                        </p>

                        @if ($proofName)
                            <div class="pd-existing-proof">
                                <span class="pd-existing-proof-icon">▤</span>

                                <span>
                                    <small>BERKAS TERSIMPAN</small>
                                    <strong>{{ $proofName }}</strong>
                                </span>
                            </div>
                        @endif
                    </div>

                </section>

            @elseif ($isPaid)

                <section class="pd-card pd-success-card">

                    <div class="pd-success-mark">✓</div>

                    <div>
                        <span class="pd-overline">PAYMENT COMPLETED</span>
                        <h2>Pembayaran selesai!</h2>
                        <p>
                            Simpan nomor pesananmu untuk memudahkan
                            pengecekan riwayat transaksi.
                        </p>

                        <a href="{{ route('pembeli.orders.index') }}" class="pd-secondary-button">
                            Lihat riwayat pesanan <span>↗</span>
                        </a>
                    </div>

                </section>

            @endif

            {{-- RECIPIENT INFORMATION --}}
            @if ($recipientName || $recipientPhone || $shippingAddress)

                <section class="pd-card pd-recipient-card">

                    <div class="pd-section-heading">
                        <div class="pd-heading-icon">⌖</div>

                        <div>
                            <span class="pd-overline">DELIVERY DETAILS</span>
                            <h2>Informasi penerima</h2>
                        </div>
                    </div>

                    <div class="pd-recipient-details">

                        @if ($recipientName)
                            <div class="pd-recipient-row">
                                <span>Nama penerima</span>
                                <strong>{{ $recipientName }}</strong>
                            </div>
                        @endif

                        @if ($recipientPhone)
                            <div class="pd-recipient-row">
                                <span>Nomor telepon</span>
                                <strong>{{ $recipientPhone }}</strong>
                            </div>
                        @endif

                        @if ($shippingAddress)
                            <div class="pd-recipient-row pd-recipient-row--address">
                                <span>Alamat pengiriman</span>
                                <strong>{{ $shippingAddress }}</strong>
                            </div>
                        @endif

                    </div>

                </section>

            @endif

        </main>

        {{-- RIGHT SIDEBAR --}}
        <aside class="pd-side-column">

            <section class="pd-card pd-summary-card">

                <div class="pd-summary-top">
                    <span class="pd-overline">ORDER SUMMARY</span>

                    <span class="pd-summary-icon">▤</span>

                    <h2>Ringkasan pesanan</h2>
                    <p>Rincian transaksi yang sedang kamu lihat.</p>
                </div>

                <div class="pd-summary-order-code">
                    <span>Nomor pesanan</span>

                    <strong>{{ $orderCode }}</strong>
                </div>

                <div class="pd-summary-row">
                    <span>Metode pembayaran</span>
                    <strong>{{ $methodLabel }}</strong>
                </div>

                <div class="pd-summary-row">
                    <span>Tanggal pesanan</span>
                    <strong>{{ $dateText }}</strong>
                </div>

                <div class="pd-summary-row pd-summary-row--status">
                    <span>Status</span>

                    <span class="pd-status-badge pd-status-badge--{{ $statusTone }}">
                        {{ $statusLabel }}
                    </span>
                </div>

                <div class="pd-summary-divider">
                    <span></span>
                    <span>✳</span>
                    <span></span>
                </div>

                <div class="pd-summary-total">
                    <span>Total pembayaran</span>

                    <strong>
                        Rp{{ number_format((float) $orderTotal, 0, ',', '.') }}
                    </strong>

                    <small>Rupiah Indonesia (IDR)</small>
                </div>

                <a
                    href="{{ route('pembeli.orders.index') }}"
                    class="pd-summary-link"
                >
                    <span>←</span>
                    Kembali ke pesanan
                </a>

            </section>

            {{-- HELP CARD --}}
            <section class="pd-help-card">

                <div class="pd-help-decoration" aria-hidden="true">?</div>

                <div class="pd-help-icon">✳</div>

                <span class="pd-overline">NEED A HAND?</span>

                <h3>Butuh bantuan?</h3>

                <p>
                    Periksa status dan instruksi di halaman ini sebelum
                    mengirim bukti pembayaran.
                </p>

                <a href="{{ route('pembeli.orders.index') }}">
                    Kembali ke riwayat <span>↗</span>
                </a>

            </section>

            <div class="pd-trust-note">
                <span class="pd-trust-icon">✓</span>

                <p>
                    <strong>Belanja produk lokal.</strong>
                    Dukung UMKM Indonesia, satu pesanan setiap waktu.
                </p>
            </div>

        </aside>

    </div>

    <footer class="pd-footer">
        <span>PL. / PASARLOKAL</span>
        <span>Bangga produk lokal Indonesia. ✳</span>
    </footer>

</div>

@endsection
