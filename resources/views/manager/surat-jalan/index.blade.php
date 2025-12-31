<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-900 px-4 py-3 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-900 px-4 py-3 rounded-lg text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @php
                $statusLabels = [
                    'ALL' => 'Semua Status',
                    'DRAFT' => 'Draft',
                    'MENUNGGU_PERSETUJUAN' => 'Menunggu Persetujuan',
                    'DITOLAK_PERSETUJUAN' => 'Ditolak Persetujuan',
                    'DIKIRIM' => 'Dikirim',
                    'DIPERIKSA' => 'Diperiksa',
                    'DITERIMA' => 'Diterima',
                    'MENUNGGU_DIKEMBALIKAN' => 'Menunggu Dikembalikan',
                    'DIKEMBALIKAN' => 'Dikembalikan',
                    'SELESAI' => 'Selesai',
                    'DITOLAK' => 'Ditolak',
                ];
                $statusStyles = [
                    'DRAFT' => 'bg-slate-100 text-slate-700',
                    'MENUNGGU_PERSETUJUAN' => 'bg-orange-100 text-orange-800',
                    'DITOLAK_PERSETUJUAN' => 'bg-red-100 text-red-700',
                    'DIKIRIM' => 'bg-blue-100 text-blue-700',
                    'DIPERIKSA' => 'bg-indigo-100 text-indigo-700',
                    'DITERIMA' => 'bg-emerald-100 text-emerald-700',
                    'MENUNGGU_DIKEMBALIKAN' => 'bg-amber-100 text-amber-800',
                    'DIKEMBALIKAN' => 'bg-teal-100 text-teal-700',
                    'SELESAI' => 'bg-emerald-100 text-emerald-700',
                    'DITOLAK' => 'bg-red-100 text-red-700',
                ];
                $tipeLabels = [
                    'TRANSFER' => 'Transfer',
                    'PEMINJAMAN' => 'Peminjaman',
                    'PENGEMBALIAN' => 'Pengembalian',
                ];
                $filters = $filters ?? [];
                $selectedStatus = $filters['status'] ?? 'MENUNGGU_PERSETUJUAN';
                $selectedTipe = $filters['tipe'] ?? '';
                $selectedGudang = $filters['gudang_id'] ?? '';
                $search = $filters['search'] ?? '';
                $orderBy = $filters['order_by'] ?? 'terbaru';
                $statsCollection = collect($stats ?? []);
            @endphp

            <div class="bg-white border border-slate-200 rounded-xl p-5 sm:p-6">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-bold text-slate-900">Persetujuan Surat Jalan</h2>
                        <p class="text-sm text-slate-500">Pantau pengiriman dari gudang yang Anda kelola.</p>
                    </div>
                    <div class="text-xs text-slate-500">
                        Status default: <span class="font-semibold text-slate-700">Menunggu Persetujuan</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="bg-white border border-slate-200 rounded-xl p-4">
                    <p class="text-xs text-slate-500">Total Surat Jalan</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $statsCollection->sum() }}</p>
                </div>
                <div class="bg-white border border-orange-200 rounded-xl p-4">
                    <p class="text-xs text-orange-600">Menunggu Persetujuan</p>
                    <p class="text-2xl font-bold text-orange-700">{{ $statsCollection->get('MENUNGGU_PERSETUJUAN', 0) }}</p>
                </div>
                <div class="bg-white border border-red-200 rounded-xl p-4">
                    <p class="text-xs text-red-600">Ditolak</p>
                    <p class="text-2xl font-bold text-red-700">{{ $statsCollection->get('DITOLAK_PERSETUJUAN', 0) }}</p>
                </div>
                <div class="bg-white border border-blue-200 rounded-xl p-4">
                    <p class="text-xs text-blue-600">Dikirim</p>
                    <p class="text-2xl font-bold text-blue-700">{{ $statsCollection->get('DIKIRIM', 0) }}</p>
                </div>
                <div class="bg-white border border-emerald-200 rounded-xl p-4">
                    <p class="text-xs text-emerald-600">Selesai</p>
                    <p class="text-2xl font-bold text-emerald-700">{{ $statsCollection->get('SELESAI', 0) }}</p>
                </div>
            </div>

            <form method="GET" action="{{ route('manager.surat-jalan.index') }}" class="bg-white border border-slate-200 rounded-xl p-5 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase">Cari Nomor</label>
                        <input type="text"
                               name="search"
                               value="{{ $search }}"
                               placeholder="XXX/F22060400/XXXX"
                               class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase">Status</label>
                        <select name="status"
                                class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                            @foreach($statusLabels as $statusValue => $label)
                                <option value="{{ $statusValue }}" {{ $selectedStatus === $statusValue ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase">Tipe</label>
                        <select name="tipe"
                                class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                            <option value="">Semua Tipe</option>
                            @foreach($tipeLabels as $tipeValue => $label)
                                <option value="{{ $tipeValue }}" {{ $selectedTipe === $tipeValue ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase">Gudang Asal</label>
                        <select name="gudang_id"
                                class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                            <option value="">Semua Gudang</option>
                            @foreach($gudangs as $gudang)
                                <option value="{{ $gudang->id }}" {{ (string) $selectedGudang === (string) $gudang->id ? 'selected' : '' }}>
                                    {{ $gudang->kode }} - {{ $gudang->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase">Urutan</label>
                        <select name="order_by"
                                class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                            <option value="terbaru" {{ $orderBy === 'terbaru' ? 'selected' : '' }}>Terbaru</option>
                            <option value="terlama" {{ $orderBy === 'terlama' ? 'selected' : '' }}>Terlama</option>
                        </select>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-3">
                    <a href="{{ route('manager.surat-jalan.index') }}"
                       class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200">
                        Reset
                    </a>
                    <button type="submit"
                            class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-pln-primary rounded-lg hover:bg-pln-light">
                        Terapkan Filter
                    </button>
                </div>
            </form>

            <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-semibold text-slate-900">Daftar Surat Jalan</h3>
                    <span class="text-xs text-slate-500">Total: {{ $suratJalans->total() }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Nomor</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Tipe</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Gudang</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Tanggal</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Item</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @forelse($suratJalans as $sj)
                                @php
                                    $statusText = $statusLabels[$sj->status] ?? $sj->status;
                                    $statusClass = $statusStyles[$sj->status] ?? 'bg-slate-100 text-slate-700';
                                    $tipeText = $tipeLabels[$sj->tipe] ?? $sj->tipe;
                                    $gudangTujuanNama = $sj->gudang_tujuan_is_custom
                                        ? ($sj->gudang_tujuan_custom_nama ?? 'Gudang Lainnya')
                                        : ($sj->gudangTujuan->nama ?? '-');
                                @endphp
                                <tr>
                                    <td class="px-5 py-3 text-sm font-semibold text-slate-900">{{ $sj->nomor }}</td>
                                    <td class="px-5 py-3 text-sm text-slate-700">{{ $tipeText }}</td>
                                    <td class="px-5 py-3 text-sm text-slate-600">
                                        <div class="font-medium">{{ $sj->gudangAsal->nama ?? '-' }}</div>
                                        <div class="text-xs text-slate-400">Tujuan: {{ $gudangTujuanNama }}</div>
                                    </td>
                                    <td class="px-5 py-3 text-sm text-slate-500">{{ $sj->tanggal?->format('d M Y') ?? '-' }}</td>
                                    <td class="px-5 py-3 text-sm">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusClass }}">
                                            {{ $statusText }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-sm text-slate-600">
                                        <div>{{ $sj->items_count ?? 0 }} jenis</div>
                                        <div class="text-xs text-slate-400">{{ (int) ($sj->items_sum_jumlah ?? 0) }} unit</div>
                                    </td>
                                    <td class="px-5 py-3 text-sm">
                                        <a href="{{ route('manager.surat-jalan.show', $sj->id) }}"
                                           class="text-pln-primary hover:text-pln-light font-semibold">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-6 text-center text-sm text-slate-500">
                                        Belum ada surat jalan untuk gudang Anda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-4 border-t border-slate-100">
                    {{ $suratJalans->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
