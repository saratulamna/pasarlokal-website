<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Umkm;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        // Ambil kata pencarian dan kategori dari URL.
        $search = mb_substr(
            trim((string) $request->query('q', '')),
            0,
            100
        );

        $selectedCategory = trim(
            (string) $request->query('kategori', '')
        );

        // Kategori aktif beserta jumlah produk yang tersedia.
        $categories = Category::query()
            ->where('is_active', true)
            ->withCount([
                'products as active_products_count' => function ($query) {
                    $query
                        ->where('status', 'aktif')
                        ->whereHas('umkm', function ($storeQuery) {
                            $storeQuery->where('status', 'aktif');
                        });
                },
            ])
            ->orderByDesc('active_products_count')
            ->orderBy('nama')
            ->limit(8)
            ->get();

        // Dasar query seluruh produk yang boleh tampil di marketplace.
        $productQuery = Product::query()
            ->where('status', 'aktif')
            ->whereHas('umkm', function ($query) {
                $query->where('status', 'aktif');
            })
            ->with([
                'umkm:id,nama_toko,slug',
                'category:id,nama,slug',
            ])
            ->withCount('favorites')
            ->withCount([
                'reviews' => function ($query) {
                    $query->where('is_visible', true);
                },
            ])
            ->withAvg([
                'reviews as reviews_avg_rating' => function ($query) {
                    $query->where('is_visible', true);
                },
            ], 'rating');

        // Pencarian nama atau deskripsi produk.
        if ($search !== '') {
            $productQuery->where(function ($query) use ($search) {
                $query
                    ->where('nama', 'like', '%'.$search.'%')
                    ->orWhere('deskripsi', 'like', '%'.$search.'%')
                    ->orWhereHas('umkm', function ($storeQuery) use ($search) {
                        $storeQuery->where(
                            'nama_toko',
                            'like',
                            '%'.$search.'%'
                        );
                    });
            });
        }

        // Filter berdasarkan slug kategori.
        if ($selectedCategory !== '') {
            $productQuery->whereHas(
                'category',
                function ($query) use ($selectedCategory) {
                    $query
                        ->where('slug', $selectedCategory)
                        ->where('is_active', true);
                }
            );
        }

        // Produk unggulan: utamakan produk terlaris.
        $featuredProducts = (clone $productQuery)
            ->orderByDesc('terjual')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        // Produk terbaru mengikuti pencarian dan kategori yang dipilih.
        $latestProducts = (clone $productQuery)
            ->latest()
            ->limit(8)
            ->get();

        // Statistik umum, tidak terpengaruh pencarian.
        $stats = [
            'products' => Product::query()
                ->where('status', 'aktif')
                ->whereHas('umkm', function ($query) {
                    $query->where('status', 'aktif');
                })
                ->count(),

            'umkms' => Umkm::where('status', 'aktif')->count(),

            'categories' => Category::where('is_active', true)->count(),
        ];

        $selectedCategoryName = $selectedCategory !== ''
            ? Category::where('slug', $selectedCategory)
                ->where('is_active', true)
                ->value('nama')
            : null;

        return view('welcome', compact(
            'categories',
            'featuredProducts',
            'latestProducts',
            'stats',
            'search',
            'selectedCategory',
            'selectedCategoryName'
        ));
    }
}
