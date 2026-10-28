<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CartController extends Controller
{
    /**
     * Menampilkan keranjang pembeli.
     */
    public function index(Request $request): View
    {
        $cart = Cart::with([
            'items.product.umkm',
            'items.product.category',
        ])
            ->where('user_id', $request->user()->id)
            ->where('status', 'aktif')
            ->first();

        $cartItems = $cart?->items ?? collect();

        $total = $cartItems->sum(function ($item) {
            return (float) $item->product->harga * (int) $item->quantity;
        });

        return view('pembeli.cart.index', compact(
            'cart',
            'cartItems',
            'total'
        ));
    }

    /**
     * Menambahkan produk ke keranjang.
     */
    public function store(
        Request $request,
        Product $product
    ): RedirectResponse {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        DB::transaction(function () use (
            $request,
            $product,
            $validated
        ) {
            $freshProduct = Product::with('umkm')
                ->whereKey($product->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $freshProduct->status !== 'aktif' ||
                ! $freshProduct->umkm ||
                $freshProduct->umkm->status !== 'aktif'
            ) {
                throw ValidationException::withMessages([
                    'product' => 'Produk ini tidak tersedia untuk dibeli.',
                ]);
            }

            if ($freshProduct->stok < 1) {
                throw ValidationException::withMessages([
                    'product' => 'Maaf, stok produk sudah habis.',
                ]);
            }

            $cart = Cart::firstOrCreate(
                [
                    'user_id' => $request->user()->id,
                    'status' => 'aktif',
                ]
            );

            $item = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $freshProduct->id)
                ->lockForUpdate()
                ->first();

            $oldQuantity = $item ? (int) $item->quantity : 0;
            $newQuantity = $oldQuantity + (int) $validated['quantity'];

            if ($newQuantity > $freshProduct->stok) {
                throw ValidationException::withMessages([
                    'quantity' => 'Jumlah melebihi stok yang tersedia. Stok saat ini: '
                        .$freshProduct->stok.' produk.',
                ]);
            }

            if ($item) {
                $item->update([
                    'quantity' => $newQuantity,
                ]);
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $freshProduct->id,
                    'quantity' => $newQuantity,
                ]);
            }
        });

        return redirect()
            ->route('cart.index')
            ->with('success', 'Produk berhasil ditambahkan ke keranjang!');
    }

    /**
     * Mengubah jumlah produk.
     */
    public function update(
        Request $request,
        CartItem $cartItem
    ): RedirectResponse {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $item = CartItem::with(['cart', 'product.umkm'])
            ->findOrFail($cartItem->id);

        abort_unless(
            $item->cart &&
            (int) $item->cart->user_id === (int) $request->user()->id &&
            $item->cart->status === 'aktif',
            404
        );

        $product = $item->product;

        if (
            ! $product ||
            $product->status !== 'aktif' ||
            ! $product->umkm ||
            $product->umkm->status !== 'aktif'
        ) {
            return back()->with(
                'error',
                'Produk sudah tidak tersedia. Hapus produk tersebut dari keranjang.'
            );
        }

        if ((int) $validated['quantity'] > (int) $product->stok) {
            return back()
                ->withInput()
                ->withErrors([
                    'quantity' => 'Jumlah melebihi stok yang tersedia: '
                        .$product->stok.' produk.',
                ]);
        }

        $item->update([
            'quantity' => (int) $validated['quantity'],
        ]);

        return redirect()
            ->route('cart.index')
            ->with('success', 'Jumlah produk berhasil diperbarui.');
    }

    /**
     * Menghapus produk dari keranjang.
     */
    public function destroy(
        Request $request,
        CartItem $cartItem
    ): RedirectResponse {
        $item = CartItem::with('cart')
            ->findOrFail($cartItem->id);

        abort_unless(
            $item->cart &&
            (int) $item->cart->user_id === (int) $request->user()->id &&
            $item->cart->status === 'aktif',
            404
        );

        $item->delete();

        return redirect()
            ->route('cart.index')
            ->with('success', 'Produk berhasil dihapus dari keranjang.');
    }
}
