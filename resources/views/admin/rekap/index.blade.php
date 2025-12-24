<x-app-layout>
    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Header --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 tracking-tight">
                        Rekap Surat Jalan
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Export data surat jalan dari semua gudang untuk pengarsipan.</p>
                </div>
            </div>

            {{-- Export Form Card --}}
            <div class="bg-white border border-gray-100 rounded-2xl shadow-xl shadow-gray-200/50 overflow-hidden">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Export ke Excel
                    </h3>

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

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            {{-- Gudang Filter --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Gudang</label>
                                <select name="gudang_id"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-cyan-500 focus:ring focus:ring-cyan-500 focus:ring-opacity-50">
                                    <option value="">Semua Gudang</option>
                                    @foreach($gudangs as $gudang)
                                        <option value="{{ $gudang->id }}">{{ $gudang->kode }} - {{ $gudang->nama }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Type Filter --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Surat Jalan</label>
                                <select name="tipe"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-cyan-500 focus:ring focus:ring-cyan-500 focus:ring-opacity-50">
                                    <option value="ALL">Semua Tipe</option>
                                    <option value="TRANSFER">Transfer</option>
                                    <option value="PEMINJAMAN">Peminjaman</option>
                                    <option value="PENGEMBALIAN">Pengembalian</option>
                                </select>
                            </div>

                            {{-- Period Filter --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Periode</label>
                                <select name="periode"
                                        x-model="periode"
                                        @change="updatePeriode()"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-cyan-500 focus:ring focus:ring-cyan-500 focus:ring-opacity-50">
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
                        <div x-show="showCustom" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                                <input type="date"
                                       name="tanggal_mulai"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-cyan-500 focus:ring focus:ring-cyan-500 focus:ring-opacity-50">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                                <input type="date"
                                       name="tanggal_selesai"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-cyan-500 focus:ring focus:ring-cyan-500 focus:ring-opacity-50">
                            </div>
                        </div>

                        {{-- Submit Button --}}
                        <div class="flex justify-end pt-4 border-t">
                            <button type="submit"
                                    class="inline-flex items-center justify-center px-6 py-3 text-sm font-medium text-white transition-all duration-200 bg-gradient-to-r from-green-600 to-emerald-500 rounded-lg shadow-md hover:shadow-lg hover:from-green-700 hover:to-emerald-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Download Excel
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Info Card --}}
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-500 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <h4 class="text-sm font-semibold text-blue-800">Informasi Export</h4>
                        <p class="text-sm text-blue-700 mt-1">
                            File Excel akan berisi 19 kolom data lengkap meliputi: Nomor Surat Jalan, Tanggal, Tipe, Status,
                            Gudang Asal & Tujuan, Info Driver & Kendaraan, PIC Tujuan, Pembuat, Waktu TTD, Catatan, dan Total Item/Jumlah.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Column Preview Card --}}
            <div class="bg-white border border-gray-100 rounded-2xl shadow-lg overflow-hidden">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Kolom Data yang Diekspor</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                        @php
                            $columns = [
                                'No', 'Nomor Surat Jalan', 'Tanggal', 'Tipe', 'Status',
                                'Gudang Asal', 'Gudang Tujuan', 'Nama Driver', 'Jenis Kendaraan',
                                'Nomor Plat', 'PIC Nama', 'PIC Jabatan', 'PIC No HP',
                                'Dibuat Oleh', 'Waktu TTD Pembuat', 'Waktu TTD Penerima',
                                'Catatan', 'Total Jenis Item', 'Total Jumlah Barang'
                            ];
                        @endphp
                        @foreach($columns as $index => $column)
                            <div class="flex items-center gap-2 text-sm">
                                <span class="w-6 h-6 bg-gray-100 rounded text-xs font-medium text-gray-600 flex items-center justify-center">
                                    {{ $index + 1 }}
                                </span>
                                <span class="text-gray-700">{{ $column }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
