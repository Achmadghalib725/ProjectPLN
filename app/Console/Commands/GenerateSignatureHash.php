<?php

namespace App\Console\Commands;

use App\Models\SuratJalan;
use Illuminate\Console\Command;

class GenerateSignatureHash extends Command
{
    protected $signature = 'surat-jalan:generate-hash {--id= : ID surat jalan tertentu} {--force : Regenerate hash meskipun sudah ada}';

    protected $description = 'Generate signature hash untuk surat jalan yang sudah ditandatangani';

    public function handle()
    {
        $id = $this->option('id');
        $force = $this->option('force');

        $query = SuratJalan::with('items')
            ->whereNotNull('ttd_pembuat_id');

        // Jika tidak force, hanya yang belum punya hash
        if (!$force) {
            $query->whereNull('signature_hash_pembuat');
        }

        if ($id) {
            $query->where('id', $id);
        }

        $suratJalans = $query->get();

        if ($suratJalans->isEmpty()) {
            $this->info('Tidak ada surat jalan yang perlu di-generate hash-nya.');
            return 0;
        }

        $this->info("Memproses {$suratJalans->count()} surat jalan...");

        $bar = $this->output->createProgressBar($suratJalans->count());
        $bar->start();

        foreach ($suratJalans as $suratJalan) {
            // Generate hash pembuat
            $suratJalan->update([
                'signature_hash_pembuat' => $suratJalan->generateDocumentHash('pembuat'),
                'signature_metadata_pembuat' => [
                    'ip_address' => 'generated-retroactively',
                    'user_agent' => 'CLI Command',
                    'signed_at' => $suratJalan->waktu_ttd_pembuat?->format('Y-m-d H:i:s'),
                    'timezone' => config('app.timezone', 'Asia/Jakarta'),
                    'note' => 'Hash generated retroactively via artisan command',
                ],
            ]);

            // Generate hash penerima jika sudah ada TTD penerima
            if ($suratJalan->ttd_penerima_id && !$suratJalan->signature_hash_penerima) {
                $suratJalan->refresh();
                $suratJalan->update([
                    'signature_hash_penerima' => $suratJalan->generateDocumentHash('penerima'),
                    'signature_metadata_penerima' => [
                        'ip_address' => 'generated-retroactively',
                        'user_agent' => 'CLI Command',
                        'signed_at' => $suratJalan->waktu_ttd_penerima?->format('Y-m-d H:i:s'),
                        'timezone' => config('app.timezone', 'Asia/Jakarta'),
                        'note' => 'Hash generated retroactively via artisan command',
                    ],
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Selesai! Hash berhasil di-generate.');

        return 0;
    }
}
