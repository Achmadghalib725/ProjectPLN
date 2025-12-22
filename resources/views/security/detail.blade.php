<x-app-layout>
    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
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

            @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-900 px-4 py-3 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Header Card --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Detail Surat Jalan</h2>
                            <p class="text-sm text-gray-500 mt-1">{{ $suratJalan->nomor }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('dashboard') }}"
                               class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-4 rounded-md transition duration-150">
                                Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Progress Status --}}
            @php
                $tipe = strtoupper($suratJalan->tipe ?? '');
                $status = strtoupper($suratJalan->status ?? 'DRAFT');
                $isRejected = $status === 'DITOLAK';

                if ($isRejected) {
                    // Show rejection path
                    if ($tipe === 'PENGEMBALIAN') {
                        $steps = ['Draft', 'Dikembalikan', 'Ditolak'];
                    } else {
                        $steps = ['Draft', 'Dikirim', 'Ditolak'];
                    }
                    $statusIndexMap = [
                        'DRAFT' => 0,
                        'DIKIRIM' => 1,
                        'DIKEMBALIKAN' => 1,
                        'DITOLAK' => 2,
                    ];
                    $currentStep = 2;
                } elseif ($tipe === 'TRANSFER') {
                    $steps = ['Draft', 'Dikirim', 'Diperiksa', 'Selesai'];
                    $statusIndexMap = [
                        'DRAFT' => 0,
                        'DIKIRIM' => 1,
                        'DIPERIKSA' => 2,
                        'SELESAI' => 3,
                    ];
                    $currentStep = $statusIndexMap[$status] ?? 0;
                } elseif ($tipe === 'PENGEMBALIAN') {
                    // PENGEMBALIAN: Draft -> Dikembalikan -> Diperiksa -> Selesai
                    $steps = ['Draft', 'Dikembalikan', 'Diperiksa', 'Selesai'];
                    $statusIndexMap = [
                        'DRAFT' => 0,
                        'DIKEMBALIKAN' => 1,
                        'DIPERIKSA' => 2,
                        'SELESAI' => 3,
                    ];
                    $currentStep = $statusIndexMap[$status] ?? 0;
                } else {
                    // PEMINJAMAN: Draft -> Dikirim -> Diperiksa -> Diterima -> Selesai
                    $steps = ['Draft', 'Dikirim', 'Diperiksa', 'Diterima', 'Selesai'];
                    $statusIndexMap = [
                        'DRAFT' => 0,
                        'DIKIRIM' => 1,
                        'DIPERIKSA' => 2,
                        'DITERIMA' => 3,
                        'SELESAI' => 4,
                    ];
                    $currentStep = $statusIndexMap[$status] ?? 0;
                }

                $maxStep = count($steps) - 1;
                if ($currentStep > $maxStep) {
                    $currentStep = $maxStep;
                }
            @endphp

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Progress</h3>
                        <span class="text-sm text-gray-500">{{ $suratJalan->tipe ?? '-' }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        @foreach($steps as $index => $label)
                            @php
                                $isCompleted = $index < $currentStep;
                                $isActive = $index === $currentStep;

                                if ($isRejected && $index === $currentStep) {
                                    $stateClass = 'bg-red-500 text-white';
                                } elseif ($isCompleted) {
                                    $stateClass = 'bg-green-500 text-white';
                                } elseif ($isActive) {
                                    $stateClass = 'bg-pln-primary text-white';
                                } else {
                                    $stateClass = 'bg-gray-200 text-gray-600';
                                }
                            @endphp
                            <div class="flex items-center w-full">
                                <div class="flex flex-col items-center">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold {{ $stateClass }}">
                                        @if($isRejected && $index === $currentStep)
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        @else
                                            {{ $index + 1 }}
                                        @endif
                                    </div>
                                    <span class="mt-2 text-xs font-semibold {{ $isCompleted || $isActive ? ($isRejected && $isActive ? 'text-red-600' : 'text-gray-900') : 'text-gray-500' }}">{{ $label }}</span>
                                </div>
                                @if($index < count($steps) - 1)
                                    <div class="flex-1 h-1 mx-2 rounded-full {{ $index < $currentStep ? ($isRejected ? 'bg-red-500' : 'bg-green-500') : 'bg-gray-200' }}"></div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Info Card --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
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
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Ringkasan</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm text-gray-500">Tanggal</dt>
                                    <dd class="mt-1 text-gray-900 font-medium">{{ $suratJalan->tanggal?->format('Y-m-d') ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm text-gray-500">Tipe</dt>
                                    <dd class="mt-1 text-gray-900 font-medium">{{ $suratJalan->tipe ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm text-gray-500">Status</dt>
                                    <dd class="mt-1 text-gray-900 font-medium">{{ $suratJalan->status ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm text-gray-500">Catatan</dt>
                                    <dd class="mt-1 text-gray-900 font-medium">{{ $suratJalan->catatan ?? '-' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Items Table --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900">Item Surat Jalan</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($suratJalan->items as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $item->item->kode ?? '-' }} - {{ $item->item->nama ?? 'Item' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->jumlah }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->keterangan ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                                        Belum ada item.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Action Buttons --}}
            @if(in_array($suratJalan->status, ['DIKIRIM', 'DIKEMBALIKAN']))
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Konfirmasi Pemeriksaan</h3>
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
                                    <svg x-show="submitting" class="animate-spin w-6 h-6" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span x-text="submitting ? 'Memproses...' : 'Konfirmasi Diperiksa'"></span>
                                </button>
                            </form>

                            {{-- Tolak Button --}}
                            <div x-data="{ showModal: false }" class="flex-1 sm:flex-none">
                                <button type="button"
                                        @click="showModal = true"
                                        class="w-full px-6 py-4 bg-red-600 hover:bg-red-700 text-white font-bold text-lg rounded-lg shadow-sm transition duration-150 flex items-center justify-center gap-3">
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
                                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 z-0" @click="showModal = false"></div>
                                        <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6 z-10">
                                            <h3 class="text-lg font-bold text-gray-900 mb-4">Tolak Surat Jalan</h3>
                                            <form action="{{ route('security.tolak', $suratJalan->id) }}" method="POST"
                                                  x-data="{ submitting: false }"
                                                  @submit="submitting = true">
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
                                                            :disabled="submitting"
                                                            class="px-4 py-2 bg-red-600 hover:bg-red-700 disabled:bg-red-400 text-white font-medium rounded-lg flex items-center gap-2">
                                                        <svg x-show="submitting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                        </svg>
                                                        <span x-text="submitting ? 'Memproses...' : 'Konfirmasi Tolak'"></span>
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($suratJalan->status === 'DIPERIKSA')
                <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center mt-6">
                    <div class="inline-flex items-center px-6 py-3 bg-green-100 text-green-800 rounded-lg">
                        <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-semibold">Surat Jalan ini sudah DIPERIKSA - Menunggu konfirmasi operator</span>
                    </div>
                </div>
            @elseif($suratJalan->status === 'DITERIMA')
                <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center mt-6">
                    <div class="inline-flex items-center px-6 py-3 bg-green-100 text-green-800 rounded-lg">
                        <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-semibold">Surat Jalan ini sudah DITERIMA oleh operator</span>
                    </div>
                </div>
            @elseif($suratJalan->status === 'DITOLAK')
                <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center mt-6">
                    <div class="inline-flex items-center px-6 py-3 bg-red-100 text-red-800 rounded-lg">
                        <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-semibold">Surat Jalan ini telah DITOLAK</span>
                    </div>
                </div>
            @elseif($suratJalan->status === 'DRAFT')
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center mt-6">
                    <div class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-800 rounded-lg">
                        <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-semibold">Surat Jalan masih berstatus DRAFT, belum bisa dikonfirmasi</span>
                    </div>
                </div>
            @else
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center mt-6">
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
</x-app-layout>
