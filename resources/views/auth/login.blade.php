<x-guest-layout>
    {{-- Card Container --}}
    <div class="bg-white rounded-xl shadow-lg overflow-hidden border-t-4 border-[#035b71]">
        <div class="p-6 sm:p-8">
            <div class="mb-6 text-center">
                <img src="{{ asset('Logo_PLN_NusantaraPower.png') }}"
                     alt="PLN Logo"
                     class="h-20 mx-auto mb-5 bg-white rounded-2xl p-3 shadow">
                <h1 class="text-base font-extrabold text-[#00aff0] tracking-wide uppercase">E-Dispatch & Tool Management System</h1>
            </div>
            {{-- Session Status --}}
            <x-auth-session-status class="mb-4" :status="session('status')" />

            {{-- Error Message (e.g., account deactivated) --}}
            @if(session('error'))
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-md">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm text-red-700">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            {{-- Login Form --}}
            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Username Field --}}
                <div class="mb-4">
                    <label for="username" class="block text-sm font-bold text-[#035b71] mb-1">
                        Username
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </span>
                        <input id="username"
                               type="text"
                               name="username"
                               value="{{ old('username') }}"
                               required
                               autofocus
                               autocomplete="username"
                               placeholder="Masukkan username"
                               class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#00aff0] focus:border-transparent">
                    </div>
                    <x-input-error :messages="$errors->get('username')" class="mt-1" />
                </div>

                {{-- Password Field --}}
                <div class="mb-4">
                    <label for="password" class="block text-sm font-bold text-[#035b71] mb-1">
                        Password
                    </label>
                    <div class="relative" x-data="{ show: false }">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </span>
                        <input id="password"
                               x-bind:type="show ? 'text' : 'password'"
                               name="password"
                               required
                               autocomplete="current-password"
                               placeholder="Masukkan password"
                               class="w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#00aff0] focus:border-transparent">
                        <button type="button"
                                x-on:click="show = !show"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                            <svg x-show="!show" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="show" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>

                {{-- Remember Me --}}
                <div class="mb-6">
                    <label for="remember_me" class="inline-flex items-center cursor-pointer">
                        <input id="remember_me"
                               type="checkbox"
                               name="remember"
                               class="w-4 h-4 rounded border-gray-300 text-[#035b71] focus:ring-[#00aff0]">
                        <span class="ml-2 text-sm text-gray-600">Ingat Saya</span>
                    </label>
                </div>

                {{-- Submit Button --}}
                <button type="submit"
                        class="w-full py-2.5 px-4 bg-[#035b71] hover:bg-[#024455] text-white font-semibold rounded-md transition duration-200 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    Masuk
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
