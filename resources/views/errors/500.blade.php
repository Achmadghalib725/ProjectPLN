<x-error-layout>
    @include('errors.partials.card', [
        'code' => '500',
        'title' => 'Kesalahan server',
        'message' => 'Terjadi kendala di server. Silakan coba lagi beberapa saat.'
    ])
</x-error-layout>
