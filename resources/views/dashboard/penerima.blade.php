@php
    $statusClasses = [
        'DIKIRIM' => 'bg-yellow-100 text-yellow-800',
        'DIPERIKSA_PENGIRIM' => 'bg-purple-100 text-purple-800',
        'DIPERIKSA_PENERIMA' => 'bg-purple-100 text-purple-800',
        'DIPERIKSA' => 'bg-purple-100 text-purple-800',
        'DITERIMA' => 'bg-emerald-100 text-emerald-700',
        'DIKEMBALIKAN' => 'bg-indigo-100 text-indigo-800',
        'MENUNGGU_DIKEMBALIKAN' => 'bg-orange-100 text-orange-800',
    ];
@endphp

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-5 rounded-xl shadow border border-slate-200">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm text-slate-500">Surat Masuk (Divisi)</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($divisiStats['total'] ?? 0) }}</h3>
                <p class="text-xs text-slate-500 mt-1">Hanya surat untuk divisi Anda</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
        </div>
    </div>

    <div class="bg-white p-5 rounded-xl shadow border border-slate-200">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm text-slate-500">Menunggu Diproses</p>
                <h3 class="text-2xl font-bold text-amber-600 mt-1">{{ number_format($divisiStats['menunggu'] ?? 0) }}</h3>
                <p class="text-xs text-slate-500 mt-1">Status DIKIRIM</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>
    </div>

    <div class="bg-white p-5 rounded-xl shadow border border-slate-200">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm text-slate-500">Sudah Diterima</p>
                <h3 class="text-2xl font-bold text-emerald-700 mt-1">{{ number_format($divisiStats['diterima'] ?? 0) }}</h3>
                <p class="text-xs text-slate-500 mt-1">Status DITERIMA</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
        </div>
    </div>
</div>

<div class="bg-white p-6 rounded-xl shadow border border-slate-200">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-bold text-[#035b71] text-lg">Surat Masuk Terbaru</h3>
        <a href="{{ route('gudang.surat-jalan.index') }}" class="text-xs font-semibold text-[#00aff0] hover:text-[#035b71]">Lihat semua</a>
    </div>
    <div class="space-y-4">
        @forelse($divisiRecent ?? collect() as $sj)
            <a href="{{ route('gudang.surat-jalan.show', $sj->id) }}" class="flex flex-col md:flex-row md:items-center md:justify-between p-4 rounded-lg border border-slate-100 hover:border-[#00aff0] hover:bg-slate-50 transition">
                <div>
                    <p class="text-sm font-semibold text-slate-800">{{ $sj->nomor }}</p>
                    <p class="text-xs text-slate-500 mt-1">
                        Asal: {{ $sj->gudangAsal?->nama ?? 'Gudang Asal' }} |
                        Tanggal: {{ $sj->tanggal?->format('d M Y') ?? '-' }}
                    </p>
                </div>
                <div class="mt-3 md:mt-0 flex items-center gap-3">
                    <span class="text-xs font-medium text-slate-500">{{ $sj->tipe }}</span>
                    <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $statusClasses[$sj->status] ?? 'bg-slate-100 text-slate-600' }}">
                        {{ $sj->status }}
                    </span>
                </div>
            </a>
        @empty
            <div class="text-sm text-slate-500 bg-slate-50 border border-dashed border-slate-200 rounded-lg p-4">
                Belum ada surat masuk untuk divisi ini.
            </div>
        @endforelse
    </div>
</div>
