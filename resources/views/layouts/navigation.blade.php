<nav class="fixed inset-y-0 left-0 z-30 bg-white shadow-2xl transition-all duration-300 ease-in-out flex flex-col justify-between border-r border-gray-200 md:static shrink-0"
    :class="sidebarOpen ? 'translate-x-0 w-64' : '-translate-x-full md:translate-x-0 w-64 md:w-20'">
    <div class="flex flex-col">
        <div class="h-20 flex items-center bg-gradient-to-r from-pln-primary to-pln-light relative overflow-hidden transition-all duration-300"
            :class="sidebarOpen ? 'px-6 justify-between' : 'px-0 justify-center'">

            <div class="absolute top-0 right-0 -mt-2 -mr-2 w-20 h-20 bg-pln-yellow opacity-10 rounded-full blur-2xl transition-opacity duration-300"
                :class="sidebarOpen ? 'opacity-10' : 'opacity-0'"></div>

            <div class="flex items-center z-10 overflow-hidden w-full"
                :class="sidebarOpen ? 'justify-start space-x-3' : 'justify-center'">
                <a href="{{ route('dashboard') }}" class="shrink-0 transition-transform duration-300 hover:scale-105">
                    <img src="{{ asset('Logo_PLN.png') }}" class="h-9 w-auto" alt="Logo">
                </a>

                <div x-show="sidebarOpen"
                    class="text-white whitespace-nowrap overflow-hidden transition-opacity duration-300 delay-100">
                    <h1 class="font-bold text-xl leading-tight tracking-wide">E-STORAGE</h1>
                    <p class="text-[10px] font-bold text-pln-yellow tracking-widest">ULPLTD/G Tanjung Karang</p>
                </div>
            </div>

            <button @click="sidebarOpen = false" x-show="sidebarOpen"
                class="md:hidden text-white hover:bg-white/20 rounded-full p-1 transition focus:outline-none z-20 absolute right-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <button @click="sidebarOpen = false" x-show="sidebarOpen"
                class="hidden md:block text-white hover:bg-white/20 rounded-full p-1 transition focus:outline-none z-20 absolute right-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
        </div>

        <div x-show="!sidebarOpen" class="hidden md:flex justify-center mt-3 pb-2 border-b border-gray-100">
            <button @click="sidebarOpen = true"
                class="text-pln-primary hover:bg-pln-light/10 p-1.5 rounded-full transition shadow-sm border border-gray-200 group">
                <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>

        <div class="mt-4 px-3 space-y-2">
            <div class="h-6 flex items-center mb-2 transition-all duration-300"
                :class="!sidebarOpen ? 'justify-center md:px-0' : 'px-3'">
                <p x-show="sidebarOpen"
                    class="text-[10px] font-bold text-gray-400 uppercase tracking-widest whitespace-nowrap">Menu Utama
                </p>
                <div x-show="!sidebarOpen" class="hidden md:block w-4 h-0.5 bg-gray-200 rounded"></div>
            </div>

            @php
                $role = Auth::user()->role ?? '';
                $menuByRole = [
                    'admin' => [
                        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'grid'],
                        ['label' => 'Kelola User', 'route' => 'admin.users.index', 'icon' => 'users'],
                        ['label' => 'Kelola Barang', 'route' => 'admin.items.index', 'icon' => 'boxes'],
                        ['label' => 'Kelola PIC', 'route' => 'admin.pics.index', 'icon' => 'id'],
                        ['label' => 'Surat Jalan Cepat', 'route' => 'admin.surat-jalan.index', 'icon' => 'truck'],
                    ],
                    'operator_gudang' => [
                        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'grid'],
                        [
                            'label' => 'Manajemen Barang',
                            'icon' => 'clipboard',
                            'children' => [
                                ['label' => 'Kelola Barang', 'route' => 'gudang.stok.index'],
                                ['label' => 'Barang Dipinjamkan', 'route' => 'gudang.stok.barang-dipinjamkan'],
                                ['label' => 'Barang Pinjaman', 'route' => 'gudang.stok.barang-pinjaman'],
                            ]
                        ],
                        ['label' => 'Surat Jalan Barang', 'route' => 'gudang.surat-jalan.index', 'icon' => 'truck'],
                        ['label' => 'Riwayat', 'route' => 'gudang.riwayat', 'icon' => 'clock'],
                    ],
                    'manager' => [
                        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'grid'],
                        ['label' => 'Surat Jalan Barang', 'route' => 'manager.surat-jalan.index', 'icon' => 'truck'],
                    ],
                    'security' => [
                        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'grid'],
                    ],
                ];
                $navItems = $menuByRole[$role] ?? [['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'grid']];
            @endphp

            @foreach($navItems as $item)
                @php
                    $hasRoute = isset($item['route']) && Route::has($item['route']);
                    $url = $hasRoute ? route($item['route']) : ($item['url'] ?? '#');
                    $isActive = $hasRoute ? request()->routeIs($item['route'] . '*') : false;
                    $hasChildren = !empty($item['children']);

                    $childActive = false;
                    if ($hasChildren) {
                        foreach ($item['children'] as $child) {
                            if (isset($child['route']) && Route::has($child['route']) && request()->routeIs($child['route'] . '*')) {
                                $childActive = true;
                                break;
                            }
                        }
                    }
                    $isActive = $isActive || $childActive;
                @endphp

                @if($hasChildren)
                    <div x-data="{ open: {{ $childActive ? 'true' : 'false' }} }" class="relative">
                        <button @click="open = !open" type="button" class="w-full group flex items-center px-3 py-3 rounded-lg transition-all duration-200 relative overflow-hidden
                                    {{ $isActive
                    ? 'bg-gradient-to-r from-pln-primary/10 to-transparent text-pln-primary font-bold'
                    : 'text-gray-600 hover:bg-gray-50 hover:text-pln-light' }}"
                            :class="!sidebarOpen ? 'justify-start md:justify-center' : ''">

                            @if($isActive)
                                <div
                                    class="absolute left-0 top-1 bottom-1 w-1 bg-pln-yellow rounded-r shadow-[0_0_10px_rgba(255,255,0,0.6)]">
                                </div>
                            @endif

                            @include('layouts.partials.nav-icon', ['icon' => $item['icon'], 'isActive' => $isActive])

                            <span x-show="sidebarOpen"
                                class="ml-3 whitespace-nowrap overflow-hidden transition-all duration-300 origin-left flex-1 text-left">
                                {{ $item['label'] }}
                            </span>

                            <svg x-show="sidebarOpen" class="w-4 h-4 ml-auto transition-transform duration-200"
                                :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>

                            <div x-show="!sidebarOpen"
                                class="hidden md:block absolute left-14 ml-2 bg-pln-primary text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity z-50 pointer-events-none whitespace-nowrap shadow-lg border-l-2 border-pln-yellow">
                                {{ $item['label'] }}
                            </div>
                        </button>

                        <div x-show="open && sidebarOpen" x-collapse class="pl-6 mt-1 space-y-1">
                            @foreach($item['children'] as $child)
                                    @php
                                        $childHasRoute = isset($child['route']) && Route::has($child['route']);
                                        $childUrl = $childHasRoute ? route($child['route']) : '#';
                                        $isChildActive = $childHasRoute && request()->routeIs($child['route'] . '*');
                                    @endphp
                                    <a href="{{ $childUrl }}" class="flex items-center px-3 py-2 text-sm rounded-lg transition-all duration-200
                                               {{ $isChildActive
                                ? 'bg-pln-primary/10 text-pln-primary font-semibold'
                                : 'text-gray-500 hover:bg-gray-50 hover:text-pln-light' }}">
                                        <span
                                            class="w-1.5 h-1.5 rounded-full mr-3 {{ $isChildActive ? 'bg-pln-primary' : 'bg-gray-300' }}"></span>
                                        {{ $child['label'] }}
                                    </a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <a href="{{ $url }}" class="group flex items-center px-3 py-3 rounded-lg transition-all duration-200 relative overflow-hidden
                           {{ $isActive
                    ? 'bg-gradient-to-r from-pln-primary/10 to-transparent text-pln-primary font-bold'
                    : 'text-gray-600 hover:bg-gray-50 hover:text-pln-light' }}"
                        :class="!sidebarOpen ? 'justify-start md:justify-center' : ''">

                        @if($isActive)
                            <div
                                class="absolute left-0 top-1 bottom-1 w-1 bg-pln-yellow rounded-r shadow-[0_0_10px_rgba(255,255,0,0.6)]">
                            </div>
                        @endif

                        @include('layouts.partials.nav-icon', ['icon' => $item['icon'], 'isActive' => $isActive])

                        <span x-show="sidebarOpen"
                            class="ml-3 whitespace-nowrap overflow-hidden transition-all duration-300 origin-left">
                            {{ $item['label'] }}
                        </span>

                        <div x-show="!sidebarOpen"
                            class="hidden md:block absolute left-14 ml-2 bg-pln-primary text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity z-50 pointer-events-none whitespace-nowrap shadow-lg border-l-2 border-pln-yellow">
                            {{ $item['label'] }}
                        </div>
                    </a>
                @endif
            @endforeach
        </div>
    </div>

    <div class="p-4 border-t border-gray-100 bg-gray-50/80">
        <div class="flex items-center transition-all duration-300"
            :class="!sidebarOpen ? 'justify-start md:justify-center flex-row md:flex-col md:space-y-3 space-x-3 md:space-x-0' : 'space-x-3'">
            <a href="{{ route('profile.edit') }}"
                class="shrink-0 h-10 w-10 rounded-full bg-gradient-to-br from-pln-primary to-pln-light flex items-center justify-center text-white font-bold shadow-md ring-2 ring-pln-light/30 hover:scale-105 transition-transform cursor-pointer"
                title="Profile">
                {{ substr(Auth::user()->name, 0, 1) }}
            </a>

            <a href="{{ route('profile.edit') }}" x-show="sidebarOpen"
                class="flex-1 min-w-0 overflow-hidden hover:opacity-80 transition-opacity">
                <p class="text-sm font-bold text-pln-primary truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ Auth::user()->jabatan ?? Auth::user()->role }}</p>
            </a>

            <form method="POST" action="{{ route('logout') }}"
                :class="!sidebarOpen ? 'md:w-full md:flex md:justify-center ml-auto md:ml-0' : ''">
                @csrf
                <button type="submit"
                    class="text-gray-400 hover:text-pln-red transition-colors p-1 rounded hover:bg-red-50"
                    title="Logout">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</nav>
