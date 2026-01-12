<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
    <a href="{{ route('gudang.stok.index') }}" class="bg-white p-5 rounded-xl shadow border border-slate-200 hover:border-[#00aff0] transition">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm text-slate-500">Stok Barang</p>
                <h3 class="text-2xl font-bold text-[#035b71] mt-1">{{ number_format($totalStockItems ?? 0) }}</h3>
                <p class="text-xs text-slate-500 mt-1">{{ number_format($totalStockUnits ?? 0) }} unit tersedia</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-[#e6f7ff] text-[#00aff0] flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4m16 0H4"></path></svg>
            </div>
        </div>
        @if(!empty($lowStockCount))
            <span class="inline-flex items-center text-xs font-semibold text-red-600 mt-3">
                {{ $lowStockCount }} stok rendah perlu perhatian
            </span>
        @else
            <span class="inline-flex items-center text-xs text-emerald-600 mt-3">Stok aman hari ini</span>
        @endif
    </a>

    <a href="{{ route('gudang.surat-jalan.index') }}" class="bg-white p-5 rounded-xl shadow border border-slate-200 hover:border-emerald-500 transition">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm text-slate-500">Total Surat Jalan</p>
                <h3 class="text-2xl font-bold text-emerald-700 mt-1">{{ number_format($totalSuratJalan ?? 0) }}</h3>
                <p class="text-xs text-slate-500 mt-1">Dari gudang Anda</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
        </div>
        <span class="inline-flex items-center text-xs text-slate-500 mt-3">Terkait gudang Anda</span>
    </a>

    <div class="bg-white p-5 rounded-xl shadow border border-slate-200 hover:border-amber-500 transition">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm text-slate-500">Barang Dipinjam</p>
                <h3 class="text-2xl font-bold text-amber-600 mt-1">{{ number_format($totalBarangDipinjam ?? 0) }}</h3>
                <p class="text-xs text-slate-500 mt-1">{{ number_format($totalPeminjamanAktif ?? 0) }} transaksi aktif</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
        </div>
        <span class="inline-flex items-center text-xs text-slate-500 mt-3">Pantau proses pengembalian</span>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="xl:col-span-2 bg-white rounded-xl shadow border border-slate-200 overflow-hidden">
        {{-- Header --}}
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-gray-900">Surat Jalan Aktif</h3>
                <p class="text-xs text-gray-500">{{ $activeSuratJalans->count() }} surat dalam proses</p>
            </div>
            <a href="{{ route('gudang.surat-jalan.index') }}" class="text-xs font-medium text-gray-500 hover:text-gray-900 flex items-center gap-1">
                Lihat semua
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        {{-- Content --}}
        <div class="divide-y divide-slate-100">
            @forelse($activeSuratJalans as $suratJalan)
                @php
                    $isIncoming = $suratJalan->gudang_tujuan_id === Auth::user()->gudang_id;
                    $statusLabel = match($suratJalan->status) {
                        'MENUNGGU_PERSETUJUAN' => 'Menunggu',
                        'DIKIRIM' => 'Dikirim',
                        'DIPERIKSA' => 'Diperiksa',
                        'DITERIMA' => 'Diterima',
                        'DITOLAK' => 'Ditolak',
                        default => $suratJalan->status
                    };
                    $tipeLabel = match($suratJalan->tipe) {
                        'PEMINJAMAN' => 'Peminjaman',
                        'PENGEMBALIAN' => 'Pengembalian',
                        default => 'Transfer'
                    };
                    $arah = $isIncoming ? 'masuk dari' : 'keluar ke';
                    $gudang = $isIncoming ? ($suratJalan->gudangAsal?->nama ?? '-') : ($suratJalan->gudangTujuan?->nama ?? '-');
                @endphp
                <a href="{{ route('gudang.surat-jalan.show', $suratJalan->id) }}"
                   class="flex items-center gap-4 px-6 py-4 hover:bg-slate-50 transition group">
                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $suratJalan->nomor }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $tipeLabel }} {{ $arah }} {{ $gudang }}</p>
                    </div>

                    {{-- Right Side --}}
                    <div class="text-right flex-shrink-0">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700">
                            {{ $statusLabel }}
                        </span>
                        <p class="text-[11px] text-gray-400 mt-1">{{ $suratJalan->tanggal?->format('d M Y') ?? '-' }}</p>
                    </div>

                    {{-- Arrow --}}
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-500 transition flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            @empty
                <div class="px-6 py-12 text-center">
                    <p class="text-sm text-gray-500">Tidak ada surat jalan aktif</p>
                    <a href="{{ route('gudang.surat-jalan.index') }}" class="inline-flex items-center mt-3 text-sm font-medium text-gray-700 hover:text-gray-900">
                        Buat Surat Jalan Baru
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </a>
                </div>
            @endforelse
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white p-6 rounded-xl shadow border border-slate-200">
            <h3 class="font-bold text-[#035b71] text-lg mb-4">Aktivitas Terakhir</h3>
            <div class="space-y-4">
                @forelse($recentActivities as $activity)
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-full {{ $activity->tipe === 'IN' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }} flex items-center justify-center">
                            @if($activity->tipe === 'IN')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                            @endif
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-slate-800">
                                {{ $activity->item?->nama ?? 'Item' }}
                                <span class="text-xs text-slate-500">({{ $activity->tipe }})</span>
                            </p>
                            <p class="text-xs text-slate-500 mt-1">
                                Qty {{ number_format($activity->jumlah ?? 0) }} •
                                {{ $activity->creator?->name ?? 'Sistem' }} •
                                {{ $activity->created_at?->diffForHumans() ?? '-' }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="text-sm text-slate-500 bg-slate-50 border border-dashed border-slate-200 rounded-lg p-4">
                        Belum ada aktivitas stok terbaru.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow border border-slate-200">
            <h3 class="font-bold text-[#035b71] text-lg mb-4">Quick Action</h3>
            <div class="grid grid-cols-1 gap-3">
                <a href="{{ route('gudang.surat-jalan.index') }}" class="flex items-center justify-between p-3 rounded-lg border border-slate-200 hover:border-[#00aff0] hover:bg-slate-50 transition">
                    <div>
                        <p class="text-sm font-semibold text-slate-800">Buat Surat Jalan</p>
                        <p class="text-xs text-slate-500">Kirim barang ke gudang tujuan</p>
                    </div>
                    <span class="text-xs font-semibold text-[#00aff0]">Mulai</span>
                </a>
                <a href="{{ route('gudang.stok.index') }}" class="flex items-center justify-between p-3 rounded-lg border border-slate-200 hover:border-[#00aff0] hover:bg-slate-50 transition">
                    <div>
                        <p class="text-sm font-semibold text-slate-800">Cek Stok Gudang</p>
                        <p class="text-xs text-slate-500">Pantau persediaan dan stok minimum</p>
                    </div>
                    <span class="text-xs font-semibold text-[#00aff0]">Lihat</span>
                </a>
                <a href="{{ route('gudang.stok.create') }}" class="flex items-center justify-between p-3 rounded-lg border border-slate-200 hover:border-[#00aff0] hover:bg-slate-50 transition">
                    <div>
                        <p class="text-sm font-semibold text-slate-800">Tambah Item Stok</p>
                        <p class="text-xs text-slate-500">Input inventaris baru</p>
                    </div>
                    <span class="text-xs font-semibold text-[#00aff0]">Tambah</span>
                </a>
            </div>
        </div>
    </div>
</div>
