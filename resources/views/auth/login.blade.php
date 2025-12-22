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
            <div class="relative">
                <x-text-input id="password" class="block mt-1 w-full pr-24 border-gray-300 focus:border-[#00aff0] focus:ring-[#00aff0] rounded-md shadow-sm"
                                type="password" name="password" required autocomplete="current-password" />
                <button type="button"
                        data-toggle-password="password"
                        aria-label="Lihat password"
                        class="absolute inset-y-0 right-0 px-3 text-gray-600 hover:text-gray-900">
                    <svg data-eye-open class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <svg data-eye-closed class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 3l18 18M10.477 10.49a3 3 0 004.043 4.043M9.88 9.88A3 3 0 0114.12 14.12M5.08 5.08C3.71 6.327 2.75 8.01 2.458 12c.91 2.9 2.91 5.1 5.542 6.3M14.12 14.12A9.965 9.965 0 0112 19c-4.477 0-8.268-2.943-9.542-7a12.318 12.318 0 012.119-3.333M9.88 9.88A3 3 0 0112 9c.49 0 .96.12 1.38.33" />
                    </svg>
                </button>
            </div>
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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-toggle-password]').forEach((button) => {
                const inputId = button.getAttribute('data-toggle-password');
                const input = document.getElementById(inputId);
                if (!input) return;

                const eyeOpen = button.querySelector('[data-eye-open]');
                const eyeClosed = button.querySelector('[data-eye-closed]');

                button.addEventListener('click', () => {
                    const isPassword = input.getAttribute('type') === 'password';
                    input.setAttribute('type', isPassword ? 'text' : 'password');
                    button.setAttribute('aria-label', isPassword ? 'Sembunyikan password' : 'Lihat password');
                    if (eyeOpen && eyeClosed) {
                        eyeOpen.classList.toggle('hidden', isPassword);
                        eyeClosed.classList.toggle('hidden', !isPassword);
                    }
                });
            });
        });
    </script>
</x-guest-layout>
