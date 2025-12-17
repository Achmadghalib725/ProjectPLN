<?php

namespace App\Http\Controllers;

use App\Http\Requests\ItemStoreRequest;
use App\Http\Requests\ItemUpdateRequest;
use App\Models\Item;
use App\Models\ItemStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $kategori = $request->input('kategori');

        $items = Item::query()
            ->when($search, function ($query, $search) {
                $query->where('nama', 'like', "%{$search}%")
                    ->orWhere('kode', 'like', "%{$search}%");
            })
            ->when($kategori, function ($query, $kategori) {
                $query->where('kategori', $kategori);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Get distinct categories for filter
        $categories = Item::distinct()->pluck('kategori')->filter();

        // Statistics
        $totalItems = Item::count();
        $totalCategories = Item::distinct('kategori')->count('kategori');

        return view('admin.items.index', compact('items', 'categories', 'totalItems', 'totalCategories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.items.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ItemStoreRequest $request)
    {
        try {
            $data = $request->validated();

            // Handle file upload
            if ($request->hasFile('gambar')) {
                $file = $request->file('gambar');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('items', $filename, 'public');
                $data['gambar_path'] = $path;
            }

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
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $item = Item::with('stocks.gudang')->findOrFail($id);

        // Get stock info across all warehouses
        $totalStockAcrossWarehouses = $item->stocks->sum('jumlah');
        $warehouseCount = $item->stocks->count();

        return view('admin.items.show', compact('item', 'totalStockAcrossWarehouses', 'warehouseCount'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $item = Item::findOrFail($id);
        return view('admin.items.edit', compact('item'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ItemUpdateRequest $request, string $id)
    {
        try {
            $item = Item::findOrFail($id);
            $data = $request->validated();

            // Handle file upload
            if ($request->hasFile('gambar')) {
                // Delete old image if exists
                if ($item->gambar_path) {
                    Storage::disk('public')->delete($item->gambar_path);
                }

                $file = $request->file('gambar');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('items', $filename, 'public');
                $data['gambar_path'] = $path;
            }

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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $item = Item::findOrFail($id);

            // Check if item is used in any warehouse
            $stockCount = ItemStock::where('item_id', $id)->count();
            if ($stockCount > 0) {
                return redirect()->back()
                    ->with('error', "Item tidak dapat dihapus karena masih digunakan di {$stockCount} gudang. Hapus stok dari gudang terlebih dahulu.");
            }

            // Delete image if exists
            if ($item->gambar_path) {
                Storage::disk('public')->delete($item->gambar_path);
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
