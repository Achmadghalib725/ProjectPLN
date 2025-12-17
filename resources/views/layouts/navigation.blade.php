<nav 
    class="fixed inset-y-0 left-0 z-30 bg-white shadow-2xl transition-all duration-300 ease-in-out flex flex-col justify-between border-r border-gray-200 md:static shrink-0"
    :class="sidebarOpen ? 'translate-x-0 w-64' : '-translate-x-full md:translate-x-0 w-64 md:w-20'"
>
    <div class="flex flex-col">
        <div class="h-20 flex items-center bg-[#035b71] relative overflow-hidden transition-all duration-300"
             :class="sidebarOpen ? 'px-6 justify-between' : 'px-0 justify-center'">
            
            <div class="absolute top-0 right-0 -mt-2 -mr-2 w-20 h-20 bg-[#ffff00] opacity-10 rounded-full blur-2xl transition-opacity duration-300" :class="sidebarOpen ? 'opacity-10' : 'opacity-0'"></div>
            
            <div class="flex items-center z-10 overflow-hidden w-full" :class="sidebarOpen ? 'justify-start space-x-3' : 'justify-center'">
                <a href="{{ route('dashboard') }}" class="transition-transform duration-300 hover:scale-105">
                    <img src="{{ asset('Logo_PLN.png') }}" alt="PLN Logo" class="block h-10 w-auto" />
                </a>
                
                <div x-show="sidebarOpen" 
                     class="text-white whitespace-nowrap overflow-hidden transition-opacity duration-300 delay-100">
                    <h1 class="font-bold text-xl leading-tight tracking-wide">MANAJEMEN</h1>
                    <p class="text-[10px] font-bold text-[#ffff00] tracking-widest">GUDANG</p>
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
             <button @click="sidebarOpen = true" class="text-[#00aff0] hover:bg-[#00aff0]/10 p-1.5 rounded-full transition shadow-sm border border-gray-200 group">
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
            
            <a href="{{ route('dashboard') }}" 
               class="group flex items-center px-3 py-3 rounded-lg transition-all duration-200 relative overflow-hidden
               {{ request()->routeIs('dashboard') 
                  ? 'bg-gradient-to-r from-[#00aff0]/10 to-transparent text-[#00aff0] font-bold' 
                  : 'text-gray-600 hover:bg-gray-50 hover:text-[#00aff0]' }}"
               :class="!sidebarOpen ? 'justify-start md:justify-center' : ''">
                
                @if(request()->routeIs('dashboard'))
                <div class="absolute left-0 top-1 bottom-1 w-1 bg-[#ffff00] rounded-r shadow-[0_0_10px_rgba(255,255,0,0.6)]"></div>
                @endif

                <svg class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover:scale-110 {{ request()->routeIs('dashboard') ? 'text-[#00aff0]' : 'text-gray-400 group-hover:text-[#00aff0]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                </svg>

                <span x-show="sidebarOpen" 
                      class="ml-3 whitespace-nowrap overflow-hidden transition-all duration-300 origin-left">
                    Dashboard
                </span>

                <div x-show="!sidebarOpen" class="hidden md:block absolute left-14 ml-2 bg-[#00aff0] text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity z-50 pointer-events-none whitespace-nowrap shadow-lg border-l-2 border-[#ffff00]">
                    Dashboard
                </div>
            </a>
        </div>
    </div>

    <div class="p-4 border-t border-gray-100 bg-gray-50/80">
        <div class="flex items-center transition-all duration-300" :class="!sidebarOpen ? 'justify-start md:justify-center flex-row md:flex-col md:space-y-3 space-x-3 md:space-x-0' : 'space-x-3'">
            
            <div class="shrink-0 h-10 w-10 rounded-full bg-gradient-to-br from-[#00aff0] to-[#008ec2] flex items-center justify-center text-white font-bold shadow-md ring-2 ring-[#00aff0]/30">
                {{ substr(Auth::user()->name, 0, 1) }}
            </div>

            <div x-show="sidebarOpen" class="flex-1 min-w-0 overflow-hidden">
                <p class="text-sm font-bold text-[#035b71] truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
            </div>
            
            <form method="POST" action="{{ route('logout') }}" :class="!sidebarOpen ? 'md:w-full md:flex md:justify-center ml-auto md:ml-0' : ''">
                @csrf
                <button type="submit" class="text-gray-400 hover:text-[#ff0000] transition-colors p-1 rounded hover:bg-red-50" title="Logout">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</nav>