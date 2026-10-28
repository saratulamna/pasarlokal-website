<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Halaman detail produk.
     */
    public function show(Product $product): View
    {
        // Produk hanya dapat dilihat jika aktif
        // dan toko UMKM-nya juga aktif.
        abort_unless(
            $product->status === 'aktif'
            && $product->umkm()
                ->where('status', 'aktif')
                ->exists(),
            404
        );

        $product->load([
            'umkm:id,nama_toko,slug,deskripsi,logo',
            'category:id,nama,slug',
        ]);

        $product->loadCount([
            'favorites',
            'reviews' => fn ($query) => $query->where('is_visible', true),
        ]);

        $product->loadAvg([
            'reviews as reviews_avg_rating' => fn ($query) => $query->where('is_visible', true),
        ], 'rating');

        $reviews = $product->reviews()
            ->where('is_visible', true)
            ->with('user:id,name')
            ->latest()
            ->paginate(5);

        $relatedQuery = Product::query()
            ->where('status', 'aktif')
            ->where('id', '!=', $product->id)
            ->whereHas('umkm', fn ($query) => $query->where('status', 'aktif'));

        if ($product->category_id !== null) {
            $relatedQuery->where('category_id', $product->category_id);
        }

        $relatedProducts = $relatedQuery
            ->with([
                'umkm:id,nama_toko,slug',
                'category:id,nama,slug',
            ])
            ->withCount('favorites')
            ->withCount([
                'reviews' => fn ($query) => $query->where('is_visible', true),
            ])
            ->withAvg([
                'reviews as reviews_avg_rating' => fn ($query) => $query->where('is_visible', true),
            ], 'rating')
            ->latest()
            ->limit(4)
            ->get();

        return view('products.show', compact(
            'product',
            'reviews',
            'relatedProducts'
        ));
    }

    /**
     * Halaman jelajahi produk untuk pembeli.
     */
    public function index(Request $request): View
    {
        $productsQuery = Product::query()
            ->where('status', 'aktif')
            ->where('stok', '>', 0)
            ->whereHas('umkm', function ($query) {
                $query->where('status', 'aktif');
            })
            ->with([
                'umkm:id,nama_toko,slug',
                'category:id,nama,slug',
            ])
            ->withCount('favorites')
            ->withCount([
                'reviews' => fn ($query) => $query->where('is_visible', true),
            ])
            ->withAvg([
                'reviews as reviews_avg_rating' => fn ($query) => $query->where('is_visible', true),
            ], 'rating');

        // Pencarian nama dan deskripsi produk.
        if ($request->filled('q')) {
            $keyword = trim($request->input('q'));

            $productsQuery->where(function ($query) use ($keyword) {
                $query->where('nama', 'like', "%{$keyword}%")
                    ->orWhere('deskripsi', 'like', "%{$keyword}%");
            });
        }

        // Filter berdasarkan kategori.
        if ($request->filled('kategori')) {
            $productsQuery->where(
                'category_id',
                $request->input('kategori')
            );
        }

        // Pengurutan produk.
        switch ($request->input('urutkan')) {
            case 'harga_terendah':
                $productsQuery->orderBy('harga', 'asc');
                break;

            case 'harga_tertinggi':
                $productsQuery->orderBy('harga', 'desc');
                break;

            case 'terlaris':
                $productsQuery->orderByDesc('terjual');
                break;

            default:
                $productsQuery->latest();
                break;
        }

        $products = $productsQuery
            ->paginate(12)
            ->withQueryString();

        $categories = Category::query()
            ->orderBy('nama')
            ->get(['id', 'nama', 'slug']);

        return view('pembeli.jelajahi', compact(
            'products',
            'categories'
        ));
    }
}