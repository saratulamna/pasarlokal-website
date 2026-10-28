<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Umkm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Memastikan penjual mempunyai toko yang sudah disetujui.
     */
    private function activeStore(Request $request): Umkm
    {
        $store = $request->user()->umkm;

        abort_unless(
            $store && $store->status === 'aktif',
            403,
            'Toko harus aktif dan telah diverifikasi untuk mengelola produk.'
        );

        return $store;
    }

    /**
     * Memastikan produk adalah milik toko pengguna yang login.
     */
    private function ownedProduct(
        Umkm $store,
        Product $product
    ): Product {
        abort_unless(
            (int) $product->umkm_id === (int) $store->id,
            404
        );

        return $product;
    }

    /**
     * Membuat slug produk yang unik.
     */
    private function uniqueSlug(
        string $name,
        ?Product $ignore = null
    ): string {
        $baseSlug = Str::slug($name);

        if ($baseSlug === '') {
            throw ValidationException::withMessages([
                'nama' => 'Nama produk tidak dapat dijadikan slug. Gunakan nama lain.',
            ]);
        }

        $slug = $baseSlug;
        $suffix = 2;

        while (true) {
            $query = Product::query()->where('slug', $slug);

            if ($ignore) {
                $query->whereKeyNot($ignore->getKey());
            }

            if (! $query->exists()) {
                return $slug;
            }

            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }
    }

    /**
     * Daftar produk milik toko.
     */
    public function index(Request $request): View
    {
        $store = $this->activeStore($request);

        $products = $store->products()
            ->with('category:id,nama,slug')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => $store->products()->count(),

            'active' => $store->products()
                ->where('status', 'aktif')
                ->count(),

            'draft' => $store->products()
                ->where('status', 'draft')
                ->count(),

            'low_stock' => $store->products()
                ->where('stok', '<=', 5)
                ->count(),
        ];

        return view('umkm.products.index', compact(
            'store',
            'products',
            'stats'
        ));
    }

    /**
     * Form tambah produk.
     */
    public function create(Request $request): View
    {
        $store = $this->activeStore($request);

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('nama')
            ->get(['id', 'nama']);

        $product = new Product;

        return view('umkm.products.create', compact(
            'store',
            'categories',
            'product'
        ));
    }

    /**
     * Simpan produk baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $store = $this->activeStore($request);

        $data = $request->validate([
            'nama' => [
                'required',
                'string',
                'min:2',
                'max:180',
            ],

            'category_id' => [
                'nullable',
                Rule::exists('categories', 'id')
                    ->where(fn ($query) => $query->where('is_active', true)
                    ),
            ],

            'deskripsi' => [
                'nullable',
                'string',
                'max:20000',
            ],

            'harga' => [
                'required',
                'numeric',
                'min:1',
                'max:9999999999999',
            ],

            'stok' => [
                'required',
                'integer',
                'min:0',
            ],

            'gambar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'status' => [
                'required',
                Rule::in(['draft', 'aktif']),
            ],
        ]);

        $data['slug'] = $this->uniqueSlug($data['nama']);

        $data['gambar'] = $request->file('gambar')
            ? $request->file('gambar')->store('products', 'public')
            : null;

        $data['terjual'] = 0;

        $store->products()->create($data);

        return redirect()
            ->route('umkm.products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * Form edit produk.
     */
    public function edit(
        Request $request,
        Product $product
    ): View {
        $store = $this->activeStore($request);
        $product = $this->ownedProduct($store, $product);

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('nama')
            ->get(['id', 'nama']);

        return view('umkm.products.edit', compact(
            'store',
            'categories',
            'product'
        ));
    }

    /**
     * Perbarui produk.
     */
    public function update(
        Request $request,
        Product $product
    ): RedirectResponse {
        $store = $this->activeStore($request);
        $product = $this->ownedProduct($store, $product);

        $data = $request->validate([
            'nama' => [
                'required',
                'string',
                'min:2',
                'max:180',
            ],

            'category_id' => [
                'nullable',
                Rule::exists('categories', 'id')
                    ->where(fn ($query) => $query->where('is_active', true)
                    ),
            ],

            'deskripsi' => [
                'nullable',
                'string',
                'max:20000',
            ],

            'harga' => [
                'required',
                'numeric',
                'min:1',
                'max:9999999999999',
            ],

            'stok' => [
                'required',
                'integer',
                'min:0',
            ],

            'gambar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'status' => [
                'required',
                Rule::in(['draft', 'aktif']),
            ],
        ]);

        if ($product->nama !== $data['nama']) {
            $data['slug'] = $this->uniqueSlug(
                $data['nama'],
                $product
            );
        }

        if ($request->hasFile('gambar')) {
            $oldImage = $product->gambar;

            $data['gambar'] = $request->file('gambar')
                ->store('products', 'public');

            if ($oldImage) {
                Storage::disk('public')->delete($oldImage);
            }
        }

        $product->update($data);

        return redirect()
            ->route('umkm.products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Hapus produk milik toko.
     */
    public function destroy(
        Request $request,
        Product $product
    ): RedirectResponse {
        $store = $this->activeStore($request);
        $product = $this->ownedProduct($store, $product);

        if ($product->gambar) {
            Storage::disk('public')->delete($product->gambar);
        }

        $product->delete();

        return redirect()
            ->route('umkm.products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }
}
