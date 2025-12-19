<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            {{-- Flash Messages --}}
            @if(session('error'))
                <div x-data="{ show: true }"
                     x-show="show"
                     x-init="setTimeout(() => show = false, 5000)"
                     class="mb-6 bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center gap-3">
                    <svg class="w-6 h-6 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{-- Back Button --}}
            <div class="mb-6">
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center text-gray-600 hover:text-gray-900 transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Dashboard
                </a>
            </div>

            {{-- Main Card --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                {{-- Header --}}
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Detail Surat Jalan</h2>
                            <p class="text-gray-500 mt-1 font-mono text-lg">{{ $suratJalan->nomor }}</p>
                        </div>
                        <div>
                            @php
                                $statusBadge = match($suratJalan->status) {
                                    'DRAFT' => 'bg-gray-100 text-gray-800 border-gray-300',
                                    'DIKIRIM' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                                    'DITERIMA' => 'bg-green-100 text-green-800 border-green-300',
                                    'SELESAI' => 'bg-blue-100 text-blue-800 border-blue-300',
                                    default => 'bg-gray-100 text-gray-800 border-gray-300'
                                };
                            @endphp
                            <span class="px-4 py-2 text-lg font-bold rounded-lg border-2 {{ $statusBadge }}">
                                {{ $suratJalan->status }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Info Section --}}
                <div class="p-6 border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Informasi Pengiriman</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm text-gray-500">Tipe</dt>
                                    <dd class="mt-1">
                                        @php
                                            $tipeBadge = match($suratJalan->tipe) {
                                                'TRANSFER' => 'bg-blue-100 text-blue-800',
                                                'PEMINJAMAN' => 'bg-purple-100 text-purple-800',
                                                'PENGEMBALIAN' => 'bg-orange-100 text-orange-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                        @endphp
                                        <span class="px-2 py-1 text-sm font-semibold rounded-full {{ $tipeBadge }}">
                                            {{ $suratJalan->tipe }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm text-gray-500">Tanggal Kirim</dt>
                                    <dd class="mt-1 text-gray-900 font-medium">{{ \Carbon\Carbon::parse($suratJalan->tanggal)->format('d F Y') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm text-gray-500">Nama Driver</dt>
                                    <dd class="mt-1 text-gray-900 font-medium">{{ $suratJalan->nama_driver ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm text-gray-500">Jenis Kendaraan</dt>
                                    <dd class="mt-1 text-gray-900 font-medium">{{ $suratJalan->jenis_kendaraan ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm text-gray-500">Nomor Plat</dt>
                                    <dd class="mt-1 text-gray-900 font-medium">{{ $suratJalan->nomor_plat ?? '-' }}</dd>
                                </div>
                                @if($suratJalan->catatan)
                                <div>
                                    <dt class="text-sm text-gray-500">Catatan</dt>
                                    <dd class="mt-1 text-gray-900">{{ $suratJalan->catatan }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Gudang</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm text-gray-500">Gudang Asal</dt>
                                    <dd class="mt-1 text-gray-900 font-medium">
                                        {{ $suratJalan->gudangAsal->nama ?? '-' }}
                                        @if($suratJalan->gudangAsal?->kode)
                                            <span class="text-gray-500">({{ $suratJalan->gudangAsal->kode }})</span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm text-gray-500">Gudang Tujuan</dt>
                                    <dd class="mt-1 text-gray-900 font-medium">
                                        {{ $suratJalan->gudangTujuan->nama ?? '-' }}
                                        @if($suratJalan->gudangTujuan?->kode)
                                            <span class="text-gray-500">({{ $suratJalan->gudangTujuan->kode }})</span>
                                        @endif
                                    </dd>
                                </div>
                                @if($suratJalan->picTujuan)
                                <div>
                                    <dt class="text-sm text-gray-500">PIC Tujuan</dt>
                                    <dd class="mt-1 text-gray-900 font-medium">{{ $suratJalan->picTujuan->nama }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>

                {{-- Items Section --}}
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">Daftar Barang</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Barang</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Satuan</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($suratJalan->items as $index => $item)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $index + 1 }}</td>
                                        <td class="px-4 py-3 text-sm font-mono text-gray-900">{{ $item->item->kode ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $item->item->nama ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $item->jumlah }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $item->item->satuan ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                                            Tidak ada item.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Action Section --}}
                <div class="p-6 bg-gray-50">
                    @if($suratJalan->status === 'DIKIRIM')
                        <div class="flex flex-col sm:flex-row gap-4">
                            {{-- Terima Button --}}
                            <form action="{{ route('security.terima', $suratJalan->id) }}" method="POST" class="flex-1"
                                  x-data="{ submitting: false }"
                                  @submit="submitting = true">
                                @csrf
                                <button type="submit"
                                        :disabled="submitting"
                                        class="w-full px-6 py-4 bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white font-bold text-lg rounded-lg shadow-sm transition duration-150 flex items-center justify-center gap-3">
                                    <svg x-show="!submitting" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <svg x-show="submitting" class="animate-spin w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span x-text="submitting ? 'Memproses...' : 'Konfirmasi Diterima'"></span>
                                </button>
                            </form>

                            {{-- Tolak Button --}}
                            <div x-data="{ showModal: false }">
                                <button type="button"
                                        @click="showModal = true"
                                        class="w-full sm:w-auto px-6 py-4 bg-red-600 hover:bg-red-700 text-white font-bold text-lg rounded-lg shadow-sm transition duration-150 flex items-center justify-center gap-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Tolak
                                </button>

                                {{-- Reject Modal --}}
                                <div x-show="showModal"
                                     x-cloak
                                     class="fixed inset-0 z-50 overflow-y-auto"
                                     x-transition:enter="ease-out duration-300"
                                     x-transition:enter-start="opacity-0"
                                     x-transition:enter-end="opacity-100"
                                     x-transition:leave="ease-in duration-200"
                                     x-transition:leave-start="opacity-100"
                                     x-transition:leave-end="opacity-0">
                                    <div class="flex items-center justify-center min-h-screen px-4">
                                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showModal = false"></div>
                                        <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6 z-10">
                                            <h3 class="text-lg font-bold text-gray-900 mb-4">Tolak Surat Jalan</h3>
                                            <form action="{{ route('security.tolak', $suratJalan->id) }}" method="POST">
                                                @csrf
                                                <div class="mb-4">
                                                    <label for="alasan" class="block text-sm font-medium text-gray-700 mb-1">Alasan Penolakan</label>
                                                    <textarea name="alasan"
                                                              id="alasan"
                                                              rows="3"
                                                              required
                                                              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200"
                                                              placeholder="Masukkan alasan penolakan..."></textarea>
                                                </div>
                                                <div class="flex justify-end gap-3">
                                                    <button type="button"
                                                            @click="showModal = false"
                                                            class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg">
                                                        Batal
                                                    </button>
                                                    <button type="submit"
                                                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg">
                                                        Konfirmasi Tolak
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif($suratJalan->status === 'DITERIMA')
                        <div class="text-center py-4">
                            <div class="inline-flex items-center px-6 py-3 bg-green-100 text-green-800 rounded-lg">
                                <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="font-semibold">Surat Jalan ini sudah dikonfirmasi DITERIMA</span>
                            </div>
                        </div>
                    @elseif($suratJalan->status === 'DRAFT')
                        <div class="text-center py-4">
                            <div class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-800 rounded-lg">
                                <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <span class="font-semibold">Surat Jalan masih berstatus DRAFT, belum bisa dikonfirmasi</span>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <div class="inline-flex items-center px-6 py-3 bg-blue-100 text-blue-800 rounded-lg">
                                <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="font-semibold">Surat Jalan ini berstatus {{ $suratJalan->status }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
