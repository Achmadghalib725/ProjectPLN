<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="username" :value="__('Username')" class="text-[#035b71] font-bold" />
            <x-text-input id="username" class="block mt-1 w-full border-gray-300 focus:border-[#00aff0] focus:ring-[#00aff0] rounded-md shadow-sm"
                            type="text" name="username" :value="old('username')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" class="text-[#035b71] font-bold" />
            <x-text-input id="password" class="block mt-1 w-full border-gray-300 focus:border-[#00aff0] focus:ring-[#00aff0] rounded-md shadow-sm"
                            type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-[#035b71] shadow-sm focus:ring-[#00aff0]" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Ingat Saya') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-primary-button class="bg-[#035b71] hover:bg-[#024455] active:bg-[#023340] focus:ring-[#00aff0]">
                {{ __('Masuk') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>