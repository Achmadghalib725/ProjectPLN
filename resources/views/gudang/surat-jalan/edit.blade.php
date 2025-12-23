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
                        gudangMode: @js(old('gudang_tujuan_mode', $suratJalan->gudang_tujuan_is_custom ? 'custom' : 'existing')),
                        selectedGudang: @js(old('gudang_tujuan_id', $suratJalan->gudang_tujuan_id)),
                        selectedPic: @js(old('pic_tujuan_id', $suratJalan->pic_tujuan_id)),
                        customGudang: {
                            nama: @js(old('gudang_custom_nama', $suratJalan->gudang_tujuan_custom_nama)),
                            alamat: @js(old('gudang_custom_alamat', $suratJalan->gudang_tujuan_custom_alamat)),
                            telepon: @js(old('gudang_custom_telepon', $suratJalan->gudang_tujuan_custom_telepon)),
                        },
                        customPic: {
                            nama: @js(old('pic_custom_nama', '')),
                            jabatan: @js(old('pic_custom_jabatan', '')),
                            no_hp: @js(old('pic_custom_no_hp', '')),
                        },
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
                        ])->values()),
                        addRow() { this.items.push({ item_id: '', jumlah: 1, keterangan: '' }); },
                        removeRow(i) { if (this.items.length > 1) this.items.splice(i, 1); },
                        filteredPics() {
                            if (this.isCustomGudang || !this.selectedGudang) return [];
                            return this.pics.filter(pic => String(pic.gudang_id) === String(this.selectedGudang));
                        },
                        get isCustomGudang() {
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

                    <form method="POST" action="{{ route('gudang.surat-jalan.update', $suratJalan->id) }}" class="space-y-6">
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
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Gudang Tujuan</label>
                                    <select name="gudang_tujuan_mode"
                                            x-model="gudangMode"
                                            @change="if (isCustomGudang) { selectedGudang = ''; selectedPic = 'lainnya'; }"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                                        <option value="existing">Gudang Terdaftar</option>
                                        <option value="custom">Gudang Lainnya</option>
                                    </select>
                                </div>
                                <div x-show="!isCustomGudang">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Gudang Tujuan</label>
                                    <select name="gudang_tujuan_id"
                                            x-model="selectedGudang"
                                            @change="handleGudangChange()"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                                        <option value="">Pilih gudang tujuan...</option>
                                        @foreach($gudangs as $gudang)
                                            <option value="{{ $gudang->id }}" {{ (string)old('gudang_tujuan_id', $suratJalan->gudang_tujuan_id) === (string)$gudang->id ? 'selected' : '' }}>
                                                {{ $gudang->kode }} - {{ $gudang->nama }}
                                            </option>
                                        @endforeach
                                    </select>
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
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Gudang Tujuan</label>
                                    <input type="text"
                                           class="w-full rounded-md border-gray-300 bg-gray-50 text-gray-700 shadow-sm"
                                           value="{{ $suratJalan->gudang_tujuan_is_custom ? ($suratJalan->gudang_tujuan_custom_nama ?? 'Gudang Lainnya') : ($suratJalan->gudangTujuan->nama ?? '-') }}"
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
                                    <template x-for="pic in filteredPics()" :key="pic.id">
                                        <option :value="pic.id" x-text="pic.nama + (pic.jabatan ? ' - ' + pic.jabatan : '')"></option>
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
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-pln-primary focus:ring focus:ring-pln-primary focus:ring-opacity-50">
                            </div>

                            @if($suratJalan->tipe === 'PEMINJAMAN')
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pengembalian (Rencana)</label>
                                    <input type="date"
                                           name="tanggal_kembali"
                                           value="{{ old('tanggal_kembali', $peminjaman?->waktu_pengembalian?->format('Y-m-d')) }}"
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
                                <div class="overflow-x-auto">
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
</x-app-layout>
