# E-Dispatch & Tool Management System

E-Dispatch & Tool Management System adalah aplikasi manajemen gudang dan surat jalan untuk pengiriman dan peminjaman barang antar gudang. Fokusnya adalah kontrol stok, alur persetujuan yang jelas, dan pelacakan status per peran.

## Gambaran umum
Aplikasi ini membantu operasional gudang dengan fitur stok, pergerakan, surat jalan, dan approval multi peran. Surat jalan dapat dipantau statusnya sampai selesai, termasuk proses penerimaan dan pengembalian.

## Fitur
- Master data gudang, item, kategori, satuan, dan PIC.
- Stok per gudang, mutasi, dan riwayat pergerakan.
- Surat jalan: draft, approval, pengiriman, pemeriksaan security, penerimaan, dan pengembalian.
- QR code, PDF surat jalan, lampiran, serta tanda tangan digital.
- Notifikasi, aktivitas, dan dashboard per peran.
- Export Excel untuk rekap dan daftar surat jalan.

## Peran dan akses
- Admin: kelola user, item, PIC, master data, dan rekap.
- Operator gudang: kelola stok, buat surat jalan, dan proses pengembalian.
- Manager: menyetujui atau menolak surat jalan dari gudang yang dikelola.
- Security: verifikasi surat jalan via QR dan konfirmasi terima atau tolak.
- Penerima: melihat dan menerima surat jalan sesuai divisi atau jabatan.

## Alur surat jalan (ringkas)
```
Draft
  -> Menunggu persetujuan
  -> Disetujui
  -> Dikirim
  -> Diperiksa (security)
  -> Diterima
  -> (Opsional) Pengembalian
  -> Selesai
```
Catatan: status detail dapat berbeda sesuai skenario, role, dan jenis transaksi.

## Teknologi
- Laravel 12, PHP 8.2
- Database PostgreSQL
- Vite, Tailwind CSS, Alpine.js
- Dompdf, Laravel Excel, Simple QR Code
- Laravel Reverb + EchO

## Prasyarat
- PHP 8.2+ dengan ekstensi umum Laravel 
- Composer
- Node.js + npm
- Database server

## Quick start
```
php artisan serve
npm run dev
```


