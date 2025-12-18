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
