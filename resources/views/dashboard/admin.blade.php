<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
    <a href="{{ route('admin.users.index') }}" class="bg-white p-5 rounded-xl shadow border border-slate-200 hover:border-[#00aff0] transition">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm text-slate-500">Total Pengguna</p>
                <h3 class="text-2xl font-bold text-[#035b71] mt-1">{{ number_format($totalUsers ?? 0) }}</h3>
                <p class="text-xs text-slate-500 mt-1">Kelola akses sistem</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-[#e6f7ff] text-[#00aff0] flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
        </div>
        <span class="inline-flex items-center text-xs text-slate-500 mt-3">Admin, Manager, Tool Man, Penerima, Security</span>
    </a>

    <a href="{{ route('admin.items.index') }}" class="bg-white p-5 rounded-xl shadow border border-slate-200 hover:border-emerald-500 transition">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm text-slate-500">Master Barang</p>
                <h3 class="text-2xl font-bold text-emerald-700 mt-1">{{ number_format($totalItems ?? 0) }}</h3>
                <p class="text-xs text-slate-500 mt-1">Database item inventaris</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            </div>
        </div>
        <span class="inline-flex items-center text-xs text-slate-500 mt-3">Kelola jenis barang</span>
    </a>

    <a href="{{ route('admin.pics.index') }}" class="bg-white p-5 rounded-xl shadow border border-slate-200 hover:border-amber-500 transition">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm text-slate-500">PIC</p>
                <h3 class="text-2xl font-bold text-amber-600 mt-1">{{ number_format($totalPics ?? 0) }} PIC</h3>
                <p class="text-xs text-slate-500 mt-1">Penanggung jawab</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
        </div>
        <span class="inline-flex items-center text-xs text-slate-500 mt-3">Pengaturan PIC</span>
    </a>
</div>

{{-- Rekap Surat Jalan Section --}}
<div class="bg-white p-6 rounded-xl shadow border border-slate-200">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-bold text-[#035b71] text-lg flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Rekap Surat Jalan
        </h3>
    </div>
    <p class="text-sm text-slate-500 mb-6">Export data surat jalan dari semua gudang untuk pengarsipan.</p>

    <form method="GET"
          action="{{ route('admin.rekap.export-excel') }}"
          x-data="{
              periode: '1_bulan',
              showCustom: false,
              updatePeriode() {
                  this.showCustom = this.periode === 'custom';
              }
          }"
          class="space-y-6">

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            {{-- Gudang Filter --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Gudang</label>
                <select name="gudang_id"
                        class="w-full rounded-md border-slate-300 shadow-sm focus:border-[#035b71] focus:ring focus:ring-[#035b71] focus:ring-opacity-50">
                    <option value="">Semua Gudang</option>
                    @foreach($gudangs as $gudang)
                        <option value="{{ $gudang->id }}">{{ $gudang->kode }} - {{ $gudang->nama }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Type Filter --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Tipe Surat Jalan</label>
                <select name="tipe"
                        class="w-full rounded-md border-slate-300 shadow-sm focus:border-[#035b71] focus:ring focus:ring-[#035b71] focus:ring-opacity-50">
                    <option value="ALL">Semua Tipe</option>
                    <option value="TRANSFER">Transfer</option>
                    <option value="PEMINJAMAN">Peminjaman</option>
                </select>
            </div>

            {{-- Status Filter --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Status Surat Jalan</label>
                <select name="status"
                        class="w-full rounded-md border-slate-300 shadow-sm focus:border-[#035b71] focus:ring focus:ring-[#035b71] focus:ring-opacity-50">
                    <option value="ALL">Semua</option>
                    <option value="BERLANGSUNG">Sedang Berlangsung</option>
                    <option value="SELESAI">Selesai</option>
                </select>
            </div>

            {{-- Period Filter --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Periode</label>
                <select name="periode"
                        x-model="periode"
                        @change="updatePeriode()"
                        class="w-full rounded-md border-slate-300 shadow-sm focus:border-[#035b71] focus:ring focus:ring-[#035b71] focus:ring-opacity-50">
                    <option value="1_minggu">1 Minggu Terakhir</option>
                    <option value="1_bulan">1 Bulan Terakhir</option>
                    <option value="3_bulan">3 Bulan Terakhir</option>
                    <option value="6_bulan">6 Bulan Terakhir</option>
                    <option value="1_tahun">1 Tahun Terakhir</option>
                    <option value="custom">Custom (Pilih Tanggal)</option>
                </select>
            </div>
        </div>

        {{-- Custom Date Range --}}
        <div x-show="showCustom" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Mulai</label>
                <input type="date"
                       name="tanggal_mulai"
                       class="w-full rounded-md border-slate-300 shadow-sm focus:border-[#035b71] focus:ring focus:ring-[#035b71] focus:ring-opacity-50">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Selesai</label>
                <input type="date"
                       name="tanggal_selesai"
                       class="w-full rounded-md border-slate-300 shadow-sm focus:border-[#035b71] focus:ring focus:ring-[#035b71] focus:ring-opacity-50">
            </div>
        </div>

        {{-- Submit Button --}}
        <div class="flex justify-end pt-4 border-t border-slate-100">
            <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-[#035b71] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#00aff0] active:scale-95 focus:outline-none transition ease-in-out duration-150">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Download Excel
            </button>
        </div>
    </form>
</div>
