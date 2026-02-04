<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Detail Surat Hasil Uji Grounding</h2>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('grounding-tests.index') }}"
                       class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-200 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-50">
                        Kembali
                    </a>
                    <a href="{{ route('grounding-tests.edit', $groundingTest) }}"
                       class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-blue-600 transition-all duration-200 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100">
                        Edit
                    </a>
                    <a href="{{ route('grounding-tests.preview', $groundingTest) }}"
                       class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-cyan-600 transition-all duration-200 bg-cyan-50 border border-cyan-200 rounded-lg hover:bg-cyan-100" target="_blank">
                        Preview PDF
                    </a>
                    <a href="{{ route('grounding-tests.pdf', $groundingTest) }}"
                       class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-emerald-600 transition-all duration-200 bg-emerald-50 border border-emerald-200 rounded-lg hover:bg-emerald-100">
                        Download PDF
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div
                    x-data="{ show: true }"
                    x-init="setTimeout(() => show = false, 3000)"
                    x-show="show"
                    x-transition.opacity.duration.300ms
                    x-cloak
                    class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Tanggal Uji</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $groundingTest->tanggal?->format('d M Y') ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Dibuat Oleh</p>
                    <p class="text-sm font-semibold text-gray-800">
                        {{ $groundingTest->nama_pembuat ?? $groundingTest->creator->name ?? '-' }}
                    </p>
                </div>
                @if($groundingTest->catatan)
                    <div class="md:col-span-3">
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Catatan</p>
                        <p class="text-sm text-gray-700 mt-1">{{ $groundingTest->catatan }}</p>
                    </div>
                @endif
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900">Daftar Titik Ukur</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">No</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Titik Ukur</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Kriteria</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Hasil Uji</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Lampiran</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach($groundingTest->items as $index => $item)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $item->titik_ukur }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $item->kriteria }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $item->hasil_uji }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        @if($item->attachment_url)
                                            <a href="{{ $item->attachment_url }}" target="_blank" class="flex items-center gap-3">
                                                <img src="{{ $item->attachment_url }}" alt="Lampiran" class="h-12 w-12 rounded object-cover border">
                                                <span class="text-xs text-blue-600">Lihat</span>
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
