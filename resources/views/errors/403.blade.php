<x-error-layout>
    @include('errors.partials.card', [
        'code' => '403',
        'title' => 'Akses ditolak',
        'message' => 'Anda tidak memiliki izin untuk membuka halaman ini.'
    ])
</x-error-layout>
