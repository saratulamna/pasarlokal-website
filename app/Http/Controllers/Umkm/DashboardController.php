<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Umkm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Halaman dashboard penjual.
     */
    public function index(Request $request): View
    {
        return $this->umkm($request);
    }

    /**
     * Menyiapkan semua data yang digunakan dashboard UMKM.
     */
    public function umkm(Request $request): View
    {
        $sellerUser = $request->user();

        /*
        |--------------------------------------------------------------------------
        | 1. Ambil toko milik pengguna yang sedang login
        |--------------------------------------------------------------------------
        */

        $store = $this->resolveStore($sellerUser);

        // Alias untuk menyesuaikan variabel yang digunakan Blade.
        $umkm = $store;
        $vendorStore = $store;
        $storeProfile = $store;

        /*
        |--------------------------------------------------------------------------
        | 2. Data produk terbaru
        |--------------------------------------------------------------------------
        */

        $products = $store
            ? $store->products()
                ->with('category')
                ->latest()
                ->limit(6)
                ->get()
            : collect();

        /*
        |--------------------------------------------------------------------------
        | 3. Total seluruh produk milik toko
        |--------------------------------------------------------------------------
        */

        $totalProducts = $store
            ? $store->products()->count()
            : 0;

        $productsCount = $totalProducts;

        /*
        |--------------------------------------------------------------------------
        | 4. Produk pilihan untuk dashboard
        |--------------------------------------------------------------------------
        */

        $topProducts = $store
            ? $store->products()
                ->with('category')
                ->orderByDesc('terjual')
                ->latest()
                ->limit(4)
                ->get()
            : collect();

        $bestSellingProducts = $topProducts;

        /*
        |--------------------------------------------------------------------------
        | 5. Peringatan stok rendah
        |--------------------------------------------------------------------------
        |
        | Stok 5 unit atau kurang dianggap perlu diperiksa.
        | Jumlah peringatan dihitung dari semua produk.
        | Daftar tampilannya dibatasi maksimal 5 produk.
        |
        */

        if ($store) {
            $lowStockCount = $store->products()
                ->where('stok', '<=', 5)
                ->count();

            $lowStockProducts = $store->products()
                ->where('stok', '<=', 5)
                ->orderBy('stok', 'asc')
                ->limit(5)
                ->get();
        } else {
            $lowStockCount = 0;
            $lowStockProducts = collect();
        }

        /*
        |--------------------------------------------------------------------------
        | 6. Ambil pesanan yang berkaitan dengan produk toko
        |--------------------------------------------------------------------------
        */

        $ordersQuery = $this->sellerOrdersQuery($store);

        $totalOrders = $ordersQuery
            ? (clone $ordersQuery)->count()
            : 0;

        $ordersCount = $totalOrders;

        // Muat data pembeli jika relasinya tersedia.
        if ($ordersQuery && method_exists(new Order(), 'user')) {
            $ordersQuery->with('user');
        }

        $recentOrders = $ordersQuery
            ? (clone $ordersQuery)
                ->latest()
                ->limit(8)
                ->get()
            : collect();

        $orders = $recentOrders;

        /*
        |--------------------------------------------------------------------------
        | 7. Ringkasan untuk kartu dashboard
        |--------------------------------------------------------------------------
        */

        $stats = [
            'products' => $totalProducts,
            'orders' => $totalOrders,
            'low_stock' => $lowStockCount,
        ];

        /*
        |--------------------------------------------------------------------------
        | 8. Data grafik penjualan
        |--------------------------------------------------------------------------
        |
        | Tidak mengisi angka penjualan palsu.
        | Grafik akan menampilkan keadaan kosong sampai data penjualan
        | mingguan benar-benar disediakan.
        |
        */

        $weeklySales = collect();

        /*
        |--------------------------------------------------------------------------
        | 9. Kirim semua data ke halaman dashboard
        |--------------------------------------------------------------------------
        */

        return view('umkm.dashboard', compact(
            'sellerUser',

            'store',
            'storeProfile',
            'umkm',
            'vendorStore',

            'products',
            'totalProducts',
            'productsCount',

            'topProducts',
            'bestSellingProducts',

            'lowStockProducts',
            'lowStockCount',

            'recentOrders',
            'orders',
            'totalOrders',
            'ordersCount',

            'stats',
            'weeklySales'
        ));
    }

    /**
     * Menemukan toko berdasarkan pengguna yang login.
     */
    private function resolveStore($user): ?Umkm
    {
        if (!$user) {
            return null;
        }

        $umkmModel = new Umkm();
        $umkmTable = $umkmModel->getTable();

        // Prioritaskan hubungan user_id pada tabel UMKM.
        if (
            Schema::hasTable($umkmTable) &&
            Schema::hasColumn($umkmTable, 'user_id')
        ) {
            $store = Umkm::query()
                ->where('user_id', $user->getAuthIdentifier())
                ->first();

            if ($store) {
                return $store;
            }
        }

        // Cadangan jika model User memiliki relasi umkm().
        if (method_exists($user, 'umkm')) {
            $store = $user->umkm()->first();

            if ($store instanceof Umkm) {
                return $store;
            }
        }

        return null;
    }

    /**
     * Membuat query pesanan yang berkaitan dengan toko.
     */
    private function sellerOrdersQuery(?Umkm $store)
    {
        if (!$store || !class_exists(Order::class)) {
            return null;
        }

        $orderModel = new Order();
        $ordersTable = $orderModel->getTable();

        if (!Schema::hasTable($ordersTable)) {
            return null;
        }

        $query = Order::query();

        // Jika pesanan memiliki kolom umkm_id, gunakan kolom tersebut.
        if (Schema::hasColumn($ordersTable, 'umkm_id')) {
            return $query->where('umkm_id', $store->getKey());
        }

        /*
        |--------------------------------------------------------------------------
        | Jika tidak, cari pesanan melalui item dan produk toko.
        |--------------------------------------------------------------------------
        */

        if (!class_exists(OrderItem::class)) {
            return $query->whereRaw('1 = 0');
        }

        $itemModel = new OrderItem();
        $itemsTable = $itemModel->getTable();

        $productModel = new Product();
        $productsTable = $productModel->getTable();

        if (
            !Schema::hasTable($itemsTable) ||
            !Schema::hasTable($productsTable) ||
            !Schema::hasColumn($productsTable, 'umkm_id')
        ) {
            return $query->whereRaw('1 = 0');
        }

        $orderColumn = collect(['order_id', 'pesanan_id'])
            ->first(
                fn ($column) => Schema::hasColumn($itemsTable, $column)
            );

        $productColumn = collect(['product_id', 'produk_id'])
            ->first(
                fn ($column) => Schema::hasColumn($itemsTable, $column)
            );

        if (!$orderColumn || !$productColumn) {
            return $query->whereRaw('1 = 0');
        }

        $orderIds = DB::table($itemsTable)
            ->join(
                $productsTable,
                $productsTable . '.id',
                '=',
                $itemsTable . '.' . $productColumn
            )
            ->where(
                $productsTable . '.umkm_id',
                $store->getKey()
            )
            ->distinct()
            ->pluck($itemsTable . '.' . $orderColumn);

        return $query->whereIn(
            $orderModel->getKeyName(),
            $orderIds
        );
    }
}