<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Gudang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Mulai query user dan load relasi gudang
        $query = User::with(['gudang', 'managedGudangs']);

        // 1. Logika Search (Cari berdasarkan Nama atau Username) - Case Insensitive
        if ($request->filled('search')) {
            $searchLower = strtolower($request->search);
            $query->where(function($q) use ($searchLower) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereRaw('LOWER(username) LIKE ?', ["%{$searchLower}%"]);
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
        $gudangs = Gudang::where('kode', '!=', 'GDG-EXT')->orderBy('nama')->get();

        return view('admin.users.index', compact('users', 'gudangs'));
    }

    public function create()
    {
        $gudangs = Gudang::where('kode', '!=', 'GDG-EXT')->orderBy('nama')->get();
        return view('admin.users.create', compact('gudangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'lowercase', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:admin,operator_gudang,security,manager,penerima'],
            'gudang_id' => ['nullable', 'exists:gudangs,id'],
            'gudang_ids' => [Rule::requiredIf($request->role === 'manager'), 'array', 'min:1'],
            'gudang_ids.*' => ['integer', 'exists:gudangs,id'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:20'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->username . '@egudang.local', // Generate dummy email
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'gudang_id' => $request->role === 'manager' ? null : $request->gudang_id,
            'jabatan' => $request->jabatan,
            'no_hp' => $request->no_hp,
            'is_active' => true,
        ]);

        if ($request->role === 'manager') {
            $user->managedGudangs()->sync($request->input('gudang_ids', []));
        }

        return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $user->load('managedGudangs');
        $gudangs = Gudang::where('kode', '!=', 'GDG-EXT')->orderBy('nama')->get();
        return view('admin.users.edit', compact('user', 'gudangs'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'lowercase', 'max:255', 'unique:users,username,'.$user->id],
            'role' => ['required', 'in:admin,operator_gudang,security,manager,penerima'],
            'gudang_id' => ['nullable', 'exists:gudangs,id'],
            'gudang_ids' => [Rule::requiredIf($request->role === 'manager'), 'array', 'min:1'],
            'gudang_ids.*' => ['integer', 'exists:gudangs,id'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
        ]);

        $data = $request->except(['password', 'gudang_ids']);

        // Admin tidak bisa dinonaktifkan
        if ($user->role === 'admin') {
            $data['is_active'] = true;
        }

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

        if ($request->role === 'manager') {
            $data['gudang_id'] = null;
        }

        $user->update($data);

        if ($request->role === 'manager') {
            $user->managedGudangs()->sync($request->input('gudang_ids', []));
        } else {
            $user->managedGudangs()->detach();
        }

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
