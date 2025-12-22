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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Detail Surat Jalan</h2>
                            <p class="text-sm text-gray-500 mt-1">{{ $suratJalan->nomor }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            {{-- PDF Buttons --}}
                            <a href="{{ route('gudang.surat-jalan.preview', $suratJalan->id) }}"
                               target="_blank"
                               class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-md transition duration-150 flex items-center gap-2 text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Preview PDF
                            </a>
                            <a href="{{ route('gudang.surat-jalan.pdf', $suratJalan->id) }}"
                               class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-md transition duration-150 flex items-center gap-2 text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Download PDF
                            </a>
                            @if($suratJalan->status === 'DRAFT' && Auth::user()?->gudang_id === $suratJalan->gudang_asal_id)
                                <a href="{{ route('gudang.surat-jalan.edit', $suratJalan->id) }}"
                                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-md transition duration-150 text-sm">
                                    Edit Draft
                                </a>
                                <form method="POST" action="{{ route('gudang.surat-jalan.approve', $suratJalan->id) }}">
                                    @csrf
                                    <button type="submit"
                                            class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-md transition duration-150 text-sm">
                                        Approve & Kirim
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('gudang.surat-jalan.destroy', $suratJalan->id) }}"
                                      onsubmit="return confirm('Hapus draft surat jalan ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="bg-red-500 hover:bg-red-600 text-white font-semibold py-1.5 px-3 rounded-md transition duration-150 text-sm">
                                        Hapus Draft
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('gudang.surat-jalan.index') }}"
                               class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-md transition duration-150 text-sm">
                                Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $tipe = strtoupper($suratJalan->tipe ?? '');
                $status = strtoupper($suratJalan->status ?? 'DRAFT');

                if ($tipe === 'TRANSFER') {
                    $steps = ['Draft', 'Dikirim', 'Diterima', 'Selesai'];
                    $statusIndexMap = [
                        'DRAFT' => 0,
                        'DIKIRIM' => 1,
                        'DITERIMA' => 2,
                        'SELESAI' => 3,
                    ];
                } else {
                    // PEMINJAMAN dan PENGEMBALIAN pakai 1 garis yang sama
                    $steps = ['Draft', 'Dikirim', 'Diterima', 'Dikembalikan', 'Selesai'];
                    $statusIndexMap = [
                        'DRAFT' => 0,
                        'DIKIRIM' => 1,
                        'DITERIMA' => 2,
                        'DIKEMBALIKAN' => 3,
                        'SELESAI' => 4,
                    ];

                    // Jika tipe Pengembalian, anggap sudah di langkah Dikembalikan kecuali sudah Selesai
                    if ($tipe === 'PENGEMBALIAN' && $status !== 'SELESAI') {
                        $status = 'DIKEMBALIKAN';
                    }
                }

                $currentStep = $statusIndexMap[$status] ?? 0;
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
                                $stateClass = $isCompleted ? 'bg-green-500 text-white'
                                    : ($isActive ? 'bg-pln-primary text-white' : 'bg-gray-200 text-gray-600');
                            @endphp
                            <div class="flex items-center w-full">
                                <div class="flex flex-col items-center">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold {{ $stateClass }}">
                                        {{ $index + 1 }}
                                    </div>
                                    <span class="mt-2 text-xs font-semibold {{ $isCompleted || $isActive ? 'text-gray-900' : 'text-gray-500' }}">{{ $label }}</span>
                                </div>
                                @if($index < count($steps) - 1)
                                    <div class="flex-1 h-1 mx-2 rounded-full {{ $index < $currentStep ? 'bg-green-500' : 'bg-gray-200' }}"></div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-500">Gudang Asal</p>
                            <p class="font-semibold text-gray-900">{{ $suratJalan->gudangAsal->nama ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Gudang Tujuan</p>
                            <p class="font-semibold text-gray-900">{{ $suratJalan->gudangTujuan->nama ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">PIC Tujuan</p>
                            <p class="font-semibold text-gray-900">
                                {{ $suratJalan->picTujuan->nama ?? '-' }}
                                @if(!empty($suratJalan->picTujuan?->no_hp))
                                    <span class="text-sm text-gray-500 font-normal">({{ $suratJalan->picTujuan->no_hp }})</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Tanggal</p>
                            <p class="font-semibold text-gray-900">{{ $suratJalan->tanggal?->format('Y-m-d') ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Nama Driver</p>
                            <p class="font-semibold text-gray-900">{{ $suratJalan->nama_driver ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Jenis Kendaraan</p>
                            <p class="font-semibold text-gray-900">{{ $suratJalan->jenis_kendaraan ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Nomor Plat</p>
                            <p class="font-semibold text-gray-900">{{ $suratJalan->nomor_plat ?? '-' }}</p>
                        </div>
                        @if(($suratJalan->tipe ?? '') === 'PEMINJAMAN')
                            <div>
                                <p class="text-sm text-gray-500">Tanggal Pengembalian</p>
                                <p class="font-semibold text-gray-900">
                                    {{ $peminjaman?->waktu_pengembalian?->format('Y-m-d') ?? '-' }}
                                </p>
                            </div>
                        @endif
                        <div>
                            <p class="text-sm text-gray-500">Tipe</p>
                            <p class="font-semibold text-gray-900">{{ $suratJalan->tipe ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Status</p>
                            <p class="font-semibold text-gray-900">{{ $suratJalan->status ?? '-' }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm text-gray-500">Catatan</p>
                            <p class="font-semibold text-gray-900">{{ $suratJalan->catatan ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

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
        </div>
    </div>
</x-app-layout>
