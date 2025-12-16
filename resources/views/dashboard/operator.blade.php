<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <a href="{{ route('gudang.stok.index') }}" class="bg-white p-4 rounded-lg shadow border border-gray-200 hover:border-[#00aff0] transition">
        <div class="text-[#035b71] font-bold text-lg">📦 Inventaris Saya</div>
        <p class="text-sm text-gray-500 mt-1">Cek stok barang di gudang ini</p>
    </a>
    
    <div class="bg-white p-4 rounded-lg shadow border border-gray-200 hover:border-green-500 transition cursor-pointer">
        <div class="flex justify-between items-center">
            <div class="text-green-700 font-bold text-lg">📥 Barang Masuk</div>
            <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full">2 Baru</span>
        </div>
        <p class="text-sm text-gray-500 mt-1">Konfirmasi penerimaan barang</p>
    </div>

    <div class="bg-white p-4 rounded-lg shadow border border-gray-200 hover:border-orange-500 transition cursor-pointer">
        <div class="text-orange-700 font-bold text-lg">📤 Barang Keluar</div>
        <p class="text-sm text-gray-500 mt-1">Status pengiriman berjalan</p>
    </div>
</div>

<h3 class="font-bold text-[#035b71] text-lg mb-4 flex items-center gap-2">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
    Pembuatan Surat Jalan Digital
</h3>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <a href="{{ route('gudang.surat-jalan.create', ['tujuan' => 'pltd_teluk']) }}" class="bg-blue-50 p-6 rounded-lg border-2 border-dashed border-blue-300 hover:bg-blue-100 hover:border-[#00aff0] transition group">
        <div class="flex flex-col items-center text-center">
            <div class="w-12 h-12 bg-[#00aff0] rounded-full flex items-center justify-center text-white mb-3 group-hover:scale-110 transition">
                <span class="font-bold text-xl">T</span>
            </div>
            <h4 class="font-bold text-[#035b71]">Ke PLTD Teluk</h4>
            <p class="text-xs text-gray-500 mt-1">Surat jalan rutin antar unit</p>
        </div>
    </a>

    <a href="{{ route('gudang.surat-jalan.create', ['tujuan' => 'tarahan']) }}" class="bg-blue-50 p-6 rounded-lg border-2 border-dashed border-blue-300 hover:bg-blue-100 hover:border-[#00aff0] transition group">
        <div class="flex flex-col items-center text-center">
             <div class="w-12 h-12 bg-[#00aff0] rounded-full flex items-center justify-center text-white mb-3 group-hover:scale-110 transition">
                <span class="font-bold text-xl">TR</span>
            </div>
            <h4 class="font-bold text-[#035b71]">Ke Tarahan</h4>
            <p class="text-xs text-gray-500 mt-1">Surat jalan rutin antar unit</p>
        </div>
    </a>

    <a href="{{ route('gudang.surat-jalan.create') }}" class="bg-gray-50 p-6 rounded-lg border-2 border-dashed border-gray-300 hover:bg-gray-100 hover:border-gray-400 transition">
        <div class="flex flex-col items-center text-center">
             <div class="w-12 h-12 bg-gray-400 rounded-full flex items-center justify-center text-white mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            </div>
            <h4 class="font-bold text-gray-700">Tujuan Lainnya</h4>
            <p class="text-xs text-gray-500 mt-1">Buat surat jalan ke gudang umum</p>
        </div>
    </a>
</div>