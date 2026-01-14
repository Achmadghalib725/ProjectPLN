<?php

namespace App\Http\Controllers;

use App\Http\Requests\ItemStoreRequest;
use App\Models\Item;

class GudangItemController extends Controller
{
    /**
     * Store a newly created item.
     */
    public function store(ItemStoreRequest $request)
    {
        $searchTerm = trim((string) $request->input('search_term', ''));
        if ($searchTerm === '') {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Wajib melakukan pencarian terlebih dahulu sebelum menambahkan item baru.');
        }

        $searchLower = strtolower($searchTerm);
        $hasMatch = Item::query()
            ->whereRaw('LOWER(nama) LIKE ?', ["%{$searchLower}%"])
            ->orWhereRaw('LOWER(kode) LIKE ?', ["%{$searchLower}%"])
            ->exists();

        if ($hasMatch) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Item serupa sudah ditemukan. Gunakan item yang sudah ada.');
        }

        try {
            $data = $request->validated();

            Item::create($data);

            return redirect()
                ->route('gudang.stok.index')
                ->with('success', 'Item baru berhasil ditambahkan. Silakan tambahkan ke stok gudang.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan item: ' . $e->getMessage());
        }
    }
}
