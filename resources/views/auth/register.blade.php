<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Nama Lengkap')" class="text-[#035b71] font-bold" />
            <x-text-input id="name" class="block mt-1 w-full border-gray-300 focus:border-[#00aff0] focus:ring-[#00aff0] rounded-md shadow-sm" 
                            type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('Email Kedinasan')" class="text-[#035b71] font-bold" />
            <x-text-input id="email" class="block mt-1 w-full border-gray-300 focus:border-[#00aff0] focus:ring-[#00aff0] rounded-md shadow-sm" 
                            type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" class="text-[#035b71] font-bold" />
            <x-text-input id="password" class="block mt-1 w-full border-gray-300 focus:border-[#00aff0] focus:ring-[#00aff0] rounded-md shadow-sm" 
                            type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" class="text-[#035b71] font-bold" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full border-gray-300 focus:border-[#00aff0] focus:ring-[#00aff0] rounded-md shadow-sm" 
                            type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <a class="underline text-sm text-gray-600 hover:text-[#00aff0] rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#00aff0]" href="{{ route('login') }}">
                {{ __('Sudah terdaftar?') }}
            </a>

            <x-primary-button class="ms-4 bg-[#035b71] hover:bg-[#024455] active:bg-[#023340] focus:ring-[#00aff0]">
                {{ __('Daftar') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
