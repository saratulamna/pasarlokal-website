<?php

namespace App\Http\Controllers\Pembeli;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Umkm;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderHistoryController extends Controller
{
    public function index(Request $request): View
    {
        // Tampilkan hanya pesanan milik pembeli yang sedang login.
        $orders = Order::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(8);

        $orderIds = $orders->getCollection()->pluck('id');

        // Ambil produk dari snapshot pesanan.
        $orderItems = OrderItem::whereIn('order_id', $orderIds)
            ->orderBy('id')
            ->get()
            ->groupBy('order_id');

        // Ambil nama toko untuk setiap pesanan.
        $storeIds = $orders->getCollection()
            ->pluck('umkm_id')
            ->filter()
            ->unique();

        $stores = Umkm::whereIn('id', $storeIds)
            ->get()
            ->keyBy('id');

        return view('pembeli.orders.index', [
            'orders' => $orders,
            'orderItems' => $orderItems,
            'stores' => $stores,
        ]);
    }
}
