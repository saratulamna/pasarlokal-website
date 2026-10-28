<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    private const ONGKIR_PER_TOKO = 10000;

    /**
     * Menampilkan halaman checkout.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $cart = Cart::with([
            'items.product.umkm',
            'items.product.category',
        ])
            ->where('user_id', $request->user()->id)
            ->where('status', 'aktif')
            ->first();

        if (! $cart || $cart->items->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Keranjang belanjamu masih kosong.');
        }

        foreach ($cart->items as $item) {
            $product = $item->product;

            if (
                ! $product ||
                $product->status !== 'aktif' ||
                ! $product->umkm ||
                $product->umkm->status !== 'aktif'
            ) {
                return redirect()
                    ->route('cart.index')
                    ->with(
                        'error',
                        'Ada produk yang sudah tidak tersedia. Periksa kembali keranjangmu.'
                    );
            }

            if (
                (int) $item->quantity < 1 ||
                (int) $item->quantity > (int) $product->stok
            ) {
                return redirect()
                    ->route('cart.index')
                    ->with(
                        'error',
                        'Ada jumlah produk yang melebihi stok. Perbarui keranjang sebelum checkout.'
                    );
            }
        }

        $subtotal = $cart->items->sum(
            fn ($item) => (float) $item->product->harga * (int) $item->quantity
        );

        $storeCount = $cart->items
            ->pluck('product.umkm_id')
            ->unique()
            ->count();

        $shippingCost = $storeCount * self::ONGKIR_PER_TOKO;
        $total = $subtotal + $shippingCost;

        return view('pembeli.checkout.index', [
            'cart' => $cart,
            'cartItems' => $cart->items,
            'subtotal' => $subtotal,
            'storeCount' => $storeCount,
            'shippingCost' => $shippingCost,
            'total' => $total,
            'user' => $request->user(),
        ]);
    }

    /**
     * Memvalidasi checkout dan membuat pesanan.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'recipient_name' => [
                'required',
                'string',
                'min:2',
                'max:150',
            ],
            'recipient_phone' => [
                'required',
                'string',
                'min:8',
                'max:20',
            ],
            'shipping_address' => [
                'required',
                'string',
                'min:10',
                'max:2000',
            ],
            'shipping_postal_code' => [
                'nullable',
                'string',
                'regex:/^[0-9]{5}$/',
            ],
            'payment_method' => [
                'required',
                'in:transfer_bank,cod',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'shipping_notes' => [
                'nullable',
                'string',
                'max:500',
            ],
        ], [
            'recipient_name.required' => 'Nama penerima wajib diisi.',
            'recipient_phone.required' => 'Nomor telepon penerima wajib diisi.',
            'shipping_address.required' => 'Alamat lengkap wajib diisi.',
            'shipping_address.min' => 'Alamat harus minimal 10 karakter.',
            'shipping_postal_code.regex' => 'Kode pos harus terdiri dari 5 angka.',
            'payment_method.required' => 'Pilih metode pembayaran.',
            'payment_method.in' => 'Metode pembayaran tidak valid.',
        ]);

        $orderIds = DB::transaction(function () use (
            $request,
            $validated
        ) {
            $cart = Cart::where('user_id', $request->user()->id)
                ->where('status', 'aktif')
                ->lockForUpdate()
                ->first();

            if (! $cart) {
                throw ValidationException::withMessages([
                    'cart' => 'Keranjang aktif tidak ditemukan.',
                ]);
            }

            $cartItems = CartItem::where('cart_id', $cart->id)
                ->lockForUpdate()
                ->get();

            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => 'Keranjang belanjamu masih kosong.',
                ]);
            }

            /*
             * Periksa kembali stok dan status produk di database.
             * Pemeriksaan ulang ini penting agar harga dan stok
             * tidak hanya bergantung pada data halaman checkout.
             */
            $preparedItems = [];

            foreach ($cartItems as $cartItem) {
                $product = Product::with('umkm')
                    ->whereKey($cartItem->product_id)
                    ->lockForUpdate()
                    ->first();

                if (
                    ! $product ||
                    $product->status !== 'aktif' ||
                    ! $product->umkm ||
                    $product->umkm->status !== 'aktif'
                ) {
                    throw ValidationException::withMessages([
                        'cart' => 'Ada produk yang sudah tidak tersedia. Periksa keranjangmu.',
                    ]);
                }

                $quantity = (int) $cartItem->quantity;

                if ($quantity < 1 || $quantity > (int) $product->stok) {
                    throw ValidationException::withMessages([
                        'cart' => 'Stok produk '.$product->nama
                            .' tidak mencukupi. Periksa kembali keranjangmu.',
                    ]);
                }

                $preparedItems[] = [
                    'cart_item_id' => $cartItem->id,
                    'product' => $product,
                    'quantity' => $quantity,
                    'price' => (float) $product->harga,
                ];
            }

            /*
             * Satu pesanan untuk setiap toko UMKM.
             */
            $groupedItems = collect($preparedItems)->groupBy(
                fn ($row) => $row['product']->umkm_id
            );

            $createdOrderIds = [];

            foreach ($groupedItems as $umkmId => $items) {
                $subtotal = $items->sum(
                    fn ($row) => $row['price'] * $row['quantity']
                );

                $shippingCost = self::ONGKIR_PER_TOKO;
                $discount = 0;
                $total = $subtotal + $shippingCost - $discount;

                $order = new Order;
                $order->order_number = $this->generateOrderNumber();
                $order->user_id = $request->user()->id;
                $order->umkm_id = $umkmId;
                $order->subtotal = $subtotal;
                $order->shipping_cost = $shippingCost;
                $order->discount = $discount;
                $order->total = $total;
                $order->status = $validated['payment_method'] === 'cod'
                    ? 'menunggu_konfirmasi'
                    : 'menunggu_pembayaran';
                $order->payment_status = 'belum_bayar';
                $order->payment_method = $validated['payment_method'];
                $order->recipient_name = $validated['recipient_name'];
                $order->recipient_phone = $validated['recipient_phone'];
                $order->shipping_address = $validated['shipping_address'];
                $order->shipping_postal_code =
                    $validated['shipping_postal_code'] ?? null;
                $order->notes = $validated['notes'] ?? null;
                $order->shipping_notes =
                    $validated['shipping_notes'] ?? null;
                $order->save();

                /*
                 * Simpan snapshot nama, gambar, dan harga produk.
                 */
                foreach ($items as $row) {
                    $product = $row['product'];
                    $quantity = $row['quantity'];
                    $unitPrice = $row['price'];

                    $orderItem = new OrderItem;
                    $orderItem->order_id = $order->id;
                    $orderItem->product_id = $product->id;
                    $orderItem->product_name = $product->nama;
                    $orderItem->product_image = $product->gambar;
                    $orderItem->harga_satuan = $unitPrice;
                    $orderItem->quantity = $quantity;
                    $orderItem->subtotal = $unitPrice * $quantity;
                    $orderItem->save();

                    /*
                     * Stok ditahan saat pesanan dibuat agar tidak
                     * melebihi stok yang tersedia.
                     */
                    $product->decrement('stok', $quantity);
                }

                /*
                 * Buat catatan pembayaran.
                 * Ini belum terhubung ke payment gateway.
                 */
                $payment = new Payment;
                $payment->order_id = $order->id;
                $payment->payment_code = $this->generatePaymentCode();
                $payment->metode = $validated['payment_method'];
                $payment->jumlah = $total;
                $payment->status = 'menunggu_pembayaran';
                $payment->save();

                $createdOrderIds[] = $order->id;
            }

            /*
             * Kosongkan keranjang setelah seluruh pesanan berhasil dibuat.
             */
            CartItem::where('cart_id', $cart->id)->delete();

            return $createdOrderIds;
        });

        return redirect()
            ->route('checkout.success')
            ->with('created_order_ids', $orderIds);
    }

    /**
     * Halaman konfirmasi pesanan berhasil dibuat.
     */
    public function success(Request $request): View|RedirectResponse
    {
        $ids = session('created_order_ids', []);

        if (empty($ids)) {
            return redirect()->route('cart.index');
        }

        $orders = Order::where('user_id', $request->user()->id)
            ->whereIn('id', $ids)
            ->latest()
            ->get();

        if ($orders->isEmpty()) {
            return redirect()->route('pembeli.dashboard');
        }

        return view('pembeli.checkout.success', compact('orders'));
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'PL-'.now()->format('Ymd')
                .'-'.Str::upper(Str::random(6));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }

    private function generatePaymentCode(): string
    {
        do {
            $code = 'PAY-'.Str::upper(Str::random(12));
        } while (Payment::where('payment_code', $code)->exists());

        return $code;
    }
}
