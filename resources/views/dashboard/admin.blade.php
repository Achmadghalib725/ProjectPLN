<div class="space-y-8">
    
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#007CA8] to-[#005E7F] p-8 shadow-lg text-white">
        <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-3xl font-bold">Selamat Datang, Admin!</h2>
                <p class="mt-2 text-cyan-100 text-sm md:text-base">
                    Sistem Manajemen Aset & Inventaris E-Gudang PLN siap digunakan.
                </p>
            </div>
            <div class="hidden md:block opacity-80">
                <svg class="w-24 h-24 text-white/20 absolute -right-6 -bottom-8" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
        </div>
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-white/10 blur-3xl"></div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <a href="{{ route('admin.users.index') }}" class="group relative overflow-hidden bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Total Pengguna</p>
                    <h3 class="mt-2 text-3xl font-extrabold text-gray-800 group-hover:text-blue-600 transition-colors">
                        {{ $totalUsers ?? 0 }}
                    </h3>
                    <p class="mt-1 text-xs text-gray-400">Admin, Operator & Security</p>
                </div>
                <div class="p-3 bg-blue-50 rounded-xl text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-all duration-300 shadow-sm">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm font-medium text-blue-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300 transform translate-y-2 group-hover:translate-y-0">
                <span>Kelola Data</span>
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </div>
        </a>

        <a href="{{ route('admin.items.index') }}" class="group relative overflow-hidden bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Master Barang</p>
                    <h3 class="mt-2 text-3xl font-extrabold text-gray-800 group-hover:text-[#00aff0] transition-colors">
                        {{ $totalItems ?? 0 }}
                    </h3>
                    <p class="mt-1 text-xs text-gray-400">Item Inventaris Terdaftar</p>
                </div>
                <div class="p-3 bg-cyan-50 rounded-xl text-[#00aff0] group-hover:bg-[#00aff0] group-hover:text-white transition-all duration-300 shadow-sm">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm font-medium text-[#00aff0] opacity-0 group-hover:opacity-100 transition-opacity duration-300 transform translate-y-2 group-hover:translate-y-0">
                <span>Lihat Inventaris</span>
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </div>
        </a>

        <div class="group relative overflow-hidden bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Lokasi Gudang</p>
                    <h3 class="mt-2 text-3xl font-extrabold text-gray-800 group-hover:text-yellow-500 transition-colors">
                        {{ $totalGudangs ?? 0 }} <span class="text-lg font-normal text-gray-400">Unit</span>
                    </h3>
                    <p class="mt-1 text-xs text-gray-400">Total Gudang & PIC</p>
                </div>
                <div class="p-3 bg-yellow-50 rounded-xl text-yellow-500 group-hover:bg-yellow-400 group-hover:text-white transition-all duration-300 shadow-sm">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm font-medium text-yellow-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300 transform translate-y-2 group-hover:translate-y-0">
                <span>Detail Lokasi</span>
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-pln-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                Aksi Cepat
            </h3>
            <div class="grid grid-cols-1 gap-4">
                <button class="flex flex-col items-center justify-center p-4 border border-gray-100 rounded-xl hover:bg-gray-50 hover:border-blue-200 transition-colors group">
                    <div class="bg-blue-100 p-2 rounded-full text-blue-600 mb-2 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                    </div>
                    <span class="text-sm font-medium text-gray-600">Tambah User & PIC</span>
                </button>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Informasi Sistem</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-3 animate-pulse"></span>
                        <span class="text-sm font-medium text-gray-600">Status Server</span>
                    </div>
                    <span class="text-sm font-bold text-green-600">Online</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-sm font-medium text-gray-600">Terakhir Update</span>
                    </div>
                    <span class="text-sm font-bold text-gray-700">{{ now()->format('d M Y') }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                     <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-sm font-medium text-gray-600">Versi Aplikasi</span>
                    </div>
                    <span class="text-sm font-bold text-gray-700">v1.0.0</span>
                </div>
            </div>
        </div>
    </div>

</div>