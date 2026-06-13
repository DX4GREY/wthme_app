<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TemplateAbangExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return ['nama', 'angkatan'];
    }

    public function array(): array
    {
        // Contoh baris pengisian di dalam excel template
        return [
            ['Muhammad Zaki', '2022'],
            ['Ahmad Farhan', '2023']
        ];
    }
}