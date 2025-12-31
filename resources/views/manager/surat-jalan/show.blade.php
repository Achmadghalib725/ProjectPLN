@use('Illuminate\Support\Facades\Storage')
<x-app-layout>
    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-900 px-4 py-3 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-900 px-4 py-3 rounded-lg text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @php
                $statusLabels = [
                    'DRAFT' => 'Draft',
                    'MENUNGGU_PERSETUJUAN' => 'Menunggu Persetujuan',
                    'DITOLAK_PERSETUJUAN' => 'Ditolak Persetujuan',
                    'DIKIRIM' => 'Dikirim',
                    'DIPERIKSA' => 'Diperiksa',
                    'DITERIMA' => 'Diterima',
                    'MENUNGGU_DIKEMBALIKAN' => 'Menunggu Dikembalikan',
                    'DIKEMBALIKAN' => 'Dikembalikan',
                    'SELESAI' => 'Selesai',
                    'DITOLAK' => 'Ditolak',
                ];
                $statusStyles = [
                    'DRAFT' => 'bg-slate-100 text-slate-700',
                    'MENUNGGU_PERSETUJUAN' => 'bg-orange-100 text-orange-800',
                    'DITOLAK_PERSETUJUAN' => 'bg-red-100 text-red-700',
                    'DIKIRIM' => 'bg-blue-100 text-blue-700',
                    'DIPERIKSA' => 'bg-indigo-100 text-indigo-700',
                    'DITERIMA' => 'bg-emerald-100 text-emerald-700',
                    'MENUNGGU_DIKEMBALIKAN' => 'bg-amber-100 text-amber-800',
                    'DIKEMBALIKAN' => 'bg-teal-100 text-teal-700',
                    'SELESAI' => 'bg-emerald-100 text-emerald-700',
                    'DITOLAK' => 'bg-red-100 text-red-700',
                ];
                $tipeLabels = [
                    'TRANSFER' => 'Transfer',
                    'PEMINJAMAN' => 'Peminjaman',
                    'PENGEMBALIAN' => 'Pengembalian',
                ];
                $statusText = $statusLabels[$suratJalan->status] ?? $suratJalan->status;
                $statusClass = $statusStyles[$suratJalan->status] ?? 'bg-slate-100 text-slate-700';
                $tipeText = $tipeLabels[$suratJalan->tipe] ?? $suratJalan->tipe;
                $gudangTujuanNama = $suratJalan->gudang_tujuan_is_custom
                    ? ($suratJalan->gudang_tujuan_custom_nama ?? 'Gudang Lainnya')
                    : ($suratJalan->gudangTujuan->nama ?? '-');
                $gudangTujuanAlamat = $suratJalan->gudang_tujuan_is_custom
                    ? ($suratJalan->gudang_tujuan_custom_alamat ?? '-')
                    : ($suratJalan->gudangTujuan->alamat ?? '-');
                $gudangTujuanTelepon = $suratJalan->gudang_tujuan_is_custom
                    ? ($suratJalan->gudang_tujuan_custom_telepon ?? '-')
                    : ($suratJalan->gudangTujuan->telepon ?? '-');
            @endphp

            <div class="bg-white border border-slate-200 rounded-xl p-5 sm:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Surat Jalan</p>
                        <h2 class="text-xl sm:text-2xl font-bold text-slate-900">{{ $suratJalan->nomor }}</h2>
                        <div class="mt-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusClass }}">
                                {{ $statusText }}
                            </span>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <a href="{{ route('manager.surat-jalan.preview', $suratJalan->id) }}"
                           target="_blank"
                           class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200">
                            Preview PDF
                        </a>
                        <a href="{{ route('manager.surat-jalan.pdf', $suratJalan->id) }}"
                           class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200">
                            Download PDF
                        </a>
                        <a href="{{ route('manager.surat-jalan.index') }}"
                           class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-50 rounded-lg hover:bg-slate-100">
                            Kembali
                        </a>
                    </div>
                </div>

                @if($suratJalan->status === 'MENUNGGU_PERSETUJUAN')
                    <div class="mt-5 flex flex-col sm:flex-row gap-3">
                        <form method="POST" action="{{ route('manager.surat-jalan.approve', $suratJalan->id) }}" class="sm:flex-1">
                            @csrf
                            <button type="submit"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-pln-primary rounded-lg hover:bg-pln-light">
                                Approve & Kirim
                            </button>
                        </form>
                        <form method="POST"
                              action="{{ route('manager.surat-jalan.reject', $suratJalan->id) }}"
                              onsubmit="return confirm('Tolak persetujuan surat jalan ini?');"
                              class="sm:flex-1">
                            @csrf
                            <button type="submit"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700">
                                Tolak Persetujuan
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-4">
                    <h3 class="font-semibold text-slate-900">Ringkasan</h3>
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-slate-500">Tipe Surat</dt>
                            <dd class="font-semibold text-slate-900">{{ $tipeText }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Tanggal</dt>
                            <dd class="font-semibold text-slate-900">{{ $suratJalan->tanggal?->format('d M Y') ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Dibuat Oleh</dt>
                            <dd class="font-semibold text-slate-900">{{ $suratJalan->pembuat->name ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">PIC Tujuan</dt>
                            <dd class="font-semibold text-slate-900">
                                {{ $suratJalan->picTujuan->nama ?? '-' }}
                                @if($suratJalan->picTujuan?->jabatan)
                                    <span class="text-xs text-slate-500">({{ $suratJalan->picTujuan->jabatan }})</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-4">
                    <h3 class="font-semibold text-slate-900">Gudang</h3>
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-slate-500">Gudang Asal</dt>
                            <dd class="font-semibold text-slate-900">{{ $suratJalan->gudangAsal->nama ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Gudang Tujuan</dt>
                            <dd class="font-semibold text-slate-900">{{ $gudangTujuanNama }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Alamat Tujuan</dt>
                            <dd class="font-semibold text-slate-900">{{ $gudangTujuanAlamat }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Telepon Tujuan</dt>
                            <dd class="font-semibold text-slate-900">{{ $gudangTujuanTelepon }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-4">
                    <h3 class="font-semibold text-slate-900">Pengiriman</h3>
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-slate-500">Nama Driver</dt>
                            <dd class="font-semibold text-slate-900">{{ $suratJalan->nama_driver ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Jenis Kendaraan</dt>
                            <dd class="font-semibold text-slate-900">{{ $suratJalan->jenis_kendaraan ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Nomor Plat</dt>
                            <dd class="font-semibold text-slate-900">{{ $suratJalan->nomor_plat ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Tanda Tangan Persetujuan</dt>
                            <dd class="font-semibold text-slate-900">
                                {{ $suratJalan->ttdPembuat->name ?? '-' }}
                                @if($suratJalan->waktu_ttd_pembuat)
                                    <div class="text-xs text-slate-500">{{ $suratJalan->waktu_ttd_pembuat->format('d M Y H:i') }}</div>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            @if($peminjaman)
                <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-3">
                    <h3 class="font-semibold text-slate-900">Informasi Peminjaman</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                        <div>
                            <p class="text-slate-500">Kode Peminjaman</p>
                            <p class="font-semibold text-slate-900">{{ $peminjaman->kode }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Status Peminjaman</p>
                            <p class="font-semibold text-slate-900">{{ $peminjaman->status }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Waktu Kirim</p>
                            <p class="font-semibold text-slate-900">{{ $peminjaman->waktu_kirim?->format('d M Y H:i') ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-4">
                <h3 class="font-semibold text-slate-900">Catatan</h3>
                <p class="text-sm text-slate-700">{{ $suratJalan->catatan ?: '-' }}</p>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-semibold text-slate-900">Detail Barang</h3>
                    <span class="text-xs text-slate-500">Total: {{ $suratJalan->items->count() }} item</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Kode</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Nama</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Satuan</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @forelse($suratJalan->items as $item)
                                <tr>
                                    <td class="px-5 py-3 text-sm text-slate-900">{{ $item->item->kode ?? '-' }}</td>
                                    <td class="px-5 py-3 text-sm text-slate-900">{{ $item->item->nama ?? 'Item' }}</td>
                                    <td class="px-5 py-3 text-sm text-slate-500">{{ $item->item->satuan ?? '-' }}</td>
                                    <td class="px-5 py-3 text-sm text-slate-700 font-semibold">{{ $item->jumlah }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-6 text-center text-sm text-slate-500">
                                        Tidak ada detail barang.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-4">
                <h3 class="font-semibold text-slate-900">Lampiran</h3>
                @if($suratJalan->attachments->isEmpty())
                    <p class="text-sm text-slate-500">Belum ada lampiran.</p>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($suratJalan->attachments as $attachment)
                            <a href="{{ Storage::url($attachment->file_path) }}"
                               target="_blank"
                               class="group border border-slate-200 rounded-lg overflow-hidden hover:border-pln-primary">
                                <img src="{{ Storage::url($attachment->file_path) }}"
                                     alt="Lampiran surat jalan"
                                     class="h-48 w-full object-cover">
                                <div class="px-4 py-2 text-xs text-slate-500 group-hover:text-pln-primary">
                                    Klik untuk melihat ukuran penuh
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
