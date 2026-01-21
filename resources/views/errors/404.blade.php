<x-error-layout>
    @include('errors.partials.card', [
        'code' => '404',
        'title' => 'Halaman tidak ditemukan',
        'message' => 'Alamat yang Anda buka tidak tersedia atau sudah dipindahkan.'
    ])
</x-error-layout>
