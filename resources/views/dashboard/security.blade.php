<div class="grid grid-cols-1 md:grid-cols-2 gap-8">
    <div class="bg-[#035b71] text-white p-8 rounded-xl shadow-lg hover:shadow-2xl transition cursor-pointer flex flex-col items-center text-center">
        <div class="bg-white p-4 rounded-full mb-4">
            <svg class="w-16 h-16 text-[#035b71]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
        </div>
        <h2 class="text-3xl font-bold">SCAN SURAT JALAN</h2>
        <p class="mt-2 text-blue-100">Gunakan kamera atau scanner untuk memvalidasi QR Code surat jalan pengemudi.</p>
        <button class="mt-6 bg-[#ffff00] text-[#035b71] font-bold py-3 px-8 rounded-full hover:bg-yellow-300 transition">
            MULAI SCANNING
        </button>
    </div>

    <div class="bg-white p-8 rounded-xl shadow-lg border-t-8 border-[#ff0000]">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Input Manual Kode Surat Jalan</h3>
        <form action="#" method="POST"> @csrf
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Nomor SJ / Plat Nomor</label>
                <input type="text" placeholder="Contoh: SJ-2025/XII/001" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:border-[#00aff0] focus:ring-1 focus:ring-[#00aff0]">
            </div>
            <button type="submit" class="w-full bg-[#00aff0] text-white font-bold py-3 rounded-lg hover:bg-[#009bd6] transition">
                Cek Validitas
            </button>
        </form>

        <div class="mt-8 border-t pt-4">
            <h4 class="font-bold text-gray-600 mb-2">Log Aktifitas Terakhir</h4>
            <ul class="text-sm space-y-2">
                <li class="flex justify-between text-green-600">
                    <span>SJ-001 (Keluar) - Valid</span>
                    <span class="text-gray-400">10:42</span>
                </li>
                <li class="flex justify-between text-red-500">
                    <span>SJ-045 (Masuk) - Ditolak</span>
                    <span class="text-gray-400">09:15</span>
                </li>
            </ul>
        </div>
    </div>
</div>