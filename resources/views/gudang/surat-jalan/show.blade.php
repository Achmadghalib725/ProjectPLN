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
                               class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-md transition duration-150 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Preview PDF
                            </a>
                            <a href="{{ route('gudang.surat-jalan.pdf', $suratJalan->id) }}"
                               class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-md transition duration-150 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Download PDF
                            </a>
                            @if($suratJalan->status === 'DRAFT' && Auth::user()?->gudang_id === $suratJalan->gudang_asal_id)
                                <a href="{{ route('gudang.surat-jalan.edit', $suratJalan->id) }}"
                                   class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-4 rounded-md transition duration-150">
                                    Edit Draft
                                </a>
                                <form method="POST" action="{{ route('gudang.surat-jalan.approve', $suratJalan->id) }}">
                                    @csrf
                                    <button type="submit"
                                            class="bg-pln-primary hover:bg-pln-light text-white font-semibold py-2 px-4 rounded-md transition duration-150">
                                        Approve & Kirim
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('gudang.surat-jalan.destroy', $suratJalan->id) }}"
                                      onsubmit="return confirm('Hapus draft surat jalan ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded-md transition duration-150">
                                        Hapus Draft
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('gudang.surat-jalan.index') }}"
                               class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-4 rounded-md transition duration-150">
                                Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $tipe = strtoupper($suratJalan->tipe ?? '');
                $status = strtoupper($suratJalan->status ?? 'DRAFT');
                $isRejected = $status === 'DITOLAK';

                if ($isRejected) {
                    // Show rejection path
                    $steps = ['Draft', 'Dikirim', 'Ditolak'];
                    $statusIndexMap = [
                        'DRAFT' => 0,
                        'DIKIRIM' => 1,
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
                    // PEMINJAMAN: Draft -> Dikirim -> Diperiksa -> Diterima -> (menunggu pengembalian) -> Selesai
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

            {{-- Action Buttons for Operator --}}
            @php
                $userGudangId = Auth::user()?->gudang_id;
                $isGudangTujuan = $userGudangId === $suratJalan->gudang_tujuan_id;
                $isGudangAsal = $userGudangId === $suratJalan->gudang_asal_id;
            @endphp

            {{-- Tombol Terima Barang untuk Operator Gudang Tujuan (status DIPERIKSA) --}}
            @if($suratJalan->status === 'DIPERIKSA' && $isGudangTujuan)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Konfirmasi Penerimaan</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            @if($suratJalan->tipe === 'PENGEMBALIAN')
                                Barang pengembalian telah diperiksa oleh security. Klik tombol di bawah untuk menerima barang dan menyelesaikan proses peminjaman.
                            @else
                                Barang telah diperiksa oleh security. Klik tombol di bawah untuk menerima barang ke gudang Anda.
                            @endif
                        </p>
                        <form method="POST" action="{{ route('gudang.surat-jalan.terima', $suratJalan->id) }}"
                              x-data="{ submitting: false }"
                              @submit="submitting = true">
                            @csrf
                            <button type="submit"
                                    :disabled="submitting"
                                    class="w-full sm:w-auto px-6 py-3 bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white font-bold rounded-lg shadow-sm transition duration-150 flex items-center justify-center gap-3">
                                <svg x-show="!submitting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <svg x-show="submitting" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-text="submitting ? 'Memproses...' : '{{ $suratJalan->tipe === "PENGEMBALIAN" ? "Terima & Selesaikan Peminjaman" : "Terima Barang" }}'"></span>
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Tombol Pengembalian Pinjaman untuk Operator Gudang Peminjam (status DITERIMA, tipe PEMINJAMAN) --}}
            @if($suratJalan->tipe === 'PEMINJAMAN' && $suratJalan->status === 'DITERIMA' && $isGudangTujuan)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Pengembalian Barang</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Barang peminjaman sudah diterima. Jika sudah selesai digunakan, Anda dapat membuat surat jalan pengembalian.
                        </p>
                        <a href="{{ route('gudang.surat-jalan.index') }}?open_return=1&peminjaman_id={{ $peminjaman?->id }}"
                           class="inline-flex items-center px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-lg shadow-sm transition duration-150 gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                            </svg>
                            Pengembalian Pinjaman
                        </a>
                    </div>
                </div>
            @endif

            {{-- Status Info Cards --}}
            @if($suratJalan->status === 'DIKIRIM' || $suratJalan->status === 'DIKEMBALIKAN')
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center mt-6">
                    <div class="inline-flex items-center px-6 py-3 bg-blue-100 text-blue-800 rounded-lg">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-semibold">Menunggu pemeriksaan oleh Security</span>
                    </div>
                </div>
            @elseif($suratJalan->status === 'DITERIMA' && $suratJalan->tipe === 'PEMINJAMAN' && !$isGudangTujuan)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center mt-6">
                    <div class="inline-flex items-center px-6 py-3 bg-yellow-100 text-yellow-800 rounded-lg">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-semibold">Barang dipinjam - Menunggu pengembalian</span>
                    </div>
                </div>
            @elseif($suratJalan->status === 'SELESAI')
                <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center mt-6">
                    <div class="inline-flex items-center px-6 py-3 bg-green-100 text-green-800 rounded-lg">
                        <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-semibold">Surat Jalan telah SELESAI</span>
                    </div>
                </div>
            @elseif($suratJalan->status === 'DITOLAK')
                <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center mt-6">
                    <div class="inline-flex items-center px-6 py-3 bg-red-100 text-red-800 rounded-lg">
                        <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-semibold">Surat Jalan telah DITOLAK</span>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
