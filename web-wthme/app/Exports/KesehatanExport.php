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
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Conditional; // IMPORT UNTUK CONDITIONAL FORMATTING EXCEL

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
    private $rawCollection;

    public function __construct($kelompok)
    {
        $this->kelompok = $kelompok;
    }

    public function collection()
    {
        $this->rawCollection = RiwayatPenyakit::where('kelompok', $this->kelompok)
            ->orderBy('nama')
            ->get();

        return $this->rawCollection;
    }

    public function bindValue(Cell $cell, $value)
    {
        $column = $cell->getColumn();
        if (in_array($column, ['D', 'F', 'G']) && $cell->getRow() > 1) {
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

        // Teks asli muncul di cell Excel: 'Merah', 'Kuning', atau 'Normal'
        $statusPita = $row->warna_pita ? $row->warna_pita : 'Normal';

        return [
            $this->rowNumber,
            $row->nama,
            $statusPita, // Kolom C (Isi teks terlihat nyata)
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
            'No', 'Nama Peserta', 'Pita Kesehatan', 'NIM', 'Kelompok', 'Nomor HP Peserta', 
            'No Telp Orang Tua/Wali', 'Alamat Rumah', 'Riwayat Alergi & Penyakit', 
            'Konsumsi Obat-Obatan', 'Riwayat Cedera Medis', 'Alergi Makanan', 
            'Informasi Tambahan P3K', 'Link Berkas Bukti Medis'
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,  'B' => 24, 'C' => 16,  'D' => 18, 'E' => 11, 
            'F' => 20, 'G' => 23, 'H' => 35, 'I' => 28, 'J' => 24, 
            'K' => 24, 'L' => 22, 'M' => 25, 'N' => 18,
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

                // Styling grid tabel dasar
                $sheet->getStyle("A2:N{$highestRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'bdd1d3'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_TOP,
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'wrapText' => true
                    ],
                ]);

                // 1. SETTING DROPDOWN VALIDATION DI KOLOM C
                for ($row = 2; $row <= $highestRow; $row++) {
                    $cellCoordinate = "C{$row}";

                    $validation = $sheet->getCell($cellCoordinate)->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_STOP);
                    $validation->setAllowBlank(false);
                    $validation->setShowInputMessage(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setShowDropDown(true);
                    $validation->setErrorTitle('Input Tidak Valid');
                    $validation->setError('Pilih opsi yang ada: Normal, Kuning, atau Merah');
                    
                    // Opsi Dropdown di Excel
                    $validation->setFormula1('"Normal,Kuning,Merah"');

                    // Set layout teks bawaan agar rapi di tengah
                    $sheet->getStyle($cellCoordinate)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle($cellCoordinate)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // 2. ATUR CONDITIONAL FORMATTING (WARNA OTOMATIS BERUBAH DI EXCEL)
                $conditionalStyles = [];

                // Aturan jika teks di cell berbunyi "Merah"
                $styleMerah = new Conditional();
                $styleMerah->setConditionType(Conditional::CONDITION_CELLIS);
                $styleMerah->setOperatorType(Conditional::OPERATOR_EQUAL);
                $styleMerah->addCondition('"Merah"');
                $styleMerah->getStyle()->getFont()->getColor()->setRGB('9B1C1C'); // Teks Merah Gelap
                $styleMerah->getStyle()->getFont()->setBold(true);
                $styleMerah->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFCCD5'); // Background Merah Muda
                $conditionalStyles[] = $styleMerah;

                // Aturan jika teks di cell berbunyi "Kuning"
                $styleKuning = new Conditional();
                $styleKuning->setConditionType(Conditional::CONDITION_CELLIS);
                $styleKuning->setOperatorType(Conditional::OPERATOR_EQUAL);
                $styleKuning->addCondition('"Kuning"');
                $styleKuning->getStyle()->getFont()->getColor()->setRGB('92400E'); // Teks Coklat/Kuning Tua
                $styleKuning->getStyle()->getFont()->setBold(true);
                $styleKuning->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEF3C7'); // Background Kuning Soft
                $conditionalStyles[] = $styleKuning;

                // Aturan jika teks di cell berbunyi "Normal"
                $styleNormal = new Conditional();
                $styleNormal->setConditionType(Conditional::CONDITION_CELLIS);
                $styleNormal->setOperatorType(Conditional::OPERATOR_EQUAL);
                $styleNormal->addCondition('"Normal"');
                $styleNormal->getStyle()->getFont()->getColor()->setRGB('1E293B'); // Teks Abu Gelap
                $styleNormal->getStyle()->getFont()->setBold(true);
                $styleNormal->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E2E8F0'); // Background Abu Terang
                $conditionalStyles[] = $styleNormal;

                // Terapkan seluruh aturan conditional formatting ini ke rentang Kolom C (dari baris 2 sampai baris terakhir)
                $sheet->getStyle("C2:C{$highestRow}")->setConditionalStyles($conditionalStyles);

                // Perataan kolom data pendek lainnya
                $sheet->getStyle("A2:A{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D2:G{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("N2:N{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Style Link Berkas
                $sheet->getStyle("N2:N{$highestRow}")->applyFromArray([
                    'font' => ['color' => ['rgb' => '0000FF'], 'underline' => true]
                ]);
            },
        ];
    }
}