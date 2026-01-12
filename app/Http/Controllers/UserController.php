<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pic;
use App\Models\Gudang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
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

        // 3. Logika Filter Gudang (user gudang langsung atau manager gudang)
        if ($request->filled('gudang_id')) {
            $gudangId = $request->gudang_id;
            $query->where(function ($q) use ($gudangId) {
                $q->where('gudang_id', $gudangId)
                    ->orWhereHas('managedGudangs', function ($managedQuery) use ($gudangId) {
                        $managedQuery->where('gudangs.id', $gudangId);
                    });
            });
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
        // Gudang wajib untuk semua role kecuali admin dan manager
        $gudangRequired = !in_array($request->role, ['admin', 'manager']);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'lowercase', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:admin,operator_gudang,security,manager,penerima'],
            'gudang_id' => [$gudangRequired ? 'required' : 'nullable', 'exists:gudangs,id'],
            'gudang_ids' => [Rule::requiredIf($request->role === 'manager'), 'array', 'min:1'],
            'gudang_ids.*' => ['integer', 'exists:gudangs,id'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:20'],
        ], [
            'gudang_id.required' => 'Lokasi gudang wajib dipilih untuk role ini.',
        ]);

        $user = DB::transaction(function () use ($validated) {
            $gudangId = $validated['role'] === 'manager'
                ? null
                : ($validated['gudang_id'] ?? null);

            $user = User::create([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['username'] . '@egudang.local', // Generate dummy email
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'gudang_id' => $gudangId,
                'jabatan' => $validated['jabatan'] ?? null,
                'no_hp' => $validated['no_hp'] ?? null,
                'is_active' => true,
            ]);

            if ($validated['role'] === 'manager') {
                $user->managedGudangs()->sync($validated['gudang_ids'] ?? []);
            }

            if (in_array($validated['role'], ['operator_gudang', 'penerima'], true)) {
                Pic::create([
                    'nama' => $validated['name'],
                    'jabatan' => $validated['jabatan'] ?? null,
                    'no_hp' => $validated['no_hp'] ?? null,
                    'gudang_id' => $gudangId,
                    'user_id' => $user->id,
                ]);
            }

            return $user;
        });

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
        // Gudang wajib untuk semua role kecuali admin dan manager
        $gudangRequired = !in_array($request->role, ['admin', 'manager']);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'lowercase', 'max:255', 'unique:users,username,'.$user->id],
            'role' => ['required', 'in:admin,operator_gudang,security,manager,penerima'],
            'gudang_id' => [$gudangRequired ? 'required' : 'nullable', 'exists:gudangs,id'],
            'gudang_ids' => [Rule::requiredIf($request->role === 'manager'), 'array', 'min:1'],
            'gudang_ids.*' => ['integer', 'exists:gudangs,id'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
        ], [
            'gudang_id.required' => 'Lokasi gudang wajib dipilih untuk role ini.',
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

        if (in_array($user->role, ['operator_gudang', 'penerima'], true)) {
            $pic = Pic::firstOrNew(['user_id' => $user->id]);
            $pic->fill([
                'nama' => $user->name,
                'jabatan' => $user->jabatan,
                'no_hp' => $user->no_hp,
                'gudang_id' => $user->gudang_id,
            ]);
            $pic->save();
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
