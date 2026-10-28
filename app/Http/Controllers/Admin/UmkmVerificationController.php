<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Umkm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UmkmVerificationController extends Controller
{
    /**
     * Menampilkan daftar toko UMKM.
     */
    public function index(Request $request): View
    {
        $allowedStatuses = [
            'menunggu_verifikasi',
            'aktif',
            'ditolak',
            'semua',
        ];

        $status = (string) $request->query(
            'status',
            'menunggu_verifikasi'
        );

        if (! in_array($status, $allowedStatuses, true)) {
            $status = 'menunggu_verifikasi';
        }

        $query = Umkm::query()
            ->with('user:id,name,email')
            ->withCount('products')
            ->latest();

        if ($status !== 'semua') {
            $query->where('status', $status);
        }

        $umkms = $query
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => Umkm::count(),

            'pending' => Umkm::where(
                'status',
                'menunggu_verifikasi'
            )->count(),

            'active' => Umkm::where(
                'status',
                'aktif'
            )->count(),

            'rejected' => Umkm::where(
                'status',
                'ditolak'
            )->count(),
        ];

        return view('admin.umkms.index', compact(
            'umkms',
            'stats',
            'status'
        ));
    }

    /**
     * Menyetujui toko yang menunggu verifikasi.
     */
    public function approve(Umkm $umkm): RedirectResponse
    {
        if ($umkm->status !== 'menunggu_verifikasi') {
            return back()->with(
                'error',
                'Hanya toko yang menunggu verifikasi yang dapat disetujui.'
            );
        }

        $umkm->update([
            'status' => 'aktif',
            'verified_at' => now(),
        ]);

        return redirect()
            ->route('admin.umkms.index')
            ->with(
                'success',
                'Toko '.$umkm->nama_toko.' berhasil disetujui.'
            );
    }

    /**
     * Menolak toko yang menunggu verifikasi.
     */
    public function reject(Umkm $umkm): RedirectResponse
    {
        if ($umkm->status !== 'menunggu_verifikasi') {
            return back()->with(
                'error',
                'Hanya toko yang menunggu verifikasi yang dapat ditolak.'
            );
        }

        $umkm->update([
            'status' => 'ditolak',
            'verified_at' => null,
        ]);

        return redirect()
            ->route('admin.umkms.index')
            ->with(
                'success',
                'Toko '.$umkm->nama_toko.' telah ditolak.'
            );
    }

    
}