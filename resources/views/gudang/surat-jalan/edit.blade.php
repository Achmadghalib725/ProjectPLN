@use('Illuminate\Support\Facades\Storage')
<x-app-layout>
    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            {{-- Flash Messages --}}
            @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-900 px-4 py-3 rounded-lg">
                    {{ session('error') }}
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Edit Draft Surat Jalan</h2>
                            <p class="text-sm text-gray-500 mt-1">{{ $suratJalan->nomor }}</p>
                        </div>
                        <a href="{{ route('gudang.surat-jalan.show', $suratJalan->id) }}"
                           class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-4 rounded-md transition duration-150">
                            Kembali
                        </a>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6"
                     x-data="{
                        items: @js(old('items', $suratJalan->items->map(fn($item) => [
                            'item_id' => $item->item_id,
                            'jumlah' => $item->jumlah,
                            'keterangan' => $item->keterangan,
                        ])->values())),
                        isPengembalian: @js($suratJalan->tipe === 'PENGEMBALIAN'),
                        gudangMode: @js(old('gudang_tujuan_mode', $suratJalan->gudang_tujuan_is_custom ? 'custom' : 'existing')),
                        selectedGudang: String(@js(old('gudang_tujuan_id', $suratJalan->gudang_tujuan_id)) || ''),
                        labelGudang: '',
                        gudangOpen: false,
                        initialPic: String(@js(old('pic_tujuan_id', $suratJalan->pic_tujuan_id ?? ($suratJalan->pic_tujuan_custom_nama ? 'lainnya' : ''))) || ''),
                        selectedPic: '',
                        allGudangs: @js(($gudangs ?? collect())->map(fn($gudang) => [
                            'id' => $gudang->id,
                            'kode' => $gudang->kode,
                            'nama' => $gudang->nama,
                        ])->values()),
                        customGudang: {
                            nama: @js(old('gudang_custom_nama', $suratJalan->gudang_tujuan_custom_nama)),
                            alamat: @js(old('gudang_custom_alamat', $suratJalan->gudang_tujuan_custom_alamat)),
                            telepon: @js(old('gudang_custom_telepon', $suratJalan->gudang_tujuan_custom_telepon)),
                        },
                        customPic: {
                            nama: @js(old('pic_custom_nama', $suratJalan->pic_tujuan_custom_nama)),
                            jabatan: @js(old('pic_custom_jabatan', $suratJalan->pic_tujuan_custom_jabatan)),
                            no_hp: @js(old('pic_custom_no_hp', $suratJalan->pic_tujuan_custom_no_hp)),
                        },
                        itemUnits: @js(($availableStocks ?? collect())->mapWithKeys(function ($stock) {
                            return [$stock->item_id => ($stock->item->satuan?->nama ?? '')];
                        })),
                        itemStocks: @js(($availableStocks ?? collect())->mapWithKeys(function ($stock) {
                            return [$stock->item_id => (int) ($stock->jumlah ?? 0)];
                        })),
                        pics: @js(($pics ?? collect())->map(fn($pic) => [
                            'id' => $pic->id,
                            'nama' => $pic->nama,
                            'jabatan' => $pic->jabatan,
                            'gudang_id' => $pic->gudang_id,
                        ])->values()),
                        addRow() { this.items.push({ item_id: '', jumlah: 1, keterangan: '' }); },
                        removeRow(i) { if (this.items.length > 1) this.items.splice(i, 1); },
                        filteredPics() {
                            // Untuk PENGEMBALIAN, selalu tampilkan PIC dari gudang tujuan
                            if (this.isPengembalian) {
                                return this.pics.filter(pic => String(pic.gudang_id) === String(this.selectedGudang));
                            }
                            if (this.isCustomGudang || !this.selectedGudang) return [];
                            return this.pics.filter(pic => String(pic.gudang_id) === String(this.selectedGudang));
                        },
                        storedPicOption() {
                            const targetPic = this.selectedPic || this.initialPic;
                            if (!targetPic || targetPic === 'lainnya') return null;
                            const inList = this.filteredPics().some(pic => String(pic.id) === String(targetPic));
                            if (inList) return null;
                            const pic = this.pics.find(item => String(item.id) === String(targetPic));
                            if (!pic) return { value: targetPic, label: 'PIC tersimpan (tidak ditemukan)' };
                            const label = pic.nama + (pic.jabatan ? ' - ' + pic.jabatan : '');
                            return { value: String(pic.id), label };
                        },
                        init() {
                            this.selectedPic = this.initialPic;
                            if (this.gudangMode === 'custom') {
                                this.selectedGudang = '';
                                this.labelGudang = this.customGudang.nama || 'Lainnya';
                            } else if (this.selectedGudang) {
                                const gudang = this.allGudangs.find(item => String(item.id) === String(this.selectedGudang));
                                this.labelGudang = gudang ? gudang.nama : '';
                            }
                            this.$nextTick(() => {
                                if (!this.selectedPic && this.initialPic) {
                                    this.selectedPic = this.initialPic;
                                }
                            });
                        },
                        get isCustomGudang() {
                            // Untuk PENGEMBALIAN, selalu anggap bukan custom
                            if (this.isPengembalian) return false;
                            return this.gudangMode === 'custom';
                        },
                        get isCustomPic() {
                            return this.selectedPic === 'lainnya';
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
                            if (this.isCustomGudang || !this.selectedGudang) {
                                this.selectedPic = '';
                                return;
                            }
                            const match = this.filteredPics().some(pic => String(pic.id) === String(this.selectedPic));
                            if (!match) {
                                this.selectedPic = '';
                            }
                        }
                     }">

                    <form method="POST" action="{{ route('gudang.surat-jalan.update', $suratJalan->id) }}" class="space-y-6" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="tipe" value="{{ $suratJalan->tipe }}">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Surat Jalan</label>
                                <input type="text"
                                       class="w-full rounded-md border-gray-300 bg-gray-50 text-gray-700 shadow-sm"
                                       value="{{ $suratJalan->tipe }}"
                                       readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Gudang Asal</label>
                                <input type="text"
                                       class="w-full rounded-md border-gray-300 bg-gray-50 text-gray-700 shadow-sm"
                                       value="{{ $suratJalan->gudangAsal->nama ?? '-' }}"
                                       readonly>
                            </div>

                            @if($suratJalan->tipe !== 'PENGEMBALIAN')
                                <div class="relative">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Gudang Tujuan</label>
                                    <div class="relative">
                                        <input type="text"
                                               x-model="labelGudang"
                                               @input="gudangOpen = true; selectedGudang = ''"
                                               @click="gudangOpen = true"
                                               @click.away="gudangOpen = false"
                                               placeholder="Cari gudang..."
                                               class="w-full rounded-md border-gray-300 shadow-sm focus:ring-pln-primary focus:border-pln-primary">

                                        <input type="hidden" name="gudang_tujuan_mode" :value="gudangMode">
                                        <input type="hidden" name="gudang_tujuan_id" :value="selectedGudang">

                                        <div x-show="gudangOpen" x-cloak class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                            <div class="p-2 border-b bg-gray-50 text-xs font-semibold text-gray-500 uppercase">Pilih dari Daftar</div>
                                            <template x-for="g in allGudangs.filter(item => item.nama.toLowerCase().includes(labelGudang.toLowerCase()))" :key="g.id">
                                                <button type="button"
                                                        @click="selectedGudang = g.id; labelGudang = g.nama; gudangMode = 'existing'; gudangOpen = false; handleGudangChange()"
                                                        class="w-full text-left px-4 py-2 text-sm hover:bg-pln-primary hover:text-white transition">
                                                    <span x-text="g.kode + ' - ' + g.nama"></span>
                                                </button>
                                            </template>
                                            <div class="border-t">
                                                <button type="button"
                                                        @click="gudangMode = 'custom'; selectedGudang = ''; labelGudang = customGudang.nama || 'Lainnya'; gudangOpen = false; selectedPic = 'lainnya'"
                                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition">
                                                    Lainnya...
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div x-show="isCustomGudang" class="md:col-span-2 bg-gray-50 border border-gray-200 rounded-lg p-4">
                                    <p class="text-sm font-semibold text-gray-900 mb-3">Gudang Lainnya</p>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Gudang</label>
                                            <input type="text" name="gudang_custom_nama" x-model="customGudang.nama"
                                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                                            <input type="text" name="gudang_custom_alamat" x-model="customGudang.alamat"
                                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">No Telp</label>
                                            <input type="text" name="gudang_custom_telepon" x-model="customGudang.telepon"
                                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode Peminjaman</label>
                                    <input type="text"
                                           class="w-full rounded-md border-gray-300 bg-gray-50 text-gray-700 shadow-sm"
                                           value="{{ $peminjaman->kode ?? '-' }}"
                                           readonly>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Gudang Pemilik (Tujuan)</label>
                                    <input type="text"
                                           class="w-full rounded-md border-gray-300 bg-gray-50 text-gray-700 shadow-sm"
                                           value="{{ $suratJalan->gudang_tujuan_is_custom ? ($suratJalan->gudang_tujuan_custom_nama ?? 'Gudang Lainnya') : ($peminjaman?->gudangPemilik?->nama ?? $suratJalan->gudangTujuan->nama ?? '-') }}"
                                           readonly>
                                </div>
                            @endif

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">PIC Tujuan <span class="text-red-500">*</span></label>
                                <select name="pic_tujuan_id"
                                        x-model="selectedPic"
                                        required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                                    <option value="">Pilih PIC...</option>
                                    <template x-if="storedPicOption()">
                                        <option :value="storedPicOption().value" x-text="storedPicOption().label"></option>
                                    </template>
                                    <template x-for="pic in filteredPics()" :key="pic.id">
                                        <option :value="String(pic.id)" x-text="pic.nama + (pic.jabatan ? ' - ' + pic.jabatan : '')"></option>
                                    </template>
                                    <option value="lainnya">Lainnya...</option>
                                </select>
                            </div>

                            <div x-show="isCustomPic" class="md:col-span-2 bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <p class="text-sm font-semibold text-gray-900 mb-3">PIC Lainnya</p>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama PIC</label>
                                        <input type="text" name="pic_custom_nama" x-model="customPic.nama"
                                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                                        <input type="text" name="pic_custom_jabatan" x-model="customPic.jabatan"
                                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">No HP</label>
                                        <input type="text" name="pic_custom_no_hp" x-model="customPic.no_hp"
                                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pengiriman</label>
                                <input type="date"
                                       name="tanggal_kirim"
                                       value="{{ old('tanggal_kirim', $suratJalan->tanggal?->format('Y-m-d')) }}"
                                       min="{{ date('Y-m-d') }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                            </div>

                            @if($suratJalan->tipe === 'PEMINJAMAN')
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pengembalian (Rencana)</label>
                                    <input type="date"
                                           name="tanggal_kembali"
                                           value="{{ old('tanggal_kembali', $peminjaman?->batas_waktu_kembali?->format('Y-m-d')) }}"
                                           min="{{ date('Y-m-d') }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                                </div>
                            @endif

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Driver</label>
                                <input type="text"
                                       name="nama_driver"
                                       value="{{ old('nama_driver', $suratJalan->nama_driver) }}"
                                       placeholder="Contoh: Budi Santoso"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kendaraan</label>
                                <input type="text"
                                       name="jenis_kendaraan"
                                       value="{{ old('jenis_kendaraan', $suratJalan->jenis_kendaraan) }}"
                                       placeholder="Contoh: Truk Box"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Plat</label>
                                <input type="text"
                                       name="nomor_plat"
                                       value="{{ old('nomor_plat', $suratJalan->nomor_plat) }}"
                                       placeholder="Contoh: B 1234 CD"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                            <textarea name="catatan"
                                      rows="3"
                                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">{{ old('catatan', $suratJalan->catatan) }}</textarea>
                        </div>

                        @if($suratJalan->tipe !== 'PENGEMBALIAN')
                            <div class="bg-gray-50 rounded-lg border border-gray-200">
                                <div class="p-4 flex items-center justify-between">
                                    <div>
                                        <p class="font-semibold text-gray-900">Daftar Barang</p>
                                        <p class="text-xs text-gray-500">Minimal 1 item. Sumber item dari stok gudang Anda.</p>
                                    </div>
                                    <button type="button"
                                            class="hidden sm:inline-flex bg-pln-primary hover:bg-pln-light text-white text-sm font-semibold px-4 py-2 rounded-md transition"
                                            @click="addRow()">
                                        + Tambah Baris
                                    </button>
                                </div>

                                {{-- Mobile Card Layout --}}
                                <div class="sm:hidden">
                                    <div class="divide-y divide-gray-200">
                                        <template x-for="(row, idx) in items" :key="idx">
                                            <div class="p-3 bg-white">
                                                <div class="flex items-start justify-between gap-2 mb-2">
                                                    <span class="text-xs font-semibold text-gray-500">Item #<span x-text="idx + 1"></span></span>
                                                    <button type="button" @click="removeRow(idx)" class="text-red-500 hover:text-red-700 p-1">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                    </button>
                                                </div>
                                                {{-- Barang --}}
                                                <div class="mb-3">
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Barang <span class="text-red-500">*</span></label>
                                                    <select class="w-full text-sm rounded-md border-gray-300 py-2.5"
                                                            x-model="row.item_id"
                                                            :name="`items[${idx}][item_id]`">
                                                        <option value="">Pilih item...</option>
                                                        @foreach($availableStocks as $stock)
                                                            <option value="{{ $stock->item_id }}">
                                                                {{ $stock->item->kode ?? '-' }} - {{ $stock->item->nama ?? 'Item' }} (Stok: {{ $stock->jumlah }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                {{-- Jumlah & Keterangan --}}
                                                <div class="grid grid-cols-3 gap-2">
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-600 mb-1">Jumlah <span class="text-red-500">*</span></label>
                                                        <input type="number" min="1" x-model="row.jumlah" :name="`items[${idx}][jumlah]`" class="w-full text-sm rounded-md border-gray-300 py-2.5">
                                                        <p x-show="row.item_id && row.jumlah > stockFor(row.item_id)" class="mt-1 text-xs text-red-600">
                                                            Stok tidak cukup
                                                        </p>
                                                    </div>
                                                    <div class="col-span-2">
                                                        <label class="block text-xs font-medium text-gray-600 mb-1">Keterangan</label>
                                                        <input type="text" x-model="row.keterangan" :name="`items[${idx}][keterangan]`" placeholder="Opsional..." class="w-full text-sm rounded-md border-gray-300 py-2.5">
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                    <button type="button" @click="addRow()" class="w-full py-3 bg-gray-50 text-sm font-bold text-pln-primary hover:bg-gray-100 uppercase border-t">+ Tambah Barang</button>
                                </div>

                                {{-- Desktop Table Layout --}}
                                <div class="hidden sm:block overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-white">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
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
                        @else
                            <div class="bg-gray-50 rounded-lg border border-gray-200">
                                <div class="p-4">
                                    <p class="font-semibold text-gray-900">Daftar Barang Pengembalian</p>
                                    <p class="text-xs text-gray-500">Jumlah mengikuti peminjaman dan tidak dapat diubah.</p>
                                </div>

                                {{-- Mobile Card Layout --}}
                                <div class="sm:hidden divide-y divide-gray-200">
                                    @foreach($suratJalan->items as $index => $item)
                                        <div class="p-3 bg-white">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-xs font-semibold text-gray-500">Item #{{ $index + 1 }}</span>
                                            </div>
                                            <p class="font-medium text-sm text-gray-900 mb-2">
                                                {{ $item->item->kode ?? '-' }} - {{ $item->item->nama ?? 'Item' }}
                                            </p>
                                            <div>
                                                <span class="block text-xs text-gray-500">Jumlah</span>
                                                <span class="text-sm font-semibold text-gray-900">{{ $item->jumlah }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Desktop Table Layout --}}
                                <div class="hidden sm:block overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-white">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($suratJalan->items as $item)
                                                <tr>
                                                    <td class="px-4 py-3 text-sm text-gray-900">
                                                        {{ $item->item->kode ?? '-' }} - {{ $item->item->nama ?? 'Item' }}
                                                    </td>
                                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $item->jumlah }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        {{-- Lampiran Gambar --}}
                        <div class="border border-dashed border-gray-300 rounded-xl bg-gray-50/50 overflow-hidden" data-camera-capture data-target-input="attachments-edit-gudang" data-max-files="{{ 3 - $suratJalan->attachments->count() }}">
                            {{-- Header --}}
                            <div class="flex items-center justify-between px-4 py-3 bg-white border-b border-gray-100">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-700">Lampiran Foto</span>
                                </div>
                                <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full" data-camera-status>{{ $suratJalan->attachments->count() }}/3</span>
                            </div>

                            <div data-delete-attachments></div>

                            {{-- Camera Panel (Hidden by default) --}}
                            <div data-camera-panel class="hidden bg-black">
                                <div class="relative">
                                    <video class="w-full h-48 object-cover" playsinline muted></video>
                                    <canvas class="hidden"></canvas>
                                    <div class="absolute bottom-3 left-0 right-0 flex items-center justify-center gap-3">
                                        <button type="button"
                                                data-camera-capture-btn
                                                class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-lg hover:bg-gray-100 transition-colors">
                                            <div class="w-10 h-10 bg-red-500 rounded-full"></div>
                                        </button>
                                        <button type="button"
                                                data-camera-close
                                                class="w-10 h-10 bg-gray-800/80 rounded-full flex items-center justify-center text-white hover:bg-gray-700 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Upload Area --}}
                            <div class="p-4">
                                @if($suratJalan->attachments->count() < 3)
                                <div class="flex flex-col sm:flex-row items-center gap-4">
                                    {{-- Action Buttons --}}
                                    <div class="flex items-center gap-2">
                                        <button type="button" data-camera-open class="md:hidden inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-colors">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            Kamera
                                        </button>
                                        <label class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-white bg-pln-primary rounded-lg hover:bg-pln-light cursor-pointer transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            Pilih File
                                            <input type="file"
                                                   id="attachments-edit-gudang"
                                                   name="attachments[]"
                                                   multiple
                                                   accept="image/jpeg,image/jpg,image/png"
                                                   capture="environment"
                                                   class="hidden">
                                        </label>
                                    </div>
                                    {{-- Info --}}
                                    <p class="text-xs text-gray-500">JPG, PNG. Maks 10MB.</p>
                                </div>
                                @else
                                <p class="text-sm text-amber-600 text-center mb-4">Slot lampiran penuh. Hapus salah satu untuk menambah yang baru.</p>
                                @endif

                                {{-- Grid Layout --}}
                                <div class="mt-4 grid grid-cols-3 gap-3">
                                    {{-- Existing Attachments (tidak di dalam data-camera-preview) --}}
                                    @foreach($suratJalan->attachments as $attachment)
                                        <div class="relative group" data-attachment-card data-attachment-id="{{ $attachment->id }}">
                                            <div class="aspect-square rounded-lg overflow-hidden border-2 border-gray-200 bg-white">
                                                <img src="{{ Storage::url($attachment->file_path) }}"
                                                     alt="{{ $attachment->file_name }}"
                                                     class="w-full h-full object-cover">
                                                <span class="hidden absolute top-1 left-1 rounded bg-red-600/90 text-white text-[10px] px-1.5 py-0.5" data-attachment-badge>
                                                    Hapus
                                                </span>
                                            </div>
                                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center">
                                                <button type="button"
                                                        class="p-1.5 bg-red-500 hover:bg-red-600 rounded-full text-white"
                                                        data-attachment-delete
                                                        title="Hapus">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                                <button type="button"
                                                        class="hidden p-1.5 bg-emerald-500 hover:bg-emerald-600 rounded-full text-white ml-2"
                                                        data-attachment-undo
                                                        title="Urungkan">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                                    </svg>
                                                </button>
                                            </div>
                                            <p class="text-xs text-red-600 mt-1 hidden text-center" data-attachment-status>Akan dihapus</p>
                                        </div>
                                    @endforeach

                                    {{-- Placeholders for new uploads (dengan data-camera-preview untuk JS) --}}
                                    <div class="contents" data-camera-preview>
                                        @for($i = 0; $i < 3 - $suratJalan->attachments->count(); $i++)
                                            <div class="aspect-square rounded-lg border-2 border-dashed border-gray-200 flex items-center justify-center bg-white" data-placeholder>
                                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/>
                                                </svg>
                                            </div>
                                        @endfor
                                    </div>
                                </div>

                                {{-- Error message --}}
                                <p class="text-xs text-red-600 mt-2 hidden" data-camera-error></p>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('gudang.surat-jalan.show', $suratJalan->id) }}"
                               class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-4 rounded-md transition duration-150">
                                Batal
                            </a>
                            <button type="submit"
                                    class="bg-pln-primary hover:bg-pln-light text-white font-semibold py-2 px-4 rounded-md transition duration-150">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function setupAttachmentDeletion() {
            const container = document.querySelector('[data-delete-attachments]');
            if (!container) {
                return;
            }

            const ensureInput = (id) => {
                if (container.querySelector(`input[value="${id}"]`)) {
                    return;
                }
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'delete_attachments[]';
                input.value = id;
                container.appendChild(input);
            };

            const removeInput = (id) => {
                const input = container.querySelector(`input[value="${id}"]`);
                if (input) {
                    input.remove();
                }
            };

            const setPending = (card, isPending) => {
                card.classList.toggle('opacity-60', isPending);
                card.classList.toggle('ring-2', isPending);
                card.classList.toggle('ring-red-200', isPending);
                const deleteBtn = card.querySelector('[data-attachment-delete]');
                const undoBtn = card.querySelector('[data-attachment-undo]');
                const status = card.querySelector('[data-attachment-status]');
                const badge = card.querySelector('[data-attachment-badge]');

                if (deleteBtn) {
                    deleteBtn.classList.toggle('hidden', isPending);
                }
                if (undoBtn) {
                    undoBtn.classList.toggle('hidden', !isPending);
                }
                if (status) {
                    status.classList.toggle('hidden', !isPending);
                }
                if (badge) {
                    badge.classList.toggle('hidden', !isPending);
                }
            };

            document.addEventListener('click', (event) => {
                const deleteBtn = event.target.closest('[data-attachment-delete]');
                if (deleteBtn) {
                    const card = deleteBtn.closest('[data-attachment-card]');
                    if (!card) {
                        return;
                    }
                    const attachmentId = card.dataset.attachmentId;
                    if (!attachmentId) {
                        return;
                    }
                    ensureInput(attachmentId);
                    setPending(card, true);
                }

                const undoBtn = event.target.closest('[data-attachment-undo]');
                if (undoBtn) {
                    const card = undoBtn.closest('[data-attachment-card]');
                    if (!card) {
                        return;
                    }
                    const attachmentId = card.dataset.attachmentId;
                    if (!attachmentId) {
                        return;
                    }
                    removeInput(attachmentId);
                    setPending(card, false);
                }
            });
        }

        function setupCameraCapture(wrapper) {
            const inputId = wrapper.dataset.targetInput;
            const input = document.getElementById(inputId);
            const maxFiles = Number(wrapper.dataset.maxFiles || 3);
            const openBtn = wrapper.querySelector('[data-camera-open]');
            const captureBtn = wrapper.querySelector('[data-camera-capture-btn]');
            const closeBtn = wrapper.querySelector('[data-camera-close]');
            const panel = wrapper.querySelector('[data-camera-panel]');
            const video = wrapper.querySelector('video');
            const canvas = wrapper.querySelector('canvas');
            const error = wrapper.querySelector('[data-camera-error]');
            const status = wrapper.querySelector('[data-camera-status]');
            const preview = wrapper.querySelector('[data-camera-preview]');

            // Store collected files separately to prevent browser replacing them
            wrapper._collectedFiles = [];

            const setError = (message) => {
                if (!error) {
                    return;
                }
                if (message) {
                    error.textContent = message;
                    error.classList.remove('hidden');
                } else {
                    error.textContent = '';
                    error.classList.add('hidden');
                }
            };

            const updateStatus = () => {
                if (!status) {
                    return;
                }
                const count = wrapper._collectedFiles.length;
                status.textContent = `${count}/${maxFiles}`;
            };

            const syncToInput = () => {
                if (!input) {
                    return;
                }
                const dataTransfer = new DataTransfer();
                wrapper._collectedFiles.forEach((file) => dataTransfer.items.add(file));
                input.files = dataTransfer.files;
            };

            const clearPreview = () => {
                if (wrapper._objectUrls) {
                    wrapper._objectUrls.forEach((url) => URL.revokeObjectURL(url));
                }
                wrapper._objectUrls = [];
                if (preview) {
                    preview.innerHTML = '';
                }
            };

            const removeFile = (index) => {
                wrapper._collectedFiles.splice(index, 1);
                syncToInput();
                renderPreview();
            };

            const renderPreview = () => {
                updateStatus();
                if (!preview) {
                    return;
                }
                clearPreview();
                const files = wrapper._collectedFiles;

                // Hide preview grid if no files
                if (files.length === 0) {
                    preview.classList.add('hidden');
                    return;
                }

                // Show preview grid and add file previews
                preview.classList.remove('hidden');
                files.forEach((file, index) => {
                    const url = URL.createObjectURL(file);
                    wrapper._objectUrls.push(url);

                    const item = document.createElement('div');
                    item.className = 'aspect-square rounded-lg overflow-hidden border-2 border-gray-200 bg-white relative group';

                    const img = document.createElement('img');
                    img.className = 'w-full h-full object-cover';
                    img.src = url;
                    img.alt = file.name;

                    const overlay = document.createElement('div');
                    overlay.className = 'absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center';

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'p-1.5 bg-red-500 hover:bg-red-600 rounded-full text-white';
                    removeBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>';
                    removeBtn.addEventListener('click', () => removeFile(index));

                    overlay.appendChild(removeBtn);
                    item.appendChild(img);
                    item.appendChild(overlay);
                    preview.appendChild(item);
                });
            };

            const isAllowedFile = (file) => {
                const type = (file.type || '').toLowerCase();
                if (type === 'image/jpeg' || type === 'image/png' || type === 'image/jpg') {
                    return true;
                }
                const name = (file.name || '').toLowerCase();
                return name.endsWith('.jpg') || name.endsWith('.jpeg') || name.endsWith('.png');
            };

            const appendFiles = (files) => {
                if (!files || files.length === 0) {
                    return;
                }
                setError('');
                files.forEach((file) => {
                    if (!isAllowedFile(file)) {
                        setError('Hanya mendukung file JPG/PNG.');
                        return;
                    }
                    if (wrapper._collectedFiles.length < maxFiles) {
                        wrapper._collectedFiles.push(file);
                    }
                });
                if (wrapper._collectedFiles.length > maxFiles) {
                    setError(`Maksimal ${maxFiles} gambar.`);
                    wrapper._collectedFiles = wrapper._collectedFiles.slice(0, maxFiles);
                }
                syncToInput();
                renderPreview();
            };

            const setHighlight = (active) => {
                wrapper.classList.toggle('ring-2', active);
                wrapper.classList.toggle('ring-pln-primary', active);
                wrapper.classList.toggle('border-pln-primary', active);
                wrapper.classList.toggle('bg-blue-50/50', active);
            };

            const normalizeFiles = () => {
                if (!input) {
                    return;
                }
                appendFiles(Array.from(input.files || []));
            };

            const stopCamera = () => {
                if (wrapper._cameraStream) {
                    wrapper._cameraStream.getTracks().forEach((track) => track.stop());
                    wrapper._cameraStream = null;
                }
                if (video) {
                    video.srcObject = null;
                }
                if (panel) {
                    panel.classList.add('hidden');
                }
            };

            const openCamera = async () => {
                setError('');
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    setError('Browser tidak mendukung akses kamera.');
                    return;
                }
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({
                        video: { facingMode: 'environment' },
                        audio: false,
                    });
                    wrapper._cameraStream = stream;
                    if (video) {
                        video.srcObject = stream;
                        await video.play();
                    }
                    if (panel) {
                        panel.classList.remove('hidden');
                    }
                } catch (err) {
                    setError('Tidak bisa mengakses kamera. Pastikan izin kamera diaktifkan.');
                }
            };

            const capturePhoto = () => {
                setError('');
                if (!input) {
                    setError('Input lampiran tidak ditemukan.');
                    return;
                }
                if (!video || !canvas) {
                    setError('Kamera belum siap.');
                    return;
                }
                if (wrapper._collectedFiles.length >= maxFiles) {
                    setError(`Maksimal ${maxFiles} gambar.`);
                    return;
                }
                const width = video.videoWidth || 1280;
                const height = video.videoHeight || 720;
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                if (!ctx) {
                    setError('Gagal mengambil gambar.');
                    return;
                }
                ctx.drawImage(video, 0, 0, width, height);
                canvas.toBlob((blob) => {
                    if (!blob) {
                        setError('Gagal menyimpan gambar.');
                        return;
                    }
                    const fileName = `camera-${Date.now()}.jpg`;
                    const file = new File([blob], fileName, { type: 'image/jpeg' });
                    wrapper._collectedFiles.push(file);
                    syncToInput();
                    renderPreview();
                }, 'image/jpeg', 0.9);
            };

            if (input) {
                input.addEventListener('change', normalizeFiles);
            }
            if (openBtn) {
                openBtn.addEventListener('click', openCamera);
            }
            if (closeBtn) {
                closeBtn.addEventListener('click', stopCamera);
            }
            if (captureBtn) {
                captureBtn.addEventListener('click', capturePhoto);
            }
            wrapper._dragCounter = 0;
            wrapper.addEventListener('dragenter', (event) => {
                event.preventDefault();
                wrapper._dragCounter += 1;
                setHighlight(true);
            });
            wrapper.addEventListener('dragover', (event) => {
                event.preventDefault();
                event.dataTransfer.dropEffect = 'copy';
                setHighlight(true);
            });
            wrapper.addEventListener('dragleave', (event) => {
                event.preventDefault();
                wrapper._dragCounter = Math.max(0, wrapper._dragCounter - 1);
                if (wrapper._dragCounter === 0) {
                    setHighlight(false);
                }
            });
            wrapper.addEventListener('drop', (event) => {
                event.preventDefault();
                wrapper._dragCounter = 0;
                setHighlight(false);
                const files = Array.from(event.dataTransfer?.files || []);
                appendFiles(files);
            });
            wrapper._stopCamera = stopCamera;
            renderPreview();
        }

        function initCameraCaptures() {
            document.querySelectorAll('[data-camera-capture]').forEach(setupCameraCapture);
        }

        document.addEventListener('DOMContentLoaded', () => {
            initCameraCaptures();
            setupAttachmentDeletion();
        });
    </script>
</x-app-layout>
