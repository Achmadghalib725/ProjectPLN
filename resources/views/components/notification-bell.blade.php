@once
<style>
    [x-cloak] { display: none !important; }
    @keyframes bell-ring {
        0%, 100% { transform: rotate(0); }
        10%, 30%, 50% { transform: rotate(10deg); }
        20%, 40% { transform: rotate(-10deg); }
        60% { transform: rotate(0); }
    }
    .bell-ringing { animation: bell-ring 0.6s ease-in-out; }
</style>
@endonce

@php
    $gudangId = auth()->user()->gudang_id ?? null;
@endphp

<div x-data="notificationBell({{ auth()->user()->unreadNotifications()->count() ?? 0 }}, {{ $gudangId ?? 'null' }})"
     x-init="init()"
     class="relative">

    <!-- Bell Button -->
    <button @click="toggle()" type="button"
            class="relative text-gray-400 hover:text-pln-primary transition-colors p-1 rounded hover:bg-gray-100 focus:outline-none"
            :class="{ 'bell-ringing': isRinging }"
            title="Notifikasi">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>

        <!-- Badge -->
        <span x-cloak x-show="unreadCount > 0"
              x-text="unreadCount > 99 ? '99+' : unreadCount"
              x-transition:enter="transition ease-out duration-200"
              x-transition:enter-start="scale-0"
              x-transition:enter-end="scale-100"
              class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[10px] font-bold rounded-full min-w-[16px] h-4 flex items-center justify-center px-1 shadow">
        </span>
    </button>

    <!-- Dropdown Panel -->
    <div x-cloak
         x-show="open"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         @click.outside="open = false"
         class="absolute w-80 bg-white rounded-xl shadow-2xl border border-gray-200 z-[9999] overflow-hidden
                top-full mt-2 right-0
                md:top-auto md:bottom-full md:mb-2 md:left-0 md:right-auto">

        <!-- Header -->
        <div class="px-4 py-3 bg-gradient-to-r from-pln-primary to-pln-light text-white flex items-center justify-between">
            <h3 class="font-semibold text-sm">Notifikasi</h3>
            <div class="flex items-center gap-2">
                <span x-show="isConnected" class="w-2 h-2 bg-green-400 rounded-full" title="Terhubung real-time"></span>
                <button x-show="unreadCount > 0" @click.stop="markAllRead()"
                        class="text-xs hover:underline opacity-90 hover:opacity-100 transition">
                    Tandai dibaca
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="max-h-72 overflow-y-auto">
            <!-- Loading -->
            <template x-if="loading">
                <div class="p-6 text-center">
                    <svg class="animate-spin h-6 w-6 mx-auto text-pln-primary" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </template>

            <!-- Empty State -->
            <template x-if="!loading && notifications.length === 0">
                <div class="p-6 text-center text-gray-500">
                    <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="text-xs">Tidak ada notifikasi</p>
                </div>
            </template>

            <!-- Notification Items -->
            <template x-if="!loading && notifications.length > 0">
                <div>
                    <template x-for="notif in notifications" :key="notif.id">
                        <div class="group relative flex items-start gap-3 px-4 py-3 hover:bg-gray-50 border-b border-gray-100 transition-colors"
                             :class="{ 'bg-blue-50/50': !notif.read_at }">

                            <!-- Clickable area for navigation -->
                            <a :href="notif.url || notif.link || '#'"
                               @click="markAsRead(notif)"
                               class="absolute inset-0 z-0"></a>

                            <!-- Icon -->
                            <div class="shrink-0 w-9 h-9 rounded-full flex items-center justify-center text-sm relative z-10 pointer-events-none"
                                 :class="getIconClass(notif.type)">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getIconPath(notif.type)" />
                                </svg>
                            </div>

                            <!-- Text -->
                            <div class="flex-1 min-w-0 relative z-10 pointer-events-none">
                                <p class="text-sm font-medium text-gray-900 truncate" x-text="notif.title"></p>
                                <p class="text-xs text-gray-500 mt-0.5 line-clamp-2" x-text="notif.message"></p>
                                <p class="text-[10px] text-gray-400 mt-1" x-text="formatTime(notif.created_at)"></p>
                            </div>

                            <!-- Unread Dot / Delete Button -->
                            <div class="shrink-0 relative z-10 flex items-center">
                                <div x-show="!notif.read_at && !notif.deleting" class="w-2 h-2 bg-blue-500 rounded-full"></div>

                                <!-- Delete Button (always visible on mobile, hover on desktop) -->
                                <button x-show="!notif.deleting"
                                        @click.stop.prevent="deleteNotification(notif)"
                                        class="p-1 rounded-full text-gray-400 hover:text-red-500 hover:bg-red-50 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-all pointer-events-auto"
                                        title="Hapus notifikasi">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>

                                <!-- Deleting spinner -->
                                <div x-show="notif.deleting" class="w-4 h-4">
                                    <svg class="animate-spin w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        <!-- Footer -->
        <div x-cloak x-show="notifications.length > 0" class="px-4 py-2 bg-gray-50 border-t border-gray-100 text-center">
            <a href="{{ route('notifications.index') }}" class="text-xs text-pln-primary hover:underline font-medium">
                Lihat semua notifikasi
            </a>
        </div>
    </div>
</div>

<script>
function notificationBell(initialCount, gudangId) {
    return {
        open: false,
        loading: false,
        notifications: [],
        unreadCount: initialCount,
        gudangId: gudangId,
        isConnected: false,
        isRinging: false,

        init() {
            // Initial fetch
            this.fetchNotifications();

            // Setup WebSocket listener
            this.setupEchoListener();

            // Fallback polling every 60 seconds (in case WebSocket fails)
            setInterval(() => {
                if (!this.isConnected) {
                    this.fetchUnreadCount();
                }
            }, 60000);
        },

        setupEchoListener() {
            if (typeof window.Echo === 'undefined') {
                console.log('Laravel Echo not available, using polling fallback');
                return;
            }

            if (!this.gudangId) {
                console.log('No gudang_id, skipping WebSocket subscription');
                return;
            }

            try {
                // Subscribe to gudang channel
                window.Echo.channel('surat-jalan.gudang.' + this.gudangId)
                    .listen('.SuratJalanStatusUpdated', (e) => {
                        console.log('Received real-time event:', e);
                        this.handleRealtimeUpdate(e);
                    })
                    .listen('SuratJalanStatusUpdated', (e) => {
                        console.log('Received real-time event:', e);
                        this.handleRealtimeUpdate(e);
                    });

                this.isConnected = true;
                console.log('WebSocket connected to channel: surat-jalan.gudang.' + this.gudangId);
            } catch (error) {
                console.error('Failed to setup Echo listener:', error);
                this.isConnected = false;
            }
        },

        handleRealtimeUpdate(event) {
            // Ring the bell
            this.ringBell();

            // Refresh notifications
            this.fetchNotifications();
        },

        ringBell() {
            this.isRinging = true;
            setTimeout(() => {
                this.isRinging = false;
            }, 600);
        },

        toggle() {
            this.open = !this.open;
            if (this.open) this.fetchNotifications();
        },

        async fetchNotifications() {
            this.loading = true;
            try {
                const res = await fetch('{{ route("notifications.index") }}', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                this.notifications = data.notifications || [];
                this.unreadCount = data.unread_count || 0;
            } catch (e) {
                console.error('Fetch notifications error:', e);
                this.notifications = [];
            } finally {
                this.loading = false;
            }
        },

        async fetchUnreadCount() {
            try {
                const res = await fetch('{{ route("notifications.unread-count") }}', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                if (data.count > this.unreadCount) {
                    this.ringBell();
                }
                this.unreadCount = data.count || 0;
            } catch (e) {
                console.error('Fetch unread count error:', e);
            }
        },

        async markAsRead(notif) {
            if (!notif.read_at) {
                try {
                    await fetch('/notifications/' + notif.id + '/read', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                        }
                    });
                    notif.read_at = new Date().toISOString();
                    this.unreadCount = Math.max(0, this.unreadCount - 1);
                } catch (e) { console.error(e); }
            }
        },

        async markAllRead() {
            try {
                await fetch('{{ route("notifications.mark-all-read") }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    }
                });
                this.notifications.forEach(n => n.read_at = new Date().toISOString());
                this.unreadCount = 0;
            } catch (e) { console.error(e); }
        },

        async deleteNotification(notif) {
            notif.deleting = true;
            try {
                const res = await fetch('/notifications/' + notif.id, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    }
                });

                if (res.ok) {
                    // Update unread count if notification was unread
                    if (!notif.read_at) {
                        this.unreadCount = Math.max(0, this.unreadCount - 1);
                    }
                    // Remove from list with animation
                    this.notifications = this.notifications.filter(n => n.id !== notif.id);
                }
            } catch (e) {
                console.error(e);
                notif.deleting = false;
            }
        },

        getIconClass(type) {
            const classes = {
                'surat_masuk': 'bg-blue-100 text-blue-600',
                'surat_siap_terima': 'bg-green-100 text-green-600',
                'surat_diterima': 'bg-teal-100 text-teal-600',
                'surat_ditolak': 'bg-red-100 text-red-600'
            };
            return classes[type] || 'bg-gray-100 text-gray-600';
        },

        getIconPath(type) {
            const paths = {
                'surat_masuk': 'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4',
                'surat_siap_terima': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                'surat_diterima': 'M5 13l4 4L19 7',
                'surat_ditolak': 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'
            };
            return paths[type] || 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9';
        },

        formatTime(dateStr) {
            if (!dateStr) return '';
            const diff = Date.now() - new Date(dateStr).getTime();
            const mins = Math.floor(diff / 60000);
            const hrs = Math.floor(diff / 3600000);
            const days = Math.floor(diff / 86400000);
            if (mins < 1) return 'Baru saja';
            if (mins < 60) return mins + ' menit lalu';
            if (hrs < 24) return hrs + ' jam lalu';
            if (days < 7) return days + ' hari lalu';
            return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
        }
    }
}
</script>
