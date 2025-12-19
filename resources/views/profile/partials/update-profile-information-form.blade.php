<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Informasi Profile
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Perbarui informasi profile Anda seperti nama dan username untuk login.
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" value="Nama Lengkap" class="text-[#035b71] font-bold" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full border-gray-300 focus:border-[#00aff0] focus:ring-[#00aff0]" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="username" value="Username" class="text-[#035b71] font-bold" />
            <x-text-input id="username" name="username" type="text" class="mt-1 block w-full border-gray-300 focus:border-[#00aff0] focus:ring-[#00aff0]" :value="old('username', $user->username)" required autocomplete="username" pattern="[a-z0-9_]+" title="Username hanya boleh huruf kecil, angka, dan underscore" />
            <x-input-error class="mt-2" :messages="$errors->get('username')" />
            <p class="mt-1 text-xs text-gray-500">Username digunakan untuk login. Hanya boleh huruf kecil, angka, dan underscore (_).</p>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button class="bg-[#035b71] hover:bg-[#024455] active:bg-[#023340] focus:ring-[#00aff0]">Simpan</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-600 font-medium"
                >Tersimpan!</p>
            @endif
        </div>
    </form>
</section>
