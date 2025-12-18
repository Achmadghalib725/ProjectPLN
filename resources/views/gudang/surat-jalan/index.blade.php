<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Flash Messages --}}
            @if(session('success'))
                <div x-data="{ show: true }"
                     x-show="show"
                     x-init="setTimeout(() => show = false, 3000)"
                     class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 text-red-900 px-4 py-3 rounded-lg">
                    <p class="font-semibold">Periksa kembali input Anda:</p>
                    <ul class="list-disc list-inside text-sm mt-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('warning'))
                <div class="mb-6 bg-yellow-50 border border-yellow-200 text-yellow-900 px-4 py-3 rounded-lg">
                    <p class="font-semibold">Peringatan stok:</p>
                    @if(is_array(session('warning')))
                        <ul class="list-disc list-inside text-sm mt-1">
                            @foreach(session('warning') as $warning)
                                <li>{{ $warning }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm mt-1">{{ session('warning') }}</p>
                    @endif
                </div>
            @endif

            {{-- Header --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Surat Jalan Barang</h2>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ Auth::user()->gudang->nama ?? 'Gudang Saya' }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">

                            <button type="button"
                                    class="inline-flex items-center px-4 py-2 bg-pln-primary hover:bg-pln-light text-white font-semibold rounded-md transition duration-150"
                                    @click="$dispatch('open-modal', 'create-surat-jalan')">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Buat Surat Jalan
                            </button>
                            <button type="button"
                                    class="inline-flex items-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-md transition duration-150"
                                    @click="$dispatch('open-modal', 'return-peminjaman')">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h13l4 4v6a2 2 0 01-2 2H6a2 2 0 01-2-2V7zm0 0V5a2 2 0 012-2h9"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17l4-4m0 0l4 4m-4-4v6"/>
                                </svg>
                                Pengembalian Peminjaman
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Statistics --}}
            <div class="grid grid-cols-1 md:grid-cols-6 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-pln-primary rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Surat Jalan</dt>
                                    <dd class="text-lg font-bold text-gray-900">{{ $stats['total'] ?? 0 }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-gray-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Draft</dt>
                                    <dd class="text-lg font-bold text-gray-900">{{ $stats['draft'] ?? 0 }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-pln-light rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17h6m-6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 104 0m-4 0h4v-5l-3-4h-5v9m0-9H5v9h4"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Dikirim</dt>
                                    <dd class="text-lg font-bold text-gray-900">{{ $stats['dikirim'] ?? 0 }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Diterima</dt>
                                    <dd class="text-lg font-bold text-gray-900">{{ $stats['diterima'] ?? 0 }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Dikembalikan</dt>
                                    <dd class="text-lg font-bold text-gray-900">{{ $stats['dikembalikan'] ?? 0 }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Selesai</dt>
                                    <dd class="text-lg font-bold text-gray-900">{{ $stats['selesai'] ?? 0 }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('gudang.surat-jalan.index') }}" class="grid grid-cols-1 md:grid-cols-7 gap-4">
                        <div class="md:col-span-2">
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari Nomor</label>
                            <input type="text"
                                   name="search"
                                   id="search"
                                   value="{{ $filters['search'] ?? '' }}"
                                   placeholder="Contoh: SJ-2025..."
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status"
                                    id="status"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                                <option value="">Semua</option>
                                @foreach(['DRAFT','DIKIRIM','DITERIMA','DIKEMBALIKAN','SELESAI'] as $statusOption)
                                    <option value="{{ $statusOption }}" {{ ($filters['status'] ?? '') === $statusOption ? 'selected' : '' }}>
                                        {{ $statusOption }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="tipe" class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
                            <select name="tipe"
                                    id="tipe"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                                <option value="">Semua</option>
                                @foreach(['TRANSFER','PEMINJAMAN','PENGEMBALIAN'] as $tipeOption)
                                    <option value="{{ $tipeOption }}" {{ ($filters['tipe'] ?? '') === $tipeOption ? 'selected' : '' }}>
                                        {{ $tipeOption }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="order_by" class="block text-sm font-medium text-gray-700 mb-1">Urutkan</label>
                            <select name="order_by"
                                    id="order_by"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                                <option value="terbaru" {{ ($filters['order_by'] ?? 'terbaru') === 'terbaru' ? 'selected' : '' }}>Terbaru</option>
                                <option value="terlama" {{ ($filters['order_by'] ?? '') === 'terlama' ? 'selected' : '' }}>Terlama</option>
                            </select>
                        </div>
                        <div>
                            <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700 mb-1">Dari</label>
                            <input type="date"
                                   name="tanggal_mulai"
                                   id="tanggal_mulai"
                                   value="{{ $filters['tanggal_mulai'] ?? '' }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                        </div>
                        <div>
                            <label for="tanggal_selesai" class="block text-sm font-medium text-gray-700 mb-1">Sampai</label>
                            <input type="date"
                                   name="tanggal_selesai"
                                   id="tanggal_selesai"
                                   value="{{ $filters['tanggal_selesai'] ?? '' }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit"
                                    class="flex-1 bg-pln-primary hover:bg-pln-light text-white font-medium py-2 px-4 rounded-md transition duration-150">
                                Filter
                            </button>
                            @if(($filters['search'] ?? '') || ($filters['status'] ?? '') || ($filters['tipe'] ?? '') || ($filters['tanggal_mulai'] ?? '') || ($filters['tanggal_selesai'] ?? '') || ($filters['order_by'] ?? 'terbaru') !== 'terbaru')
                                <a href="{{ route('gudang.surat-jalan.index') }}"
                                   class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-4 rounded-md transition duration-150">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- Table --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900">Daftar Surat Jalan</h3>
                        <div class="text-sm text-gray-500">Terbaru (maks. 20)</div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gudang Asal</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gudang Tujuan</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PIC</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ringkasan</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pembuat</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($suratJalans as $index => $sj)
                                @php
                                    $status = $sj->status ?? 'DRAFT';
                                    $displayStatus = $sj->tipe === 'PENGEMBALIAN' && $status === 'DIKIRIM'
                                        ? 'DIKEMBALIKAN'
                                        : $status;
                                    $statusClass = match ($status) {
                                        'DRAFT' => 'bg-gray-100 text-gray-800',
                                        'DIKIRIM' => 'bg-blue-100 text-blue-800',
                                        'DITERIMA' => 'bg-yellow-100 text-yellow-800',
                                        'SELESAI' => 'bg-green-100 text-green-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                    if ($displayStatus === 'DIKEMBALIKAN') {
                                        $statusClass = 'bg-indigo-100 text-indigo-800';
                                    }
                                @endphp
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        {{ $sj->nomor ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $sj->tanggal?->format('Y-m-d') ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $sj->gudangAsal->nama ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $sj->gudangTujuan->nama ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $sj->picTujuan->nama ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-pln-primary/10 text-pln-primary">
                                            {{ $sj->tipe ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusClass }}">
                                            {{ $displayStatus }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $sj->items_count ?? 0 }} item / {{ $sj->items_sum_jumlah ?? 0 }} unit
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $sj->pembuat->name ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center gap-2">
                                            @if(!empty($sj->id))
                                                <a href="{{ route('gudang.surat-jalan.show', $sj->id) }}"
                                                   class="text-pln-primary hover:text-pln-light"
                                                   title="Lihat Detail">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                </a>
                                            @else
                                                <span class="text-gray-300" title="Belum tersedia">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="px-6 py-10 text-center text-gray-500">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                        <p class="mt-2 font-medium">Belum ada Surat Jalan</p>
                                        <p class="text-sm">Mulai dengan membuat Surat Jalan baru.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            
        </div>
    </div>

    <x-modal name="create-surat-jalan" focusable>
        <div class="p-6"
             x-data="{
                mode: @js(old('mode', 'transfer')),
                items: @js(old('items', [['item_id' => '', 'jumlah' => 1, 'keterangan' => '']])),
                selectedGudang: @js(old('gudang_tujuan_id', '')),
                selectedPic: @js(old('pic_tujuan_id', '')),
                itemUnits: @js(($availableStocks ?? collect())->mapWithKeys(function ($stock) {
                    return [$stock->item_id => ($stock->item->satuan ?? '')];
                })),
                itemStocks: @js(($availableStocks ?? collect())->mapWithKeys(function ($stock) {
                    return [$stock->item_id => (int) ($stock->jumlah ?? 0)];
                })),
                pics: @js(($pics ?? collect())->map(fn($pic) => [
                    'id' => $pic->id,
                    'nama' => $pic->nama,
                    'jabatan' => $pic->jabatan,
                    'gudang_id' => $pic->gudang_id,
                    'no_hp' => $pic->no_hp,
                ])->values()),
                addRow() { this.items.push({ item_id: '', jumlah: 1, keterangan: '' }); },
                removeRow(i) { if (this.items.length > 1) this.items.splice(i, 1); },
                filteredPics() {
                    if (!this.selectedGudang) return [];
                    return this.pics.filter(pic => String(pic.gudang_id) === String(this.selectedGudang));
                },
                unitFor(itemId) {
                    if (!itemId) return '';
                    return this.itemUnits[itemId] ?? '';
                },
                stockFor(itemId) {
                    if (!itemId) return 0;
                    return this.itemStocks[itemId] ?? 0;
                },
                handleGudangChange() {
                    if (!this.selectedGudang) {
                        this.selectedPic = '';
                        return;
                    }
                    const match = this.filteredPics().some(pic => String(pic.id) === String(this.selectedPic));
                    if (!match) {
                        this.selectedPic = '';
                    }
                }
             }">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Buat Surat Jalan Baru</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Pilih jenis proses: Transfer Barang (antar gudang) atau Peminjaman Barang (ada tanggal pengembalian).
                    </p>
                </div>
                <button type="button" class="text-gray-400 hover:text-gray-600"
                        x-on:click="$dispatch('close-modal', 'create-surat-jalan')">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 mb-6">
                <button type="button"
                        class="flex-1 px-4 py-3 rounded-lg border text-sm font-semibold transition"
                        :class="mode === 'transfer' ? 'border-pln-primary bg-pln-primary/10 text-pln-primary' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'"
                        @click="mode = 'transfer'">
                    Transfer Barang
                    <div class="text-xs font-normal mt-1 text-gray-500">Perpindahan barang antar gudang (tanpa tanggal kembali)</div>
                </button>
                <button type="button"
                        class="flex-1 px-4 py-3 rounded-lg border text-sm font-semibold transition"
                        :class="mode === 'peminjaman' ? 'border-pln-primary bg-pln-primary/10 text-pln-primary' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'"
                        @click="mode = 'peminjaman'">
                    Peminjaman Barang
                    <div class="text-xs font-normal mt-1 text-gray-500">Peminjaman sementara (wajib tanggal pengembalian)</div>
                </button>
            </div>

            <form method="POST" action="{{ route('gudang.surat-jalan.store') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="mode" :value="mode">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gudang Tujuan</label>
                        <select name="gudang_tujuan_id"
                                x-model="selectedGudang"
                                @change="handleGudangChange()"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                            <option value="">Pilih gudang tujuan...</option>
                            @foreach($gudangs as $gudang)
                                <option value="{{ $gudang->id }}" {{ (string)old('gudang_tujuan_id') === (string)$gudang->id ? 'selected' : '' }}>
                                    {{ $gudang->kode }} - {{ $gudang->nama }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Gudang asal otomatis dari user login.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">PIC Tujuan <span class="text-red-500">*</span></label>
                        <select name="pic_tujuan_id"
                                x-model="selectedPic"
                                required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                            <option value="">Pilih PIC...</option>
                            <template x-for="pic in filteredPics()" :key="pic.id">
                                <option :value="pic.id" x-text="pic.nama + (pic.jabatan ? ' - ' + pic.jabatan : '')"></option>
                            </template>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">PIC wajib dipilih sesuai gudang tujuan.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pengiriman</label>
                        <input type="date"
                               name="tanggal_kirim"
                               value="{{ old('tanggal_kirim', now()->toDateString()) }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                    </div>

                    <div x-show="mode === 'peminjaman'">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pengembalian</label>
                        <input type="date"
                               name="tanggal_kembali"
                               value="{{ old('tanggal_kembali') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                        <p class="text-xs text-gray-500 mt-1">Dipakai untuk menghitung durasi peminjaman.</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                    <textarea name="catatan"
                              rows="3"
                              placeholder="Contoh: Untuk kebutuhan operasional / proyek ..."
                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">{{ old('catatan') }}</textarea>
                </div>

                <div class="bg-gray-50 rounded-lg border border-gray-200">
                    <div class="p-4 flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-gray-900">Daftar Barang</p>
                            <p class="text-xs text-gray-500">Minimal 1 item. Sumber item dari stok gudang Anda.</p>
                        </div>
                        <button type="button"
                                class="bg-pln-primary hover:bg-pln-light text-white text-sm font-semibold px-4 py-2 rounded-md transition"
                                @click="addRow()">
                            + Tambah Baris
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-white">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-28">Satuan</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Jumlah</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="(row, idx) in items" :key="idx">
                                    <tr>
                                        <td class="px-4 py-3">
                                            <select class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50"
                                                    x-model="row.item_id"
                                                    :name="`items[${idx}][item_id]`">
                                                <option value="">Pilih item...</option>
                                                @foreach($availableStocks as $stock)
                                                    <option value="{{ $stock->item_id }}">
                                                        {{ $stock->item->kode ?? '-' }} - {{ $stock->item->nama ?? 'Item' }} (Stok: {{ $stock->jumlah }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="text"
                                                   class="w-full rounded-md border-gray-300 bg-gray-50 text-gray-700 shadow-sm"
                                                   :value="unitFor(row.item_id)"
                                                   placeholder="-"
                                                   readonly>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number"
                                                   min="1"
                                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50"
                                                   x-model="row.jumlah"
                                                   :name="`items[${idx}][jumlah]`">
                                            <p x-show="row.item_id && row.jumlah > stockFor(row.item_id)"
                                               class="mt-1 text-xs text-red-600">
                                                Stok tidak cukup (tersedia <span x-text="stockFor(row.item_id)"></span>)
                                            </p>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="text"
                                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50"
                                                   placeholder="Opsional..."
                                                   x-model="row.keterangan"
                                                   :name="`items[${idx}][keterangan]`">
                                        </td>
                                        <td class="px-4 py-3">
                                            <button type="button"
                                                    class="text-red-600 hover:text-red-900"
                                                    title="Hapus baris"
                                                    @click="removeRow(idx)">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button type="button"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-4 rounded-md transition duration-150"
                            x-on:click="$dispatch('close-modal', 'create-surat-jalan')">
                        Batal
                    </button>
                    <button type="submit"
                            class="bg-pln-primary hover:bg-pln-light text-white font-semibold py-2 px-4 rounded-md transition duration-150">
                        Simpan Draft
                    </button>
                </div>
            </form>
        </div>
    </x-modal>

    <x-modal name="return-peminjaman" focusable>
        <div class="p-6"
             x-data="{
                selectedPeminjamanId: @js(old('peminjaman_id', '')),
                selectedPic: @js(old('pic_tujuan_id', '')),
                peminjamans: @js(($activePeminjamans ?? collect())->map(fn($p) => [
                    'id' => $p->id,
                    'kode' => $p->kode,
                    'gudang_pemilik_id' => $p->gudang_pemilik_id,
                    'gudang_pemilik_nama' => $p->gudangPemilik->nama ?? '-',
                    'items' => $p->items->map(fn($item) => [
                        'kode' => $item->item->kode ?? '-',
                        'nama' => $item->item->nama ?? 'Item',
                        'satuan' => $item->item->satuan ?? '-',
                        'jumlah' => $item->jumlah_dipinjam,
                    ]),
                ])->values()),
                pics: @js(($pics ?? collect())->map(fn($pic) => [
                    'id' => $pic->id,
                    'nama' => $pic->nama,
                    'jabatan' => $pic->jabatan,
                    'gudang_id' => $pic->gudang_id,
                ])->values()),
                selectedPeminjaman() {
                    return this.peminjamans.find(p => String(p.id) === String(this.selectedPeminjamanId));
                },
                filteredPics() {
                    const peminjaman = this.selectedPeminjaman();
                    if (!peminjaman) return [];
                    return this.pics.filter(pic => String(pic.gudang_id) === String(peminjaman.gudang_pemilik_id));
                },
                handlePeminjamanChange() {
                    const match = this.filteredPics().some(pic => String(pic.id) === String(this.selectedPic));
                    if (!match) {
                        this.selectedPic = '';
                    }
                }
             }">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Pengembalian Peminjaman Barang</h3>
                    <p class="text-sm text-gray-500 mt-1">Pilih kode peminjaman, lalu sistem menyiapkan surat jalan pengembalian.</p>
                </div>
                <button type="button" class="text-gray-400 hover:text-gray-600"
                        x-on:click="$dispatch('close-modal', 'return-peminjaman')">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('gudang.surat-jalan.return') }}" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kode Peminjaman</label>
                        <select name="peminjaman_id"
                                x-model="selectedPeminjamanId"
                                @change="handlePeminjamanChange()"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                            <option value="">Pilih kode peminjaman...</option>
                            <template x-for="p in peminjamans" :key="p.id">
                                <option :value="p.id" x-text="p.kode"></option>
                            </template>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Hanya peminjaman dengan status Dikirim/Diterima.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gudang Pemilik</label>
                        <input type="text"
                               class="w-full rounded-md border-gray-300 bg-gray-50 text-gray-700 shadow-sm"
                               :value="selectedPeminjaman() ? selectedPeminjaman().gudang_pemilik_nama : '-'"
                               readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">PIC Tujuan <span class="text-red-500">*</span></label>
                        <select name="pic_tujuan_id"
                                x-model="selectedPic"
                                required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                            <option value="">Pilih PIC...</option>
                            <template x-for="pic in filteredPics()" :key="pic.id">
                                <option :value="pic.id" x-text="pic.nama + (pic.jabatan ? ' - ' + pic.jabatan : '')"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pengiriman</label>
                        <input type="date"
                               name="tanggal_kirim"
                               value="{{ old('tanggal_kirim', now()->toDateString()) }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                    <textarea name="catatan"
                              rows="3"
                              placeholder="Contoh: Pengembalian barang sesuai peminjaman..."
                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">{{ old('catatan') }}</textarea>
                </div>

                <div class="bg-gray-50 rounded-lg border border-gray-200">
                    <div class="p-4">
                        <p class="font-semibold text-gray-900">Barang yang Dikembalikan</p>
                        <p class="text-xs text-gray-500">Jumlah otomatis penuh sesuai peminjaman.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-white">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Satuan</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-if="!selectedPeminjaman()">
                                    <tr>
                                        <td colspan="3" class="px-4 py-6 text-center text-sm text-gray-500">
                                            Pilih kode peminjaman untuk melihat item.
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="selectedPeminjaman()">
                                    <template x-for="(item, idx) in selectedPeminjaman().items" :key="idx">
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-900" x-text="item.kode + ' - ' + item.nama"></td>
                                            <td class="px-4 py-3 text-sm text-gray-500" x-text="item.satuan"></td>
                                            <td class="px-4 py-3 text-sm text-gray-900" x-text="item.jumlah"></td>
                                        </tr>
                                    </template>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button type="button"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-4 rounded-md transition duration-150"
                            x-on:click="$dispatch('close-modal', 'return-peminjaman')">
                        Batal
                    </button>
                    <button type="submit"
                            class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-4 rounded-md transition duration-150">
                        Simpan Draft Pengembalian
                    </button>
                </div>
            </form>
        </div>
    </x-modal>
</x-app-layout>
