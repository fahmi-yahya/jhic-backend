<?php

namespace App\Console\Commands;

use App\Services\LulusanImporter;
use Illuminate\Console\Command;

class ImportLulusan extends Command
{
    protected $signature = 'lulusan:import {file : Path ke file .xlsx}';
    protected $description = 'Import data lulusan dari file excel "Tahun_Lulus_XXXX.xlsx"';

    public function handle(LulusanImporter $importer): int
    {
        $path = $this->argument('file');
        if (!file_exists($path)) {
            $this->error("File tidak ditemukan: {$path}");
            return self::FAILURE;
        }

        $this->info("Membaca {$path} ...");
        $hasil = $importer->import($path);

        foreach ($hasil['warnings'] as $w) {
            $this->warn("  {$w}");
        }

        $this->info("Selesai. {$hasil['imported']} baris tersimpan, {$hasil['skipped']} baris dilewati.");
        return self::SUCCESS;
    }
}
