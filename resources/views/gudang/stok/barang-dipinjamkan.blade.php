<x-app-layout>
    <div class="py-4 sm:py-8 lg:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Header --}}
            <div class="mb-4 sm:mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
                    <div class="text-center sm:text-left">
                        <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Barang Dipinjamkan</h2>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1">Barang milik gudang Anda yang dipinjam gudang lain</p>
                    </div>
                    <a href="{{ route('gudang.stok.index') }}"
                       class="inline-flex items-center justify-center gap-2 px-4 py-2.5 sm:py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Kembali
                    </a>
                </div>
            </div>

            {{-- Statistics Cards --}}
            <div class="grid grid-cols-2 gap-3 sm:gap-4 mb-4 sm:mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-5">
                    <div class="flex flex-col sm:flex-row items-center sm:items-center gap-2 sm:gap-4">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                        </div>
                        <div class="text-center sm:text-left">
                            <p class="text-xs sm:text-sm text-gray-500">Peminjaman Aktif</p>
                            <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ $totalAktif }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-5">
                    <div class="flex flex-col sm:flex-row items-center sm:items-center gap-2 sm:gap-4">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 {{ $totalOverdue > 0 ? 'bg-red-100' : 'bg-green-100' }} rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 {{ $totalOverdue > 0 ? 'text-red-600' : 'text-green-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="text-center sm:text-left">
                            <p class="text-xs sm:text-sm text-gray-500">Melewati Batas</p>
                            <p class="text-xl sm:text-2xl font-bold {{ $totalOverdue > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $totalOverdue }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-4 sm:mb-6" x-data="{ showFilter: false }">
                {{-- Mobile Filter Toggle --}}
                <div class="sm:hidden p-4 border-b border-gray-100">
                    <button @click="showFilter = !showFilter" type="button" class="w-full flex items-center justify-between text-gray-700">
                        <span class="flex items-center gap-2 font-medium">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            Filter & Pencarian
                            @if(request()->hasAny(['search', 'status']))
                                <span class="bg-pln-primary text-white text-xs px-2 py-0.5 rounded-full">Aktif</span>
                            @endif
                        </span>
                        <svg class="w-5 h-5 transition-transform" :class="{ 'rotate-180': showFilter }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                </div>

                {{-- Filter Form --}}
                <div class="p-4" :class="{ 'hidden sm:block': !showFilter }">
                    <form method="GET" class="flex flex-col sm:flex-row gap-3 sm:gap-4">
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
                                <option value="MENUNGGU_DIKEMBALIKAN" {{ request('status') === 'MENUNGGU_DIKEMBALIKAN' ? 'selected' : '' }}>Menunggu Dikembalikan</option>
                                <option value="DIKEMBALIKAN" {{ request('status') === 'DIKEMBALIKAN' ? 'selected' : '' }}>Dikembalikan</option>
                                <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="flex-1 sm:flex-none px-6 py-2.5 sm:py-2 bg-pln-primary text-white rounded-lg hover:bg-pln-primary/90 transition">
                                Filter
                            </button>
                            @if(request()->hasAny(['search', 'status']))
                                <a href="{{ route('gudang.stok.barang-dipinjamkan') }}" class="px-4 py-2.5 sm:py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- Table --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                {{-- Mobile Card View --}}
                <div class="sm:hidden">
                    @forelse($peminjamans as $peminjaman)
                        @php
                            $activeStatuses = ['DITERIMA', 'DIKEMBALIKAN', 'MENUNGGU_DIKEMBALIKAN'];
                            $isActiveStatus = in_array($peminjaman->status, $activeStatuses);
                            $isOverdue = $peminjaman->batas_waktu_kembali &&
                                         $peminjaman->batas_waktu_kembali->isPast() &&
                                         $isActiveStatus;
                            $itemCount = $peminjaman->items->count();
                            $totalQty = $peminjaman->items->sum(fn($i) => $i->jumlah_dipinjam ?? $i->jumlah);
                            $statusColor = match($peminjaman->status) {
                                'DIKIRIM' => 'bg-blue-100 text-blue-800',
                                'DIPERIKSA' => 'bg-cyan-100 text-cyan-800',
                                'DITERIMA' => 'bg-yellow-100 text-yellow-800',
                                'DIKEMBALIKAN' => 'bg-orange-100 text-orange-800',
                                'MENUNGGU_DIKEMBALIKAN' => 'bg-yellow-100 text-yellow-800',
                                'SELESAI' => 'bg-green-100 text-green-800',
                                default => 'bg-gray-100 text-gray-800'
                            };
                        @endphp
                        <div class="p-4 border-b border-gray-200 {{ $isOverdue ? 'bg-red-50 border-l-4 border-l-red-500' : '' }}">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900 text-sm">{{ $peminjaman->kode }}</h3>
                                    <p class="text-xs text-gray-500">{{ $peminjaman->waktu_kirim?->format('d M Y') }}</p>
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                    {{ $peminjaman->status }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-2 mb-3 text-xs">
                                <div class="bg-gray-50 rounded p-2">
                                    <p class="text-gray-500">Dipinjam Oleh</p>
                                    <p class="font-medium text-gray-900 truncate">
                                        {{ $peminjaman->gudang_peminjam_is_custom ? ($peminjaman->gudang_peminjam_custom_nama ?? 'Gudang Lainnya') : ($peminjaman->gudangPeminjam->nama ?? '-') }}
                                    </p>
                                </div>
                                <div class="bg-gray-50 rounded p-2">
                                    <p class="text-gray-500">Item</p>
                                    <p class="font-medium text-gray-900">{{ $itemCount }} item ({{ $totalQty }} unit)</p>
                                </div>
                            </div>

                            @if($peminjaman->batas_waktu_kembali)
                                <div class="mb-3 text-xs">
                                    <span class="text-gray-500">Batas Waktu:</span>
                                    <span class="font-medium {{ $isOverdue ? 'text-red-600' : 'text-gray-900' }}">
                                        {{ $peminjaman->batas_waktu_kembali->format('d M Y') }}
                                    </span>
                                    @if($isOverdue)
                                        <span class="text-red-600 font-medium ml-1">(Terlambat)</span>
                                    @endif
                                </div>
                            @endif

                            <div class="flex items-center justify-end pt-2 border-t border-gray-100">
                                @if($peminjaman->suratJalanKirim)
                                    <a href="{{ route('gudang.surat-jalan.show', $peminjaman->suratJalanKirim->id) }}"
                                       class="inline-flex items-center gap-1 text-xs text-pln-primary hover:text-pln-primary/80 font-medium">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Lihat Detail
                                    </a>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <p class="font-medium">Tidak ada barang yang sedang dipinjamkan</p>
                        </div>
                    @endforelse
                </div>

                {{-- Desktop Table View --}}
                <div class="hidden sm:block overflow-x-auto">
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
                                    // Status yang masih aktif (belum selesai)
                                    $activeStatuses = ['DITERIMA', 'DIKEMBALIKAN', 'MENUNGGU_DIKEMBALIKAN'];
                                    $isActiveStatus = in_array($peminjaman->status, $activeStatuses);

                                    // Cek overdue - berlaku untuk status aktif yang melewati deadline
                                    $isOverdue = $peminjaman->batas_waktu_kembali &&
                                                 $peminjaman->batas_waktu_kembali->isPast() &&
                                                 $isActiveStatus;

                                    // Durasi dihitung dari waktu diterima (atau waktu kirim untuk gudang eksternal).
                                    $startDate = $peminjaman->gudang_peminjam_is_custom
                                        ? $peminjaman->waktu_kirim
                                        : $peminjaman->waktu_diterima;
                                    $endDate = $peminjaman->status === 'SELESAI' ? $peminjaman->waktu_selesai : now();
                                    $canCalculateDuration = $startDate !== null;
                                @endphp
                                <tr class="{{ $isOverdue ? 'bg-red-50' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $peminjaman->kode }}</div>
                                        <div class="text-xs text-gray-500">{{ $peminjaman->waktu_kirim?->format('d M Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $peminjaman->gudang_peminjam_is_custom ? ($peminjaman->gudang_peminjam_custom_nama ?? 'Gudang Lainnya') : ($peminjaman->gudangPeminjam->nama ?? '-') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $itemCount = $peminjaman->items->count();
                                            $totalQty = $peminjaman->items->sum(fn($i) => $i->jumlah_dipinjam ?? $i->jumlah);
                                        @endphp
                                        <div x-data="{ showItems: false }" @click.away="showItems = false" class="relative">
                                            <button @click="showItems = !showItems"
                                                    type="button"
                                                    class="inline-flex items-center gap-2 text-sm text-pln-primary hover:text-pln-primary/80 font-medium bg-pln-primary/5 px-3 py-1.5 rounded-lg transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                </svg>
                                                <span>{{ $itemCount }} item ({{ $totalQty }} unit)</span>
                                                <svg class="w-4 h-4 transition-transform" :class="showItems ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </button>
                                            <div x-show="showItems"
                                                 x-transition:enter="transition ease-out duration-100"
                                                 x-transition:enter-start="opacity-0 scale-95"
                                                 x-transition:enter-end="opacity-100 scale-100"
                                                 x-transition:leave="transition ease-in duration-75"
                                                 x-transition:leave-start="opacity-100 scale-100"
                                                 x-transition:leave-end="opacity-0 scale-95"
                                                 class="absolute left-0 mt-2 z-50 w-72 bg-white rounded-lg shadow-2xl border border-gray-200">
                                                <div class="bg-gray-50 px-3 py-2 border-b border-gray-200 rounded-t-lg">
                                                    <span class="text-xs font-semibold text-gray-500 uppercase">Daftar Item ({{ $itemCount }})</span>
                                                </div>
                                                <div class="p-2 space-y-1 {{ $itemCount > 3 ? 'max-h-36 overflow-y-auto' : '' }}">
                                                    @foreach($peminjaman->items as $item)
                                                        <div class="text-sm text-gray-900 flex justify-between items-center p-2 hover:bg-gray-50 rounded">
                                                            <div class="flex-1 min-w-0 mr-2">
                                                                <span class="font-medium">{{ $item->item->kode ?? '-' }}</span>
                                                                <span class="text-gray-500 block text-xs truncate">{{ $item->item->nama ?? '-' }}</span>
                                                            </div>
                                                            <span class="text-pln-primary font-bold bg-pln-primary/10 px-2 py-0.5 rounded shrink-0">{{ $item->jumlah_dipinjam ?? $item->jumlah }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($canCalculateDuration)
                                            @php
                                                $totalMinutes = $startDate->diffInMinutes($endDate);
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
                                                Sejak {{ $startDate->format('d M Y') }}
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-400 italic">Belum diterima</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($peminjaman->batas_waktu_kembali)
                                            @php
                                                $deadline = $peminjaman->batas_waktu_kembali;
                                                $now = now();
                                                $diffMinutes = $now->diffInMinutes($deadline, false);
                                                $isOverdueCalc = $diffMinutes < 0 && $isActiveStatus;
                                                $absDiffMinutes = abs($diffMinutes);

                                                $deadlineDays = floor($absDiffMinutes / (60 * 24));
                                                $deadlineHours = floor(($absDiffMinutes % (60 * 24)) / 60);
                                                $deadlineMinutes = $absDiffMinutes % 60;

                                                $deadlineParts = [];
                                                if ($deadlineDays > 0) $deadlineParts[] = $deadlineDays . ' hari';
                                                if ($deadlineHours > 0) $deadlineParts[] = $deadlineHours . ' jam';
                                                if ($deadlineMinutes > 0 && $deadlineDays < 1) $deadlineParts[] = $deadlineMinutes . ' menit';
                                                $deadlineText = !empty($deadlineParts) ? implode(', ', $deadlineParts) : '0 menit';
                                            @endphp
                                            <div class="text-sm font-medium {{ $isOverdueCalc ? 'text-red-600' : 'text-gray-900' }}">
                                                {{ $deadline->format('d M Y') }}
                                            </div>
                                            @if($isOverdueCalc)
                                                <div class="flex items-center gap-1 text-xs text-red-600 font-medium mt-1">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Terlambat {{ $deadlineText }}
                                                </div>
                                            @elseif($isActiveStatus && $diffMinutes > 0)
                                                <div class="text-xs {{ $deadlineDays <= 3 ? 'text-yellow-600 font-medium' : 'text-gray-500' }} mt-1">
                                                    Sisa {{ $deadlineText }}
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-sm text-gray-400 italic">Tidak ada batas</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusColor = match($peminjaman->status) {
                                                'DIKIRIM' => 'bg-blue-100 text-blue-800',
                                                'DIPERIKSA' => 'bg-cyan-100 text-cyan-800',
                                                'DITERIMA' => 'bg-yellow-100 text-yellow-800',
                                                'DIKEMBALIKAN' => 'bg-orange-100 text-orange-800',
                                                'MENUNGGU_DIKEMBALIKAN' => 'bg-yellow-100 text-yellow-800',
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
