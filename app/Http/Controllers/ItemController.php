<?php

namespace App\Http\Controllers;

use App\Http\Requests\ItemStoreRequest;
use App\Http\Requests\ItemUpdateRequest;
use App\Models\Item;
use App\Models\ItemStock;
use App\Models\ItemCategory;
use App\Models\ItemUnit;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    /**
     * Menampilkan daftar item dengan fitur pencarian dan filter.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $kategori = $request->input('kategori');

        $items = Item::query()
            ->when($search, function ($query, $search) {
                $searchLower = strtolower($search);
                $query->where(function ($q) use ($searchLower) {
                    $q->whereRaw('LOWER(nama) LIKE ?', ["%{$searchLower}%"])
                      ->orWhereRaw('LOWER(kode) LIKE ?', ["%{$searchLower}%"]);
                });
            })
            ->when($kategori, function ($query, $kategori) {
                $query->whereRaw('LOWER(kategori) = ?', [strtolower($kategori)]);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15)->onEachSide(1)
            ->withQueryString();

        // Mengambil kategori dari tabel item_categories untuk filter
        $categories = ItemCategory::orderBy('nama')->pluck('nama');

        // Mengambil satuan dari tabel item_units untuk dropdown
        $satuans = ItemUnit::orderBy('nama')->pluck('nama');

        $allItems = Item::select('id', 'nama', 'kode', 'kategori', 'satuan', 'deskripsi')
            ->orderBy('nama')
            ->get();

        // Statistik untuk Dashboard Master Barang
        $totalItems = Item::count();
        $totalCategories = ItemCategory::count();

        return view('admin.items.index', compact('items', 'categories', 'satuans', 'allItems', 'totalItems', 'totalCategories'));
    }

    /**
     * Menampilkan form tambah item.
     * Mengambil data $items agar dropdown kategori & satuan bisa muncul.
     */
    public function create()
    {
        return redirect()->route('admin.items.index');
    }

    /**
     * Menyimpan item baru ke database.
     */
    public function store(ItemStoreRequest $request)
    {
        try {
            $searchTerm = trim((string) $request->input('search_term', ''));
            if ($searchTerm === '') {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['search_term' => 'Wajib melakukan pencarian terlebih dahulu sebelum menambahkan item baru.']);
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
                    ->withErrors(['search_term' => 'Item serupa sudah ditemukan. Gunakan item yang sudah ada.']);
            }

            $data = $request->validated();

            // Normalisasi kategori dan satuan ke lowercase untuk konsistensi
            $data['kategori'] = strtolower($data['kategori']);
            $data['satuan'] = strtolower($data['satuan']);

            Item::create($data);

            return redirect()->route('admin.items.index')
                ->with('success', 'Item berhasil ditambahkan ke master data');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan item: ' . $e->getMessage());
        }
    }


    /**
     * Menampilkan detail item tertentu.
     */
    public function show(string $id)
    {
        $item = Item::with('stocks.gudang')->findOrFail($id);

        // Menghitung total stok di seluruh gudang
        $totalStockAcrossWarehouses = $item->stocks->sum('jumlah');
        $warehouseCount = $item->stocks->count();

        return view('admin.items.show', compact('item', 'totalStockAcrossWarehouses', 'warehouseCount'));
    }

    /**
     * Redirect ke halaman index (edit menggunakan modal).
     */
    public function edit(string $id)
    {
        return redirect()->route('admin.items.index');
    }

    /**
     * Memperbarui data item di database.
     */
    public function update(ItemUpdateRequest $request, string $id)
    {
        try {
            $item = Item::findOrFail($id);
            $data = $request->validated();

            // Normalisasi kategori dan satuan ke lowercase untuk konsistensi
            $data['kategori'] = strtolower($data['kategori']);
            $data['satuan'] = strtolower($data['satuan']);

            $item->update($data);

            return redirect()->route('admin.items.index')
                ->with('success', 'Item berhasil diupdate');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mengupdate item: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus item dari master data.
     */
    public function destroy(string $id)
    {
        try {
            $item = Item::findOrFail($id);

            // Validasi: Cek apakah item masih memiliki stok di gudang manapun
            $stockCount = ItemStock::where('item_id', $id)->count();
            if ($stockCount > 0) {
                return redirect()->back()
                    ->with('error', "Item tidak dapat dihapus karena masih digunakan di {$stockCount} gudang. Hapus stok dari gudang terlebih dahulu.");
            }

            $item->delete();

            return redirect()->route('admin.items.index')
                ->with('success', 'Item berhasil dihapus dari master data');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus item: ' . $e->getMessage());
        }
    }
}
