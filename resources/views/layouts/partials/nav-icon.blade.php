@switch($icon)
    {{-- ICON MENGELOLA AKUN/USER TERBARU --}}
    @case('user-group')
    @case('users')
        <svg class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover:scale-110 {{ $isActive ? 'text-pln-primary' : 'text-gray-400 group-hover:text-pln-light' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        @break

    {{-- ICON KELOLA BARANG --}}
    @case('boxes')
        <svg class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover:scale-110 {{ $isActive ? 'text-pln-primary' : 'text-gray-400 group-hover:text-pln-light' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7l9 5 9-5M3 17l9 5 9-5M3 7v10m9-5v10m9-10v10"></path>
        </svg>
        @break

    {{-- ICON KELOLA PIC --}}
    @case('id')
        <svg class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover:scale-110 {{ $isActive ? 'text-pln-primary' : 'text-gray-400 group-hover:text-pln-light' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2zm9 3h3m-3 4h3M8 8h.01M8 12h5m-5 4h5"></path>
        </svg>
        @break

    {{-- ICON MANAJEMEN BARANG --}}
    @case('clipboard')
        <svg class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover:scale-110 {{ $isActive ? 'text-pln-primary' : 'text-gray-400 group-hover:text-pln-light' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5h6m-6 4h6m-6 4h6m-3 4H7a2 2 0 01-2-2V7a2 2 0 012-2h2m6 0h2a2 2 0 012 2v10a2 2 0 01-2 2h-4"></path>
        </svg>
        @break

    {{-- ICON SURAT JALAN --}}
    @case('truck')
        <svg class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover:scale-110 {{ $isActive ? 'text-pln-primary' : 'text-gray-400 group-hover:text-pln-light' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17h6m-6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 104 0m-4 0h4v-5l-3-4h-5v9m0-9H5v9h4"></path>
        </svg>
        @break

    {{-- ICON RIWAYAT --}}
    @case('clock')
        <svg class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover:scale-110 {{ $isActive ? 'text-pln-primary' : 'text-gray-400 group-hover:text-pln-light' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        @break

    {{-- ICON LOGOUT / PROFILE --}}
    @case('user')
        <svg class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover:scale-110 {{ $isActive ? 'text-pln-primary' : 'text-gray-400 group-hover:text-pln-light' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
        </svg>
        @break

    {{-- ICON DASHBOARD / DEFAULT --}}
    @case('grid')
    @default
        <svg class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover:scale-110 {{ $isActive ? 'text-pln-primary' : 'text-gray-400 group-hover:text-pln-light' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
        </svg>
@endswitch