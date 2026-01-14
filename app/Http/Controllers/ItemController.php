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
        $kategoriId = $request->input('kategori');

        $items = Item::query()
            ->with(['kategori', 'satuan'])
            ->when($search, function ($query, $search) {
                $searchLower = strtolower($search);
                $query->where(function ($q) use ($searchLower) {
                    $q->whereRaw('LOWER(nama) LIKE ?', ["%{$searchLower}%"])
                      ->orWhereRaw('LOWER(kode) LIKE ?', ["%{$searchLower}%"]);
                });
            })
            ->when($kategoriId, function ($query, $kategoriId) {
                $query->where('kategori_id', $kategoriId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(25)->onEachSide(1)
            ->withQueryString();

        // Mengambil kategori dari item_categories
        $categories = ItemCategory::orderBy('nama')->get();

        // Mengambil satuan dari item_units
        $satuans = ItemUnit::orderBy('nama')->get();

        // All items untuk pencarian duplikat
        $allItems = Item::with(['kategori', 'satuan'])
            ->select('id', 'nama', 'kode', 'kategori_id', 'satuan_id', 'tipe', 'deskripsi')
            ->orderBy('nama')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama' => $item->nama,
                    'kode' => $item->kode,
                    'kategori_id' => $item->kategori_id,
                    'kategori' => $item->kategori?->nama,
                    'satuan_id' => $item->satuan_id,
                    'satuan' => $item->satuan?->nama,
                    'tipe' => $item->tipe,
                    'deskripsi' => $item->deskripsi,
                ];
            });

        // Statistik untuk Dashboard Master Barang
        $totalItems = Item::count();
        $totalCategories = $categories->count();

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
