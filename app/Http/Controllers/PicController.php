<?php

namespace App\Http\Controllers;

use App\Models\Pic;
use App\Models\Gudang;
use Illuminate\Http\Request;

class PicController extends Controller
{
    public function index(Request $request)
    {
        $query = Pic::with('gudang');

        // Logika Search (Cari berdasarkan Nama, Jabatan, atau No HP)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('jabatan', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%");
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
            'jabatan' => ['nullable', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'gudang_id' => ['nullable', 'exists:gudangs,id'],
        ]);

        Pic::create([
            'nama' => $request->nama,
            'jabatan' => $request->jabatan,
            'no_hp' => $request->no_hp,
            'gudang_id' => $request->gudang_id,
        ]);

        return redirect()->route('admin.pics.index')->with('success', 'PIC berhasil ditambahkan.');
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

        return redirect()->route('admin.pics.index')->with('success', 'PIC berhasil diperbarui.');
    }

    public function destroy(Pic $pic)
    {
        $pic->delete();
        return redirect()->route('admin.pics.index')->with('success', 'PIC berhasil dihapus.');
    }
}
