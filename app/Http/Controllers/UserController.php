<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Gudang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Mulai query user dan load relasi gudang
        $query = User::with('gudang');

        // 1. Logika Search (Cari berdasarkan Nama atau Username)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%");
            });
        }

        // 2. Logika Filter Role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Eksekusi query dengan pagination
        // ->appends($request->all()) berguna agar saat pindah halaman (page 2, 3), filter tidak hilang
        $users = $query->latest()->paginate(10)->appends($request->all());

        // Load semua gudang untuk modal create/edit
        $gudangs = Gudang::all();

        return view('admin.users.index', compact('users', 'gudangs'));
    }

    public function create()
    {
        $gudangs = Gudang::all();
        return view('admin.users.create', compact('gudangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'lowercase', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:admin,operator_gudang,security'],
            'gudang_id' => ['nullable', 'exists:gudangs,id'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:20'],
        ]);

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->username . '@egudang.local', // Generate dummy email
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'gudang_id' => $request->gudang_id, // Nullable jika admin
            'jabatan' => $request->jabatan,
            'no_hp' => $request->no_hp,
            'is_active' => true,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $gudangs = Gudang::all();
        return view('admin.users.edit', compact('user', 'gudangs'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'lowercase', 'max:255', 'unique:users,username,'.$user->id],
            'role' => ['required', 'in:admin,operator_gudang,security'],
            'gudang_id' => ['nullable', 'exists:gudangs,id'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
        ]);

        $data = $request->except('password');

        // Update email if username changes
        if ($request->username !== $user->username) {
            $data['email'] = $request->username . '@egudang.local';
        }

        // Update password hanya jika diisi
        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Rules\Password::defaults()],
            ]);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }
        
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus.');
    }
}