<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Header --}}
            <div class="mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Barang Dipinjamkan</h2>
                        <p class="text-sm text-gray-500 mt-1">Daftar barang milik gudang Anda yang sedang dipinjam gudang lain</p>
                    </div>
                    <a href="{{ route('gudang.stok.index') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Kembali
                    </a>
                </div>
            </div>

            {{-- Statistics Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Peminjaman Aktif</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $totalAktif }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 {{ $totalOverdue > 0 ? 'bg-red-100' : 'bg-green-100' }} rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 {{ $totalOverdue > 0 ? 'text-red-600' : 'text-green-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Melewati Batas Waktu</p>
                            <p class="text-2xl font-bold {{ $totalOverdue > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $totalOverdue }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
                <form method="GET" class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Cari kode, gudang, atau item..."
                               class="w-full rounded-lg border-gray-300 focus:border-pln-primary focus:ring focus:ring-pln-primary/20">
                    </div>
                    <div class="sm:w-48">
                        <select name="status" class="w-full rounded-lg border-gray-300 focus:border-pln-primary focus:ring focus:ring-pln-primary/20">
                            <option value="">Semua Status</option>
                            <option value="DIKIRIM" {{ request('status') === 'DIKIRIM' ? 'selected' : '' }}>Dikirim</option>
                            <option value="DIPERIKSA" {{ request('status') === 'DIPERIKSA' ? 'selected' : '' }}>Diperiksa</option>
                            <option value="DITERIMA" {{ request('status') === 'DITERIMA' ? 'selected' : '' }}>Diterima</option>
                            <option value="DIKEMBALIKAN" {{ request('status') === 'DIKEMBALIKAN' ? 'selected' : '' }}>Dikembalikan</option>
                            <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                        </select>
                    </div>
                    <button type="submit" class="px-6 py-2 bg-pln-primary text-white rounded-lg hover:bg-pln-primary/90 transition">
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('gudang.stok.barang-dipinjamkan') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                            Reset
                        </a>
                    @endif
                </form>
            </div>

            {{-- Table --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kode Peminjaman</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Dipinjam Oleh</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Item</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Durasi Pinjam</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Batas Waktu</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($peminjamans as $peminjaman)
                                @php
                                    $isOverdue = $peminjaman->batas_waktu_kembali &&
                                                 $peminjaman->batas_waktu_kembali->isPast() &&
                                                 in_array($peminjaman->status, ['DITERIMA']);

                                    // Calculate duration
                                    $startDate = $peminjaman->waktu_diterima ?? $peminjaman->waktu_kirim ?? $peminjaman->created_at;
                                    $endDate = $peminjaman->waktu_selesai ?? now();
                                    $durasiHari = $startDate ? $startDate->diffInDays($endDate) : 0;
                                    $durasiJam = $startDate ? $startDate->diffInHours($endDate) % 24 : 0;

                                    // Days until/past deadline
                                    $daysUntilDeadline = $peminjaman->batas_waktu_kembali
                                        ? now()->diffInDays($peminjaman->batas_waktu_kembali, false)
                                        : null;
                                @endphp
                                <tr class="{{ $isOverdue ? 'bg-red-50' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $peminjaman->kode }}</div>
                                        <div class="text-xs text-gray-500">{{ $peminjaman->waktu_kirim?->format('d M Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $peminjaman->gudangPeminjam->nama ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $itemCount = $peminjaman->items->count();
                                        @endphp
                                        @if($itemCount === 1)
                                            @php $firstItem = $peminjaman->items->first(); @endphp
                                            <div class="text-sm text-gray-900">
                                                <span class="font-medium">{{ $firstItem->item->kode ?? '-' }}</span>
                                                <span class="text-gray-600">- {{ $firstItem->item->nama ?? '-' }}</span>
                                                <span class="text-pln-primary font-medium">({{ $firstItem->jumlah_dipinjam ?? $firstItem->jumlah }})</span>
                                            </div>
                                        @elseif($itemCount <= 2)
                                            <div class="space-y-1">
                                                @foreach($peminjaman->items as $item)
                                                    <div class="text-sm text-gray-900">
                                                        <span class="font-medium">{{ $item->item->kode ?? '-' }}</span> - {{ $item->item->nama ?? '-' }}
                                                        <span class="text-pln-primary">({{ $item->jumlah_dipinjam ?? $item->jumlah }})</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div x-data="{ showItems: false }" class="relative">
                                                <button @click="showItems = !showItems"
                                                        class="inline-flex items-center gap-1 text-sm text-pln-primary hover:text-pln-primary/80 font-medium">
                                                    <span>{{ $itemCount }} item</span>
                                                    <svg class="w-4 h-4 transition-transform" :class="showItems ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                    </svg>
                                                </button>
                                                <div x-show="showItems"
                                                     x-transition
                                                     @click.away="showItems = false"
                                                     class="absolute z-20 left-0 mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-200 p-3 space-y-2">
                                                    @foreach($peminjaman->items as $item)
                                                        <div class="text-sm text-gray-900 flex justify-between items-center">
                                                            <div>
                                                                <span class="font-medium">{{ $item->item->kode ?? '-' }}</span>
                                                                <span class="text-gray-600 block text-xs">{{ $item->item->nama ?? '-' }}</span>
                                                            </div>
                                                            <span class="text-pln-primary font-semibold">{{ $item->jumlah_dipinjam ?? $item->jumlah }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $totalMinutes = $startDate ? $startDate->diffInMinutes($endDate) : 0;
                                            $days = floor($totalMinutes / (60 * 24));
                                            $hours = floor(($totalMinutes % (60 * 24)) / 60);
                                            $minutes = $totalMinutes % 60;

                                            $durationParts = [];
                                            if ($days > 0) $durationParts[] = $days . ' hari';
                                            if ($hours > 0) $durationParts[] = $hours . ' jam';
                                            if ($minutes > 0 && $days < 1) $durationParts[] = $minutes . ' menit';
                                            $durationText = !empty($durationParts) ? implode(', ', $durationParts) : '0 menit';
                                        @endphp
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $durationText }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Sejak {{ $startDate?->format('d M Y') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($peminjaman->batas_waktu_kembali)
                                            <div class="text-sm {{ $isOverdue ? 'text-red-600 font-bold' : 'text-gray-900' }}">
                                                {{ $peminjaman->batas_waktu_kembali->format('d M Y') }}
                                            </div>
                                            @if($isOverdue)
                                                <div class="flex items-center gap-1 text-xs text-red-600 font-medium">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Terlambat {{ abs($daysUntilDeadline) }} hari
                                                </div>
                                            @elseif($daysUntilDeadline !== null && $daysUntilDeadline <= 3 && $daysUntilDeadline >= 0)
                                                <div class="text-xs text-yellow-600 font-medium">
                                                    {{ $daysUntilDeadline }} hari lagi
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-sm text-gray-400">Tidak ada batas</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusColor = match($peminjaman->status) {
                                                'DIKIRIM' => 'bg-blue-100 text-blue-800',
                                                'DIPERIKSA' => 'bg-cyan-100 text-cyan-800',
                                                'DITERIMA' => 'bg-yellow-100 text-yellow-800',
                                                'DIKEMBALIKAN' => 'bg-orange-100 text-orange-800',
                                                'SELESAI' => 'bg-green-100 text-green-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                            {{ $peminjaman->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($peminjaman->suratJalanKirim)
                                            <a href="{{ route('gudang.surat-jalan.show', $peminjaman->suratJalanKirim->id) }}"
                                               class="text-pln-primary hover:text-pln-primary/80 text-sm font-medium">
                                                Lihat Detail
                                            </a>
                                        @else
                                            <span class="text-gray-400 text-sm">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                            </svg>
                                            <p class="text-gray-500">Tidak ada barang yang sedang dipinjamkan</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($peminjamans->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $peminjamans->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
