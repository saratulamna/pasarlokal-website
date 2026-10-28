<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', 'semua');

        if (! in_array($status, ['semua', 'aktif', 'nonaktif'], true)) {
            $status = 'semua';
        }

        $query = Category::query()
            ->select('categories.*')
            ->selectSub(
                Product::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn(
                        'products.category_id',
                        'categories.id'
                    ),
                'products_count'
            );

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $keyword = '%' . $search . '%';

                $builder
                    ->where('categories.nama', 'like', $keyword)
                    ->orWhere('categories.slug', 'like', $keyword)
                    ->orWhere('categories.deskripsi', 'like', $keyword);
            });
        }

        if ($status === 'aktif') {
            $query->where('categories.is_active', true);
        } elseif ($status === 'nonaktif') {
            $query->where('categories.is_active', false);
        }

        $categories = $query
            ->orderBy('categories.nama')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => Category::count(),
            'active' => Category::where('is_active', true)->count(),
            'inactive' => Category::where('is_active', false)->count(),
            'products' => Product::count(),
        ];

        return view('admin.categories.index', compact(
            'categories',
            'stats',
            'search',
            'status'
        ));
    }

    public function create(): View
    {
        return view('admin.categories.form', [
            'category' => new Category(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'gambar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'nama.required' => 'Nama kategori wajib diisi.',
            'nama.max' => 'Nama kategori maksimal 150 karakter.',
            'gambar.image' => 'File gambar harus berupa gambar.',
            'gambar.mimes' => 'Gambar harus berformat JPG, PNG, atau WEBP.',
            'gambar.max' => 'Ukuran gambar maksimal 2 MB.',
        ]);

        $category = new Category();

        $category->nama = $validated['nama'];
        $category->slug = $this->uniqueSlug($validated['nama']);
        $category->deskripsi = $validated['deskripsi'] ?? null;
        $category->is_active = $request->boolean('is_active');

        if ($request->hasFile('gambar')) {
            $category->gambar = $request->file('gambar')
                ->store('categories', 'public');
        }

        $category->save();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', compact('category'));
    }

    public function update(
        Request $request,
        Category $category
    ): RedirectResponse {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'gambar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'nama.required' => 'Nama kategori wajib diisi.',
            'nama.max' => 'Nama kategori maksimal 150 karakter.',
            'gambar.image' => 'File gambar harus berupa gambar.',
            'gambar.mimes' => 'Gambar harus berformat JPG, PNG, atau WEBP.',
            'gambar.max' => 'Ukuran gambar maksimal 2 MB.',
        ]);

        $category->nama = $validated['nama'];
        $category->slug = $this->uniqueSlug(
            $validated['nama'],
            $category->id
        );
        $category->deskripsi = $validated['deskripsi'] ?? null;
        $category->is_active = $request->boolean('is_active');

        if ($request->hasFile('gambar')) {
            $oldImage = $category->gambar;

            $category->gambar = $request->file('gambar')
                ->store('categories', 'public');

            if ($oldImage) {
                Storage::disk('public')->delete($oldImage);
            }
        }

        $category->save();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $hasProducts = Product::where(
            'category_id',
            $category->id
        )->exists();

        if ($hasProducts) {
            return back()->with(
                'error',
                'Kategori tidak dapat dihapus karena masih digunakan oleh produk.'
            );
        }

        if ($category->gambar) {
            Storage::disk('public')->delete($category->gambar);
        }

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }

    private function uniqueSlug(
        string $name,
        ?int $ignoreId = null
    ): string {
        $baseSlug = Str::slug($name);

        if ($baseSlug === '') {
            $baseSlug = 'kategori';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            Category::query()
                ->where('slug', $slug)
                ->when(
                    $ignoreId !== null,
                    fn ($query) => $query->where('id', '!=', $ignoreId)
                )
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}