<?php

namespace App\Exports;

use App\Models\RiwayatPenyakit;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class KesehatanExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        $sheets = [];
        $kelompoks = RiwayatPenyakit::distinct()->pluck('kelompok')->sort();

        foreach ($kelompoks as $kelompok) {
            $sheets[] = new KesehatanPerKelompokSheet($kelompok);
        }

        return $sheets;
    }
}

class KesehatanPerKelompokSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize, WithEvents
{
    private $kelompok;

    public function __construct($kelompok)
    {
        $this->kelompok = $kelompok;
    }

    public function collection()
    {
        return RiwayatPenyakit::where('kelompok', $this->kelompok)
            ->orderByRaw("FIELD(kondisi_kesehatan, 'Perlu Perhatian', 'Cukup', 'Baik')")
            ->orderBy('nama')
            ->get()
            ->map(function ($row, $index) {
                return [
                    $index + 1,
                    $row->nama,
                    " " . $row->nim, // Tambah spasi agar NIM tidak jadi format E+ (scientific)
                    $row->kelompok,
                    strtoupper($row->kondisi_kesehatan),
                    $row->riwayat_penyakit ?? '-',
                    $row->alergi ?? '-',
                    $row->obat_rutin ?? '-',
                    $row->keterangan_tambahan ?? '-',
                ];
            });
    }

    public function headings(): array
    {
        return ['No', 'Nama Peserta', 'NIM', 'Kelompok', 'Kondisi', 'Riwayat Penyakit', 'Alergi', 'Obat Rutin', 'Keterangan'];
    }

    public function title(): string
    {
        return 'Kelompok ' . $this->kelompok;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style Header
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '002f45']
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                
                // Set Border untuk semua data
                $sheet->getStyle("A1:I{$highestRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'bdd1d3'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Pewarnaan Baris Berdasarkan Status (Kolom E)
                for ($row = 2; $row <= $highestRow; $row++) {
                    $status = $sheet->getCell("E{$row}")->getValue();
                    
                    if ($status == 'PERLU PERHATIAN') {
                        $sheet->getStyle("E{$row}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['rgb' => '9c0006']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'ffc7ce']]
                        ]);
                    } elseif ($status == 'CUKUP') {
                        $sheet->getStyle("E{$row}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['rgb' => '9c6500']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'ffeb9c']]
                        ]);
                    } elseif ($status == 'BAIK') {
                        $sheet->getStyle("E{$row}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['rgb' => '006100']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'c6efce']]
                        ]);
                    }
                }
            },
        ];
    }
}