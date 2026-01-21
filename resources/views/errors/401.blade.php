<x-error-layout>
    @include('errors.partials.card', [
        'code' => '401',
        'title' => 'Autentikasi diperlukan',
        'message' => 'Anda perlu login untuk mengakses halaman ini.'
    ])
</x-error-layout>
