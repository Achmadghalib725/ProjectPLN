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
use App\Models\SuratJalan;
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
        // Exclude:
        // - DIKEMBALIKAN/DIPERIKSA: barang sudah OUT dari gudang peminjam (dalam perjalanan kembali)
        // - Peminjaman yang surat pengembaliannya sudah di-approve manager (stok sudah OUT)
        $totalBorrowed = PeminjamanItem::query()
            ->join('peminjamans', 'peminjaman_items.peminjaman_id', '=', 'peminjamans.id')
            ->where('peminjamans.gudang_peminjam_id', $gudangId)
            ->whereNotIn('peminjamans.status', ['SELESAI', 'DITOLAK'])
            ->where(function ($query) {
                $query->whereNotNull('peminjamans.waktu_diterima')
                    ->orWhereNotNull('peminjamans.waktu_ttd_penerima');
            })
            ->sum(DB::raw('GREATEST(COALESCE(peminjaman_items.jumlah_diterima, peminjaman_items.jumlah_dipinjam) - COALESCE(peminjaman_items.jumlah_dikembalikan, 0), 0)'));

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
            ->whereIn('status', ['DIKIRIM', 'DIPERIKSA', 'DITERIMA', 'DIKEMBALIKAN', 'DIKEMBALIKAN_SEBAGIAN', 'MENUNGGU_DIKEMBALIKAN'])
            ->count();
        $countPinjaman = Peminjaman::where('gudang_peminjam_id', $gudangId)
            ->whereIn('status', ['DIKIRIM', 'DIPERIKSA', 'DITERIMA', 'DIKEMBALIKAN', 'DIKEMBALIKAN_SEBAGIAN', 'MENUNGGU_DIKEMBALIKAN'])
            ->count();

        // Tab-specific data
        if ($tab === 'dipinjamkan') {
            // Barang yang dipinjamkan ke gudang lain
            $peminjamans = $this->getBarangDipinjamkan($gudangId, $search, $status, $sort);
            $totalAktif = Peminjaman::where('gudang_pemilik_id', $gudangId)
                ->whereIn('status', ['DITERIMA', 'DIKEMBALIKAN', 'DIKEMBALIKAN_SEBAGIAN', 'MENUNGGU_DIKEMBALIKAN'])
                ->count();
            $totalOverdue = Peminjaman::where('gudang_pemilik_id', $gudangId)
                ->whereIn('status', ['DITERIMA', 'DIKEMBALIKAN', 'DIKEMBALIKAN_SEBAGIAN', 'MENUNGGU_DIKEMBALIKAN'])
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
                ->whereIn('status', ['DITERIMA', 'DIKEMBALIKAN', 'DIKEMBALIKAN_SEBAGIAN'])
                ->count();
            $totalOverdue = Peminjaman::where('gudang_peminjam_id', $gudangId)
                ->whereIn('status', ['DITERIMA', 'DIKEMBALIKAN', 'DIKEMBALIKAN_SEBAGIAN'])
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
            // Hitung borrowed qty hanya untuk peminjaman yang barangnya MASIH ADA di gudang peminjam
            // Exclude:
            // - SELESAI/DITOLAK: peminjaman sudah selesai
            // - DIKEMBALIKAN/DIPERIKSA: barang sudah OUT dari gudang peminjam (dalam proses pengembalian)
            // - Peminjaman yang surat pengembaliannya sudah di-approve manager (stok sudah OUT)
            $borrowedTotals = PeminjamanItem::query()
                ->select('peminjaman_items.item_id', DB::raw('SUM(GREATEST(COALESCE(peminjaman_items.jumlah_diterima, peminjaman_items.jumlah_dipinjam) - COALESCE(peminjaman_items.jumlah_dikembalikan, 0), 0)) as total'))
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
            // Borrowed qty tidak boleh melebihi jumlah stok yang ada
            // Jika barang sudah dikembalikan (OUT) tapi peminjaman belum SELESAI,
            // borrowed_qty harus reflect stok yang sebenarnya ada di gudang
            $stock->borrowed_qty = min($borrowed, (int) $stock->jumlah);
            $stock->own_qty = max(0, (int) $stock->jumlah - $stock->borrowed_qty);
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
        return Peminjaman::with(['gudangPeminjam', 'items.item', 'suratJalanKirim', 'suratJalanKembali'])
            ->where('gudang_pemilik_id', $gudangId)
            ->whereIn('status', ['DIKIRIM', 'DIPERIKSA', 'DITERIMA', 'DIKEMBALIKAN', 'DIKEMBALIKAN_SEBAGIAN', 'MENUNGGU_DIKEMBALIKAN'])
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
                    $query->whereIn('status', ['DITERIMA', 'DIKEMBALIKAN', 'DIKEMBALIKAN_SEBAGIAN', 'MENUNGGU_DIKEMBALIKAN'])
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
        return Peminjaman::with(['gudangPemilik', 'items.item', 'suratJalanKirim', 'suratJalanKembali'])
            ->where('gudang_peminjam_id', $gudangId)
            ->whereIn('status', ['DIKIRIM', 'DIPERIKSA', 'DITERIMA', 'DIKEMBALIKAN', 'DIKEMBALIKAN_SEBAGIAN', 'MENUNGGU_DIKEMBALIKAN'])
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
                    $query->whereIn('status', ['DITERIMA', 'DIKEMBALIKAN', 'DIKEMBALIKAN_SEBAGIAN'])
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
            ->sum(DB::raw('GREATEST(COALESCE(peminjaman_items.jumlah_diterima, peminjaman_items.jumlah_dipinjam) - COALESCE(peminjaman_items.jumlah_dikembalikan, 0), 0)'));

        $stock->borrowed_qty = (int) $borrowedQty;
        $stock->own_qty = max(0, (int) $stock->jumlah - $stock->borrowed_qty);

        // Get movement history
        $movements = StockMovement::with(['creator', 'suratJalan.pembuat'])
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

        $movements = StockMovement::with(['item', 'gudang', 'creator', 'suratJalan.pembuat'])
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
                    })
                    ->orWhere(function ($subQuery) use ($searchLower) {
                        $subQuery->where('referensi_type', 'SuratJalan')
                            ->whereHas('suratJalan.pembuat', function ($userQuery) use ($searchLower) {
                                $userQuery->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"]);
                            });
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

        // Get peminjaman records with their pengembalian surat jalans
        // Show peminjaman that:
        // 1. Are fully completed (SELESAI)
        // 2. Have partial returns completed (DIKEMBALIKAN_SEBAGIAN)
        // 3. Have another return in progress BUT already have completed returns (DIKEMBALIKAN with existing SELESAI SJ)
        $peminjamans = Peminjaman::with([
            'items.item',
            'gudangPeminjam',
            'gudangPemilik',
            'suratJalanKirim',
            // Only load SELESAI pengembalian SJ for riwayat display
            'suratJalanPengembalians' => function ($query) {
                $query->where('status', 'SELESAI')->with('items.item');
            },
        ])
            ->where(function ($query) {
                $query->whereIn('status', ['SELESAI', 'DIKEMBALIKAN_SEBAGIAN'])
                    ->orWhere(function ($q) {
                        // Has another return in progress but already has at least one completed pengembalian SJ
                        // Include all intermediate statuses during return process
                        $q->whereIn('status', ['DIKEMBALIKAN', 'DIPERIKSA', 'DIPERIKSA_PENERIMA', 'MENUNGGU_DIKEMBALIKAN'])
                            ->whereHas('suratJalanPengembalians', function ($sjq) {
                                $sjq->where('status', 'SELESAI');
                            });
                    });
            })
            ->where(function ($query) use ($gudangId, $tipePinjam) {
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
                        ->orWhereHas('items.item', function ($iq) use ($searchLower) {
                            $iq->whereRaw('LOWER(nama) LIKE ?', ["%{$searchLower}%"]);
                        });
                });
            })
            ->when($tanggalPinjam, function ($query, $tanggalPinjam) {
                $query->whereRaw(
                    'DATE(COALESCE(waktu_diterima, waktu_kirim, waktu_pengajuan, created_at)) >= ?',
                    [$tanggalPinjam]
                );
            })
            ->when($tanggalKembali, function ($query, $tanggalKembali) {
                $query->whereDate('waktu_selesai', '<=', $tanggalKembali);
            });

        // Apply sorting
        if ($sort === 'terlama') {
            $peminjamans = $peminjamans->orderByRaw('COALESCE(waktu_diterima, waktu_kirim, waktu_pengajuan, created_at) asc');
        } else {
            $peminjamans = $peminjamans->orderByRaw('COALESCE(waktu_diterima, waktu_kirim, waktu_pengajuan, created_at) desc');
        }

        // Paginate
        $peminjamans = $peminjamans->paginate(20)->withQueryString();

        // Add computed fields and prepare pengembalian data
        $peminjamans->getCollection()->transform(function ($pinjam) use ($gudangId) {
            $startTime = $pinjam->waktu_diterima
                ?? $pinjam->waktu_kirim
                ?? $pinjam->waktu_pengajuan
                ?? $pinjam->created_at;
            $pinjam->waktu_mulai = $startTime;
            $waktuKembali = $pinjam->waktu_selesai
                ?? $pinjam->waktu_pengembalian
                ?? $pinjam->suratJalanPengembalians->first()?->updated_at;
            $pinjam->waktu_kembali = $waktuKembali;
            $endTime = $waktuKembali ?? now();

            $totalMinutes = 0;
            if ($startTime && $endTime) {
                $start = \Carbon\Carbon::parse($startTime);
                $end = \Carbon\Carbon::parse($endTime);
                $totalMinutes = $start->diffInMinutes($end);
            }

            $pinjam->is_owner = $pinjam->gudang_pemilik_id === $gudangId;
            $pinjam->total_hari = (int) floor($totalMinutes / (60 * 24));
            $pinjam->total_jam = (int) floor(($totalMinutes % (60 * 24)) / 60);
            $pinjam->total_menit = (int) ($totalMinutes % 60);

            // Process pengembalian entries with duration calculation
            $pinjam->pengembalian_entries = $pinjam->suratJalanPengembalians
                ->where('status', 'SELESAI')
                ->map(function ($sj) use ($startTime) {
                    $endTime = $sj->updated_at;
                    $totalMinutes = 0;
                    if ($startTime && $endTime) {
                        $start = \Carbon\Carbon::parse($startTime);
                        $end = \Carbon\Carbon::parse($endTime);
                        $totalMinutes = $start->diffInMinutes($end);
                    }
                    $sj->durasi_hari = (int) floor($totalMinutes / (60 * 24));
                    $sj->durasi_jam = (int) floor(($totalMinutes % (60 * 24)) / 60);
                    $sj->durasi_menit = (int) ($totalMinutes % 60);
                    return $sj;
                });

            return $pinjam;
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
            })->whereIn('status', ['DIKIRIM', 'DITERIMA', 'DIKEMBALIKAN', 'DIKEMBALIKAN_SEBAGIAN'])->count(),
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
