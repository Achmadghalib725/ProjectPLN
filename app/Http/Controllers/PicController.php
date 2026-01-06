<?php

namespace App\Http\Controllers;

use App\Models\Pic;
use App\Models\Gudang;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PicController extends Controller
{
    public function index(Request $request)
    {
        $query = Pic::with('gudang');

        // Logika Search (Cari berdasarkan Nama, Jabatan, atau No HP) - Case Insensitive
        if ($request->filled('search')) {
            $searchLower = strtolower($request->search);
            $query->where(function($q) use ($searchLower) {
                $q->whereRaw('LOWER(nama) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereRaw('LOWER(jabatan) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereRaw('LOWER(no_hp) LIKE ?', ["%{$searchLower}%"]);
            });
        }

        // Filter berdasarkan Gudang
        if ($request->filled('gudang_id')) {
            $query->where('gudang_id', $request->gudang_id);
        }

        $pics = $query->latest()->paginate(10)->appends($request->all());
        $gudangs = Gudang::all();

        return view('admin.pics.index', compact('pics', 'gudangs'));
    }

    public function create()
    {
        $gudangs = Gudang::all();
        return view('admin.pics.create', compact('gudangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'lowercase', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'gudang_id' => ['nullable', 'exists:gudangs,id'],
        ]);

        $userJabatan = $request->jabatan;
        $username = trim((string) $request->username);

        DB::transaction(function () use ($request, $userJabatan, $username) {
            $user = User::create([
                'name' => $request->nama,
                'username' => $username,
                'email' => $username . '@egudang.local',
                'password' => $request->password,
                'role' => 'lainnya',
                'gudang_id' => $request->gudang_id,
                'jabatan' => $userJabatan,
                'no_hp' => $request->no_hp,
                'is_active' => true,
            ]);

            Pic::create([
                'nama' => $request->nama,
                'jabatan' => $request->jabatan,
                'no_hp' => $request->no_hp,
                'gudang_id' => $request->gudang_id,
                'user_id' => $user->id,
            ]);
        });

        return redirect()
            ->route('admin.pics.index')
            ->with('success', 'PIC berhasil ditambahkan dan akun login dibuat.');
    }

    public function edit(Pic $pic)
    {
        $gudangs = Gudang::all();
        return view('admin.pics.edit', compact('pic', 'gudangs'));
    }

    public function update(Request $request, Pic $pic)
    {
        $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'gudang_id' => ['nullable', 'exists:gudangs,id'],
        ]);

        $pic->update([
            'nama' => $request->nama,
            'jabatan' => $request->jabatan,
            'no_hp' => $request->no_hp,
            'gudang_id' => $request->gudang_id,
        ]);

        if ($pic->user) {
            $userJabatan = $request->jabatan;
            $pic->user->update([
                'name' => $request->nama,
                'jabatan' => $userJabatan,
                'no_hp' => $request->no_hp,
                'gudang_id' => $request->gudang_id,
            ]);
        }

        return redirect()->route('admin.pics.index')->with('success', 'PIC berhasil diperbarui.');
    }

    public function destroy(Pic $pic)
    {
        $pic->delete();
        return redirect()->route('admin.pics.index')->with('success', 'PIC berhasil dihapus.');
    }

}
