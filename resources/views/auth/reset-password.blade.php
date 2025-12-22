<x-guest-layout>
    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <div class="relative">
                <x-text-input id="password" class="block mt-1 w-full pr-24" type="password" name="password" required autocomplete="new-password" />
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

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <div class="relative">
                <x-text-input id="password_confirmation" class="block mt-1 w-full pr-24"
                                    type="password"
                                    name="password_confirmation" required autocomplete="new-password" />
                <button type="button"
                        data-toggle-password="password_confirmation"
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

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Reset Password') }}
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
