<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemUnit;
use Illuminate\Http\Request;

class ItemMetaController extends Controller
{
    /**
     * Get all categories with item count.
     */
    public function categories()
    {
        $categories = ItemCategory::orderBy('nama')->get()->map(function ($cat) {
            return [
                'id' => $cat->id,
                'nama' => $cat->nama,
                'items_count' => $cat->items_count,
            ];
        });

        return response()->json($categories);
    }

    /**
     * Store a new category.
     */
    public function storeCategory(Request $request)
    {
        $nama = strtolower(trim($request->nama ?? ''));

        if (empty($nama)) {
            return response()->json([
                'success' => false,
                'message' => 'Nama kategori harus diisi',
            ], 422);
        }

        // Check if already exists (case insensitive)
        $exists = ItemCategory::whereRaw('LOWER(nama) = ?', [$nama])->exists();
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori "' . $nama . '" sudah ada',
            ], 422);
        }

        $category = ItemCategory::create(['nama' => $nama]);

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil ditambahkan',
            'data' => [
                'id' => $category->id,
                'nama' => $category->nama,
                'items_count' => 0,
            ],
        ]);
    }

    /**
     * Delete a category.
     */
    public function destroyCategory(ItemCategory $category)
    {
        $count = Item::where('kategori_id', $category->id)->count();

        if ($count > 0) {
            return response()->json([
                'success' => false,
                'message' => "Tidak dapat dihapus, digunakan oleh {$count} item",
            ], 422);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil dihapus',
        ]);
    }

    /**
     * Get all units with item count.
     */
    public function units()
    {
        $units = ItemUnit::orderBy('nama')->get()->map(function ($unit) {
            return [
                'id' => $unit->id,
                'nama' => $unit->nama,
                'items_count' => $unit->items_count,
            ];
        });

        return response()->json($units);
    }

    /**
     * Store a new unit.
     */
    public function storeUnit(Request $request)
    {
        $nama = strtolower(trim($request->nama ?? ''));

        if (empty($nama)) {
            return response()->json([
                'success' => false,
                'message' => 'Nama satuan harus diisi',
            ], 422);
        }

        // Check if already exists (case insensitive)
        $exists = ItemUnit::whereRaw('LOWER(nama) = ?', [$nama])->exists();
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Satuan "' . $nama . '" sudah ada',
            ], 422);
        }

        $unit = ItemUnit::create(['nama' => $nama]);

        return response()->json([
            'success' => true,
            'message' => 'Satuan berhasil ditambahkan',
            'data' => [
                'id' => $unit->id,
                'nama' => $unit->nama,
                'items_count' => 0,
            ],
        ]);
    }

    /**
     * Delete a unit.
     */
    public function destroyUnit(ItemUnit $unit)
    {
        $count = Item::where('satuan_id', $unit->id)->count();

        if ($count > 0) {
            return response()->json([
                'success' => false,
                'message' => "Tidak dapat dihapus, digunakan oleh {$count} item",
            ], 422);
        }

        $unit->delete();

        return response()->json([
            'success' => true,
            'message' => 'Satuan berhasil dihapus',
        ]);
    }
}
