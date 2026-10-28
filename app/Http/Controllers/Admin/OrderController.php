<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    /**
     * Daftar pesanan dan ringkasan transaksi.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', 'string', 'max:60'],
            'payment_status' => ['nullable', 'string', 'max:60'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
            'per_page' => ['nullable', 'integer', 'in:10,25,50'],
        ]);

        $search = trim((string) ($filters['q'] ?? ''));
        $status = (string) ($filters['status'] ?? '');
        $paymentStatus = (string) ($filters['payment_status'] ?? '');
        $from = $filters['from'] ?? '';
        $to = $filters['to'] ?? '';
        $perPage = (int) ($filters['per_page'] ?? 10);

        if ($from !== '' && $to !== '' && $to < $from) {
            return redirect()
                ->route('admin.orders.index')
                ->with('error', 'Tanggal akhir tidak boleh lebih awal dari tanggal awal.');
        }

        /*
         * Opsi filter diambil dari nilai yang benar-benar tersimpan.
         */
        $orderStatuses = DB::table('orders')
            ->whereNotNull('status')
            ->where('status', '<>', '')
            ->distinct()
            ->orderBy('status')
            ->pluck('status');

        $paymentStatuses = DB::table('orders')
            ->whereNotNull('payment_status')
            ->where('payment_status', '<>', '')
            ->distinct()
            ->orderBy('payment_status')
            ->pluck('payment_status');

        if ($status !== '' && ! $orderStatuses->contains($status)) {
            $status = '';
        }

        if (
            $paymentStatus !== '' &&
            ! $paymentStatuses->contains($paymentStatus)
        ) {
            $paymentStatus = '';
        }

        /*
         * Agregasi item agar satu pesanan tidak terduplikasi
         * ketika memiliki beberapa baris produk.
         */
        $itemSummary = DB::table('order_items')
            ->select('order_id')
            ->selectRaw('COUNT(*) AS item_lines')
            ->selectRaw('COALESCE(SUM(quantity), 0) AS item_quantity')
            ->groupBy('order_id');

        /*
         * Ambil pembayaran terakhir untuk setiap pesanan.
         * Riwayat lengkap tetap ditampilkan pada halaman detail.
         */
        $latestPayment = DB::table('payments')
            ->select('order_id')
            ->selectRaw('MAX(id) AS latest_payment_id')
            ->groupBy('order_id');

        /*
         * Statistik global, tidak terpengaruh filter tabel.
         */
        $stats = [
            'total' => DB::table('orders')->count(),

            'awaiting_payment' => DB::table('orders')
                ->whereIn(DB::raw('LOWER(payment_status)'), [
                    'menunggu_pembayaran',
                    'belum_dibayar',
                    'unpaid',
                    'pending',
                ])
                ->count(),

            'processing' => DB::table('orders')
                ->whereIn(DB::raw('LOWER(status)'), [
                    'baru',
                    'pending',
                    'diproses',
                    'processing',
                ])
                ->count(),

            'shipped' => DB::table('orders')
                ->whereIn(DB::raw('LOWER(status)'), [
                    'dikirim',
                    'shipped',
                ])
                ->count(),

            'completed' => DB::table('orders')
                ->whereIn(DB::raw('LOWER(status)'), [
                    'selesai',
                    'completed',
                ])
                ->count(),

            'cancelled' => DB::table('orders')
                ->whereIn(DB::raw('LOWER(status)'), [
                    'dibatalkan',
                    'cancelled',
                    'gagal',
                    'failed',
                ])
                ->count(),
        ];

        /*
         * Dasar query daftar pesanan.
         */
        $query = DB::table('orders as o')
            ->leftJoin('users as u', 'u.id', '=', 'o.user_id')
            ->leftJoin('umkms as um', 'um.id', '=', 'o.umkm_id')
            ->leftJoinSub(
                $itemSummary,
                'item_summary',
                fn ($join) => $join->on(
                    'item_summary.order_id',
                    '=',
                    'o.id'
                )
            )
            ->leftJoinSub(
                $latestPayment,
                'latest_payment',
                fn ($join) => $join->on(
                    'latest_payment.order_id',
                    '=',
                    'o.id'
                )
            )
            ->leftJoin(
                'payments as p',
                'p.id',
                '=',
                'latest_payment.latest_payment_id'
            )
            ->select([
                'o.id',
                'o.order_number',
                'o.user_id',
                'o.umkm_id',
                'o.subtotal',
                'o.shipping_cost',
                'o.discount',
                'o.total',
                'o.status',
                'o.payment_status',
                'o.payment_method',
                'o.recipient_name',
                'o.created_at',
                'o.paid_at',
                'o.shipped_at',
                'o.completed_at',
                'o.cancelled_at',

                'u.name as buyer_account_name',
                'u.email as buyer_email',
                'um.nama_toko as store_name',

                'item_summary.item_lines',
                'item_summary.item_quantity',

                'p.payment_code as latest_payment_code',
                'p.status as latest_payment_status',
            ]);

        /*
         * Pencarian nomor pesanan, pembeli, toko, dan kode pembayaran.
         */
        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $keyword = '%' . $search . '%';

                $builder
                    ->where('o.order_number', 'like', $keyword)
                    ->orWhere('o.recipient_name', 'like', $keyword)
                    ->orWhere('o.recipient_phone', 'like', $keyword)
                    ->orWhere('u.name', 'like', $keyword)
                    ->orWhere('u.email', 'like', $keyword)
                    ->orWhere('um.nama_toko', 'like', $keyword)
                    ->orWhere('p.payment_code', 'like', $keyword);
            });
        }

        if ($status !== '') {
            $query->where('o.status', $status);
        }

        if ($paymentStatus !== '') {
            $query->where('o.payment_status', $paymentStatus);
        }

        if ($from !== '') {
            $query->whereDate('o.created_at', '>=', $from);
        }

        if ($to !== '') {
            $query->whereDate('o.created_at', '<=', $to);
        }

        $orders = $query
            ->orderByDesc('o.created_at')
            ->orderByDesc('o.id')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.orders.index', compact(
            'orders',
            'stats',
            'orderStatuses',
            'paymentStatuses',
            'search',
            'status',
            'paymentStatus',
            'from',
            'to',
            'perPage',
        ));
    }

    /**
     * Rincian pesanan, produk, alamat, dan riwayat pembayaran.
     */
    public function show(int $order): View
    {
        $orderData = DB::table('orders as o')
            ->leftJoin('users as u', 'u.id', '=', 'o.user_id')
            ->leftJoin('umkms as um', 'um.id', '=', 'o.umkm_id')
            ->select([
                'o.*',
                'u.name as buyer_account_name',
                'u.email as buyer_email',
                'u.phone as buyer_phone',
                'um.nama_toko as store_name',
            ])
            ->where('o.id', $order)
            ->first();

        abort_if($orderData === null, 404);

        /*
         * Gunakan snapshot nama, harga, dan gambar dari order_items.
         */
        $items = DB::table('order_items as oi')
            ->leftJoin('products as pr', 'pr.id', '=', 'oi.product_id')
            ->select([
                'oi.*',
                'pr.slug as current_product_slug',
            ])
            ->where('oi.order_id', $order)
            ->orderBy('oi.id')
            ->get();

        $payments = DB::table('payments')
            ->where('order_id', $order)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return view('admin.orders.show', [
            'order' => $orderData,
            'items' => $items,
            'payments' => $payments,
        ]);
    }
}