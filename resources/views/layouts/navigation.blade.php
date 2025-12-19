<nav 
    class="fixed inset-y-0 left-0 z-30 bg-white shadow-2xl transition-all duration-300 ease-in-out flex flex-col justify-between border-r border-gray-200 md:static shrink-0"
    :class="sidebarOpen ? 'translate-x-0 w-64' : '-translate-x-full md:translate-x-0 w-64 md:w-20'"
>
    <div class="flex flex-col">
        <div class="h-20 flex items-center bg-gradient-to-r from-pln-primary to-pln-light relative overflow-hidden transition-all duration-300"
             :class="sidebarOpen ? 'px-6 justify-between' : 'px-0 justify-center'">
            
            <div class="absolute top-0 right-0 -mt-2 -mr-2 w-20 h-20 bg-pln-yellow opacity-10 rounded-full blur-2xl transition-opacity duration-300" :class="sidebarOpen ? 'opacity-10' : 'opacity-0'"></div>
            
            <div class="flex items-center z-10 overflow-hidden w-full" :class="sidebarOpen ? 'justify-start space-x-3' : 'justify-center'">
                <a href="{{ route('dashboard') }}" class="shrink-0 bg-white p-1.5 rounded-full shadow-lg border-2 border-pln-yellow transition-transform duration-300 hover:scale-105">
                    <x-application-logo class="block h-7 w-auto fill-current text-pln-primary" />
                </a>
                
                <div x-show="sidebarOpen" 
                     class="text-white whitespace-nowrap overflow-hidden transition-opacity duration-300 delay-100">
                    <h1 class="font-bold text-xl leading-tight tracking-wide">E-Gudang</h1>
                    <p class="text-[10px] font-bold text-pln-yellow tracking-widest">ULPLTD/G Tanjung Karang</p>
                </div>
            </div>

            <button @click="sidebarOpen = false" 
                    class="md:hidden text-white hover:bg-white/20 rounded-full p-1 transition focus:outline-none z-20 absolute right-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <button @click="sidebarOpen = false" 
                    class="hidden md:block text-white hover:bg-white/20 rounded-full p-1 transition focus:outline-none z-20 absolute right-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
        </div>
        
        <div x-show="!sidebarOpen" class="hidden md:flex justify-center mt-3 pb-2 border-b border-gray-100">
             <button @click="sidebarOpen = true" class="text-pln-primary hover:bg-pln-light/10 p-1.5 rounded-full transition shadow-sm border border-gray-200 group">
                <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>

        <div class="mt-4 px-3 space-y-2">
            <div class="h-6 flex items-center mb-2 transition-all duration-300" :class="!sidebarOpen ? 'justify-center md:px-0' : 'px-3'">
                <p x-show="sidebarOpen" class="text-[10px] font-bold text-gray-400 uppercase tracking-widest whitespace-nowrap">Menu Utama</p>
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
                    ],
                    'operator_gudang' => [
                        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'grid'],
                        ['label' => 'Manajemen Barang', 'route' => 'gudang.stok.index', 'icon' => 'clipboard'],
                        ['label' => 'Surat Jalan Barang', 'route' => 'gudang.surat-jalan.index', 'icon' => 'truck'],
                        ['label' => 'Riwayat', 'route' => 'gudang.riwayat', 'icon' => 'clock'],
                    ],
                    'security' => [
                        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'grid'],
                        ['label' => 'Konfirmasi Surat Jalan', 'route' => 'security.scan', 'icon' => 'shield'],
                    ],
                ];
                $navItems = $menuByRole[$role] ?? [['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'grid']];
            @endphp

            @foreach($navItems as $item)
                @php
                    $hasRoute = isset($item['route']) && Route::has($item['route']);
                    $url = $hasRoute ? route($item['route']) : ($item['url'] ?? '#');
                    $isActive = $hasRoute ? request()->routeIs($item['route'] . '*') : false;
                @endphp

                <a href="{{ $url }}"
                   class="group flex items-center px-3 py-3 rounded-lg transition-all duration-200 relative overflow-hidden
                   {{ $isActive 
                      ? 'bg-gradient-to-r from-pln-primary/10 to-transparent text-pln-primary font-bold' 
                      : 'text-gray-600 hover:bg-gray-50 hover:text-pln-light' }}"
                   :class="!sidebarOpen ? 'justify-start md:justify-center' : ''">
                    
                    @if($isActive)
                    <div class="absolute left-0 top-1 bottom-1 w-1 bg-pln-yellow rounded-r shadow-[0_0_10px_rgba(255,255,0,0.6)]"></div>
                    @endif

                    @switch($item['icon'])
                        @case('users')
                            <svg class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover:scale-110 {{ $isActive ? 'text-pln-primary' : 'text-gray-400 group-hover:text-pln-light' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-1a6 6 0 00-9-5.197M12 7a4 4 0 11-8 0 4 4 0 018 0zm9 4a4 4 0 10-8 0 4 4 0 008 0zM6 21v-1a6 6 0 0112 0v1"></path>
                            </svg>
                            @break
                        @case('boxes')
                            <svg class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover:scale-110 {{ $isActive ? 'text-pln-primary' : 'text-gray-400 group-hover:text-pln-light' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7l9 5 9-5M3 17l9 5 9-5M3 7v10m9-5v10m9-10v10"></path>
                            </svg>
                            @break
                        @case('id')
                            <svg class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover:scale-110 {{ $isActive ? 'text-pln-primary' : 'text-gray-400 group-hover:text-pln-light' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2zm9 3h3m-3 4h3M8 8h.01M8 12h5m-5 4h5"></path>
                            </svg>
                            @break
                        @case('clipboard')
                            <svg class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover:scale-110 {{ $isActive ? 'text-pln-primary' : 'text-gray-400 group-hover:text-pln-light' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5h6m-6 4h6m-6 4h6m-3 4H7a2 2 0 01-2-2V7a2 2 0 012-2h2m6 0h2a2 2 0 012 2v10a2 2 0 01-2 2h-4"></path>
                            </svg>
                            @break
                        @case('truck')
                            <svg class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover:scale-110 {{ $isActive ? 'text-pln-primary' : 'text-gray-400 group-hover:text-pln-light' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17h6m-6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 104 0m-4 0h4v-5l-3-4h-5v9m0-9H5v9h4"></path>
                            </svg>
                            @break
                        @case('clock')
                            <svg class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover:scale-110 {{ $isActive ? 'text-pln-primary' : 'text-gray-400 group-hover:text-pln-light' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            @break
                        @case('shield')
                            <svg class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover:scale-110 {{ $isActive ? 'text-pln-primary' : 'text-gray-400 group-hover:text-pln-light' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22s8-4 8-10V6l-8-4-8 4v6c0 6 8 10 8 10zM9 12l2 2 4-4"></path>
                            </svg>
                            @break
                        @case('grid')
                        @default
                            <svg class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover:scale-110 {{ $isActive ? 'text-pln-primary' : 'text-gray-400 group-hover:text-pln-light' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                            </svg>
                    @endswitch

                    <span x-show="sidebarOpen" 
                          class="ml-3 whitespace-nowrap overflow-hidden transition-all duration-300 origin-left">
                        {{ $item['label'] }}
                    </span>

                    <div x-show="!sidebarOpen" class="hidden md:block absolute left-14 ml-2 bg-pln-primary text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity z-50 pointer-events-none whitespace-nowrap shadow-lg border-l-2 border-pln-yellow">
                        {{ $item['label'] }}
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    <div class="p-4 border-t border-gray-100 bg-gray-50/80">
        <div class="flex items-center transition-all duration-300" :class="!sidebarOpen ? 'justify-start md:justify-center flex-row md:flex-col md:space-y-3 space-x-3 md:space-x-0' : 'space-x-3'">
            
            <div class="shrink-0 h-10 w-10 rounded-full bg-gradient-to-br from-pln-primary to-pln-light flex items-center justify-center text-white font-bold shadow-md ring-2 ring-pln-light/30">
                {{ substr(Auth::user()->name, 0, 1) }}
            </div>

            <div x-show="sidebarOpen" class="flex-1 min-w-0 overflow-hidden">
                <p class="text-sm font-bold text-pln-primary truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
            </div>
            
            <form method="POST" action="{{ route('logout') }}" :class="!sidebarOpen ? 'md:w-full md:flex md:justify-center ml-auto md:ml-0' : ''">
                @csrf
                <button type="submit" class="text-gray-400 hover:text-pln-red transition-colors p-1 rounded hover:bg-red-50" title="Logout">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</nav>
