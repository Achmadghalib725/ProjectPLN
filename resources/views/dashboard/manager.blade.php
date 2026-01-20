<div class="space-y-6">
    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-slate-200">
        <div class="p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-lg font-bold text-slate-900">Dashboard Manager</h3>
                <p class="text-sm text-slate-500">Ringkasan persetujuan surat jalan.</p>
            </div>
            <div class="text-xs text-slate-500">
                Gudang dikelola:
                <span class="font-semibold text-slate-700">
                    {{ ($managerGudangs ?? collect())->pluck('nama')->implode(', ') ?: '-' }}
                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-500">Total Surat Jalan</p>
            <p class="text-2xl font-bold text-slate-900">{{ $managerStats['total'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl border border-orange-200 p-4">
            <p class="text-xs text-orange-600">Menunggu Persetujuan</p>
            <p class="text-2xl font-bold text-orange-700">{{ $managerStats['menunggu_persetujuan'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl border border-red-200 p-4">
            <p class="text-xs text-red-600">Ditolak Persetujuan</p>
            <p class="text-2xl font-bold text-red-700">{{ $managerStats['ditolak_persetujuan'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl border border-blue-200 p-4">
            <p class="text-xs text-blue-600">Sedang Diproses</p>
            <p class="text-2xl font-bold text-blue-700">{{ $managerStats['dikirim'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl border border-emerald-200 p-4">
            <p class="text-xs text-emerald-600">Selesai</p>
            <p class="text-2xl font-bold text-emerald-700">{{ $managerStats['selesai'] ?? 0 }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h4 class="font-semibold text-slate-900">Surat Jalan Terbaru</h4>
            <a href="{{ route('manager.surat-jalan.index') }}" class="text-sm text-pln-primary hover:text-pln-light font-semibold">Lihat semua</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nomor</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Gudang Asal</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    @forelse($managerRecent ?? collect() as $sj)
                        @php
                            $status = $sj->status ?? 'DRAFT';
                            $statusClass = match ($status) {
                                'DRAFT' => 'bg-gray-100 text-gray-800',
                                'MENUNGGU_PERSETUJUAN' => 'bg-orange-100 text-orange-800',
                                'DITOLAK_PERSETUJUAN' => 'bg-red-100 text-red-800',
                                'DIKIRIM' => 'bg-blue-100 text-blue-800',
                                'DIPERIKSA_PENGIRIM' => 'bg-cyan-100 text-cyan-800',
                                'DIPERIKSA_PENERIMA' => 'bg-purple-100 text-purple-800',
                                'DIKEMBALIKAN' => 'bg-indigo-100 text-indigo-800',
                                'MENUNGGU_DIKEMBALIKAN' => 'bg-yellow-100 text-yellow-800',
                                'DIPERIKSA' => 'bg-purple-100 text-purple-800',
                                'DITERIMA' => 'bg-yellow-100 text-yellow-800',
                                'DITOLAK' => 'bg-red-100 text-red-800',
                                'SELESAI' => 'bg-green-100 text-green-800',
                                default => 'bg-gray-100 text-gray-800',
                            };
                            $statusLabel = match ($status) {
                                'DRAFT' => 'Draft',
                                'MENUNGGU_PERSETUJUAN' => 'Menunggu Persetujuan',
                                'DITOLAK_PERSETUJUAN' => 'Ditolak Persetujuan',
                                'DIKIRIM' => 'Dikirim',
                                'DIPERIKSA_PENGIRIM' => 'Menunggu Pemeriksaan',
                                'DIPERIKSA_PENERIMA' => 'Diperiksa Penerima',
                                'DIKEMBALIKAN' => 'Dikembalikan',
                                'MENUNGGU_DIKEMBALIKAN' => 'Menunggu Dikembalikan',
                                'DIPERIKSA' => 'Diperiksa',
                                'DITERIMA' => 'Diterima',
                                'DITOLAK' => 'Ditolak',
                                'SELESAI' => 'Selesai',
                                default => $status,
                            };
                        @endphp
                        <tr>
                            <td class="px-5 py-3 text-sm font-semibold text-slate-900">{{ $sj->nomor ?? '-' }}</td>
                            <td class="px-5 py-3 text-sm text-slate-700">{{ $sj->gudangAsal->nama ?? '-' }}</td>
                            <td class="px-5 py-3 text-sm text-slate-500">{{ $sj->tanggal?->format('d M Y') ?? '-' }}</td>
                            <td class="px-5 py-3 text-sm">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-sm">
                                <a href="{{ route('manager.surat-jalan.show', $sj->id) }}" class="text-pln-primary hover:text-pln-light font-semibold">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-6 text-center text-sm text-slate-500">Belum ada surat jalan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
