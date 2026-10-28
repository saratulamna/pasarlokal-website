<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        /*
        |--------------------------------------------------------------------------
        | DATA FILTER
        |--------------------------------------------------------------------------
        */

        $search = trim((string) $request->query('q', ''));

        $category = (string) $request->query('category', '');
        $status = (string) $request->query('status', '');
        $stock = (string) $request->query('stock', '');

        $categories = Category::query()
            ->orderBy('nama')
            ->get(['id', 'nama']);

        $categoryIds = $categories->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (
            $category !== '' &&
            (! ctype_digit($category) ||
            ! in_array((int) $category, $categoryIds, true))
        ) {
            $category = '';
        }

        $statuses = Product::query()
            ->whereNotNull('status')
            ->where('status', '<>', '')
            ->distinct()
            ->orderBy('status')
            ->pluck('status');

        if (! $statuses->contains($status)) {
            $status = '';
        }

        $allowedStockFilters = ['habis', 'menipis', 'tersedia'];

        if (! in_array($stock, $allowedStockFilters, true)) {
            $stock = '';
        }

        /*
        |--------------------------------------------------------------------------
        | STATISTIK PRODUK
        |--------------------------------------------------------------------------
        */

        $stats = [
            'total' => Product::count(),

            'active' => Product::whereIn('status', [
                'aktif',
                'active',
                'published',
                'publish',
                'tersedia',
            ])->count(),

            'low_stock' => Product::where('stok', '>', 0)
                ->where('stok', '<=', 5)
                ->count(),

            'out_stock' => Product::where('stok', '<=', 0)
                ->count(),

            'sold' => (int) Product::sum('terjual'),
        ];

        /*
        |--------------------------------------------------------------------------
        | QUERY DAFTAR PRODUK
        |--------------------------------------------------------------------------
        |
        | Menggunakan LEFT JOIN supaya informasi toko dan kategori
        | tetap bisa ditampilkan tanpa bergantung pada eager loading.
        |
        */

        $query = Product::query()
            ->leftJoin('umkms', 'products.umkm_id', '=', 'umkms.id')
            ->leftJoin(
                'categories',
                'products.category_id',
                '=',
                'categories.id'
            )
            ->select([
                'products.*',
                'umkms.nama_toko as admin_store_name',
                'categories.nama as admin_category_name',
            ]);

        /*
        |--------------------------------------------------------------------------
        | PENCARIAN
        |--------------------------------------------------------------------------
        */

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $keyword = '%' . $search . '%';

                $builder
                    ->where('products.nama', 'like', $keyword)
                    ->orWhere('products.slug', 'like', $keyword)
                    ->orWhere('products.deskripsi', 'like', $keyword)
                    ->orWhere('umkms.nama_toko', 'like', $keyword)
                    ->orWhere('categories.nama', 'like', $keyword);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER KATEGORI
        |--------------------------------------------------------------------------
        */

        if ($category !== '') {
            $query->where('products.category_id', (int) $category);
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER STATUS
        |--------------------------------------------------------------------------
        */

        if ($status !== '') {
            $query->where('products.status', $status);
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER STOK
        |--------------------------------------------------------------------------
        */

        if ($stock === 'habis') {
            $query->where('products.stok', '<=', 0);
        } elseif ($stock === 'menipis') {
            $query->where('products.stok', '>', 0)
                ->where('products.stok', '<=', 5);
        } elseif ($stock === 'tersedia') {
            $query->where('products.stok', '>', 5);
        }

        /*
        |--------------------------------------------------------------------------
        | HASIL DAN PAGINATION
        |--------------------------------------------------------------------------
        */

        $products = $query
            ->orderByDesc('products.created_at')
            ->orderByDesc('products.id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.products.index', compact(
            'products',
            'categories',
            'statuses',
            'stats',
            'search',
            'category',
            'status',
            'stock',
        ));
    }
}