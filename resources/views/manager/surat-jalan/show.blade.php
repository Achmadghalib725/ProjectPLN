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
                    'DITOLAK_PERSETUJUAN' => 'Persetujuan Ditolak',
                    'DIKIRIM' => 'Dikirim',
                    'DIPERIKSA_PENGIRIM' => 'Menunggu Pemeriksaan',
                    'DIPERIKSA_PENERIMA' => 'Diperiksa Penerima',
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
                    'DIPERIKSA_PENGIRIM' => 'bg-cyan-100 text-cyan-700',
                    'DIPERIKSA_PENERIMA' => 'bg-indigo-100 text-indigo-700',
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
                $picNama = $suratJalan->picTujuan->nama ?? $suratJalan->pic_tujuan_custom_nama;
                $picJabatan = $suratJalan->picTujuan?->jabatan ?? $suratJalan->pic_tujuan_custom_jabatan;
                $picNoHp = $suratJalan->picTujuan?->no_hp ?? $suratJalan->pic_tujuan_custom_no_hp;
                $historyMap = $suratJalan->relationLoaded('statusHistories')
                    ? $suratJalan->statusHistories->groupBy('status')
                    : collect();
                $historyTime = function ($statuses) use ($historyMap) {
                    $statusList = is_array($statuses) ? $statuses : [$statuses];
                    foreach ($statusList as $status) {
                        $entry = $historyMap->get($status)?->last();
                        if ($entry?->occurred_at) {
                            return $entry->occurred_at;
                        }
                    }
                    return null;
                };
                $approvalTime = $historyTime(['DIPERIKSA_PENGIRIM', 'DIKIRIM']) ?? $suratJalan->waktu_ttd_pembuat;
                $pengembalianKirimAt = $suratJalan->tipe === 'PENGEMBALIAN'
                    ? ($historyTime(['DIKEMBALIKAN']) ?? $peminjaman?->waktu_pengembalian)
                    : null;
                $waktuKirim = $suratJalan->tipe === 'PENGEMBALIAN'
                    ? $pengembalianKirimAt
                    : $peminjaman?->waktu_kirim;
            @endphp

            <div class="bg-white border border-slate-200 rounded-xl p-5 sm:p-6" data-surat-jalan-header>
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Surat Jalan</p>
                        <h2 class="text-xl sm:text-2xl font-bold text-slate-900">{{ $suratJalan->nomor }}</h2>
                        <div class="mt-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusClass }}" data-surat-jalan-status data-base-class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold">
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
                        @php
                            $isFutureDate = $suratJalan->tanggal && $suratJalan->tanggal->isAfter(now()->startOfDay());
                        @endphp
                        <form method="POST"
                              action="{{ route('manager.surat-jalan.approve', $suratJalan->id) }}"
                              class="sm:flex-1"
                              id="approve-surat-jalan-form">
                            @csrf
                            @if($isFutureDate)
                                <button type="button"
                                        class="w-full inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-pln-primary rounded-lg hover:bg-pln-light"
                                        @click="$dispatch('open-modal', 'approve-confirm')">
                                    Approve & Kirim
                                </button>
                            @else
                                <button type="submit"
                                        class="w-full inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-pln-primary rounded-lg hover:bg-pln-light">
                                    Approve & Kirim
                                </button>
                            @endif
                        </form>
                        <button type="button"
                                @click="$dispatch('open-reject-approval', { action: '{{ route('manager.surat-jalan.reject', $suratJalan->id) }}' })"
                                class="w-full sm:flex-1 inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700">
                            Tolak Persetujuan
                        </button>
                    </div>
                    @if($isFutureDate)
                        <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                            <div class="flex items-start gap-2">

                                <p class="text-sm text-amber-800">
                                    <strong>Perhatian:</strong> Tanggal pengiriman surat jalan ini adalah <strong>{{ $suratJalan->tanggal->format('d M Y') }}</strong>.
                                </p>
                            </div>
                        </div>
                    @endif
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
                                {{ $picNama ?? '-' }}
                                @if($picJabatan)
                                    <span class="text-xs text-slate-500">({{ $picJabatan }})</span>
                                @endif
                                @if($picNoHp)
                                    <span class="text-xs text-slate-500">({{ $picNoHp }})</span>
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
                                @if($approvalTime)
                                    <div class="text-xs text-slate-500">{{ $approvalTime->format('d M Y H:i') }}</div>
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
                            <p class="font-semibold text-slate-900" data-peminjaman-status>{{ $peminjaman->status }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500">{{ $suratJalan->tipe === 'PENGEMBALIAN' ? 'Waktu Pengembalian' : 'Waktu Kirim' }}</p>
                            <p class="font-semibold text-slate-900">{{ $waktuKirim?->format('d M Y H:i') ?? '-' }}</p>
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
                    <h3 class="font-semibold text-slate-900">Daftar Barang</h3>
                    <span class="text-xs text-slate-500">Total: {{ $suratJalan->items->count() }} item</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase w-12">No</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Kode</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Nama Barang</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Jumlah</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Satuan</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @forelse($suratJalan->items as $index => $item)
                                <tr>
                                    <td class="px-5 py-3 text-sm text-slate-500">{{ $index + 1 }}</td>
                                    <td class="px-5 py-3 text-sm text-slate-900">{{ $item->item->kode ?? '-' }}</td>
                                    <td class="px-5 py-3 text-sm text-slate-900">{{ $item->item->nama ?? 'Item' }}</td>
                                    <td class="px-5 py-3 text-sm text-slate-700 font-semibold">{{ $item->jumlah }}</td>
                                    <td class="px-5 py-3 text-sm text-slate-500">{{ $item->item->satuan?->nama ?? '-' }}</td>
                                    <td class="px-5 py-3 text-sm text-slate-500">{{ $item->keterangan ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-6 text-center text-sm text-slate-500">
                                        Tidak ada daftar barang.
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
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (!window.Echo) {
                return;
            }
            const suratJalanId = {{ (int) $suratJalan->id }};
            const refreshHeader = async () => {
                const current = document.querySelector('[data-surat-jalan-header]');
                if (!current) {
                    return;
                }
                try {
                    const url = new URL(window.location.href);
                    url.searchParams.set('no_cache', '1');
                    const response = await fetch(url.toString(), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (!response.ok) {
                        return;
                    }
                    const html = await response.text();
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    const next = doc.querySelector('[data-surat-jalan-header]');
                    if (next) {
                        current.innerHTML = next.innerHTML;
                    }
                } catch (error) {
                    console.error('Realtime refresh failed', error);
                }
            };
            const statusLabels = {
                DRAFT: 'Draft',
                MENUNGGU_PERSETUJUAN: 'Menunggu Persetujuan',
                DITOLAK_PERSETUJUAN: 'Persetujuan Ditolak',
                DIKIRIM: 'Dikirim',
                DIPERIKSA_PENGIRIM: 'Menunggu Pemeriksaan',
                DIPERIKSA_PENERIMA: 'Diperiksa Penerima',
                DIPERIKSA: 'Diperiksa',
                DITERIMA: 'Diterima',
                MENUNGGU_DIKEMBALIKAN: 'Menunggu Dikembalikan',
                DIKEMBALIKAN: 'Dikembalikan',
                SELESAI: 'Selesai',
                DITOLAK: 'Ditolak',
            };
            const statusClassMap = {
                DRAFT: 'bg-slate-100 text-slate-700',
                MENUNGGU_PERSETUJUAN: 'bg-orange-100 text-orange-800',
                DITOLAK_PERSETUJUAN: 'bg-red-100 text-red-700',
                DIKIRIM: 'bg-blue-100 text-blue-700',
                DIPERIKSA_PENGIRIM: 'bg-cyan-100 text-cyan-700',
                DIPERIKSA_PENERIMA: 'bg-indigo-100 text-indigo-700',
                DIPERIKSA: 'bg-indigo-100 text-indigo-700',
                DITERIMA: 'bg-emerald-100 text-emerald-700',
                MENUNGGU_DIKEMBALIKAN: 'bg-amber-100 text-amber-800',
                DIKEMBALIKAN: 'bg-teal-100 text-teal-700',
                SELESAI: 'bg-emerald-100 text-emerald-700',
                DITOLAK: 'bg-red-100 text-red-700',
            };

            window.Echo.channel(`surat-jalan.detail.${suratJalanId}`)
                .listen('.SuratJalanStatusUpdated', (payload) => {
                    if (!payload || payload.id !== suratJalanId) {
                        return;
                    }
                    const status = payload.status || 'DRAFT';
                    document.querySelectorAll('[data-surat-jalan-status]').forEach((badge) => {
                        const baseClass = badge.dataset.baseClass || '';
                        const nextClass = statusClassMap[status] || 'bg-slate-100 text-slate-700';
                        badge.className = `${baseClass} ${nextClass}`.trim();
                        badge.textContent = statusLabels[status] || status;
                    });
                    if (payload.peminjaman_status) {
                        document.querySelectorAll('[data-peminjaman-status]').forEach((node) => {
                            node.textContent = payload.peminjaman_status;
                        });
                    }
                    refreshHeader();
                });
        });
    </script>
    <x-modal name="approve-confirm" focusable>
        <div class="p-6">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86l-7.1 12.3A2 2 0 004.9 19h14.2a2 2 0 001.71-2.84l-7.1-12.3a2 2 0 00-3.42 0z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Konfirmasi Persetujuan</h2>
                    <p class="text-sm text-slate-600">
                        Perhatian: Tanggal pengiriman surat jalan ini adalah <strong>{{ $suratJalan->tanggal->format('d M Y') }}</strong>.
                        Apakah Anda yakin ingin menyetujui surat jalan ini?
                    </p>
                </div>
            </div>
            <div class="mt-5 flex justify-end gap-3">
                <button type="button"
                        class="inline-flex items-center px-4 py-2 text-sm font-semibold text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200"
                        x-on:click="$dispatch('close-modal', 'approve-confirm')">
                    Batal
                </button>
                <button type="button"
                        class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-pln-primary rounded-lg hover:bg-pln-light"
                        x-on:click="document.getElementById('approve-surat-jalan-form').submit()">
                    Ya, Setujui
                </button>
            </div>
        </div>
    </x-modal>

    <x-modal name="reject-approval" focusable>
        <div class="p-6"
             x-data="{ formAction: '' }"
             x-on:open-reject-approval.window="formAction = $event.detail.action; $dispatch('open-modal', 'reject-approval')">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Tolak Persetujuan</h2>
                    <p class="text-sm text-slate-600">Masukkan alasan penolakan untuk surat jalan ini.</p>
                </div>
            </div>
            <form method="POST" x-bind:action="formAction" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label for="reject_reason_manager" class="block text-sm font-medium text-slate-700 mb-1">Alasan Penolakan</label>
                    <textarea id="reject_reason_manager"
                              name="alasan"
                              rows="3"
                              required
                              class="w-full rounded-lg border-slate-300 focus:border-red-500 focus:ring focus:ring-red-200"
                              placeholder="Tuliskan alasan penolakan..."></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button"
                            class="inline-flex items-center px-4 py-2 text-sm font-semibold text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200"
                            x-on:click="$dispatch('close-modal', 'reject-approval')">
                        Batal
                    </button>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700">
                        Tolak
                    </button>
                </div>
            </form>
        </div>
    </x-modal>

    <x-confirm-delete-modal />
</x-app-layout>
