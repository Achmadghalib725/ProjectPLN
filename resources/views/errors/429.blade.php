<x-error-layout>
    @include('errors.partials.card', [
        'code' => '429',
        'title' => 'Terlalu banyak permintaan',
        'message' => 'Anda mengirim terlalu banyak permintaan. Tunggu sebentar lalu coba lagi.'
    ])
</x-error-layout>
