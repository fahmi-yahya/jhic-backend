<?php

namespace App\Services;

use App\Models\Lulusan;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Logic baca file "Tahun_Lulus_XXXX.xlsx" (sheet "Data Responden") dan
 * simpan ke tabel lulusans. Dipakai dari DUA tempat:
 * - app/Console/Commands/ImportLulusan.php (import lewat terminal)
 * - app/Http/Controllers/Api/LulusanController.php (upload dari frontend)
 * Supaya logic baca-excelnya cuma ada di satu tempat, tidak dobel.
 */
class LulusanImporter
{
    private array $kolomDicari = [
        'nisn' => 'NISN',
        'nama' => 'Nama',
        'tahun_lulus' => 'Tahun Lulus',
        'komp_keahlian' => 'Komp. Keahlian',
        'jenis_kelamin' => 'Jenis Kelamin',
        'status_aktifitas' => 'Status Aktifitas Lulusan',
        'keterangan' => 'Keterangan',
        'jabatan_bekerja' => 'Jabatan (Bekerja)',
        'nama_tempat_kerja' => 'Nama Tempat Kerja',
        'bidang_usaha' => 'Bidang Usaha',
        'nama_perguruan_tinggi' => 'Nama Perguruan Tinggi',
        'nama_prodi' => 'Nama Prodi',
    ];

    /**
     * @return array{imported: int, skipped: int, warnings: string[]}
     */
    public function import(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getSheetByName('Data Responden') ?? $spreadsheet->getActiveSheet();

        $rows = $sheet->toArray(null, true, true, false);
        $header = array_shift($rows) ?? [];

        $idx = [];
        $warnings = [];
        foreach ($this->kolomDicari as $field => $headerName) {
            $found = array_search($headerName, $header, true);
            if ($found === false) {
                $warnings[] = "Kolom \"{$headerName}\" tidak ketemu, dilewati.";
            }
            $idx[$field] = $found;
        }

        $imported = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $get = fn(string $field) => $idx[$field] !== false
                ? trim((string) ($row[$idx[$field]] ?? ''))
                : null;

            $nisn = $get('nisn');
            $nama = $get('nama');
            $tahun = $get('tahun_lulus');

            if (!$nama && !$nisn) {
                continue; // baris kosong, bukan error
            }

            if (!$tahun || !is_numeric($tahun)) {
                $skipped++;
                continue;
            }

            Lulusan::updateOrCreate(
                [
                    'nisn' => $nisn ?: ('noNISN-' . md5($nama . $tahun)),
                    'tahun_lulus' => (int) $tahun,
                ],
                [
                    'nama' => $nama,
                    'komp_keahlian' => $get('komp_keahlian'),
                    'jenis_kelamin' => $get('jenis_kelamin'),
                    'status_aktifitas' => $get('status_aktifitas') ?: null,
                    'keterangan' => $get('keterangan'),
                    'jabatan_bekerja' => $get('jabatan_bekerja'),
                    'nama_tempat_kerja' => $get('nama_tempat_kerja'),
                    'bidang_usaha' => $get('bidang_usaha'),
                    'nama_perguruan_tinggi' => $get('nama_perguruan_tinggi'),
                    'nama_prodi' => $get('nama_prodi'),
                ],
            );

            $imported++;
        }

        return compact('imported', 'skipped', 'warnings');
    }
}
