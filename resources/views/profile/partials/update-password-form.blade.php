<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Ubah Password
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Pastikan akun Anda menggunakan password yang kuat untuk menjaga keamanan.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" value="Password Saat Ini" class="text-[#035b71] font-bold" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full border-gray-300 focus:border-[#00aff0] focus:ring-[#00aff0]" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" value="Password Baru" class="text-[#035b71] font-bold" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full border-gray-300 focus:border-[#00aff0] focus:ring-[#00aff0]" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" value="Konfirmasi Password Baru" class="text-[#035b71] font-bold" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full border-gray-300 focus:border-[#00aff0] focus:ring-[#00aff0]" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button class="bg-[#035b71] hover:bg-[#024455] active:bg-[#023340] focus:ring-[#00aff0]">Simpan</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-600 font-medium"
                >Password berhasil diubah!</p>
            @endif
        </div>
    </form>
</section>
