<?php

namespace App\Http\Controllers;

use App\Http\Requests\StokStoreRequest;
use App\Http\Requests\StokUpdateRequest;
use App\Models\Item;
use App\Models\ItemStock;
use App\Models\Peminjaman;
use App\Models\PeminjamanItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StokController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $gudangId = $this->getGudangId();
        $search = $request->input('search');
        $kategori = $request->input('kategori');

        $stocks = ItemStock::with(['item', 'gudang'])
            ->where('gudang_id', $gudangId)
            ->when($search, function ($query, $search) {
                $searchLower = strtolower($search);
                $query->whereHas('item', function ($q) use ($searchLower) {
                    $q->whereRaw('LOWER(nama) LIKE ?', ["%{$searchLower}%"])
                        ->orWhereRaw('LOWER(kode) LIKE ?', ["%{$searchLower}%"]);
                });
            })
            ->when($kategori, function ($query, $kategori) {
                $query->whereHas('item', function ($q) use ($kategori) {
                    $q->where('kategori', $kategori);
                });
            })
            ->paginate(15)
            ->withQueryString();

        $borrowedTotals = collect();
        if ($stocks->count() > 0) {
            $itemIds = $stocks->pluck('item_id')->unique()->values();
            $borrowedTotals = PeminjamanItem::query()
                ->select('peminjaman_items.item_id', DB::raw('SUM(peminjaman_items.jumlah_dipinjam) as total'))
                ->join('peminjamans', 'peminjaman_items.peminjaman_id', '=', 'peminjamans.id')
                ->where('peminjamans.gudang_peminjam_id', $gudangId)
                ->whereNotIn('peminjamans.status', ['SELESAI', 'DITOLAK'])
                ->where(function ($query) {
                    $query->whereNotNull('peminjamans.waktu_diterima')
                        ->orWhereNotNull('peminjamans.waktu_ttd_penerima');
                })
                ->whereIn('peminjaman_items.item_id', $itemIds)
                ->groupBy('peminjaman_items.item_id')
                ->pluck('total', 'peminjaman_items.item_id');
        }

        $stocks->getCollection()->transform(function ($stock) use ($borrowedTotals) {
            $borrowed = (int) ($borrowedTotals[$stock->item_id] ?? 0);
            $stock->borrowed_qty = $borrowed;
            $stock->own_qty = max(0, (int) $stock->jumlah - $borrowed);
            return $stock;
        });

        $lowStockCount = ItemStock::where('gudang_id', $gudangId)
            ->whereColumn('jumlah', '<', 'stok_minimum')
            ->count();

        $totalItems = ItemStock::where('gudang_id', $gudangId)->count();
        $totalUnits = ItemStock::where('gudang_id', $gudangId)->sum('jumlah');

        // Get available categories for filter
        $categories = Item::distinct()->pluck('kategori')->filter();

        // Get items NOT yet in this warehouse (for create modal)
        $existingItemIds = ItemStock::where('gudang_id', $gudangId)->pluck('item_id');
        $availableItems = Item::whereNotIn('id', $existingItemIds)->get();

        return view('gudang.stok.index', compact('stocks', 'lowStockCount', 'totalItems', 'totalUnits', 'categories', 'availableItems'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $gudangId = $this->getGudangId();

        // Get items NOT yet in this warehouse
        $existingItemIds = ItemStock::where('gudang_id', $gudangId)->pluck('item_id');
        $availableItems = Item::whereNotIn('id', $existingItemIds)->get();

        return view('gudang.stok.create', compact('availableItems'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StokStoreRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $gudangId = $this->getGudangId();

                // Create ItemStock
                $stock = ItemStock::create([
                    'item_id' => $request->item_id,
                    'gudang_id' => $gudangId,
                    'jumlah' => $request->jumlah,
                    'stok_minimum' => $request->stok_minimum
                ]);

                // Log to StockMovement
                $this->logStockMovement(
                    $request->item_id,
                    $gudangId,
                    'IN',
                    $request->jumlah,
                    0,
                    $request->jumlah,
                    'StokBaru',
                    $request->keterangan ?? 'Penambahan stok baru'
                );
            });

            return redirect()->route('gudang.stok.index')
                ->with('success', 'Item berhasil ditambahkan ke inventaris gudang');
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
        $stock = ItemStock::with(['item', 'gudang'])->findOrFail($id);

        // Security check
        $this->verifyStockOwnership($stock);

        $borrowedQty = PeminjamanItem::query()
            ->join('peminjamans', 'peminjaman_items.peminjaman_id', '=', 'peminjamans.id')
            ->where('peminjamans.gudang_peminjam_id', $stock->gudang_id)
            ->where('peminjaman_items.item_id', $stock->item_id)
            ->whereNotIn('peminjamans.status', ['SELESAI', 'DITOLAK'])
            ->where(function ($query) {
                $query->whereNotNull('peminjamans.waktu_diterima')
                    ->orWhereNotNull('peminjamans.waktu_ttd_penerima');
            })
            ->sum('peminjaman_items.jumlah_dipinjam');

        $stock->borrowed_qty = (int) $borrowedQty;
        $stock->own_qty = max(0, (int) $stock->jumlah - $stock->borrowed_qty);

        // Get movement history
        $movements = StockMovement::with('creator')
            ->where('item_id', $stock->item_id)
            ->where('gudang_id', $stock->gudang_id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('gudang.stok.show', compact('stock', 'movements'));
    }

    /**
     * Display all stock movement history for the warehouse
     */
    public function riwayat(Request $request)
    {
        $gudangId = $this->getGudangId();
        $search = $request->input('search');
        $tipe = $request->input('tipe');
        $referensi = $request->input('referensi');

        $movements = StockMovement::with(['item', 'gudang', 'creator'])
            ->where('gudang_id', $gudangId)
            ->when($search, function ($query, $search) {
                $searchLower = strtolower($search);
                $query->where(function ($q) use ($searchLower) {
                    $q->whereHas('item', function ($itemQuery) use ($searchLower) {
                        $itemQuery->whereRaw('LOWER(nama) LIKE ?', ["%{$searchLower}%"])
                            ->orWhereRaw('LOWER(kode) LIKE ?', ["%{$searchLower}%"]);
                    })
                    ->orWhereRaw('LOWER(keterangan) LIKE ?', ["%{$searchLower}%"])
                    ->orWhereHas('creator', function ($userQuery) use ($searchLower) {
                        $userQuery->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"]);
                    });
                });
            })
            ->when($tipe, function ($query, $tipe) {
                $query->where('tipe', $tipe);
            })
            ->when($referensi, function ($query, $referensi) {
                $query->where('referensi_type', $referensi);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Get available referensi types for filter
        $referensiTypes = StockMovement::where('gudang_id', $gudangId)
            ->distinct()
            ->pluck('referensi_type')
            ->filter();

        return view('gudang.riwayat', compact('movements', 'referensiTypes'));
    }

    /**
     * Display items lent out to other warehouses (Barang Dipinjamkan)
     */
    public function barangDipinjamkan(Request $request)
    {
        $gudangId = $this->getGudangId();
        $search = $request->input('search');
        $status = $request->input('status');

        // Get peminjaman where this warehouse is the owner (pemilik)
        $activeStatuses = ['DIKIRIM', 'DIPERIKSA', 'DITERIMA', 'DIKEMBALIKAN', 'MENUNGGU_DIKEMBALIKAN'];
        $peminjamans = Peminjaman::with([
                'items.item',
                'gudangPeminjam',
                'gudangPemilik',
                'suratJalanKirim',
                'suratJalanKembali'
            ])
            ->where('gudang_pemilik_id', $gudangId)
            ->whereIn('status', $activeStatuses)
            ->when($search, function ($query, $search) {
                $searchLower = strtolower($search);
                $query->where(function ($q) use ($searchLower) {
                    $q->whereRaw('LOWER(kode) LIKE ?', ["%{$searchLower}%"])
                        ->orWhereHas('gudangPeminjam', function ($gq) use ($searchLower) {
                            $gq->whereRaw('LOWER(nama) LIKE ?', ["%{$searchLower}%"]);
                        })
                        ->orWhere(function ($gq) use ($searchLower) {
                            $gq->where('gudang_peminjam_is_custom', true)
                                ->whereRaw('LOWER(gudang_peminjam_custom_nama) LIKE ?', ["%{$searchLower}%"]);
                        })
                        ->orWhereHas('items.item', function ($iq) use ($searchLower) {
                            $iq->whereRaw('LOWER(nama) LIKE ?', ["%{$searchLower}%"])
                                ->orWhereRaw('LOWER(kode) LIKE ?', ["%{$searchLower}%"]);
                        });
                });
            })
            ->when($status, function ($query, $status) {
                if ($status === 'overdue') {
                    $query->whereNotNull('batas_waktu_kembali')
                        ->where('batas_waktu_kembali', '<', now())
                        ->whereIn('status', ['DITERIMA']);
                } else {
                    $query->where('status', $status);
                }
            })
            ->orderByDesc('waktu_kirim')
            ->paginate(15)
            ->withQueryString();

        // Statistics
        $totalAktif = Peminjaman::where('gudang_pemilik_id', $gudangId)
            ->whereIn('status', $activeStatuses)
            ->count();

        $totalOverdue = Peminjaman::where('gudang_pemilik_id', $gudangId)
            ->whereNotNull('batas_waktu_kembali')
            ->where('batas_waktu_kembali', '<', now())
            ->whereIn('status', ['DITERIMA'])
            ->count();

        return view('gudang.stok.barang-dipinjamkan', compact('peminjamans', 'totalAktif', 'totalOverdue'));
    }

    /**
     * Display items borrowed from other warehouses (Barang Pinjaman)
     */
    public function barangPinjaman(Request $request)
    {
        $gudangId = $this->getGudangId();
        $search = $request->input('search');
        $status = $request->input('status');

        // Get peminjaman where this warehouse is the borrower (peminjam)
        $peminjamans = Peminjaman::with([
                'items.item',
                'gudangPeminjam',
                'gudangPemilik',
                'suratJalanKirim',
                'suratJalanKembali'
            ])
            ->where('gudang_peminjam_id', $gudangId)
            ->whereIn('status', ['DIKIRIM', 'DIPERIKSA', 'DITERIMA', 'DIKEMBALIKAN'])
            ->when($search, function ($query, $search) {
                $searchLower = strtolower($search);
                $query->where(function ($q) use ($searchLower) {
                    $q->whereRaw('LOWER(kode) LIKE ?', ["%{$searchLower}%"])
                        ->orWhereHas('gudangPemilik', function ($gq) use ($searchLower) {
                            $gq->whereRaw('LOWER(nama) LIKE ?', ["%{$searchLower}%"]);
                        })
                        ->orWhereHas('items.item', function ($iq) use ($searchLower) {
                            $iq->whereRaw('LOWER(nama) LIKE ?', ["%{$searchLower}%"])
                                ->orWhereRaw('LOWER(kode) LIKE ?', ["%{$searchLower}%"]);
                        });
                });
            })
            ->when($status, function ($query, $status) {
                if ($status === 'overdue') {
                    $query->whereNotNull('batas_waktu_kembali')
                        ->where('batas_waktu_kembali', '<', now())
                        ->whereIn('status', ['DITERIMA']);
                } else {
                    $query->where('status', $status);
                }
            })
            ->orderByDesc('waktu_kirim')
            ->paginate(15)
            ->withQueryString();

        // Statistics
        $totalAktif = Peminjaman::where('gudang_peminjam_id', $gudangId)
            ->whereIn('status', ['DIKIRIM', 'DIPERIKSA', 'DITERIMA', 'DIKEMBALIKAN'])
            ->count();

        $totalOverdue = Peminjaman::where('gudang_peminjam_id', $gudangId)
            ->whereNotNull('batas_waktu_kembali')
            ->where('batas_waktu_kembali', '<', now())
            ->whereIn('status', ['DITERIMA'])
            ->count();

        return view('gudang.stok.barang-pinjaman', compact('peminjamans', 'totalAktif', 'totalOverdue'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $stock = ItemStock::with('item')->findOrFail($id);

        // Security check
        $this->verifyStockOwnership($stock);

        return view('gudang.stok.edit', compact('stock'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StokUpdateRequest $request, string $id)
    {
        try {
            DB::transaction(function () use ($request, $id) {
                $stock = ItemStock::lockForUpdate()->findOrFail($id);

                // Security check
                $this->verifyStockOwnership($stock);

                $stokSebelum = $stock->jumlah;
                $adjustment = $request->adjustment_quantity;

                // Check if there's a stock adjustment or just stok_minimum update
                if ($adjustment && $adjustment > 0) {
                    // Calculate new stock
                    if ($request->adjustment_type === 'add') {
                        $stokSesudah = $stokSebelum + $adjustment;
                        $tipe = 'IN';
                    } else {
                        $stokSesudah = $stokSebelum - $adjustment;
                        $tipe = 'OUT';

                        // Prevent negative stock
                        if ($stokSesudah < 0) {
                            throw new \Exception('Stok tidak boleh negatif. Jumlah pengurangan melebihi stok tersedia.');
                        }
                    }

                    // Update stock with adjustment
                    $stock->update([
                        'jumlah' => $stokSesudah,
                        'stok_minimum' => $request->stok_minimum
                    ]);

                    // Log movement
                    $this->logStockMovement(
                        $stock->item_id,
                        $stock->gudang_id,
                        $tipe,
                        $adjustment,
                        $stokSebelum,
                        $stokSesudah,
                        'PenyesuaianManual',
                        $request->keterangan
                    );
                } else {
                    // Only update stok_minimum (no stock adjustment)
                    $stock->update([
                        'stok_minimum' => $request->stok_minimum
                    ]);
                }
            });

            return redirect()->route('gudang.stok.index')
                ->with('success', 'Data stok berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::transaction(function () use ($id) {
                $stock = ItemStock::findOrFail($id);

                // Security check
                $this->verifyStockOwnership($stock);

                // Can only delete if stock is 0
                if ($stock->jumlah > 0) {
                    throw new \Exception('Tidak dapat menghapus stok dengan jumlah > 0. Sesuaikan stok ke 0 terlebih dahulu.');
                }

                // Log deletion
                $this->logStockMovement(
                    $stock->item_id,
                    $stock->gudang_id,
                    'OUT',
                    0,
                    0,
                    0,
                    'HapusStok',
                    'Penghapusan item dari inventaris gudang'
                );

                $stock->delete();
            });

            return redirect()->route('gudang.stok.index')
                ->with('success', 'Item berhasil dihapus dari inventaris gudang');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Get operator's warehouse ID with security check
     *
     * @return int
     */
    private function getGudangId(): int
    {
        $gudangId = Auth::user()->gudang_id;

        if (!$gudangId) {
            abort(403, 'User tidak memiliki gudang yang ditugaskan');
        }

        return $gudangId;
    }

    /**
     * Verify stock ownership
     *
     * @param ItemStock $stock
     * @return void
     */
    private function verifyStockOwnership(ItemStock $stock): void
    {
        if ($stock->gudang_id !== $this->getGudangId()) {
            abort(403, 'Anda tidak berhak mengakses inventaris gudang lain');
        }
    }

    /**
     * Log stock movement to audit trail
     *
     * @param int $itemId
     * @param int $gudangId
     * @param string $tipe
     * @param int $jumlah
     * @param int $stokSebelum
     * @param int $stokSesudah
     * @param string $referensiType
     * @param string|null $keterangan
     * @return void
     */
    private function logStockMovement(
        int $itemId,
        int $gudangId,
        string $tipe,
        int $jumlah,
        int $stokSebelum,
        int $stokSesudah,
        string $referensiType,
        ?string $keterangan = null
    ): void {
        StockMovement::create([
            'item_id' => $itemId,
            'gudang_id' => $gudangId,
            'tipe' => $tipe,
            'jumlah' => $jumlah,
            'stok_sebelum' => $stokSebelum,
            'stok_sesudah' => $stokSesudah,
            'referensi_type' => $referensiType,
            'created_by' => Auth::id(),
            'keterangan' => $keterangan
        ]);
    }
}
