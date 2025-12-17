<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.users.index') }}" class="text-white hover:text-[#ffff00] transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <h2 class="font-semibold text-xl text-white leading-tight">
                    {{ __('Detail Pengguna') }}
                </h2>
            </div>
            <a href="{{ route('admin.users.edit', $user) }}" class="px-4 py-2 bg-[#ffff00] text-[#035b71] rounded-lg text-sm font-bold hover:bg-[#ffff00]/90 transition">
                Edit Pengguna
            </a>
        </div>
    </x-slot>

    <x-slot name="title">
        Detail Pengguna - {{ $user->name }}
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Header Profile -->
                    <div class="flex items-center gap-4 pb-6 border-b border-gray-200">
                        <div class="w-20 h-20 rounded-full bg-[#00aff0] flex items-center justify-center text-white text-3xl font-bold">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h3>
                            <p class="text-gray-600">{{ $user->email }}</p>
                            <div class="flex gap-2 mt-2">
                                <span class="px-3 py-1 rounded-full text-xs font-bold
                                    {{ $user->role === 'admin' ? 'bg-red-100 text-red-600' :
                                      ($user->role === 'operator_gudang' ? 'bg-blue-100 text-blue-600' : 'bg-green-100 text-green-600') }}">
                                    {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                </span>
                                @if($user->is_active)
                                    <span class="px-3 py-1 bg-green-100 text-green-600 rounded-full text-xs font-bold">Aktif</span>
                                @else
                                    <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-bold">Nonaktif</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Detail Information -->
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Informasi Dasar -->
                        <div>
                            <h4 class="text-lg font-bold text-gray-900 mb-4">Informasi Dasar</h4>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Nama Lengkap</label>
                                    <p class="mt-1 text-gray-900">{{ $user->name }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Email</label>
                                    <p class="mt-1 text-gray-900">{{ $user->email }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">No. HP</label>
                                    <p class="mt-1 text-gray-900">{{ $user->no_hp ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Jabatan</label>
                                    <p class="mt-1 text-gray-900">{{ $user->jabatan ?? '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Informasi Akses -->
                        <div>
                            <h4 class="text-lg font-bold text-gray-900 mb-4">Informasi Akses</h4>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Role</label>
                                    <p class="mt-1 text-gray-900">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Gudang / Unit Kerja</label>
                                    <p class="mt-1 text-gray-900">{{ $user->gudang ? $user->gudang->nama : 'Tidak ada (Admin)' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Status Akun</label>
                                    <p class="mt-1">
                                        @if($user->is_active)
                                            <span class="text-green-600 font-semibold">Aktif</span>
                                        @else
                                            <span class="text-gray-600 font-semibold">Nonaktif</span>
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Terdaftar Sejak</label>
                                    <p class="mt-1 text-gray-900">{{ $user->created_at->format('d M Y, H:i') }}</p>
                                </div>
                                @if($user->updated_at != $user->created_at)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Terakhir Diperbarui</label>
                                    <p class="mt-1 text-gray-900">{{ $user->updated_at->format('d M Y, H:i') }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-8 pt-6 border-t border-gray-200 flex gap-3">
                        <a href="{{ route('admin.users.edit', $user) }}"
                           class="px-6 py-2 bg-[#00aff0] text-white rounded-lg hover:bg-[#00aff0]/90 transition font-bold">
                            Edit Pengguna
                        </a>
                        <a href="{{ route('admin.users.index') }}"
                           class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition font-bold">
                            Kembali
                        </a>
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="ml-auto"
                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna {{ $user->name }}? Tindakan ini tidak dapat dibatalkan.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-6 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition font-bold">
                                Hapus Pengguna
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
