<?php

namespace App\Exports;

use App\Models\Notulensi;
use App\Models\NotulensiPoin;
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

class NotulensiExport implements WithMultipleSheets
{
    protected $notulensiId;

    public function __construct($id)
    {
        $this->notulensiId = $id;
    }

    public function sheets(): array
    {
        $sheets = [];
        $poinDivisi = NotulensiPoin::where('notulensi_id', $this->notulensiId)
            ->distinct()
            ->pluck('divisi');

        foreach ($poinDivisi as $divisi) {
            $sheets[] = new NotulensiPerDivisiSheet($this->notulensiId, $divisi);
        }

        return $sheets;
    }
}

class NotulensiPerDivisiSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize, WithEvents
{
    private $id;
    private $divisi;

    public function __construct($id, $divisi)
    {
        $this->id = $id;
        $this->divisi = $divisi;
    }

    public function collection()
    {
        $notulensi = Notulensi::find($this->id);
        
        return NotulensiPoin::where('notulensi_id', $this->id)
            ->where('divisi', $this->divisi)
            ->get()
            ->map(function ($row, $index) use ($notulensi) {
                return [
                    $index + 1,
                    $notulensi->tanggal,
                    $notulensi->topik,
                    $this->divisi,
                    $row->isi_poin,
                ];
            });
    }

    public function headings(): array
    {
        return ['No', 'Tanggal', 'Topik Rapat', 'Divisi', 'Poin Pembahasan'];
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
                
                // Wrap text untuk kolom Poin Pembahasan (Kolom E) agar tidak memanjang ke samping
                $sheet->getStyle("E2:E{$highestRow}")->getAlignment()->setWrapText(true);
                
                // Atur lebar kolom E secara manual karena isinya bisa sangat panjang
                $sheet->getColumnDimension('E')->setWidth(80);

                // Border dan Vertical Center
                $sheet->getStyle("A1:E{$highestRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'bdd1d3'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_TOP, // Top agar rapi jika teks panjang
                    ],
                ]);
            },
        ];
    }
}