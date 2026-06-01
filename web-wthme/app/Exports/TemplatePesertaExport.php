<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TemplatePesertaExport implements FromArray, WithHeadings
{
    /**
     * Menentukan Heading / Baris Pertama Excel
     */
    public function headings(): array
    {
        return [
            'nim',
            'nama',
            'angkatan',
            'kelompok',
            'gender'
        ];
    }

    /**
     * Memberikan contoh baris pengisian data (Optional)
     */
    public function array(): array
    {
        return [
            [
                'nim' => '221101001',
                'nama' => 'Ahmad Fauzi',
                'angkatan' => '2024',
                'kelompok' => '1',
                'gender' => 'L',
            ]
        ];
    }
}