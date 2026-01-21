<x-error-layout>
    @include('errors.partials.card', [
        'code' => '503',
        'title' => 'Layanan tidak tersedia',
        'message' => 'Layanan sedang tidak tersedia karena pemeliharaan. Silakan coba lagi nanti.'
    ])
</x-error-layout>
