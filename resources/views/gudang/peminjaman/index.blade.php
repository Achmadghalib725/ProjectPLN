<x-app-layout>
    <div class="py-4 sm:py-8 lg:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Flash Messages --}}
            @if(session('success'))
                <div x-data="{ show: true }"
                     x-show="show"
                     x-init="setTimeout(() => show = false, 3000)"
                     class="fixed top-4 left-4 right-4 sm:left-auto sm:right-4 sm:w-auto bg-green-500 text-white px-4 sm:px-6 py-3 rounded-xl shadow-lg z-50 flex items-center gap-2 text-sm sm:text-base">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div x-data="{ show: true }"
                     x-show="show"
                     class="fixed top-4 left-4 right-4 sm:left-auto sm:right-4 sm:w-auto bg-red-500 text-white px-4 sm:px-6 py-3 rounded-xl shadow-lg z-50 flex items-center gap-2 text-sm sm:text-base">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            @endif

            {{-- Header Section --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-lg mb-4 sm:mb-6">
                <div class="p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 sm:gap-4">
                        <div class="text-center sm:text-left">
                            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Peminjaman Barang</h2>
                            <p class="text-xs sm:text-sm text-gray-600 mt-1">
                                {{ Auth::user()->gudang->nama ?? 'Gudang Saya' }} - Kelola barang dipinjam dan dipinjamkan
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Tab Navigation - Dipinjamkan & Pinjaman --}}
                <div class="border-t border-gray-200">
                    <nav class="flex" data-ajax-tabs>
                        {{-- Tab Dipinjamkan --}}
                        <a href="{{ route('gudang.peminjaman.index', ['tab' => 'dipinjamkan']) }}"
                           data-ajax-tab
                           data-ajax-target="#peminjaman-content"
                           class="flex-1 py-4 px-4 border-b-2 font-medium text-sm text-center transition-colors
                               {{ $tab === 'dipinjamkan' ? 'border-[#035b71] text-[#035b71]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            <div class="flex items-center justify-center gap-2">
                                {{-- Icon Outgoing --}}
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                <span>Dipinjamkan</span>
                            </div>
                        </a>
                        {{-- Tab Pinjaman --}}
                        <a href="{{ route('gudang.peminjaman.index', ['tab' => 'pinjaman']) }}"
                           data-ajax-tab
                           data-ajax-target="#peminjaman-content"
                           class="flex-1 py-4 px-4 border-b-2 font-medium text-sm text-center transition-colors
                               {{ $tab === 'pinjaman' ? 'border-[#035b71] text-[#035b71]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            <div class="flex items-center justify-center gap-2">
                                {{-- Icon Incoming --}}
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                </svg>
                                <span>Pinjaman</span>
                            </div>
                        </a>
                    </nav>
                </div>
            </div>

            {{-- Tab Content --}}
            <div id="peminjaman-content" data-ajax-container>
                @if($tab === 'dipinjamkan')
                    @include('gudang.peminjaman.tab-dipinjamkan')
                @else
                    @include('gudang.peminjaman.tab-pinjaman')
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
