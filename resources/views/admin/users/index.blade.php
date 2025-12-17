<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="text-white hover:text-[#ffff00] transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <h2 class="font-semibold text-xl text-white leading-tight">
                    {{ __('Kelola Pengguna') }}
                </h2>
            </div>
            <a href="{{ route('admin.users.create') }}" class="px-4 py-2 bg-[#ffff00] text-[#035b71] rounded-lg text-sm font-bold hover:bg-[#ffff00]/90 transition">
                + Tambah User
            </a>
        </div>
    </x-slot>

    <x-slot name="title">
        Kelola Pengguna
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4">
                <div class="p-4 bg-gray-50">
                    <form method="GET" action="{{ route('admin.users.index') }}" class="space-y-4">
                        <div class="flex flex-wrap gap-3 items-end">
                            <!-- Search -->
                            <div class="flex-1 min-w-[200px]">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Cari Pengguna</label>
                                <input type="text" name="search" value="{{ request('search') }}"
                                    placeholder="Nama atau email..."
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-[#00aff0] focus:border-[#00aff0]">
                            </div>

                            <!-- Filter Role -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Role</label>
                                <select name="role" class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-[#00aff0] focus:border-[#00aff0]">
                                    <option value="">Semua Role</option>
                                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="operator_gudang" {{ request('role') == 'operator_gudang' ? 'selected' : '' }}>Operator Gudang</option>
                                    <option value="security" {{ request('role') == 'security' ? 'selected' : '' }}>Security</option>
                                </select>
                            </div>

                            <!-- Filter Status -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                                <select name="status" class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-[#00aff0] focus:border-[#00aff0]">
                                    <option value="">Semua Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                                </select>
                            </div>

                            <!-- Buttons -->
                            <div class="flex gap-2">
                                <button type="submit" class="px-4 py-2 bg-[#00aff0] text-white text-sm rounded-lg hover:bg-[#00aff0]/90 transition font-bold">
                                    🔍 Filter
                                </button>
                                @if(request()->hasAny(['search', 'role', 'status']))
                                    <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-400 transition font-bold">
                                        ✕ Reset
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Active Filters Display -->
            @if(request()->hasAny(['search', 'role', 'status']))
                <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex flex-wrap gap-2 items-center">
                        <span class="text-sm font-medium text-gray-700">Filter Aktif:</span>
                        @if(request('search'))
                            <span class="px-3 py-1 bg-white border border-gray-300 rounded-full text-xs">
                                Pencarian: <strong>{{ request('search') }}</strong>
                            </span>
                        @endif
                        @if(request('role'))
                            <span class="px-3 py-1 bg-white border border-gray-300 rounded-full text-xs">
                                Role: <strong>{{ ucfirst(str_replace('_', ' ', request('role'))) }}</strong>
                            </span>
                        @endif
                        @if(request('status'))
                            <span class="px-3 py-1 bg-white border border-gray-300 rounded-full text-xs">
                                Status: <strong>{{ request('status') == 'active' ? 'Aktif' : 'Nonaktif' }}</strong>
                            </span>
                        @endif
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3">Nama</th>
                                    <th class="px-6 py-3">Email</th>
                                    <th class="px-6 py-3">Role</th>
                                    <th class="px-6 py-3">Gudang</th>
                                    <th class="px-6 py-3">Jabatan</th>
                                    <th class="px-6 py-3">Status</th>
                                    <th class="px-6 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium text-gray-900">
                                        {{ $user->name }}
                                        @if($user->no_hp)
                                            <br><span class="text-xs text-gray-500">{{ $user->no_hp }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">{{ $user->email }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 rounded-full text-xs font-bold
                                            {{ $user->role === 'admin' ? 'bg-red-100 text-red-600' :
                                              ($user->role === 'operator_gudang' ? 'bg-blue-100 text-blue-600' : 'bg-green-100 text-green-600') }}">
                                            {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $user->gudang ? $user->gudang->nama : '-' }}
                                    </td>
                                    <td class="px-6 py-4">{{ $user->jabatan ?? '-' }}</td>
                                    <td class="px-6 py-4">
                                        @if($user->is_active)
                                            <span class="px-2 py-1 bg-green-100 text-green-600 rounded-full text-xs font-bold">Aktif</span>
                                        @else
                                            <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-bold">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('admin.users.show', $user) }}" class="text-green-600 hover:underline font-bold" title="Lihat Detail">
                                                Detail
                                            </a>
                                            <span class="text-gray-300">|</span>
                                            <a href="{{ route('admin.users.edit', $user) }}" class="text-[#00aff0] hover:underline font-bold" title="Edit Pengguna">
                                                Edit
                                            </a>
                                            <span class="text-gray-300">|</span>
                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna {{ $user->name }}?\n\nEmail: {{ $user->email }}\nRole: {{ ucfirst(str_replace('_', ' ', $user->role)) }}\n\nTindakan ini tidak dapat dibatalkan!')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:underline font-bold" title="Hapus Pengguna">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        @if(request()->hasAny(['search', 'role', 'status']))
                                            <div class="py-8">
                                                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                </svg>
                                                <p class="text-lg font-medium">Tidak ada hasil yang ditemukan</p>
                                                <p class="text-sm mt-1">Coba ubah filter atau kata kunci pencarian</p>
                                            </div>
                                        @else
                                            <div class="py-8">
                                                <p class="text-lg">Belum ada data pengguna.</p>
                                                <p class="text-sm mt-1">Klik tombol "+ Tambah User" untuk menambahkan pengguna baru</p>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>