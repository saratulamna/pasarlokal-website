<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PaymentVerificationController extends Controller
{
    /**
     * Daftar bukti transfer yang perlu diperiksa admin.
     */
    public function index(): View
    {
        $payments = Payment::where('metode', 'transfer_bank')
            ->whereNotNull('payment_proof')
            ->where('status', 'menunggu_verifikasi')
            ->orderByDesc('created_at')
            ->paginate(15);

        $records = $payments->getCollection()->map(function ($payment) {
            $order = Order::find($payment->order_id);

            return (object) [
                'payment' => $payment,
                'order' => $order,
                'buyer' => $order
                    ? User::find($order->user_id)
                    : null,
                'store' => $order
                    ? Umkm::find($order->umkm_id)
                    : null,
            ];
        });

        $payments->setCollection($records);

        return view('admin.payments.index', compact('payments'));
    }

    /**
     * Menampilkan bukti pembayaran secara privat kepada admin.
     */
    public function proof(Payment $payment)
    {
        abort_unless(
            $payment->metode === 'transfer_bank'
            && filled($payment->payment_proof),
            404
        );

        $disk = Storage::disk('local');

        abort_unless(
            $disk->exists($payment->payment_proof),
            404
        );

        return response()->file(
            $disk->path($payment->payment_proof)
        );
    }

    /**
     * Mengesahkan pembayaran.
     */
    public function approve(
        Request $request,
        Payment $payment
    ): RedirectResponse {
        $adminId = $request->user()->getAuthIdentifier();

        $approved = DB::transaction(function () use (
            $payment,
            $adminId
        ) {
            $lockedPayment = Payment::whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $lockedPayment->status !== 'menunggu_verifikasi'
                || ! filled($lockedPayment->payment_proof)
                || ! Storage::disk('local')->exists(
                    $lockedPayment->payment_proof
                )
            ) {
                return false;
            }

            $order = Order::whereKey($lockedPayment->order_id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedPayment->status = 'berhasil';
            $lockedPayment->verified_by = $adminId;
            $lockedPayment->paid_at = now();
            $lockedPayment->catatan =
                'Pembayaran diverifikasi oleh admin.';
            $lockedPayment->save();

            $order->payment_status = 'dibayar';
            $order->paid_at = now();

            if ($order->status === 'menunggu_pembayaran') {
                $order->status = 'menunggu_konfirmasi';
            }

            $order->save();

            return true;
        });

        if (! $approved) {
            return back()->with(
                'error',
                'Pembayaran sudah diproses atau bukti transfer tidak tersedia.'
            );
        }

        return redirect()
            ->route('admin.payments.index')
            ->with(
                'success',
                'Pembayaran berhasil diverifikasi.'
            );
    }

    /**
     * Menolak bukti pembayaran dengan alasan wajib.
     */
    public function reject(
        Request $request,
        Payment $payment
    ): RedirectResponse {
        $validated = $request->validate([
            'catatan' => [
                'required',
                'string',
                'min:5',
                'max:1000',
            ],
        ], [
            'catatan.required' => 'Tuliskan alasan penolakan.',
            'catatan.min' => 'Alasan penolakan minimal 5 karakter.',
        ]);

        $adminId = $request->user()->getAuthIdentifier();

        $rejected = DB::transaction(function () use (
            $payment,
            $validated,
            $adminId
        ) {
            $lockedPayment = Payment::whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedPayment->status !== 'menunggu_verifikasi') {
                return false;
            }

            $order = Order::whereKey($lockedPayment->order_id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedPayment->status = 'ditolak';
            $lockedPayment->verified_by = $adminId;
            $lockedPayment->catatan = $validated['catatan'];
            $lockedPayment->paid_at = null;
            $lockedPayment->save();

            $order->payment_status = 'belum_bayar';
            $order->status = 'menunggu_pembayaran';
            $order->paid_at = null;
            $order->save();

            return true;
        });

        if (! $rejected) {
            return back()->with(
                'error',
                'Bukti pembayaran ini sudah diproses sebelumnya.'
            );
        }

        return redirect()
            ->route('admin.payments.index')
            ->with(
                'success',
                'Bukti ditolak. Pembeli dapat mengirim bukti pembayaran baru.'
            );
    }
}
