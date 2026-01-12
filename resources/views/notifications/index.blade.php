<x-app-layout>
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Notifikasi</h1>
                <p class="text-sm text-gray-600 mt-1">{{ $unreadCount }} notifikasi belum dibaca</p>
            </div>

            @if($notifications->count() > 0)
                <div class="flex gap-2">
                    @if($unreadCount > 0)
                        <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="px-4 py-2 text-sm bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                                Tandai Semua Dibaca
                            </button>
                        </form>
                    @endif
                    <form action="{{ route('notifications.clear-all') }}" method="POST"
                          onsubmit="return confirm('Hapus semua notifikasi?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="px-4 py-2 text-sm bg-white border border-red-300 text-red-600 rounded-lg hover:bg-red-50 transition">
                            Hapus Semua
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <!-- Flash Message -->
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <!-- Notifications List -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            @forelse($notifications as $notification)
                <div class="flex items-start gap-4 p-4 border-b border-gray-100 hover:bg-gray-50 transition {{ !$notification->read_at ? 'bg-blue-50/30' : '' }}">
                    <!-- Icon -->
                    <div class="shrink-0 w-12 h-12 rounded-full flex items-center justify-center
                        @switch($notification->type)
                            @case('surat_masuk') bg-blue-100 text-blue-600 @break
                            @case('surat_siap_terima') bg-green-100 text-green-600 @break
                            @case('surat_diterima') bg-teal-100 text-teal-600 @break
                            @case('surat_ditolak') bg-red-100 text-red-600 @break
                            @default bg-gray-100 text-gray-600
                        @endswitch
                    ">
                        @switch($notification->type)
                            @case('surat_masuk')
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8m-5 5h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293h3.172a1 1 0 00.707-.293l2.414-2.414a1 1 0 01.707-.293H20" />
                                </svg>
                                @break
                            @case('surat_siap_terima')
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                @break
                            @case('surat_diterima')
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                @break
                            @case('surat_ditolak')
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                @break
                            @default
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                        @endswitch
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $notification->title }}</h3>
                                <p class="text-sm text-gray-600 mt-1">{{ $notification->message }}</p>
                                <p class="text-xs text-gray-400 mt-2">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>

                            @if(!$notification->read_at)
                                <span class="shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2"></span>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-3 mt-3">
                            @if($notification->url)
                                <form action="{{ route('notifications.read', $notification->id) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="redirect" value="1">
                                    <button type="submit" class="text-sm text-pln-primary hover:underline font-medium">
                                        Lihat Detail
                                    </button>
                                </form>
                            @endif

                            @if(!$notification->read_at)
                                <form action="{{ route('notifications.read', $notification->id) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="redirect" value="0">
                                    <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">
                                        Tandai Dibaca
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-red-500 hover:text-red-700">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900">Tidak ada notifikasi</h3>
                    <p class="text-sm text-gray-500 mt-1">Anda akan menerima notifikasi saat ada surat jalan masuk</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
