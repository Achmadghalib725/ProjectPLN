<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemStock;
use App\Models\ItemUnit;
use App\Models\Gudang;

class ItemTarahanSeeder extends Seeder
{
    public function run(): void
    {
        // Create Categories
        $categories = [
            'alat ukur' => ItemCategory::firstOrCreate(['nama' => 'Alat Ukur']),
            'kunci' => ItemCategory::firstOrCreate(['nama' => 'Kunci']),
            'special tool' => ItemCategory::firstOrCreate(['nama' => 'Special Tool']),
            'proteksi' => ItemCategory::firstOrCreate(['nama' => 'Proteksi']),
        ];

        // Create Units
        $units = [
            'buah' => ItemUnit::firstOrCreate(['nama' => 'Buah']),
            'set' => ItemUnit::firstOrCreate(['nama' => 'Set']),
        ];

        // Data Items Gudang Tarahan
        $items = [
            ['kategori' => 'Alat Ukur', 'nama' => 'Dial Defleksi Meter', 'ukuran' => null, 'jumlah' => 1, 'satuan' => 'Buah', 'tipe' => 'Mekanik'],
            ['kategori' => 'Alat Ukur', 'nama' => 'Sigmat', 'ukuran' => '30mm', 'jumlah' => 1, 'satuan' => 'Buah', 'tipe' => 'Mekanik'],
            ['kategori' => 'Alat Ukur', 'nama' => 'Jangka Sorong', 'ukuran' => null, 'jumlah' => 1, 'satuan' => 'Buah', 'tipe' => 'Mekanik'],
            ['kategori' => 'Alat Ukur', 'nama' => 'Bore Gauge', 'ukuran' => '450mm', 'jumlah' => 1, 'satuan' => 'Buah', 'tipe' => 'Mekanik'],
            ['kategori' => 'Alat Ukur', 'nama' => 'Bore Gauge', 'ukuran' => '250-450mm', 'jumlah' => 1, 'satuan' => 'Buah', 'tipe' => 'Mekanik'],
            ['kategori' => 'Alat Ukur', 'nama' => 'Outset', 'ukuran' => '103-144', 'jumlah' => 1, 'satuan' => 'Buah', 'tipe' => 'Mekanik'],
            ['kategori' => 'Alat Ukur', 'nama' => 'Pressure Indicator', 'ukuran' => null, 'jumlah' => 1, 'satuan' => 'Buah', 'tipe' => 'Mekanik'],
            ['kategori' => 'Alat Ukur', 'nama' => 'Dial Magnet', 'ukuran' => null, 'jumlah' => 2, 'satuan' => 'Buah', 'tipe' => 'Mekanik'],
            ['kategori' => 'Alat Ukur', 'nama' => 'Termograph', 'ukuran' => null, 'jumlah' => 1, 'satuan' => 'Buah', 'tipe' => 'Mekanik'],
            ['kategori' => 'Alat Ukur', 'nama' => 'Mikrometer', 'ukuran' => null, 'jumlah' => 1, 'satuan' => 'Buah', 'tipe' => 'Mekanik'],
            ['kategori' => 'Kunci', 'nama' => 'Kunci Ring', 'ukuran' => '30mm', 'jumlah' => 1, 'satuan' => 'Buah', 'tipe' => 'Mekanik'],
            ['kategori' => 'Kunci', 'nama' => 'Kunci Ring', 'ukuran' => '24mm', 'jumlah' => 1, 'satuan' => 'Buah', 'tipe' => 'Mekanik'],
            ['kategori' => 'Kunci', 'nama' => 'Kunci Momen', 'ukuran' => null, 'jumlah' => 11, 'satuan' => 'Buah', 'tipe' => 'Mekanik'],
            ['kategori' => 'Special Tool', 'nama' => 'Tracker', 'ukuran' => null, 'jumlah' => 2, 'satuan' => 'Buah', 'tipe' => 'Mekanik'],
            ['kategori' => 'Special Tool', 'nama' => 'Kunci Pukul', 'ukuran' => '55mm', 'jumlah' => 1, 'satuan' => 'Buah', 'tipe' => 'Mekanik'],
            ['kategori' => 'Special Tool', 'nama' => 'Kunci Pukul', 'ukuran' => '46mm', 'jumlah' => 1, 'satuan' => 'Buah', 'tipe' => 'Mekanik'],
            ['kategori' => 'Special Tool', 'nama' => 'Pompa HIdrolik', 'ukuran' => 'Angin', 'jumlah' => 1, 'satuan' => 'Set', 'tipe' => 'Mekanik'],
            ['kategori' => 'Special Tool', 'nama' => 'Pompa HIdrolik', 'ukuran' => 'Elektrik', 'jumlah' => 1, 'satuan' => 'Set', 'tipe' => 'Mekanik'],
            ['kategori' => 'Special Tool', 'nama' => 'Grounding', 'ukuran' => null, 'jumlah' => 1, 'satuan' => 'Set', 'tipe' => 'Listrik'],
            ['kategori' => 'Special Tool', 'nama' => 'Megger', 'ukuran' => '5000V', 'jumlah' => 3, 'satuan' => 'Set', 'tipe' => 'Listrik'],
            ['kategori' => 'Special Tool', 'nama' => 'Hand Tap', 'ukuran' => null, 'jumlah' => 3, 'satuan' => 'Set', 'tipe' => 'Mekanik'],
            ['kategori' => 'Special Tool', 'nama' => 'Takel', 'ukuran' => '1 Ton', 'jumlah' => 1, 'satuan' => 'Buah', 'tipe' => 'Mekanik'],
            ['kategori' => 'Special Tool', 'nama' => 'Takel', 'ukuran' => '2 Ton', 'jumlah' => 1, 'satuan' => 'Buah', 'tipe' => 'Mekanik'],
            ['kategori' => 'Special Tool', 'nama' => 'Takel', 'ukuran' => '3 Ton', 'jumlah' => 1, 'satuan' => 'Buah', 'tipe' => 'Mekanik'],
            ['kategori' => 'Special Tool', 'nama' => 'Takel', 'ukuran' => '5 Ton', 'jumlah' => 1, 'satuan' => 'Buah', 'tipe' => 'Mekanik'],
            ['kategori' => 'Special Tool', 'nama' => 'Tool Ring Piston', 'ukuran' => null, 'jumlah' => 3, 'satuan' => 'Buah', 'tipe' => 'Mekanik'],
            ['kategori' => 'Special Tool', 'nama' => 'Pompa Greace', 'ukuran' => null, 'jumlah' => 4, 'satuan' => 'Buah', 'tipe' => 'Mekanik'],
            ['kategori' => 'Special Tool', 'nama' => 'Mesin Las', 'ukuran' => null, 'jumlah' => 2, 'satuan' => 'Set', 'tipe' => 'Mekanik'],
            ['kategori' => 'Special Tool', 'nama' => 'Catcher/Kacer', 'ukuran' => null, 'jumlah' => 1, 'satuan' => 'Buah', 'tipe' => 'Mekanik'],
            ['kategori' => 'Special Tool', 'nama' => 'Anting-Anting', 'ukuran' => null, 'jumlah' => 30, 'satuan' => 'Buah', 'tipe' => 'Mekanik'],
            ['kategori' => 'Special Tool', 'nama' => 'Heater', 'ukuran' => null, 'jumlah' => 1, 'satuan' => 'Set', 'tipe' => 'Listrik'],
            ['kategori' => 'Special Tool', 'nama' => 'Tool Spring Valve', 'ukuran' => null, 'jumlah' => 1, 'satuan' => 'Buah', 'tipe' => 'Mekanik'],
            ['kategori' => 'Special Tool', 'nama' => 'Gerinda', 'ukuran' => null, 'jumlah' => 5, 'satuan' => 'Buah', 'tipe' => 'Mekanik'],
            ['kategori' => 'Special Tool', 'nama' => 'Gerinda Besar', 'ukuran' => null, 'jumlah' => 1, 'satuan' => 'Buah', 'tipe' => 'Mekanik'],
            ['kategori' => 'Special Tool', 'nama' => 'Bor', 'ukuran' => null, 'jumlah' => 2, 'satuan' => 'Buah', 'tipe' => 'Mekanik'],
            ['kategori' => 'Special Tool', 'nama' => 'Bor Portable', 'ukuran' => null, 'jumlah' => 1, 'satuan' => 'Buah', 'tipe' => 'Mekanik'],
            ['kategori' => 'Special Tool', 'nama' => 'Impact', 'ukuran' => 'Angin', 'jumlah' => 1, 'satuan' => 'Buah', 'tipe' => 'Mekanik'],
            ['kategori' => 'Special Tool', 'nama' => 'Impact', 'ukuran' => 'Elektrik', 'jumlah' => 1, 'satuan' => 'Buah', 'tipe' => 'Mekanik'],
            ['kategori' => 'Special Tool', 'nama' => 'Blower Portable', 'ukuran' => null, 'jumlah' => 1, 'satuan' => 'Buah', 'tipe' => 'Mekanik'],
            ['kategori' => 'Special Tool', 'nama' => 'Alat Ukur Spring', 'ukuran' => null, 'jumlah' => 1, 'satuan' => 'Set', 'tipe' => 'Mekanik'],
            ['kategori' => 'Proteksi', 'nama' => 'Faceshield', 'ukuran' => null, 'jumlah' => 6, 'satuan' => 'Buah', 'tipe' => 'Mekanik'],
            ['kategori' => 'Proteksi', 'nama' => 'Helm Las', 'ukuran' => null, 'jumlah' => 2, 'satuan' => 'Buah', 'tipe' => 'Mekanik'],
            ['kategori' => 'Proteksi', 'nama' => 'Safety Belt', 'ukuran' => null, 'jumlah' => 5, 'satuan' => 'Buah', 'tipe' => 'Mekanik'],
        ];

        // Get Gudang Tarahan
        $gudangTarahan = Gudang::where('nama', 'like', '%Tarahan%')->first();

        if (!$gudangTarahan) {
            $this->command->warn('ItemTarahanSeeder: Gudang Tarahan tidak ditemukan, seed dibatalkan.');
            return;
        }

        $createdItems = 0;
        $processedStocks = 0;

        foreach ($items as $itemData) {
            $kategoriKey = strtolower(trim($itemData['kategori']));
            $satuanKey = strtolower(trim($itemData['satuan']));
            $tipe = strtolower(trim($itemData['tipe']));

            $kategori = $categories[$kategoriKey] ?? ItemCategory::firstOrCreate([
                'nama' => ucwords($kategoriKey),
            ]);

            $satuan = $units[$satuanKey] ?? ItemUnit::firstOrCreate([
                'nama' => ucwords($satuanKey),
            ]);

            $nama = trim(preg_replace('/\s+/', ' ', $itemData['nama']));
            $ukuran = $itemData['ukuran'];
            $ukuran = is_string($ukuran) ? trim(preg_replace('/\s+/', ' ', $ukuran)) : null;
            $ukuran = $ukuran === '' ? null : $ukuran;
            if ($ukuran) {
                $nama = trim($nama . ' ' . $ukuran);
            }

            $item = Item::firstOrCreate(
                [
                    'nama' => $nama,
                    'kategori_id' => $kategori->id,
                    'satuan_id' => $satuan->id,
                    'tipe' => $tipe,
                    'deskripsi' => null,
                ]
            );

            if ($item->wasRecentlyCreated) {
                $createdItems++;
            }

            ItemStock::updateOrCreate(
                [
                    'item_id' => $item->id,
                    'gudang_id' => $gudangTarahan->id,
                ],
                [
                    'jumlah' => $itemData['jumlah'],
                    'stok_minimum' => 0,
                ]
            );

            $processedStocks++;
        }

        $this->command->info('ItemTarahanSeeder: processed ' . count($items) . ' items, created ' . $createdItems . ' items, and upserted ' . $processedStocks . ' stocks.');
    }
}
