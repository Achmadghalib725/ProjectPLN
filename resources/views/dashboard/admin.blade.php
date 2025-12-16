<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <a href="{{ route('admin.users.index') }}" class="bg-white p-6 rounded-lg shadow-md border-l-4 border-red-600 hover:shadow-xl transition transform hover:-translate-y-1">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 uppercase">Total Pengguna</p>
                <h3 class="text-2xl font-bold text-gray-800">12</h3> </div>
            <div class="p-3 bg-red-100 rounded-full text-red-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-600">Kelola Admin, Operator & PIC</div>
    </a>

    <a href="{{ route('admin.items.index') }}" class="bg-white p-6 rounded-lg shadow-md border-l-4 border-[#00aff0] hover:shadow-xl transition transform hover:-translate-y-1">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 uppercase">Master Barang</p>
                <h3 class="text-2xl font-bold text-gray-800">145</h3>
            </div>
            <div class="p-3 bg-blue-100 rounded-full text-[#00aff0]">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-600">Database seluruh item inventaris</div>
    </a>

    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-yellow-400 hover:shadow-xl transition transform hover:-translate-y-1 cursor-pointer">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 uppercase">Data PIC & Gudang</p>
                <h3 class="text-2xl font-bold text-gray-800">5 Unit</h3>
            </div>
            <div class="p-3 bg-yellow-100 rounded-full text-yellow-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-600">Pengaturan Lokasi & Penanggung Jawab</div>
    </div>
</div>