<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemStock;
use App\Models\ItemUnit;
use App\Models\Gudang;

class ItemMekanikSeeder extends Seeder
{
    public function run(): void
    {
        // Create Categories
        $categories = [
            'alat ukur' => ItemCategory::firstOrCreate(['nama' => 'Alat Ukur']),
            'kunci' => ItemCategory::firstOrCreate(['nama' => 'Kunci']),
            'lainnya' => ItemCategory::firstOrCreate(['nama' => 'Lainnya']),
            'proteksi' => ItemCategory::firstOrCreate(['nama' => 'Proteksi']),
            'special tool' => ItemCategory::firstOrCreate(['nama' => 'Special Tool']),
            'tools' => ItemCategory::firstOrCreate(['nama' => 'Tools']),
        ];

        // Create Units
        $units = [
            'buah' => ItemUnit::firstOrCreate(['nama' => 'Buah']),
            'set' => ItemUnit::firstOrCreate(['nama' => 'Set']),
            'pasang' => ItemUnit::firstOrCreate(['nama' => 'Pasang']),
        ];

        // Data Items Gudang Mekanik
        $items = [
            // Alat Ukur
            ['kode' => 'ALA-001', 'nama' => 'Sigmat 15', 'kategori' => 'alat ukur', 'satuan' => 'buah'],
            ['kode' => 'ALA-002', 'nama' => 'Sigmat 20', 'kategori' => 'alat ukur', 'satuan' => 'buah'],
            ['kode' => 'ALA-003', 'nama' => 'Sigmat Digital', 'kategori' => 'alat ukur', 'satuan' => 'buah'],
            ['kode' => 'ALA-004', 'nama' => 'Mikrometer 25-50mm', 'kategori' => 'alat ukur', 'satuan' => 'buah'],
            ['kode' => 'ALA-005', 'nama' => 'Dial Defleksi Meter', 'kategori' => 'alat ukur', 'satuan' => 'buah'],
            ['kode' => 'ALA-006', 'nama' => 'Outset 400-600mm', 'kategori' => 'alat ukur', 'satuan' => 'buah'],
            ['kode' => 'ALA-007', 'nama' => 'Inset Inci', 'kategori' => 'alat ukur', 'satuan' => 'buah'],
            ['kode' => 'ALA-008', 'nama' => 'Dial Magnet', 'kategori' => 'alat ukur', 'satuan' => 'buah'],

            // Kunci
            ['kode' => 'KUN-001', 'nama' => 'Pas Ring 10mm', 'kategori' => 'kunci', 'satuan' => 'buah'],
            ['kode' => 'KUN-002', 'nama' => 'Pas Ring 11mm', 'kategori' => 'kunci', 'satuan' => 'buah'],
            ['kode' => 'KUN-003', 'nama' => 'Pas Ring 12mm', 'kategori' => 'kunci', 'satuan' => 'buah'],
            ['kode' => 'KUN-004', 'nama' => 'Pas Ring 13mm', 'kategori' => 'kunci', 'satuan' => 'buah'],
            ['kode' => 'KUN-005', 'nama' => 'Pas Ring 14mm', 'kategori' => 'kunci', 'satuan' => 'buah'],
            ['kode' => 'KUN-006', 'nama' => 'Pas Ring 15mm', 'kategori' => 'kunci', 'satuan' => 'buah'],
            ['kode' => 'KUN-007', 'nama' => 'Pas Ring 16mm', 'kategori' => 'kunci', 'satuan' => 'buah'],
            ['kode' => 'KUN-008', 'nama' => 'Pas Ring 17mm', 'kategori' => 'kunci', 'satuan' => 'buah'],
            ['kode' => 'KUN-009', 'nama' => 'Pas Ring 19mm', 'kategori' => 'kunci', 'satuan' => 'buah'],
            ['kode' => 'KUN-010', 'nama' => 'Pas Ring 21mm', 'kategori' => 'kunci', 'satuan' => 'buah'],
            ['kode' => 'KUN-011', 'nama' => 'Pas Ring 22mm', 'kategori' => 'kunci', 'satuan' => 'buah'],
            ['kode' => 'KUN-012', 'nama' => 'Pas Ring 24mm', 'kategori' => 'kunci', 'satuan' => 'buah'],
            ['kode' => 'KUN-013', 'nama' => 'Pas Ring 27mm', 'kategori' => 'kunci', 'satuan' => 'buah'],
            ['kode' => 'KUN-014', 'nama' => 'Pas Ring 28mm', 'kategori' => 'kunci', 'satuan' => 'buah'],
            ['kode' => 'KUN-015', 'nama' => 'Pas Ring 30mm', 'kategori' => 'kunci', 'satuan' => 'buah'],
            ['kode' => 'KUN-016', 'nama' => 'Pas Ring 32mm', 'kategori' => 'kunci', 'satuan' => 'buah'],
            ['kode' => 'KUN-017', 'nama' => 'Pas Ring 34mm', 'kategori' => 'kunci', 'satuan' => 'buah'],
            ['kode' => 'KUN-018', 'nama' => 'Pas Ring 36mm', 'kategori' => 'kunci', 'satuan' => 'buah'],
            ['kode' => 'KUN-019', 'nama' => 'Pas Ring 41mm', 'kategori' => 'kunci', 'satuan' => 'buah'],
            ['kode' => 'KUN-020', 'nama' => 'Kunci Pipa', 'kategori' => 'kunci', 'satuan' => 'buah'],
            ['kode' => 'KUN-021', 'nama' => 'Tang Buaya', 'kategori' => 'kunci', 'satuan' => 'buah'],

            // Lainnya
            ['kode' => 'LAI-001', 'nama' => 'Terpal', 'kategori' => 'lainnya', 'satuan' => 'buah'],

            // Proteksi
            ['kode' => 'PRO-001', 'nama' => 'Helm Las', 'kategori' => 'proteksi', 'satuan' => 'buah'],
            ['kode' => 'PRO-002', 'nama' => 'Helm Gerinda', 'kategori' => 'proteksi', 'satuan' => 'buah'],

            // Special Tool
            ['kode' => 'SPE-001', 'nama' => 'Jack SWD', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-002', 'nama' => 'Jack Sulzer', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-003', 'nama' => 'Jack RAM', 'kategori' => 'special tool', 'satuan' => 'pasang'],
            ['kode' => 'SPE-004', 'nama' => 'Kunci Pukul', 'kategori' => 'special tool', 'satuan' => 'buah'],
            ['kode' => 'SPE-005', 'nama' => 'Welding Tools', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-006', 'nama' => 'Stut Jack', 'kategori' => 'special tool', 'satuan' => 'buah'],
            ['kode' => 'SPE-007', 'nama' => 'Jack Man Bearing', 'kategori' => 'special tool', 'satuan' => 'pasang'],
            ['kode' => 'SPE-008', 'nama' => 'Tool Ring Piston', 'kategori' => 'special tool', 'satuan' => 'buah'],
            ['kode' => 'SPE-009', 'nama' => 'Tool Baut Tanam', 'kategori' => 'special tool', 'satuan' => 'buah'],
            ['kode' => 'SPE-010', 'nama' => 'Filter Motti', 'kategori' => 'special tool', 'satuan' => 'buah'],
            ['kode' => 'SPE-011', 'nama' => 'Tongkang Tools', 'kategori' => 'special tool', 'satuan' => 'buah'],
            ['kode' => 'SPE-012', 'nama' => 'Pompa Greace', 'kategori' => 'special tool', 'satuan' => 'buah'],
            ['kode' => 'SPE-013', 'nama' => 'Tool Sparator', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-014', 'nama' => 'Mur Cyl Head', 'kategori' => 'special tool', 'satuan' => 'buah'],
            ['kode' => 'SPE-015', 'nama' => 'Mata Bor', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-016', 'nama' => 'Bor', 'kategori' => 'special tool', 'satuan' => 'buah'],
            ['kode' => 'SPE-017', 'nama' => 'Grindra', 'kategori' => 'special tool', 'satuan' => 'buah'],
            ['kode' => 'SPE-018', 'nama' => 'Anting-Anting', 'kategori' => 'special tool', 'satuan' => 'buah'],
            ['kode' => 'SPE-019', 'nama' => 'Tracker', 'kategori' => 'special tool', 'satuan' => 'buah'],
            ['kode' => 'SPE-020', 'nama' => 'Palu', 'kategori' => 'special tool', 'satuan' => 'buah'],
            ['kode' => 'SPE-021', 'nama' => 'Pahat', 'kategori' => 'special tool', 'satuan' => 'buah'],
            ['kode' => 'SPE-022', 'nama' => 'Spit Cat', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-023', 'nama' => 'Takel 3 Ton', 'kategori' => 'special tool', 'satuan' => 'buah'],
            ['kode' => 'SPE-024', 'nama' => 'ST MESIN M.A.N', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-025', 'nama' => 'ST SULZER PENAHAN CAP', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-026', 'nama' => 'ST SULZER SKIR INJECTOR', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-027', 'nama' => 'ST SULZER PENAHAN PISTON', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-028', 'nama' => 'ST SULZER SKIR VALVE SEAT', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-029', 'nama' => 'ST SULZER SPRING VALVE', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-030', 'nama' => 'ST SULZER BOLD CAP MAIN BEARING', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-031', 'nama' => 'ST SULZER MELEPAS INJECTOR', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-032', 'nama' => 'ST SULZER MELEPAS PISTON', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-033', 'nama' => 'ST SULZER ROCKER GEAR', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-034', 'nama' => 'ST SULZER INJECTION PUMP', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-035', 'nama' => 'ST SULZER SKIR VALVE', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-036', 'nama' => 'ST SULZER MELEPAS VALVE SEAT', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-037', 'nama' => 'ST SULZER MELEPAS LINER', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-038', 'nama' => 'ST SULZER FICTOR MOTTY', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-039', 'nama' => 'ST SULZER SKIR LINER', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-040', 'nama' => 'ST SULZER PEMASANGAN PISTON', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-041', 'nama' => 'ST SULZER UJI KEBOCORAN CYL HEAD', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-042', 'nama' => 'ST SULZER SKIR CYL HEAD', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-043', 'nama' => 'ST SULZER MELEPAS CYL HEAD', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-044', 'nama' => 'ST SULZER CAP CRANKPIN BEARING', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-045', 'nama' => 'ST SULZER TURBOCHARGER', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-046', 'nama' => 'ST SWD BRACKET CAP MAIN BEARING', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-047', 'nama' => 'ST SWD MELEPAS HOUSING VALVE', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-048', 'nama' => 'ST SWD ROCKER ARM', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-049', 'nama' => 'ST SWD SHAFT ROCKER ARM', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-050', 'nama' => 'ST SWD MELEPAS PISTON', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-051', 'nama' => 'ST SWD CAM SHAFT', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-052', 'nama' => 'ST SWD MELEPAS INJECTOR', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-053', 'nama' => 'ST SWD VALVE SEAT', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-054', 'nama' => 'ST SWD STUD BOLD CYL HEAD', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-055', 'nama' => 'ST SWD SKIR VALVE', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-056', 'nama' => 'ST SWD INJECTOR', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-057', 'nama' => 'ST SWD SKIR HOUSING VALVE', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-058', 'nama' => 'ST SWD MELEPAS CYL HEAD', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-059', 'nama' => 'ST SWD MELEPAS LINER', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-060', 'nama' => 'ST SWD HYDRAULIC JACK HOUSING VALVE', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-061', 'nama' => 'ST SWD SKIR LANDING HOUSING VALVE', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-062', 'nama' => 'ST SWD PEMASANGAN PISTON', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-063', 'nama' => 'ST SWD SKIR LINER', 'kategori' => 'special tool', 'satuan' => 'set'],
            ['kode' => 'SPE-064', 'nama' => 'ST SWD SKIR CYL HEAD', 'kategori' => 'special tool', 'satuan' => 'set'],

            // Tools
            ['kode' => 'TOO-001', 'nama' => 'Gagang Shok', 'kategori' => 'tools', 'satuan' => 'set'],
            ['kode' => 'TOO-002', 'nama' => 'Pas Ring', 'kategori' => 'tools', 'satuan' => 'set'],
        ];

        // Data Stok untuk Gudang Teluk Betung (using nama for lookup)
        $stocks = [
            'Dial Defleksi Meter' => 1,
            'Dial Magnet' => 1,
            'Inset Inci' => 1,
            'Mikrometer 25-50mm' => 1,
            'Outset 400-600mm' => 1,
            'Sigmat 15' => 1,
            'Sigmat 20' => 1,
            'Sigmat Digital' => 1,
            'Kunci Pipa' => 4,
            'Pas Ring 10mm' => 4,
            'Pas Ring 11mm' => 3,
            'Pas Ring 12mm' => 4,
            'Pas Ring 13mm' => 4,
            'Pas Ring 14mm' => 3,
            'Pas Ring 15mm' => 4,
            'Pas Ring 16mm' => 2,
            'Pas Ring 17mm' => 4,
            'Pas Ring 19mm' => 5,
            'Pas Ring 21mm' => 3,
            'Pas Ring 22mm' => 8,
            'Pas Ring 24mm' => 8,
            'Pas Ring 27mm' => 4,
            'Pas Ring 28mm' => 2,
            'Pas Ring 30mm' => 3,
            'Pas Ring 32mm' => 3,
            'Pas Ring 34mm' => 1,
            'Pas Ring 36mm' => 3,
            'Pas Ring 41mm' => 1,
            'Tang Buaya' => 3,
            'Terpal' => 1,
            'Helm Gerinda' => 3,
            'Helm Las' => 3,
            'Anting-Anting' => 25,
            'Bor' => 2,
            'Filter Motti' => 3,
            'Grindra' => 3,
            'Jack Man Bearing' => 2,
            'Jack RAM' => 2,
            'Jack SWD' => 2,
            'Jack Sulzer' => 2,
            'Kunci Pukul' => 17,
            'Mata Bor' => 1,
            'Mur Cyl Head' => 7,
            'Pahat' => 3,
            'Palu' => 3,
            'Pompa Greace' => 7,
            'ST MESIN M.A.N' => 2,
            'ST SULZER BOLD CAP MAIN BEARING' => 3,
            'ST SULZER CAP CRANKPIN BEARING' => 3,
            'ST SULZER FICTOR MOTTY' => 1,
            'ST SULZER INJECTION PUMP' => 2,
            'ST SULZER MELEPAS CYL HEAD' => 2,
            'ST SULZER MELEPAS INJECTOR' => 1,
            'ST SULZER MELEPAS LINER' => 1,
            'ST SULZER MELEPAS PISTON' => 1,
            'ST SULZER MELEPAS VALVE SEAT' => 1,
            'ST SULZER PEMASANGAN PISTON' => 1,
            'ST SULZER PENAHAN CAP' => 1,
            'ST SULZER PENAHAN PISTON' => 1,
            'ST SULZER ROCKER GEAR' => 1,
            'ST SULZER SKIR CYL HEAD' => 2,
            'ST SULZER SKIR INJECTOR' => 1,
            'ST SULZER SKIR LINER' => 1,
            'ST SULZER SKIR VALVE' => 5,
            'ST SULZER SKIR VALVE SEAT' => 2,
            'ST SULZER SPRING VALVE' => 2,
            'ST SULZER TURBOCHARGER' => 1,
            'ST SULZER UJI KEBOCORAN CYL HEAD' => 1,
            'ST SWD BRACKET CAP MAIN BEARING' => 4,
            'ST SWD CAM SHAFT' => 1,
            'ST SWD HYDRAULIC JACK HOUSING VALVE' => 1,
            'ST SWD INJECTOR' => 1,
            'ST SWD MELEPAS CYL HEAD' => 1,
            'ST SWD MELEPAS HOUSING VALVE' => 2,
            'ST SWD MELEPAS INJECTOR' => 1,
            'ST SWD MELEPAS LINER' => 1,
            'ST SWD MELEPAS PISTON' => 1,
            'ST SWD PEMASANGAN PISTON' => 1,
            'ST SWD ROCKER ARM' => 1,
            'ST SWD SHAFT ROCKER ARM' => 1,
            'ST SWD SKIR CYL HEAD' => 1,
            'ST SWD SKIR HOUSING VALVE' => 1,
            'ST SWD SKIR LANDING HOUSING VALVE' => 1,
            'ST SWD SKIR LINER' => 1,
            'ST SWD SKIR VALVE' => 1,
            'ST SWD STUD BOLD CYL HEAD' => 1,
            'ST SWD VALVE SEAT' => 1,
            'Spit Cat' => 2,
            'Stut Jack' => 25,
            'Takel 3 Ton' => 2,
            'Tongkang Tools' => 6,
            'Tool Baut Tanam' => 19,
            'Tool Ring Piston' => 3,
            'Tool Sparator' => 1,
            'Tracker' => 3,
            'Welding Tools' => 2,
            'Gagang Shok' => 1,
            'Pas Ring' => 1,
        ];

        // Insert Items with foreign keys
        $createdItems = [];
        foreach ($items as $itemData) {
            $item = Item::create([
                'kode' => $itemData['kode'],
                'nama' => $itemData['nama'],
                'kategori_id' => $categories[$itemData['kategori']]->id,
                'satuan_id' => $units[$itemData['satuan']]->id,
                'tipe' => 'mekanik',
                'deskripsi' => null,
            ]);
            $createdItems[$item->nama] = $item->id;
        }

        // Get Gudang Teluk Betung
        $gudangTelukBetung = Gudang::where('nama', 'like', '%Teluk Betung%')->first();

        if ($gudangTelukBetung) {
            // Insert Stocks
            foreach ($stocks as $itemName => $jumlah) {
                if (isset($createdItems[$itemName])) {
                    ItemStock::create([
                        'item_id' => $createdItems[$itemName],
                        'gudang_id' => $gudangTelukBetung->id,
                        'jumlah' => $jumlah,
                        'stok_minimum' => 0,
                    ]);
                }
            }
        }

        $this->command->info('ItemMekanikSeeder: Inserted ' . count($items) . ' items and ' . count($stocks) . ' stocks with foreign keys.');
    }
}
