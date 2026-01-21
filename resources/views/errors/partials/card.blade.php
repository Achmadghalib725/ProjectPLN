<div class="bg-white/90 backdrop-blur rounded-2xl shadow-lg p-6 sm:p-8 text-center border border-white/50">
    <div class="text-5xl sm:text-6xl font-extrabold text-[#035b71]">
        {{ $code }}
    </div>
    <h1 class="mt-2 text-lg sm:text-xl font-semibold text-gray-800">
        {{ $title }}
    </h1>
    <p class="mt-3 text-sm text-gray-600 leading-relaxed">
        {{ $message }}
    </p>
    <div class="mt-6 grid gap-2">
        <a href="{{ url('/') }}" class="inline-flex items-center justify-center rounded-lg bg-[#035b71] px-4 py-2 text-sm font-semibold text-white shadow hover:bg-[#02485a]">
            Kembali ke Beranda
        </a>
        <button type="button" onclick="window.history.back()" class="inline-flex items-center justify-center rounded-lg border border-white/70 bg-white/70 px-4 py-2 text-sm font-semibold text-[#035b71] hover:bg-white">
            Kembali
        </button>
    </div>
</div>
