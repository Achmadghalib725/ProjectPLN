<x-error-layout>
    @include('errors.partials.card', [
        'code' => '419',
        'title' => 'Sesi berakhir',
        'message' => 'Sesi Anda sudah berakhir. Silakan muat ulang halaman atau login kembali.'
    ])
</x-error-layout>
