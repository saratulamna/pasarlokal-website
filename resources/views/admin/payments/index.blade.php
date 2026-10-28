@extends('layouts.admin')

@section('title', 'Verifikasi Pembayaran')
@section('breadcrumb', 'Verifikasi Pembayaran')
@section('body-class', 'admin-payments-page')

@section('content')
@php
    $statusLabels = [
        'pending' => 'Menunggu verifikasi',
        'menunggu_verifikasi' => 'Menunggu verifikasi',
        'menunggu_pembayaran' => 'Menunggu pembayaran',
        'belum_dibayar' => 'Belum dibayar',
        'unpaid' => 'Belum dibayar',
        'paid' => 'Dibayar',
        'dibayar' => 'Dibayar',
        'lunas' => 'Lunas',
        'verified' => 'Terverifikasi',
        'berhasil' => 'Berhasil',
        'sukses' => 'Berhasil',
        'rejected' => 'Ditolak',
        'ditolak' => 'Ditolak',
        'failed' => 'Gagal',
        'gagal' => 'Gagal',
        'refunded' => 'Dikembalikan',
        'dikembalikan' => 'Dikembalikan',
    ];

    $statusTone = static function ($status): string {
        $value = strtolower((string) $status);

        if (in_array($value, ['paid', 'dibayar', 'lunas', 'verified', 'berhasil', 'sukses'], true)) {
            return 'success';
        }

        if (in_array($value, ['rejected', 'ditolak', 'failed', 'gagal', 'refunded', 'dikembalikan'], true)) {
            return 'danger';
        }

        if (in_array($value, ['pending', 'menunggu_verifikasi'], true)) {
            return 'pending';
        }

        if (in_array($value, ['menunggu_pembayaran', 'belum_dibayar', 'unpaid'], true)) {
            return 'neutral';
        }

        return 'neutral';
    };

    $money = static fn ($value): string => 'Rp' . number_format((float) $value, 0, ',', '.');
    $activeFilter = request('status', 'pending');
@endphp

<div class="ap-page">
    <section class="ap-hero">
        <div class="ap-hero-copy">
            <span class="ap-eyebrow"><span class="ap-eyebrow-dot"></span> FINANCE WORKSPACE</span>
            <h1>Verifikasi <span>pembayaran.</span></h1>
            <p>Periksa bukti transfer, cocokkan detail transaksi, lalu setujui pembayaran yang valid.</p>
        </div>
        <div class="ap-hero-stamp" aria-hidden="true">
            <span class="ap-stamp-icon">↗</span>
            <span>PAYMENT<br>CONTROL</span>
            <strong>PL / 04</strong>
        </div>
    </section>

    @if (session('success'))
        <div class="ap-alert ap-alert-success" role="status">
            <span class="ap-alert-icon">✓</span>
            <div><strong>Berhasil</strong><p>{{ session('success') }}</p></div>
            <button type="button" class="ap-alert-close" aria-label="Tutup notifikasi" onclick="this.parentElement.remove()">×</button>
        </div>
    @endif

    @if (session('error'))
        <div class="ap-alert ap-alert-error" role="alert">
            <span class="ap-alert-icon">!</span>
            <div><strong>Belum berhasil</strong><p>{{ session('error') }}</p></div>
            <button type="button" class="ap-alert-close" aria-label="Tutup notifikasi" onclick="this.parentElement.remove()">×</button>
        </div>
    @endif

    @if ($errors->any())
        <div class="ap-alert ap-alert-error" role="alert">
            <span class="ap-alert-icon">!</span>
            <div><strong>Periksa kembali input</strong><p>{{ $errors->first() }}</p></div>
        </div>
    @endif

    <section class="ap-stats-grid" aria-label="Ringkasan pembayaran">
        <article class="ap-stat-card ap-stat-total">
            <div class="ap-stat-top"><span>Total catatan</span><span class="ap-stat-icon">▤</span></div>
            <strong>{{ number_format((int) ($stats['total'] ?? 0)) }}</strong>
            <small>Semua transaksi pembayaran</small>
            <span class="ap-stat-bottom-line"></span>
        </article>
        <article class="ap-stat-card ap-stat-pending">
            <div class="ap-stat-top"><span>Perlu diperiksa</span><span class="ap-stat-icon">◷</span></div>
            <strong>{{ number_format((int) ($stats['pending'] ?? 0)) }}</strong>
            <small>Menunggu tindakan admin</small>
            <span class="ap-stat-bottom-line"></span>
        </article>
        <article class="ap-stat-card ap-stat-verified">
            <div class="ap-stat-top"><span>Terverifikasi</span><span class="ap-stat-icon">✓</span></div>
            <strong>{{ number_format((int) ($stats['verified'] ?? 0)) }}</strong>
            <small>Pembayaran telah disetujui</small>
            <span class="ap-stat-bottom-line"></span>
        </article>
        <article class="ap-stat-card ap-stat-rejected">
            <div class="ap-stat-top"><span>Ditolak</span><span class="ap-stat-icon">×</span></div>
            <strong>{{ number_format((int) ($stats['rejected'] ?? 0)) }}</strong>
            <small>Bukti tidak valid atau ditolak</small>
            <span class="ap-stat-bottom-line"></span>
        </article>
    </section>

    <section class="ap-work-panel">
        <div class="ap-panel-heading">
            <div>
                <span class="ap-eyebrow">TRANSACTION QUEUE</span>
                <h2>Antrean pembayaran</h2>
                <p>Gunakan filter untuk menemukan transaksi yang perlu ditinjau.</p>
            </div>
            <div class="ap-results-pill"><span></span>{{ $payments->total() }} transaksi ditemukan</div>
        </div>

        <form action="{{ route('admin.payments.index') }}" method="GET" class="ap-filter-form">
            <label class="ap-search-field">
                <span aria-hidden="true">⌕</span>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari kode pembayaran, pesanan, pembeli..." aria-label="Cari pembayaran">
            </label>

            <label class="ap-filter-select">
                <span>Status</span>
                <select name="status">
                    <option value="pending" @selected($activeFilter === 'pending')>Perlu diperiksa</option>
                    <option value="verified" @selected($activeFilter === 'verified')>Terverifikasi</option>
                    <option value="rejected" @selected($activeFilter === 'rejected')>Ditolak</option>
                    <option value="all" @selected($activeFilter === 'all')>Semua status</option>
                </select>
            </label>

            <label class="ap-filter-date">
                <span>Dari tanggal</span>
                <input type="date" name="from" value="{{ request('from') }}">
            </label>

            <label class="ap-filter-date">
                <span>Sampai tanggal</span>
                <input type="date" name="to" value="{{ request('to') }}">
            </label>

            <button class="ap-filter-submit" type="submit">Terapkan filter <span>↗</span></button>
            <a class="ap-filter-reset" href="{{ route('admin.payments.index') }}">Reset</a>
        </form>

        <div class="ap-table-wrap">
            <table class="ap-table">
                <thead>
                    <tr>
                        <th>PEMBAYARAN</th>
                        <th>PEMBELI / TOKO</th>
                        <th>METODE</th>
                        <th>NOMINAL</th>
                        <th>BUKTI</th>
                        <th>STATUS</th>
                        <th class="ap-action-column">TINDAKAN</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        @php
                            $rawPaymentStatus = strtolower((string) $payment->status);
                            $statusLabel = $statusLabels[$rawPaymentStatus] ?? ($rawPaymentStatus !== '' ? ucfirst(str_replace('_', ' ', $rawPaymentStatus)) : 'Belum diatur');
                            $tone = $statusTone($rawPaymentStatus);
                            $isPending = in_array($rawPaymentStatus, ['pending', 'menunggu_verifikasi'], true);
                            $hasProof = filled($payment->payment_proof);
                        @endphp
                        <tr>
                            <td>
                                <div class="ap-payment-primary">{{ $payment->payment_code ?: 'PAY-' . $payment->id }}</div>
                                <div class="ap-payment-secondary">Pesanan {{ $payment->order_number ?: 'ORD-' . $payment->order_id }}</div>
                                <div class="ap-payment-date">
                                    {{ $payment->created_at ? \Illuminate\Support\Carbon::parse($payment->created_at)->translatedFormat('d M Y · H:i') : 'Tanggal tidak tersedia' }}
                                </div>
                            </td>
                            <td>
                                <div class="ap-buyer-name">{{ $payment->recipient_name ?: ($payment->buyer_name ?: 'Pembeli') }}</div>
                                <div class="ap-buyer-email">{{ $payment->buyer_email ?: 'Email tidak tersedia' }}</div>
                                <div class="ap-store-name"><span>↳</span> {{ $payment->store_name ?: 'Toko tidak tercatat' }}</div>
                            </td>
                            <td>
                                <span class="ap-method-chip">{{ $payment->metode ? ucfirst(str_replace('_', ' ', $payment->metode)) : 'Belum diatur' }}</span>
                                @if ($payment->provider_reference)
                                    <small class="ap-reference">Ref: {{ $payment->provider_reference }}</small>
                                @endif
                            </td>
                            <td>
                                <strong class="ap-amount">{{ $money($payment->jumlah) }}</strong>
                                @if (!empty($payment->order_total) && (float) $payment->order_total !== (float) $payment->jumlah)
                                    <small class="ap-amount-note">Total pesanan {{ $money($payment->order_total) }}</small>
                                @endif
                            </td>
                            <td>
                                @if ($hasProof)
                                    <a href="{{ route('admin.payments.proof', $payment->id) }}" class="ap-proof-button" target="_blank" rel="noopener noreferrer">
                                        <span>▧</span> Lihat bukti <span class="ap-external">↗</span>
                                    </a>
                                @else
                                    <span class="ap-proof-missing"><span>—</span> Belum ada bukti</span>
                                @endif
                            </td>
                            <td>
                                <span class="ap-status ap-status-{{ $tone }}"><i></i>{{ $statusLabel }}</span>
                                @if ($payment->paid_at)
                                    <small class="ap-paid-date">Dibayar {{ \Illuminate\Support\Carbon::parse($payment->paid_at)->format('d/m/Y') }}</small>
                                @endif
                            </td>
                            <td class="ap-actions-cell">
                                @if ($isPending)
                                    <form method="POST" action="{{ route('admin.payments.approve', $payment->id) }}" class="ap-approve-form" onsubmit="return confirm('Setujui pembayaran {{ $payment->payment_code ?: ('PAY-' . $payment->id) }}?');">
                                        @csrf
                                        @method('PATCH')
                                        <button class="ap-approve-button" type="submit" @disabled(!$hasProof) title="{{ $hasProof ? 'Setujui pembayaran' : 'Bukti pembayaran belum tersedia' }}">
                                            <span>✓</span> Setujui
                                        </button>
                                    </form>
                                    <details class="ap-reject-details">
                                        <summary class="ap-reject-button"><span>×</span> Tolak</summary>
                                        <form method="POST" action="{{ route('admin.payments.reject', $payment->id) }}" class="ap-reject-form" onsubmit="return confirm('Tolak pembayaran ini?');">
                                            @csrf
                                            @method('PATCH')
                                            <label for="reason-{{ $payment->id }}">Alasan penolakan <small>(opsional)</small></label>
                                            <textarea id="reason-{{ $payment->id }}" name="catatan" rows="2" maxlength="1000" placeholder="Contoh: nominal tidak sesuai..."></textarea>
                                            <button type="submit">Konfirmasi penolakan</button>
                                        </form>
                                    </details>
                                @else
                                    <span class="ap-action-complete">{{ in_array($rawPaymentStatus, ['paid', 'dibayar', 'lunas', 'verified', 'berhasil', 'sukses'], true) ? '✓ Sudah diperiksa' : '— Tidak ada tindakan' }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="ap-empty-state">
                                    <span class="ap-empty-icon">▤</span>
                                    <h3>Belum ada pembayaran di sini</h3>
                                    <p>Coba ubah status atau kata kunci pencarian untuk melihat transaksi lainnya.</p>
                                    @if (request()->query())
                                        <a href="{{ route('admin.payments.index', ['status' => 'all']) }}">Tampilkan semua transaksi</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($payments->hasPages())
            <div class="ap-pagination">
                <span>Menampilkan <strong>{{ $payments->firstItem() ?? 0 }}–{{ $payments->lastItem() ?? 0 }}</strong> dari <strong>{{ $payments->total() }}</strong> catatan</span>
                <div>{{ $payments->onEachSide(1)->links() }}</div>
            </div>
        @endif
    </section>

    <footer class="ap-footer">
        <span>© {{ date('Y') }} PASARLOKAL / ADMIN STUDIO</span>
        <span>Verifikasi pembayaran dengan teliti. <b>✳</b></span>
    </footer>
</div>
@endsection
