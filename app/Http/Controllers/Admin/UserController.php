<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    private array $allowedRoles = [
        'admin',
        'umkm',
        'pembeli',
    ];

    public function index(Request $request): View
    {
        $query = User::query();

        $search = trim($request->input('q', ''));
        $role = $request->input('role', '');
        $verification = $request->input('verification', '');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (in_array($role, $this->allowedRoles, true)) {
            $query->where('role', $role);
        }

        if ($verification === 'verified') {
            $query->whereNotNull('email_verified_at');
        } elseif ($verification === 'unverified') {
            $query->whereNull('email_verified_at');
        }

        $users = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $usersCount = User::count();

        $roleCounts = [
            'admin' => User::where('role', 'admin')->count(),
            'umkm' => User::where('role', 'umkm')->count(),
            'pembeli' => User::where('role', 'pembeli')->count(),
        ];

        $verifiedCount = User::whereNotNull(
            'email_verified_at'
        )->count();

        $unverifiedCount = User::whereNull(
            'email_verified_at'
        )->count();

        return view('admin.users.index', compact(
            'users',
            'search',
            'role',
            'verification',
            'usersCount',
            'roleCounts',
            'verifiedCount',
            'unverifiedCount'
        ));
    }

    public function create(): View
    {
        return view('admin.users.form', [
            'user' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'role' => [
                'required',
                Rule::in($this->allowedRoles),
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'role.required' => 'Role pengguna wajib dipilih.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = new User;
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', compact('user'));
    }

    public function update(
        Request $request,
        User $user
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'role' => [
                'required',
                Rule::in($this->allowedRoles),
            ],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan pengguna lain.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $emailChanged = $user->email !== $validated['email'];

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        // Jangan biarkan admin mengubah role akun yang sedang digunakan.
        if ((int) $user->id !== (int) Auth::id()) {
            $user->role = $validated['role'];
        }

        // Password hanya diganti jika kolom diisi.
        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        // Email baru harus diverifikasi kembali.
        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        // Cegah admin menghapus akunnya sendiri.
        // Cegah admin menghapus akunnya sendiri.
        if ((int) $user->id === (int) Auth::id()) {
            return back()->with(
                'error',
                'Akun yang sedang kamu gunakan tidak dapat dihapus.'
            );
        }   

        try {
            $user->delete();
        } catch (QueryException $exception) {
            report($exception);

            return back()->with(
                'error',
                'Pengguna tidak dapat dihapus karena masih terkait dengan data lain.'
            );
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }
}