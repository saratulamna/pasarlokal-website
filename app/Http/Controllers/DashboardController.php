<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard administrator PasarLokal.
     */
    public function admin(): View
    {
        /** @var \App\Models\User $adminUser */
        $adminUser = Auth::user();
        abort_if(!$adminUser, 401);

        $pendingStatuses = [
            'menunggu_pembayaran',
            'menunggu pembayaran',
            'menunggu_konfirmasi',
            'menunggu konfirmasi',
            'menunggu_verifikasi',
            'menunggu verifikasi',
            'belum_dibayar',
            'unpaid',
            'pending',
            'baru',
        ];

        $paidStatuses = [
            'paid',
            'dibayar',
            'lunas',
            'verified',
            'terverifikasi',
            'berhasil',
            'sukses',
        ];

        // Statistik transaksi.
        $ordersCount = DB::table('orders')->count();

        $pendingOrdersCount = DB::table('orders')
            ->where(function ($query) use ($pendingStatuses) {
                $query->whereIn(
                    DB::raw("LOWER(COALESCE(status, ''))"),
                    $pendingStatuses
                )->orWhereIn(
                    DB::raw("LOWER(COALESCE(payment_status, ''))"),
                    $pendingStatuses
                );
            })
            ->count();

        // Omzet hanya menghitung pembayaran yang berstatus berhasil.
        $paidOrders = static function () use ($paidStatuses) {
            return DB::table('orders')->whereIn(
                DB::raw("LOWER(COALESCE(payment_status, ''))"),
                $paidStatuses
            );
        };

        $totalRevenue = (float) $paidOrders()->sum('total');

        $monthlyRevenue = (float) $paidOrders()
            ->whereBetween(
                DB::raw('DATE(COALESCE(paid_at, created_at))'),
                [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()]
            )
            ->sum('total');

        // Statistik produk.
        $productsCount = DB::table('products')->count();

        $activeProductsCount = DB::table('products')
            ->whereIn(
                DB::raw("LOWER(COALESCE(status, ''))"),
                ['aktif', 'active', 'published', 'tersedia', 'available']
            )
            ->count();

        $lowStockCount = DB::table('products')
            ->where('stok', '<=', 5)
            ->count();

        // Statistik UMKM.
        $umkmCount = DB::table('umkms')->count();

        $activeUmkmCount = DB::table('umkms')
            ->whereIn(
                DB::raw("LOWER(COALESCE(status, ''))"),
                ['aktif', 'active', 'verified', 'terverifikasi']
            )
            ->count();

        $pendingUmkmCount = DB::table('umkms')
            ->whereIn(
                DB::raw("LOWER(COALESCE(status, ''))"),
                [
                    'menunggu_verifikasi',
                    'menunggu verifikasi',
                    'pending',
                    'menunggu',
                    'diajukan',
                ]
            )
            ->count();

        // Statistik pengguna.
        $usersCount = DB::table('users')->count();

        $buyersCount = DB::table('users')
            ->whereIn(
                DB::raw("LOWER(COALESCE(role, ''))"),
                ['pembeli', 'buyer', 'customer']
            )
            ->count();

        // Grafik omzet selama 7 hari terakhir.
        $salesChart = [];
        $startDate = now()->startOfDay()->subDays(6);

        for ($i = 0; $i < 7; $i++) {
            $date = $startDate->copy()->addDays($i);

            $dailyRevenue = (float) $paidOrders()
                ->whereDate(DB::raw('COALESCE(paid_at, created_at)'), $date->toDateString())
                ->sum('total');

            $salesChart[] = [
                'label' => $date->translatedFormat('D'),
                'date' => $date->format('d M Y'),
                'total' => $dailyRevenue,
            ];
        }

        // Pesanan terbaru, beserta nama pembeli.
        $recentOrders = DB::table('orders as o')
            ->leftJoin('users as u', 'u.id', '=', 'o.user_id')
            ->select([
                'o.id',
                'o.order_number',
                'o.status',
                'o.payment_status',
                'o.created_at',
                'o.total as admin_total',
                'u.name as buyer_name',
            ])
            ->orderByDesc('o.created_at')
            ->orderByDesc('o.id')
            ->limit(5)
            ->get();

        // Produk terbaru dan kategori/toko untuk halaman dashboard.
        $recentProducts = DB::table('products as p')
            ->leftJoin('umkms as um', 'um.id', '=', 'p.umkm_id')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->select([
                'p.id',
                'p.nama',
                'p.slug',
                'p.harga',
                'p.stok',
                'p.gambar',
                'p.status',
                'p.created_at',
                'um.nama_toko as dashboard_store_name',
                'c.nama as dashboard_category_name',
            ])
            ->orderByDesc('p.created_at')
            ->orderByDesc('p.id')
            ->limit(5)
            ->get()
            ->map(function ($product) {
                $product->umkm = (object) [
                    'nama_toko' => $product->dashboard_store_name,
                ];

                $product->category = (object) [
                    'nama' => $product->dashboard_category_name,
                ];

                return $product;
            });

        // Pengguna terbaru.
        $recentUsers = DB::table('users')
            ->select(['id', 'name', 'email', 'role', 'created_at'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        // Pertahankan array stats untuk bagian view/fitur lama yang mungkin menggunakannya.
        $stats = [
            'users' => $usersCount,
            'buyers' => $buyersCount,
            'orders' => $ordersCount,
            'pending_orders' => $pendingOrdersCount,
            'products' => $productsCount,
            'active_products' => $activeProductsCount,
            'umkms' => $umkmCount,
            'active_umkms' => $activeUmkmCount,
            'pending_umkms' => $pendingUmkmCount,
            'low_stock' => $lowStockCount,
            'revenue' => $totalRevenue,
        ];

        return view('admin.dashboard', compact(
            'adminUser',
            'stats',
            'ordersCount',
            'pendingOrdersCount',
            'productsCount',
            'activeProductsCount',
            'lowStockCount',
            'umkmCount',
            'activeUmkmCount',
            'pendingUmkmCount',
            'usersCount',
            'buyersCount',
            'totalRevenue',
            'monthlyRevenue',
            'salesChart',
            'recentOrders',
            'recentProducts',
            'recentUsers'
        ));
    }

    /**
     * Dashboard pembeli. Menyediakan $stats dan $recentOrders seperti controller lama.
     */
    public function pembeli(): View
    {
        /** @var \App\Models\User $buyer */
        $buyer = Auth::user();
        abort_if(!$buyer, 401);
        $buyerId = $buyer->id;

        $buyerOrders = static function () use ($buyerId) {
            return DB::table('orders')->where('user_id', $buyerId);
        };

        $ordersCount = (clone $buyerOrders())->count();

        $pendingStatuses = [
            'menunggu_pembayaran', 'menunggu pembayaran', 'menunggu_konfirmasi',
            'menunggu konfirmasi', 'menunggu_verifikasi', 'menunggu verifikasi',
            'belum_dibayar', 'unpaid', 'pending', 'baru',
        ];

        $pendingOrdersCount = (clone $buyerOrders())
            ->whereIn(DB::raw("LOWER(COALESCE(status, ''))"), $pendingStatuses)
            ->count();

        $paidStatuses = ['paid', 'dibayar', 'lunas', 'verified', 'terverifikasi', 'berhasil', 'sukses'];

        $completedOrdersCount = (clone $buyerOrders())
            ->whereIn(DB::raw("LOWER(COALESCE(payment_status, ''))"), $paidStatuses)
            ->count();

        $totalSpent = (float) (clone $buyerOrders())
            ->whereIn(DB::raw("LOWER(COALESCE(payment_status, ''))"), $paidStatuses)
            ->sum('total');

        $recentOrders = Order::query()
            ->where('user_id', $buyerId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $stats = [
            'orders' => $ordersCount,
            'total_orders' => $ordersCount,
            'pending' => $pendingOrdersCount,
            'pending_orders' => $pendingOrdersCount,
            'completed' => $completedOrdersCount,
            'completed_orders' => $completedOrdersCount,
            'spent' => $totalSpent,
            'total_spent' => $totalSpent,
        ];

        return view('pembeli.dashboard', compact('stats', 'recentOrders'));
    }

    /**
     * Dashboard penjual UMKM.
     * Mengirim variabel lama ($store, $stats, $recentOrders) plus variabel untuk
     * dashboard penjual yang menggunakan data produk, pesanan, stok, dan omzet.
     */
    public function umkm(): View
    {
        /** @var \App\Models\User $seller */
        $seller = Auth::user();
        abort_if(!$seller, 401);

        $store = Umkm::query()
            ->where('user_id', $seller->id)
            ->first();

        $umkm = $store;
        $storeId = $store?->id ?? 0;

        $sellerProductsQuery = static function () use ($storeId) {
            return DB::table('products')->where('umkm_id', $storeId);
        };

        $sellerOrdersQuery = static function () use ($storeId) {
            return DB::table('orders')->where('umkm_id', $storeId);
        };

        $totalProducts = (clone $sellerProductsQuery())->count();
        $activeProductsCount = (clone $sellerProductsQuery())
            ->whereIn(DB::raw("LOWER(COALESCE(status, ''))"), ['aktif', 'active', 'published', 'tersedia', 'available'])
            ->count();

        $totalOrders = (clone $sellerOrdersQuery())->count();

        $pendingStatuses = [
            'menunggu_pembayaran', 'menunggu pembayaran', 'menunggu_konfirmasi',
            'menunggu konfirmasi', 'menunggu_verifikasi', 'menunggu verifikasi',
            'belum_dibayar', 'unpaid', 'pending', 'baru',
        ];

        $pendingOrderCount = (clone $sellerOrdersQuery())
            ->whereIn(DB::raw("LOWER(COALESCE(status, ''))"), $pendingStatuses)
            ->count();

        $paidStatuses = ['paid', 'dibayar', 'lunas', 'verified', 'terverifikasi', 'berhasil', 'sukses'];

        $paidSellerOrders = static function () use ($storeId, $paidStatuses) {
            return DB::table('orders')
                ->where('umkm_id', $storeId)
                ->whereIn(DB::raw("LOWER(COALESCE(payment_status, ''))"), $paidStatuses);
        };

        $totalRevenue = (float) $paidSellerOrders()->sum('total');

        $products = Product::query()
            ->where('umkm_id', $storeId)
            ->with('category')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        $topProducts = Product::query()
            ->where('umkm_id', $storeId)
            ->with('category')
            ->orderByDesc('terjual')
            ->orderByDesc('id')
            ->limit(4)
            ->get();

        $lowStockProducts = Product::query()
            ->where('umkm_id', $storeId)
            ->where('stok', '<=', 5)
            ->orderBy('stok')
            ->get();

        $lowStockCount = $lowStockProducts->count();

        $recentOrders = DB::table('orders as o')
            ->leftJoin('users as u', 'u.id', '=', 'o.user_id')
            ->where('o.umkm_id', $storeId)
            ->select([
                'o.*',
                'u.name as buyer_name',
                'u.email as buyer_email',
            ])
            ->orderByDesc('o.created_at')
            ->orderByDesc('o.id')
            ->limit(10)
            ->get();

        $orders = $recentOrders;

        $weeklySales = [];
        $startDate = now()->startOfDay()->subDays(6);

        for ($i = 0; $i < 7; $i++) {
            $date = $startDate->copy()->addDays($i);

            $dailyRevenue = (float) $paidSellerOrders()
                ->whereDate(DB::raw('COALESCE(paid_at, created_at)'), $date->toDateString())
                ->sum('total');

            $weeklySales[] = [
                'label' => $date->translatedFormat('D'),
                'date' => $date->format('d M Y'),
                'total' => $dailyRevenue,
                'value' => $dailyRevenue,
            ];
        }

        $stats = [
            'products' => $totalProducts,
            'active_products' => $activeProductsCount,
            'orders' => $totalOrders,
            'pending_orders' => $pendingOrderCount,
            'revenue' => $totalRevenue,
            'low_stock' => $lowStockCount,
        ];

        // Alias variabel untuk kompatibilitas Blade versi lama dan baru.
        $productsCount = $totalProducts;
        $ordersCount = $totalOrders;
        $lowStockCount = $lowStockProducts->count();

        return view('umkm.dashboard', compact(
            'store',
            'umkm',
            'stats',
            'recentOrders',
            'products',
            'orders',
            'totalRevenue',
            'totalProducts',
            'totalOrders',
            'lowStockProducts',
            'lowStockCount',
            'weeklySales',
            'topProducts',
            'productsCount',
            'ordersCount',
            'activeProductsCount',
            'pendingOrderCount'
        ));
    }
}
