<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    private const ROLES = [
        'admin' => 'Admin',
        'manajer_proyek' => 'Manajer proyek',
        'petugas_lapangan' => 'Petugas lapangan',
        'klien' => 'Klien',
    ];

    public function index(Request $request)
    {
        $request->validate([
            'role' => ['nullable', Rule::in(array_keys(self::ROLES))],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $users = User::with('roles')
            ->when($request->filled('role'), fn ($q) => $q->role($request->input('role')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $cari = '%' . $request->input('q') . '%';
                $q->where(fn ($w) => $w->where('name', 'ilike', $cari)->orWhere('email', 'ilike', $cari));
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', ['users' => $users, 'roles' => self::ROLES]);
    }

    public function create()
    {
        return view('users.create', ['roles' => self::ROLES, 'user' => new User()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(array_keys(self::ROLES))],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // di-hash otomatis oleh cast 'hashed'
        ]);
        $user->assignRole($data['role']);

        return redirect()->route('users.index')->with('status', "Pengguna {$user->name} ditambahkan.");
    }

    public function edit(User $user)
    {
        return view('users.edit', [
            'user' => $user,
            'roles' => self::ROLES,
            'roleSekarang' => $user->getRoleNames()->first(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(array_keys(self::ROLES))],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        if ($user->is($request->user()) && $data['role'] !== 'admin') {
            return back()->withInput()->withErrors(['role' => 'Kamu tidak bisa menurunkan role akunmu sendiri.']);
        }

        $user->fill(['name' => $data['name'], 'email' => $data['email']]);

        $passwordBerubah = ! empty($data['password']);
        if ($passwordBerubah) {
            $user->password = $data['password'];
        }

        $user->save();
        $user->syncRoles([$data['role']]);

        if ($passwordBerubah) {
            $user->tokens()->delete(); // cabut token API lama
        }

        return redirect()->route('users.index')->with('status', "Pengguna {$user->name} diperbarui.");
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->is($request->user())) {
            return redirect()->route('users.index')->withErrors(['hapus' => 'Kamu tidak bisa menghapus akunmu sendiri.']);
        }

        if (DailyReport::where('user_id', $user->id)->exists()) {
            return redirect()->route('users.index')
                ->withErrors(['hapus' => "{$user->name} sudah punya laporan harian, tidak bisa dihapus (laporannya ikut terhapus)."]);
        }

        if ($user->managedProjects()->exists()) {
            return redirect()->route('users.index')
                ->withErrors(['hapus' => "{$user->name} masih menjadi manajer proyek. Alihkan proyeknya dulu."]);
        }

        $user->tokens()->delete();
        $user->delete();

        return redirect()->route('users.index')->with('status', 'Pengguna dihapus.');
    }
}