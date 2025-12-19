<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Header --}}
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Pengaturan Profile</h2>
                <p class="text-sm text-gray-600 mt-1">Kelola informasi akun dan keamanan Anda</p>
            </div>

            <div class="space-y-6">
                {{-- Profile Information --}}
                <div class="p-4 sm:p-8 bg-white shadow-sm sm:rounded-lg border border-gray-200">
                    <div class="max-w-xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                {{-- Update Password --}}
                <div class="p-4 sm:p-8 bg-white shadow-sm sm:rounded-lg border border-gray-200">
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
