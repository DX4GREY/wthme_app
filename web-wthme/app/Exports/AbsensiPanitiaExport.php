<?php

namespace App\Exports;

use App\Models\AbsensiPanitia;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AbsensiPanitiaExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        $sheets = [];
        $divisis = AbsensiPanitia::distinct()->pluck('divisi')->sort();
        
        foreach ($divisis as $divisi) {
            $sheets[] = new AbsensiPerDivisiSheet($divisi);
        }
        
        return $sheets;
    }
}

class AbsensiPerDivisiSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    private $divisi;
    
    public function __construct($divisi)
    {
        $this->divisi = $divisi;
    }
    
    public function collection()
    {
        return AbsensiPanitia::where('divisi', $this->divisi)
            ->orderBy('nama')
            ->get()
            ->map(function ($row, $index) {
                return [
                    'No'          => $index + 1,
                    'Nama'        => $row->nama,
                    'NIM'         => $row->nim,
                    'Divisi'      => $row->divisi,
                    'Status'      => $row->status === 'hadir' ? 'HADIR' : 'TIDAK HADIR',
                    'Waktu Absen' => $row->waktu_absen ? $row->waktu_absen->format('d/m/Y H:i') : '-',
                ];
            });
    }
    
    public function headings(): array
    {
        return ['No', 'Nama', 'NIM', 'Divisi', 'Status Kehadiran', 'Waktu Absen'];
    }
    
    public function title(): string
    {
        return $this->divisi;
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002f45']],
            ],
        ];
    }
}