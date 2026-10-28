<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Models\Umkm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StoreController extends Controller
{
    /**
     * Menampilkan formulir pembuatan toko.
     */
    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user()->umkm()->exists()) {
            return redirect()
                ->route('umkm.dashboard')
                ->with('success', 'Kamu sudah memiliki toko.');
        }

        return view('umkm.store.create');
    }

    /**
     * Menyimpan toko baru milik pengguna UMKM.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Satu akun UMKM hanya boleh memiliki satu toko.
        if ($user->umkm()->exists()) {
            return redirect()
                ->route('umkm.dashboard')
                ->with('success', 'Akunmu sudah memiliki toko.');
        }

        $data = $request->validate([
            'nama_toko' => [
                'required',
                'string',
                'min:3',
                'max:150',
            ],

            'deskripsi' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'telepon' => [
                'nullable',
                'string',
                'max:30',
            ],

            'alamat' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $baseSlug = Str::slug($data['nama_toko']);

        if ($baseSlug === '') {
            throw ValidationException::withMessages([
                'nama_toko' => 'Nama toko tidak dapat dijadikan alamat toko. Gunakan nama yang berbeda.',
            ]);
        }

        // Pastikan slug toko unik.
        $slug = $baseSlug;
        $suffix = 2;

        while (
            Umkm::where('slug', $slug)->exists()
        ) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        $user->umkm()->create([
            'nama_toko' => $data['nama_toko'],
            'slug' => $slug,
            'deskripsi' => $data['deskripsi'] ?? null,
            'telepon' => $data['telepon'] ?? null,
            'alamat' => $data['alamat'] ?? null,

            // Toko menunggu persetujuan admin.
            'status' => 'menunggu_verifikasi',
            'verified_at' => null,
        ]);

        return redirect()
            ->route('umkm.dashboard')
            ->with(
                'success',
                'Toko berhasil dibuat dan sedang menunggu verifikasi admin.'
            );
    }
}
