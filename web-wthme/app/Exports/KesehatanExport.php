<?php

namespace App\Exports;

use App\Models\RiwayatPenyakit;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

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

class KesehatanPerKelompokSheet extends DefaultValueBinder implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths, WithMapping, WithCustomValueBinder, WithEvents
{
    private $kelompok;
    private $rowNumber = 0;

    public function __construct($kelompok)
    {
        $this->kelompok = $kelompok;
    }

    public function collection()
    {
        return RiwayatPenyakit::where('kelompok', $this->kelompok)
            ->orderBy('nama')
            ->get();
    }

    public function bindValue(Cell $cell, $value)
    {
        $column = $cell->getColumn();
        
        if (in_array($column, ['C', 'E', 'F']) && $cell->getRow() > 1) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function map($row): array
    {
        $this->rowNumber++;
        
        $urlFoto = $row->bukti_kesehatan ? asset('storage/' . $row->bukti_kesehatan) : null;
        $hyperlink = $urlFoto ? '=HYPERLINK("' . $urlFoto . '", "Lihat Foto")' : '-';

        return [
            $this->rowNumber,
            $row->nama,
            $row->nim ? trim($row->nim) : '-',                
            $row->kelompok,
            $row->no_telp ? trim($row->no_telp) : '-',          
            $row->no_telp_ortu ? trim($row->no_telp_ortu) : '-', 
            $row->alamat_rumah ?? '-',
            $row->riwayat_penyakit ?? '-',
            $row->obat_rutin ?? '-',
            $row->riwayat_cedera ?? '-',
            $row->alergi_makanan ?? '-',
            $row->keterangan_tambahan ?? '-',
            $hyperlink,
        ];
    }

    public function headings(): array
    {
        return [
            'No', 'Nama Peserta', 'NIM', 'Kelompok', 'Nomor HP Peserta', 
            'No Telp Orang Tua/Wali', 'Alamat Rumah', 'Riwayat Alergi & Penyakit', 
            'Konsumsi Obat-Obatan', 'Riwayat Cedera Medis', 'Alergi Makanan', 
            'Informasi Tambahan P3K', 'Link Berkas Bukti Medis'
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6, 'B' => 24, 'C' => 18, 'D' => 11, 'E' => 20, 
            'F' => 23, 'G' => 35, 'H' => 28, 'I' => 24, 'J' => 24, 
            'K' => 22, 'L' => 25, 'M' => 18,
        ];
    }

    public function title(): string
    {
        return 'Kelompok ' . $this->kelompok;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '002f45']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                
                $sheet->getRowDimension(1)->setRowHeight(30);

                // Mengatur semua baris data menjadi RATA ATAS (Top Align) & RATA KIRI (Left Align) secara default
                $sheet->getStyle("A2:M{$highestRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'bdd1d3'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_TOP,       // Rata Atas (Top Align)
                        'horizontal' => Alignment::HORIZONTAL_LEFT,  // Rata Kiri (Left Align)
                        'wrapText' => true
                    ],
                ]);

                // Khusus kolom data pendek (No, NIM, Kelompok, No HP, Link Bukti) dibuat RATA TENGAH-ATAS agar proporsional
                $sheet->getStyle("A2:A{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C2:F{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("M2:M{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Style pewarnaan khusus Link Berkas di Kolom M
                $sheet->getStyle("M2:M{$highestRow}")->applyFromArray([
                    'font' => ['color' => ['rgb' => '0000FF'], 'underline' => true]
                ]);
            },
        ];
    }
}