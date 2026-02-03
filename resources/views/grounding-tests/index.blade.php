<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex flex-col ml-4 md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Surat Hasil Uji Grounding</h2>
                    <p class="text-sm text-gray-500 mt-1">Kelola surat hasil uji grounding dengan lampiran per titik ukur.</p>
                </div>
                <a href="{{ route('grounding-tests.create') }}"
                   class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-white transition-all duration-200 bg-gradient-to-r from-pln-primary to-pln-light rounded-lg shadow-md hover:shadow-lg hover:from-pln-primary/90 hover:to-pln-light/90">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Buat Surat
                </a>
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

            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Titik</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Dibuat Oleh</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($groundingTests as $test)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        {{ $test->tanggal?->format('d M Y') ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        {{ $test->items_count ?? 0 }} titik
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        {{ $test->nama_pembuat ?? $test->creator->name ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <div class="inline-flex items-center gap-2">
                                            <a href="{{ route('grounding-tests.show', $test) }}"
                                               class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-600 hover:bg-gray-50">
                                                Detail
                                            </a>
                                            <a href="{{ route('grounding-tests.edit', $test) }}"
                                               class="px-3 py-1.5 rounded-md border border-blue-200 text-blue-600 hover:bg-blue-50">
                                                Edit
                                            </a>
                                            <a href="{{ route('grounding-tests.pdf', $test) }}"
                                               class="px-3 py-1.5 rounded-md border border-emerald-200 text-emerald-600 hover:bg-emerald-50">
                                                PDF
                                            </a>
                                            <button
                                                type="button"
                                                @click="$dispatch('open-delete-modal', {
                                                    title: 'Hapus Surat Uji Grounding',
                                                    message: 'Apakah Anda yakin ingin menghapus surat uji grounding tanggal {{ $test->tanggal?->format('d M Y') ?? '-' }}? Data tidak dapat dikembalikan.',
                                                    action: '{{ route('grounding-tests.destroy', $test) }}'
                                                })"
                                                class="px-3 py-1.5 rounded-md border border-red-200 text-red-600 hover:bg-red-50">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-500">
                                        Belum ada surat hasil uji grounding.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($groundingTests->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $groundingTests->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <x-confirm-delete-modal />
</x-app-layout>
