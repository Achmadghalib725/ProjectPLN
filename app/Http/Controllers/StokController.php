<?php

namespace App\Http\Controllers;

use App\Http\Requests\StokStoreRequest;
use App\Http\Requests\StokUpdateRequest;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemStock;
use App\Models\ItemUnit;
use App\Models\Peminjaman;
use App\Models\PeminjamanItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StokController extends Controller
{
    /**
     * Display a listing of the resource with tabs.
     */
    public function index(Request $request)
    {
        $gudangId = $this->getGudangId();
        $tab = $request->input('tab', 'stok');
        $search = $request->input('search');
        $kategori = $request->input('kategori');
        $status = $request->input('status');
        $tipe = $request->input('tipe'); // Filter by item tipe (mekanik/listrik)
        $sort = $request->input('sort', 'terbaru'); // Sorting: terbaru/terlama

        // Common data
        $lowStockCount = ItemStock::where('gudang_id', $gudangId)
            ->whereColumn('jumlah', '<', 'stok_minimum')
            ->count();
        $totalItems = ItemStock::where('gudang_id', $gudangId)->count();

        // Total unit yang sedang dipinjam dari gudang lain
        $totalBorrowed = PeminjamanItem::query()
            ->join('peminjamans', 'peminjaman_items.peminjaman_id', '=', 'peminjamans.id')
            ->where('peminjamans.gudang_peminjam_id', $gudangId)
            ->whereNotIn('peminjamans.status', ['SELESAI', 'DITOLAK'])
            ->where(function ($query) {
                $query->whereNotNull('peminjamans.waktu_diterima')
                    ->orWhereNotNull('peminjamans.waktu_ttd_penerima');
            })
            ->sum('peminjaman_items.jumlah_dipinjam');

        $categories = ItemCategory::orderBy('nama')->get();
        $satuans = ItemUnit::orderBy('nama')->get();
        $allItems = Item::with(['kategori', 'satuan'])
            ->select('id', 'nama', 'kode', 'kategori_id', 'satuan_id', 'tipe')
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
                ];
            });

        // Get items NOT yet in this warehouse (for create modal)
        $existingItemIds = ItemStock::where('gudang_id', $gudangId)->pluck('item_id');
        $availableItems = Item::whereNotIn('id', $existingItemIds)->get();

        // Count for tab badges
        $countDipinjamkan = Peminjaman::where('gudang_pemilik_id', $gudangId)
            ->whereIn('status', ['DIKIRIM', 'DIPERIKSA', 'DITERIMA', 'DIKEMBALIKAN', 'MENUNGGU_DIKEMBALIKAN'])
            ->count();
        $countPinjaman = Peminjaman::where('gudang_peminjam_id', $gudangId)
            ->whereIn('status', ['DIKIRIM', 'DIPERIKSA', 'DITERIMA', 'DIKEMBALIKAN', 'MENUNGGU_DIKEMBALIKAN'])
            ->count();

        // Tab-specific data
        if ($tab === 'dipinjamkan') {
            // Barang yang dipinjamkan ke gudang lain
            $peminjamans = $this->getBarangDipinjamkan($gudangId, $search, $status, $sort);
            $totalAktif = Peminjaman::where('gudang_pemilik_id', $gudangId)
                ->whereIn('status', ['DITERIMA', 'DIKEMBALIKAN', 'MENUNGGU_DIKEMBALIKAN'])
                ->count();
            $totalOverdue = Peminjaman::where('gudang_pemilik_id', $gudangId)
                ->whereIn('status', ['DITERIMA', 'DIKEMBALIKAN', 'MENUNGGU_DIKEMBALIKAN'])
                ->whereNotNull('batas_waktu_kembali')
                ->where('batas_waktu_kembali', '<', now())
                ->count();

            return view('gudang.stok.index', compact(
                'tab', 'peminjamans', 'totalAktif', 'totalOverdue',
                'lowStockCount', 'totalItems', 'totalBorrowed', 'categories', 'satuans', 'availableItems', 'allItems',
                'countDipinjamkan', 'countPinjaman'
            ));
        } elseif ($tab === 'pinjaman') {
            // Barang yang dipinjam dari gudang lain
            $peminjamans = $this->getBarangPinjaman($gudangId, $search, $status, $sort);
            $totalAktif = Peminjaman::where('gudang_peminjam_id', $gudangId)
                ->whereIn('status', ['DITERIMA', 'DIKEMBALIKAN'])
                ->count();
            $totalOverdue = Peminjaman::where('gudang_peminjam_id', $gudangId)
                ->whereIn('status', ['DITERIMA', 'DIKEMBALIKAN'])
                ->whereNotNull('batas_waktu_kembali')
                ->where('batas_waktu_kembali', '<', now())
                ->count();

            return view('gudang.stok.index', compact(
                'tab', 'peminjamans', 'totalAktif', 'totalOverdue',
                'lowStockCount', 'totalItems', 'totalBorrowed', 'categories', 'satuans', 'availableItems', 'allItems',
                'countDipinjamkan', 'countPinjaman'
            ));
        }

        // Default: Stok Gudang
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
                    $q->where('kategori_id', $kategori);
                });
            })
            ->when($tipe, function ($query, $tipe) {
                $query->whereHas('item', function ($q) use ($tipe) {
                    $q->where('tipe', $tipe);
                });
            })
            ->paginate(25)->onEachSide(1)
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

        return view('gudang.stok.index', compact(
            'tab', 'stocks', 'lowStockCount', 'totalItems', 'totalBorrowed', 'categories', 'satuans', 'availableItems', 'allItems',
            'countDipinjamkan', 'countPinjaman'
        ));
    }

    /**
     * Get barang dipinjamkan data
     */
    private function getBarangDipinjamkan(int $gudangId, ?string $search, ?string $status, string $sort = 'terbaru')
    {
        return Peminjaman::with(['gudangPeminjam', 'items.item'])
            ->where('gudang_pemilik_id', $gudangId)
            ->whereIn('status', ['DIKIRIM', 'DIPERIKSA', 'DITERIMA', 'DIKEMBALIKAN', 'MENUNGGU_DIKEMBALIKAN'])
            ->when($search, function ($query, $search) {
                $searchLower = strtolower($search);
                $query->where(function ($q) use ($searchLower) {
                    $q->whereRaw('LOWER(kode) LIKE ?', ["%{$searchLower}%"])
                        ->orWhereHas('gudangPeminjam', function ($gq) use ($searchLower) {
                            $gq->whereRaw('LOWER(nama) LIKE ?', ["%{$searchLower}%"]);
                        })
                        ->orWhereHas('items.item', function ($iq) use ($searchLower) {
                            $iq->whereRaw('LOWER(nama) LIKE ?', ["%{$searchLower}%"]);
                        });
                });
            })
            ->when($status, function ($query, $status) {
                if ($status === 'overdue') {
                    $query->whereIn('status', ['DITERIMA', 'DIKEMBALIKAN', 'MENUNGGU_DIKEMBALIKAN'])
                        ->whereNotNull('batas_waktu_kembali')
                        ->where('batas_waktu_kembali', '<', now());
                } else {
                    $query->where('status', $status);
                }
            })
            ->orderBy('created_at', $sort === 'terlama' ? 'asc' : 'desc')
            ->paginate(25)->onEachSide(1)
            ->withQueryString();
    }

    /**
     * Get barang pinjaman data
     */
    private function getBarangPinjaman(int $gudangId, ?string $search, ?string $status, string $sort = 'terbaru')
    {
        return Peminjaman::with(['gudangPemilik', 'items.item'])
            ->where('gudang_peminjam_id', $gudangId)
            ->whereIn('status', ['DIKIRIM', 'DIPERIKSA', 'DITERIMA', 'DIKEMBALIKAN', 'MENUNGGU_DIKEMBALIKAN'])
            ->when($search, function ($query, $search) {
                $searchLower = strtolower($search);
                $query->where(function ($q) use ($searchLower) {
                    $q->whereRaw('LOWER(kode) LIKE ?', ["%{$searchLower}%"])
                        ->orWhereHas('gudangPemilik', function ($gq) use ($searchLower) {
                            $gq->whereRaw('LOWER(nama) LIKE ?', ["%{$searchLower}%"]);
                        })
                        ->orWhereHas('items.item', function ($iq) use ($searchLower) {
                            $iq->whereRaw('LOWER(nama) LIKE ?', ["%{$searchLower}%"]);
                        });
                });
            })
            ->when($status, function ($query, $status) {
                if ($status === 'overdue') {
                    $query->whereIn('status', ['DITERIMA', 'DIKEMBALIKAN'])
                        ->whereNotNull('batas_waktu_kembali')
                        ->where('batas_waktu_kembali', '<', now());
                } else {
                    $query->where('status', $status);
                }
            })
            ->orderBy('created_at', $sort === 'terlama' ? 'asc' : 'desc')
            ->paginate(25)->onEachSide(1)
            ->withQueryString();
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
        $tab = $request->input('tab', 'pergerakan');
        $search = $request->input('search');

        $data = [
            'tab' => $tab,
            'search' => $search,
        ];

        if ($tab === 'pergerakan') {
            $data = array_merge($data, $this->getRiwayatPergerakan($gudangId, $request));
        } elseif ($tab === 'surat-jalan') {
            $data = array_merge($data, $this->getRiwayatSuratJalan($gudangId, $request));
        } elseif ($tab === 'peminjaman') {
            $data = array_merge($data, $this->getRiwayatPeminjaman($gudangId, $request));
        }

        return view('gudang.riwayat', $data);
    }

    /**
     * Get stock movement history data
     */
    private function getRiwayatPergerakan(int $gudangId, Request $request): array
    {
        $search = $request->input('search');
        $tipe = $request->input('tipe');
        $referensi = $request->input('referensi');
        $sort = $request->input('sort', 'terbaru');
        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai');

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
            ->when($tanggalMulai, function ($query, $tanggalMulai) {
                $query->whereDate('created_at', '>=', $tanggalMulai);
            })
            ->when($tanggalSelesai, function ($query, $tanggalSelesai) {
                $query->whereDate('created_at', '<=', $tanggalSelesai);
            })
            ->orderBy('created_at', $sort === 'terlama' ? 'asc' : 'desc')
            ->paginate(20)
            ->withQueryString();

        $referensiTypes = StockMovement::where('gudang_id', $gudangId)
            ->distinct()
            ->pluck('referensi_type')
            ->filter();

        return [
            'movements' => $movements,
            'referensiTypes' => $referensiTypes,
            'tipe' => $tipe,
            'referensi' => $referensi,
        ];
    }

    /**
     * Get surat jalan history data (only SELESAI status)
     */
    private function getRiwayatSuratJalan(int $gudangId, Request $request): array
    {
        $search = $request->input('search');
        $tipe = $request->input('tipe_sj');
        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai');
        $gudangAsal = $request->input('gudang_asal');
        $gudangTujuan = $request->input('gudang_tujuan');
        $sort = $request->input('sort', 'terbaru');

        $suratJalans = \App\Models\SuratJalan::with([
                'gudangAsal',
                'gudangTujuan',
                'pembuat',
                'picTujuan',
                'items.item',
                'peminjaman.suratJalanKembali:id,nomor',
                'peminjamanKembali.suratJalanKirim:id,nomor',
            ])
            ->withCount('items')
            ->withSum('items', 'jumlah')
            ->where(function ($query) use ($gudangId) {
                $query->where('gudang_asal_id', $gudangId)
                    ->orWhere('gudang_tujuan_id', $gudangId);
            })
            ->where('status', 'SELESAI') // Only show completed surat jalan
            ->when($search, function ($query, $search) {
                $searchLower = strtolower($search);
                $query->where(function ($q) use ($searchLower) {
                    $q->whereRaw('LOWER(nomor) LIKE ?', ["%{$searchLower}%"])
                        ->orWhereHas('gudangAsal', function ($gq) use ($searchLower) {
                            $gq->whereRaw('LOWER(nama) LIKE ?', ["%{$searchLower}%"]);
                        })
                        ->orWhereHas('gudangTujuan', function ($gq) use ($searchLower) {
                            $gq->whereRaw('LOWER(nama) LIKE ?', ["%{$searchLower}%"]);
                        })
                        ->orWhere(function ($gq) use ($searchLower) {
                            $gq->where('gudang_tujuan_is_custom', true)
                                ->whereRaw('LOWER(gudang_tujuan_custom_nama) LIKE ?', ["%{$searchLower}%"]);
                        });
                });
            })
            ->when($tipe, function ($query, $tipe) {
                $query->where('tipe', $tipe);
            })
            ->when($gudangAsal, function ($query, $gudangAsal) {
                $searchLower = strtolower($gudangAsal);
                $query->whereHas('gudangAsal', function ($gq) use ($searchLower) {
                    $gq->whereRaw('LOWER(nama) LIKE ?', ["%{$searchLower}%"]);
                });
            })
            ->when($gudangTujuan, function ($query, $gudangTujuan) {
                $searchLower = strtolower($gudangTujuan);
                $query->where(function ($q) use ($searchLower) {
                    $q->whereHas('gudangTujuan', function ($gq) use ($searchLower) {
                        $gq->whereRaw('LOWER(nama) LIKE ?', ["%{$searchLower}%"]);
                    })
                    ->orWhere(function ($gq) use ($searchLower) {
                        $gq->where('gudang_tujuan_is_custom', true)
                            ->whereRaw('LOWER(gudang_tujuan_custom_nama) LIKE ?', ["%{$searchLower}%"]);
                    });
                });
            })
            ->when($tanggalMulai, function ($query, $tanggalMulai) {
                $query->whereDate('tanggal', '>=', $tanggalMulai);
            })
            ->when($tanggalSelesai, function ($query, $tanggalSelesai) {
                $query->whereDate('tanggal', '<=', $tanggalSelesai);
            })
            ->orderBy('tanggal', $sort === 'terlama' ? 'asc' : 'desc')
            ->orderBy('created_at', $sort === 'terlama' ? 'asc' : 'desc')
            ->paginate(20)
            ->withQueryString();

        // Get statistics
        $baseQuery = \App\Models\SuratJalan::where(function ($query) use ($gudangId) {
            $query->where('gudang_asal_id', $gudangId)
                ->orWhere('gudang_tujuan_id', $gudangId);
        })->where('status', 'SELESAI');

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'transfer' => (clone $baseQuery)->where('tipe', 'TRANSFER')->count(),
            'peminjaman' => (clone $baseQuery)->where('tipe', 'PEMINJAMAN')->count(),
            'pengembalian' => (clone $baseQuery)->where('tipe', 'PENGEMBALIAN')->count(),
        ];

        return [
            'suratJalans' => $suratJalans,
            'tipe_sj' => $tipe,
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'gudang_asal' => $gudangAsal,
            'gudang_tujuan' => $gudangTujuan,
            'stats' => $stats,
        ];
    }

    /**
     * Get peminjaman history data with duration calculation
     */
    private function getRiwayatPeminjaman(int $gudangId, Request $request): array
    {
        $search = $request->input('search');
        $status = $request->input('status_pinjam');
        $kondisi = $request->input('kondisi');
        $tipePinjam = $request->input('tipe_pinjam');
        $sort = $request->input('sort', 'terbaru');
        $tanggalPinjam = $request->input('tanggal_pinjam');
        $tanggalKembali = $request->input('tanggal_kembali');

        $peminjamans = Peminjaman::with(['items.item', 'gudangPeminjam', 'gudangPemilik'])
            ->where('status', 'SELESAI') // Riwayat hanya menampilkan peminjaman yang sudah selesai
            ->where(function ($query) use ($gudangId, $tipePinjam) {
                // Filter based on tipe_pinjam (dipinjamkan = owner, meminjam = borrower)
                if ($tipePinjam === 'dipinjamkan') {
                    $query->where('gudang_pemilik_id', $gudangId);
                } elseif ($tipePinjam === 'meminjam') {
                    $query->where('gudang_peminjam_id', $gudangId);
                } else {
                    $query->where('gudang_pemilik_id', $gudangId)
                        ->orWhere('gudang_peminjam_id', $gudangId);
                }
            })
            ->when($search, function ($query, $search) {
                $searchLower = strtolower($search);
                $query->where(function ($q) use ($searchLower) {
                    $q->whereRaw('LOWER(kode) LIKE ?', ["%{$searchLower}%"])
                        ->orWhereHas('gudangPeminjam', function ($gq) use ($searchLower) {
                            $gq->whereRaw('LOWER(nama) LIKE ?', ["%{$searchLower}%"]);
                        })
                        ->orWhereHas('gudangPemilik', function ($gq) use ($searchLower) {
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
                $query->where('status', $status);
            })
            ->when($kondisi, function ($query, $kondisi) {
                $query->whereHas('items', function ($q) use ($kondisi) {
                    $q->where('kondisi_kembali', $kondisi);
                });
            })
            ->when($tanggalPinjam, function ($query, $tanggalPinjam) {
                $query->whereDate('waktu_diterima', '>=', $tanggalPinjam);
            })
            ->when($tanggalKembali, function ($query, $tanggalKembali) {
                $query->whereDate('waktu_selesai', '<=', $tanggalKembali);
            })
            ->orderBy('created_at', $sort === 'terlama' ? 'asc' : 'desc')
            ->paginate(20)
            ->withQueryString();

        // Calculate duration for each peminjaman
        $peminjamans->getCollection()->transform(function ($peminjaman) use ($gudangId) {
            // Determine if current gudang is owner or borrower
            $peminjaman->is_owner = $peminjaman->gudang_pemilik_id === $gudangId;

            $startTime = $peminjaman->waktu_diterima
                ?? $peminjaman->waktu_kirim
                ?? $peminjaman->waktu_pengajuan
                ?? $peminjaman->created_at;
            $endTime = $peminjaman->waktu_selesai
                ?? $peminjaman->waktu_pengembalian;

            $peminjaman->waktu_pinjam = $startTime ? \Carbon\Carbon::parse($startTime) : null;

            // Calculate duration
            if ($peminjaman->waktu_pinjam) {
                $start = $peminjaman->waktu_pinjam;
                $end = $endTime ? \Carbon\Carbon::parse($endTime) : now();

                // Calculate total minutes first, then convert to days and hours
                $totalMinutes = $start->diffInMinutes($end);
                $peminjaman->total_hari = (int) floor($totalMinutes / (60 * 24));
                $peminjaman->total_jam = (int) floor(($totalMinutes % (60 * 24)) / 60);
                $peminjaman->total_menit = (int) ($totalMinutes % 60);
            } else {
                $peminjaman->total_hari = 0;
                $peminjaman->total_jam = 0;
                $peminjaman->total_menit = 0;
            }

            return $peminjaman;
        });

        // Get statistics
        $stats = [
            'total' => Peminjaman::where(function ($q) use ($gudangId) {
                $q->where('gudang_pemilik_id', $gudangId)->orWhere('gudang_peminjam_id', $gudangId);
            })->count(),
            'selesai' => Peminjaman::where(function ($q) use ($gudangId) {
                $q->where('gudang_pemilik_id', $gudangId)->orWhere('gudang_peminjam_id', $gudangId);
            })->where('status', 'SELESAI')->count(),
            'aktif' => Peminjaman::where(function ($q) use ($gudangId) {
                $q->where('gudang_pemilik_id', $gudangId)->orWhere('gudang_peminjam_id', $gudangId);
            })->whereIn('status', ['DIKIRIM', 'DITERIMA', 'DIKEMBALIKAN'])->count(),
        ];

        return [
            'peminjamans' => $peminjamans,
            'status_pinjam' => $status,
            'kondisi' => $kondisi,
            'tipe_pinjam' => $tipePinjam,
            'stats' => $stats,
        ];
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
