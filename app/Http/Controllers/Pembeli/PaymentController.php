<?php

namespace App\Http\Controllers\Pembeli;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class PaymentController extends Controller
{
    /**
     * Menampilkan detail pembayaran pesanan milik pembeli.
     */
    public function show(Request $request, Order $order): View
    {
        $buyerId = $request->user()->getAuthIdentifier();

        abort_unless(
            (int) $order->user_id === (int) $buyerId,
            404
        );

        abort_unless(
            $order->payment_method === 'transfer_bank',
            404
        );

        $payment = Payment::where('order_id', $order->id)
            ->latest('id')
            ->firstOrFail();

        $items = OrderItem::where('order_id', $order->id)
            ->orderBy('id')
            ->get();

        $bankName = config('pasarlokal.payment.bank_name');
        $bankAccountName = config('pasarlokal.payment.bank_account_name');
        $bankAccountNumber = config('pasarlokal.payment.bank_account_number');

        $bankConfigured = filled($bankName)
            && filled($bankAccountName)
            && filled($bankAccountNumber);

        return view('pembeli.payments.show', compact(
            'order',
            'payment',
            'items',
            'bankName',
            'bankAccountName',
            'bankAccountNumber',
            'bankConfigured'
        ));
    }

    /**
     * Mengunggah bukti pembayaran secara privat.
     */
    public function upload(
        Request $request,
        Order $order
    ): RedirectResponse {
        $buyerId = $request->user()->getAuthIdentifier();

        abort_unless(
            (int) $order->user_id === (int) $buyerId,
            404
        );

        abort_unless(
            $order->payment_method === 'transfer_bank',
            404
        );

        // Pastikan seluruh informasi rekening sudah tersedia.
        $bankConfigured = filled(
            config('pasarlokal.payment.bank_name')
        ) && filled(
            config('pasarlokal.payment.bank_account_name')
        ) && filled(
            config('pasarlokal.payment.bank_account_number')
        );

        if (! $bankConfigured) {
            return back()->with(
                'error',
                'Informasi rekening tujuan belum lengkap. Hubungi admin.'
            );
        }

        $payment = Payment::where('order_id', $order->id)
            ->latest('id')
            ->firstOrFail();

        if ($payment->status === 'berhasil') {
            return back()->with(
                'error',
                'Pembayaran untuk pesanan ini sudah diverifikasi.'
            );
        }

        if ($payment->status === 'menunggu_verifikasi') {
            return back()->with(
                'error',
                'Bukti pembayaran sedang diperiksa admin.'
            );
        }

        $validated = $request->validate([
            'payment_proof' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:4096',
            ],
        ], [
            'payment_proof.required' => 'Pilih file bukti pembayaran terlebih dahulu.',

            'payment_proof.mimes' => 'Bukti harus berupa JPG, JPEG, PNG, WEBP, atau PDF.',

            'payment_proof.max' => 'Ukuran bukti pembayaran maksimal 4 MB.',
        ]);

        $newPath = $validated['payment_proof']
            ->store('payment-proofs', 'local');

        if (! $newPath) {
            return back()->with(
                'error',
                'Bukti pembayaran gagal disimpan. Silakan coba kembali.'
            );
        }

        $oldPath = $payment->payment_proof;

        try {
            DB::transaction(function () use (
                $payment,
                $order,
                $newPath
            ) {
                // Kunci data agar pemeriksaan status tetap konsisten.
                $lockedPayment = Payment::whereKey($payment->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $lockedOrder = Order::whereKey($order->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $lockedOrder->payment_method !== 'transfer_bank'
                    || $lockedPayment->status === 'berhasil'
                    || $lockedPayment->status === 'menunggu_verifikasi'
                ) {
                    throw ValidationException::withMessages([
                        'payment_proof' => 'Status pembayaran sudah berubah. Muat ulang halaman untuk melihat status terbaru.',
                    ]);
                }

                // Hanya status yang mengizinkan pembayaran ulang.
                $allowedStatuses = [
                    'menunggu_pembayaran',
                    'belum_bayar',
                    'ditolak',
                    'gagal',
                ];

                if (! in_array(
                    $lockedPayment->status,
                    $allowedStatuses,
                    true
                )) {
                    throw ValidationException::withMessages([
                        'payment_proof' => 'Status pembayaran saat ini tidak mengizinkan pengiriman bukti.',
                    ]);
                }

                $lockedPayment->payment_proof = $newPath;
                $lockedPayment->status = 'menunggu_verifikasi';
                $lockedPayment->verified_by = null;
                $lockedPayment->paid_at = null;
                $lockedPayment->catatan = null;
                $lockedPayment->save();

                $lockedOrder->payment_status = 'menunggu_verifikasi';
                $lockedOrder->save();
            });
        } catch (Throwable $exception) {
            // Bersihkan file baru jika proses database gagal.
            Storage::disk('local')->delete($newPath);

            throw $exception;
        }

        // Hapus bukti lama setelah penggantinya berhasil disimpan.
        if ($oldPath && $oldPath !== $newPath) {
            Storage::disk('local')->delete($oldPath);
        }

        return redirect()
            ->route('pembeli.payments.show', $order->id)
            ->with(
                'success',
                'Bukti pembayaran berhasil dikirim. Silakan menunggu verifikasi admin.'
            );
    }
}
