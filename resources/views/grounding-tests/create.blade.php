<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Buat Surat Hasil Uji Grounding</h2>
                    <p class="text-sm text-gray-500 mt-1">Isi data titik ukur grounding dan lampiran per titik.</p>
                </div>
                <a href="{{ route('grounding-tests.index') }}"
                   class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-gray-700 transition-all duration-200 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-50">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali
                </a>
            </div>

            <div class="bg-white border border-gray-100 rounded-2xl shadow-xl shadow-gray-200/40 overflow-hidden">
                <div class="p-8">
                    @if($errors->any())
                        <div class="mb-6 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('grounding-tests.store') }}" enctype="multipart/form-data" autocomplete="off">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <x-input-label for="nama_pembuat" :value="__('Nama Pembuat')" class="text-gray-700 font-semibold" />
                                <x-text-input id="nama_pembuat" class="block w-full border-gray-200 focus:ring-cyan-500 focus:border-cyan-500 rounded-xl transition-all"
                                              type="text" name="nama_pembuat"
                                              :value="old('nama_pembuat', Auth::user()?->name)"
                                              placeholder="Nama pembuat" required />
                                <x-input-error :messages="$errors->get('nama_pembuat')" />
                            </div>
                            <div class="space-y-2">
                                <x-input-label for="tanggal" :value="__('Tanggal Uji')" class="text-gray-700 font-semibold" />
                                <x-text-input id="tanggal" class="block w-full border-gray-200 focus:ring-cyan-500 focus:border-cyan-500 rounded-xl transition-all" type="date" name="tanggal" :value="old('tanggal', now()->toDateString())" required />
                                <x-input-error :messages="$errors->get('tanggal')" />
                            </div>
                            <div class="space-y-2 md:col-span-3">
                                <x-input-label for="catatan" :value="__('Catatan (Opsional)')" class="text-gray-700 font-semibold" />
                                <textarea id="catatan" name="catatan" rows="3"
                                    class="block w-full border-gray-200 focus:ring-cyan-500 focus:border-cyan-500 rounded-xl transition-all"
                                    placeholder="Tambahkan catatan bila perlu...">{{ old('catatan') }}</textarea>
                                <x-input-error :messages="$errors->get('catatan')" />
                            </div>
                        </div>

                        <div class="mt-10 space-y-4">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">Detail Titik Ukur</h3>
                                    <p class="text-xs text-gray-500">Lampiran maksimal 1 gambar per titik ukur.</p>
                                </div>
                            </div>

                            @php
                                $items = old('items');
                                if (!$items || !is_array($items) || count($items) === 0) {
                                    $items = [
                                        ['titik_ukur' => '', 'kriteria' => '', 'hasil_uji' => ''],
                                    ];
                                }
                            @endphp

                            <div class="overflow-x-auto">
                                <table class="w-full border border-gray-200 rounded-xl overflow-hidden">
                                    <thead class="bg-gray-50 hidden sm:table-header-group">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">No</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Detail Pengukuran</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Lampiran</th>
                                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="grounding-items-body" data-next-index="{{ count($items) }}" class="block sm:table-row-group">
                                        @foreach($items as $index => $row)
                                            <tr class="border-t border-gray-100 block sm:table-row sm:border-0">
                                                <td class="px-4 py-3 text-sm text-gray-600 block sm:table-cell">
                                                    <span class="block text-[11px] font-semibold uppercase text-gray-400 sm:hidden">No</span>
                                                    <span data-row-number>{{ $index + 1 }}</span>
                                                </td>
                                                <td class="px-4 py-3 block sm:table-cell">
                                                    <div class="grid gap-3 sm:grid-cols-3">
                                                        <div class="space-y-1">
                                                            <span class="block text-[11px] font-semibold uppercase text-gray-400">Titik ukur</span>
                                                            <input type="text" name="items[{{ $index }}][titik_ukur]"
                                                                value="{{ $row['titik_ukur'] ?? '' }}"
                                                                class="w-full rounded-lg border-gray-200 focus:border-cyan-500 focus:ring-cyan-500"
                                                                placeholder="Contoh: Depan Ruang HAR" required>
                                                            <x-input-error :messages="$errors->get('items.' . $index . '.titik_ukur')" />
                                                        </div>
                                                        <div class="space-y-1">
                                                            <span class="block text-[11px] font-semibold uppercase text-gray-400">Kriteria</span>
                                                            <input type="number" name="items[{{ $index }}][kriteria]" inputmode="decimal" step="0.01"
                                                                value="{{ $row['kriteria'] ?? '' }}"
                                                                class="w-full rounded-lg border-gray-200 focus:border-cyan-500 focus:ring-cyan-500"
                                                                placeholder="Contoh: 0.12" required>
                                                            <x-input-error :messages="$errors->get('items.' . $index . '.kriteria')" />
                                                        </div>
                                                        <div class="space-y-1">
                                                            <span class="block text-[11px] font-semibold uppercase text-gray-400">Hasil uji</span>
                                                            <input type="number" name="items[{{ $index }}][hasil_uji]" inputmode="decimal" step="0.01"
                                                                value="{{ $row['hasil_uji'] ?? '' }}"
                                                                class="w-full rounded-lg border-gray-200 focus:border-cyan-500 focus:ring-cyan-500"
                                                                placeholder="Contoh: 0.07" required>
                                                            <x-input-error :messages="$errors->get('items.' . $index . '.hasil_uji')" />
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 block sm:table-cell">
                                                    <span class="block text-[11px] font-semibold uppercase text-gray-400 sm:hidden">Lampiran</span>
                                                    <div class="space-y-2">
                                                        <input type="file" name="items[{{ $index }}][attachment]" accept="image/png,image/jpeg"
                                                            class="block w-full text-sm text-gray-700 file:mr-3 file:rounded-lg file:border-0 file:bg-cyan-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-cyan-700 hover:file:bg-cyan-100">
                                                        <p class="text-[11px] text-gray-400">Format JPG/PNG, maks 10MB.</p>
                                                        <x-input-error :messages="$errors->get('items.' . $index . '.attachment')" />
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 text-right block sm:table-cell">
                                                    <span class="block text-[11px] font-semibold uppercase text-gray-400 sm:hidden">Aksi</span>
                                                    <button type="button" data-remove-row
                                                        class="text-sm text-red-600 hover:text-red-700">
                                                        Hapus
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="flex items-center justify-start">
                                <button type="button" id="add-grounding-row"
                                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-pln-primary border border-pln-primary/40 rounded-lg hover:bg-pln-primary/10">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Tambah Baris
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-10 pt-6 border-t border-gray-100 gap-4">
                            <a href="{{ route('grounding-tests.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">Batal</a>
                            <button type="submit"
                                    class="inline-flex items-center justify-center px-8 py-3 text-sm font-bold text-white transition-all duration-200 bg-gradient-to-r from-blue-600 to-cyan-500 rounded-xl shadow-md hover:shadow-lg hover:from-blue-700 hover:to-cyan-600">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Simpan Surat
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <template id="grounding-row-template">
        <tr class="border-t border-gray-100 block sm:table-row sm:border-0">
            <td class="px-4 py-3 text-sm text-gray-600 block sm:table-cell">
                <span class="block text-[11px] font-semibold uppercase text-gray-400 sm:hidden">No</span>
                <span data-row-number></span>
            </td>
            <td class="px-4 py-3 block sm:table-cell">
                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="space-y-1">
                        <span class="block text-[11px] font-semibold uppercase text-gray-400">Titik ukur</span>
                        <input type="text" name="items[__INDEX__][titik_ukur]"
                            class="w-full rounded-lg border-gray-200 focus:border-cyan-500 focus:ring-cyan-500"
                            placeholder="Contoh: Depan Ruang HAR" required>
                    </div>
                    <div class="space-y-1">
                        <span class="block text-[11px] font-semibold uppercase text-gray-400">Kriteria</span>
                        <input type="number" name="items[__INDEX__][kriteria]" inputmode="decimal" step="0.01"
                            class="w-full rounded-lg border-gray-200 focus:border-cyan-500 focus:ring-cyan-500"
                            placeholder="Contoh: 0.12" required>
                    </div>
                    <div class="space-y-1">
                        <span class="block text-[11px] font-semibold uppercase text-gray-400">Hasil uji</span>
                        <input type="number" name="items[__INDEX__][hasil_uji]" inputmode="decimal" step="0.01"
                            class="w-full rounded-lg border-gray-200 focus:border-cyan-500 focus:ring-cyan-500"
                            placeholder="Contoh: 0.07" required>
                    </div>
                </div>
            </td>
            <td class="px-4 py-3 block sm:table-cell">
                <span class="block text-[11px] font-semibold uppercase text-gray-400 sm:hidden">Lampiran</span>
                <div class="space-y-2">
                    <input type="file" name="items[__INDEX__][attachment]" accept="image/png,image/jpeg"
                        class="block w-full text-sm text-gray-700 file:mr-3 file:rounded-lg file:border-0 file:bg-cyan-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-cyan-700 hover:file:bg-cyan-100">
                    <p class="text-[11px] text-gray-400">Format JPG/PNG, maks 10MB.</p>
                </div>
            </td>
            <td class="px-4 py-3 text-right block sm:table-cell">
                <span class="block text-[11px] font-semibold uppercase text-gray-400 sm:hidden">Aksi</span>
                <button type="button" data-remove-row class="text-sm text-red-600 hover:text-red-700">Hapus</button>
            </td>
        </tr>
    </template>

    <script>
        (function () {
            function initGroundingRows() {
                const tableBody = document.getElementById('grounding-items-body');
                const template = document.getElementById('grounding-row-template');
                const addButton = document.getElementById('add-grounding-row');

                if (!tableBody || !template || !addButton) {
                    return;
                }

                if (addButton.dataset.bound === '1') {
                    return;
                }
                addButton.dataset.bound = '1';

                let nextIndex = parseInt(tableBody.dataset.nextIndex || '0', 10);

                const updateRowNumbers = () => {
                    Array.from(tableBody.querySelectorAll('[data-row-number]')).forEach((el, idx) => {
                        el.textContent = String(idx + 1);
                    });
                };

                const addRow = () => {
                    const html = template.innerHTML.replace(/__INDEX__/g, String(nextIndex));
                    const wrapper = document.createElement('tbody');
                    wrapper.innerHTML = html.trim();
                    const row = wrapper.firstElementChild;
                    tableBody.appendChild(row);
                    nextIndex += 1;
                    updateRowNumbers();
                };

                addButton.addEventListener('click', addRow);
                tableBody.addEventListener('click', (event) => {
                    const target = event.target.closest('[data-remove-row]');
                    if (!target) {
                        return;
                    }
                    const row = target.closest('tr');
                    if (!row) {
                        return;
                    }
                    row.remove();
                    updateRowNumbers();
                });

                updateRowNumbers();
            }

            document.addEventListener('DOMContentLoaded', initGroundingRows);
            document.addEventListener('turbo:load', initGroundingRows);
        })();
    </script>
</x-app-layout>

