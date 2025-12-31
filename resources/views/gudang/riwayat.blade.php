<x-app-layout>
    <div class="py-4 sm:py-8 lg:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Header Section --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-lg sm:rounded-lg mb-4 sm:mb-6">
                <div class="p-4 sm:p-6">
                    <div class="flex justify-between items-center">
                        <div class="text-center sm:text-left flex-1">
                            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Riwayat</h2>
                            <p class="text-xs sm:text-sm text-gray-600 mt-1">
                                {{ Auth::user()->gudang->nama ?? 'Gudang Saya' }}
                            </p>
                        </div>
                        <div class="hidden sm:flex items-center gap-2">
                            <svg class="w-8 h-8 text-[#035b71]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabs Navigation --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-lg sm:rounded-lg mb-4 sm:mb-6">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex" aria-label="Tabs" data-ajax-tabs>
                        <a href="{{ route('gudang.riwayat', ['tab' => 'pergerakan']) }}"
                           data-ajax-tab
                           data-ajax-target="#riwayat-content"
                           class="w-1/3 py-3 sm:py-4 px-1 text-center border-b-2 font-medium text-xs sm:text-sm {{ $tab === 'pergerakan' ? 'border-[#035b71] text-[#035b71]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} active:bg-gray-50 transition">
                            <div class="flex items-center justify-center gap-1.5 sm:gap-2">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                </svg>
                                <span class="hidden sm:inline">Pergerakan Stok</span>
                                <span class="sm:hidden">Stok</span>
                            </div>
                        </a>
                        <a href="{{ route('gudang.riwayat', ['tab' => 'surat-jalan']) }}"
                           data-ajax-tab
                           data-ajax-target="#riwayat-content"
                           class="w-1/3 py-3 sm:py-4 px-1 text-center border-b-2 font-medium text-xs sm:text-sm {{ $tab === 'surat-jalan' ? 'border-[#035b71] text-[#035b71]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} active:bg-gray-50 transition">
                            <div class="flex items-center justify-center gap-1.5 sm:gap-2">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="hidden sm:inline">Surat Jalan</span>
                                <span class="sm:hidden">Surat</span>
                            </div>
                        </a>
                        <a href="{{ route('gudang.riwayat', ['tab' => 'peminjaman']) }}"
                           data-ajax-tab
                           data-ajax-target="#riwayat-content"
                           class="w-1/3 py-3 sm:py-4 px-1 text-center border-b-2 font-medium text-xs sm:text-sm {{ $tab === 'peminjaman' ? 'border-[#035b71] text-[#035b71]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} active:bg-gray-50 transition">
                            <div class="flex items-center justify-center gap-1.5 sm:gap-2">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                </svg>
                                <span>Peminjaman</span>
                            </div>
                        </a>
                    </nav>
                </div>
            </div>

            {{-- Tab Content --}}
            <div id="riwayat-content">
                @if($tab === 'pergerakan')
                    @include('gudang.riwayat.pergerakan')
                @elseif($tab === 'surat-jalan')
                    @include('gudang.riwayat.surat-jalan')
                @elseif($tab === 'peminjaman')
                    @include('gudang.riwayat.peminjaman')
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
