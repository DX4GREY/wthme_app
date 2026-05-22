<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TemplateExcelSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan folder ada
        if (!file_exists(public_path('templates'))) {
            mkdir(public_path('templates'), 0755, true);
        }

        Excel::store(new class implements FromArray, WithStyles {
            public function array(): array
            {
                return [
                    ['nama', 'nim', 'angkatan', 'divisi', 'email'],
                    ['Contoh Nama Panitia', '2201001', '2022', 'Konsumsi', 'contoh@email.com'],
                    ['Nama Panitia Lain',   '2201002', '2022', 'Acara',    'lain@email.com'],
                ];
            }

            public function styles(Worksheet $sheet): array
            {
                return [
                    1 => [
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002f45']],
                    ],
                ];
            }
        }, 'templates/template-import-panitia.xlsx', 'public');

        // Pindahkan ke folder public
        $from = storage_path('app/public/templates/template-import-panitia.xlsx');
        $to   = public_path('templates/template-import-panitia.xlsx');
        if (file_exists($from)) {
            copy($from, $to);
        }

        $this->command->info('Template Excel berhasil dibuat di public/templates/');
    }
}