@if ($paginator->hasPages())
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();

        // Build page array: 2 awal, current page, 2 akhir (with dots in gaps)
        $pages = collect();

        // First 2 pages
        $pages->push(1);
        if ($lastPage >= 2) {
            $pages->push(2);
        }

        // Always include current page
        $pages->push($currentPage);

        // Last 2 pages
        if ($lastPage > 3) {
            $pages->push($lastPage - 1);
        }
        if ($lastPage > 2) {
            $pages->push($lastPage);
        }

        // Remove duplicates, filter valid, sort
        $pages = $pages->filter(fn($p) => $p >= 1 && $p <= $lastPage)->unique()->sort()->values();

        // Build final array with dots in gaps
        $finalPages = collect();
        $prev = 0;
        foreach ($pages as $page) {
            if ($prev > 0 && $page - $prev > 1) {
                $finalPages->push('...');
            }
            $finalPages->push($page);
            $prev = $page;
        }
    @endphp

    <nav role="navigation" aria-label="Navigasi Halaman" x-data="{
        showInput: false,
        pageInput: '',
        buttonEl: null,
        popupStyle: {},
        openPopup(event) {
            this.buttonEl = event.currentTarget;
            this.showInput = true;
            this.$nextTick(() => {
                const rect = this.buttonEl.getBoundingClientRect();
                const popupHeight = 90;
                const spaceBelow = window.innerHeight - rect.bottom;

                if (spaceBelow < popupHeight + 20) {
                    // Tidak cukup ruang di bawah, tampilkan di atas
                    this.popupStyle = {
                        position: 'fixed',
                        bottom: (window.innerHeight - rect.top + 8) + 'px',
                        left: rect.left + 'px',
                        zIndex: 9999
                    };
                } else {
                    // Tampilkan di bawah
                    this.popupStyle = {
                        position: 'fixed',
                        top: (rect.bottom + 8) + 'px',
                        left: rect.left + 'px',
                        zIndex: 9999
                    };
                }
                this.$refs.pageInput.focus();
            });
        },
        closePopup() {
            this.showInput = false;
            this.pageInput = '';
        }
    }" @keydown.escape.window="closePopup()">

        {{-- Mobile View --}}
        <div class="flex gap-2 items-center justify-between sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-200 cursor-not-allowed rounded-lg">
                    Sebelumnya
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-4 py-2 text-sm font-medium text-[#035b71] bg-white border border-gray-200 rounded-lg hover:bg-[#035b71]/5 transition">
                    Sebelumnya
                </a>
            @endif

            <span class="text-sm text-gray-600">
                {{ $currentPage }} / {{ $lastPage }}
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-4 py-2 text-sm font-medium text-[#035b71] bg-white border border-gray-200 rounded-lg hover:bg-[#035b71]/5 transition">
                    Berikutnya
                </a>
            @else
                <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-200 cursor-not-allowed rounded-lg">
                    Berikutnya
                </span>
            @endif
        </div>

        {{-- Desktop View --}}
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-600">
                    Menampilkan
                    @if ($paginator->firstItem())
                        <span class="font-semibold text-gray-900">{{ $paginator->firstItem() }}</span>
                        -
                        <span class="font-semibold text-gray-900">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    dari
                    <span class="font-semibold text-gray-900">{{ $paginator->total() }}</span>
                    data
                </p>
            </div>

            <div class="relative">
                <span class="inline-flex items-center gap-1">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span class="inline-flex items-center justify-center w-9 h-9 text-gray-300 bg-gray-50 border border-gray-200 rounded-lg cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center justify-center w-9 h-9 text-[#035b71] bg-white border border-gray-200 rounded-lg hover:bg-[#035b71]/5 hover:border-[#035b71]/30 transition" aria-label="Sebelumnya">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </a>
                    @endif

                    {{-- Custom Pagination Elements --}}
                    @foreach ($finalPages as $item)
                        @if ($item === '...')
                            <button
                                @click="openPopup($event)"
                                class="inline-flex items-center justify-center w-9 h-9 text-gray-400 text-sm hover:text-[#035b71] hover:bg-[#035b71]/5 rounded-lg transition cursor-pointer"
                                title="Klik untuk ke halaman tertentu"
                            >
                                ...
                            </button>
                        @elseif ($item == $currentPage)
                            <span aria-current="page" class="inline-flex items-center justify-center w-9 h-9 text-sm font-semibold text-white bg-[#035b71] border border-[#035b71] rounded-lg">
                                {{ $item }}
                            </span>
                        @else
                            <a href="{{ $paginator->url($item) }}" class="inline-flex items-center justify-center w-9 h-9 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-[#035b71]/5 hover:text-[#035b71] hover:border-[#035b71]/30 transition" aria-label="Ke halaman {{ $item }}">
                                {{ $item }}
                            </a>
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center justify-center w-9 h-9 text-[#035b71] bg-white border border-gray-200 rounded-lg hover:bg-[#035b71]/5 hover:border-[#035b71]/30 transition" aria-label="Berikutnya">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @else
                        <span class="inline-flex items-center justify-center w-9 h-9 text-gray-300 bg-gray-50 border border-gray-200 rounded-lg cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </span>
                    @endif
                </span>

            </div>

            {{-- Page Jump Input Modal (teleported to body) --}}
            <template x-teleport="body">
                <div
                    x-show="showInput"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    @click.away="closePopup()"
                    :style="popupStyle"
                    class="bg-white rounded-lg shadow-lg border border-gray-200 p-3"
                    style="min-width: 200px;"
                >
                    <form
                        @submit.prevent="
                            let page = parseInt(pageInput);
                            if (page >= 1 && page <= {{ $lastPage }}) {
                                window.location.href = '{{ $paginator->url(1) }}'.replace('page=1', 'page=' + page);
                            }
                            closePopup();
                        "
                    >
                        <label class="block text-xs font-medium text-gray-600 mb-2">Ke halaman (1-{{ $lastPage }})</label>
                        <div class="flex gap-2">
                            <input
                                x-ref="pageInput"
                                x-model="pageInput"
                                type="number"
                                min="1"
                                max="{{ $lastPage }}"
                                class="flex-1 w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#035b71]/20 focus:border-[#035b71]"
                                placeholder="No. halaman"
                            >
                            <button
                                type="submit"
                                class="px-3 py-2 text-sm font-medium text-white bg-[#035b71] rounded-lg hover:bg-[#035b71]/90 transition"
                            >
                                Go
                            </button>
                        </div>
                    </form>
                </div>
            </template>
        </div>
    </nav>
@endif
