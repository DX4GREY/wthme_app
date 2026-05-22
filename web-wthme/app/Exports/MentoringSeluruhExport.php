<?php

namespace App\Exports;

use App\Models\MentoringDetail;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

// --- CLASS UTAMA UNTUK MULTI-SHEET ---
class MentoringSeluruhExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        $sheets = [];
        // Ambil daftar nomor kelompok yang ada di database
        $listKelompok = MentoringDetail::join('users', 'mentoring_details.peserta_id', '=', 'users.id')
            ->distinct()
            ->orderBy('users.kelompok', 'asc')
            ->pluck('users.kelompok');

        foreach ($listKelompok as $kelompok) {
            // Kita panggil class internal untuk tiap tab/worksheet
            $sheets[] = new MentoringSheetPerKelompok($kelompok);
        }

        return $sheets;
    }
}

// --- CLASS INTERNAL UNTUK ISI PER WORKSHEET ---
class MentoringSheetPerKelompok implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize
{
    protected $kelompok;

    public function __construct($kelompok)
    {
        $this->kelompok = $kelompok;
    }

    // Nama Tab/Worksheet di bawah Excel
    public function title(): string
    {
        return 'Kelompok ' . $this->kelompok;
    }

    public function collection()
    {
        return MentoringDetail::with(['mentoring', 'peserta'])
            ->whereHas('peserta', function($q) {
                $q->where('kelompok', $this->kelompok);
            })
            ->join('mentorings', 'mentoring_details.mentoring_id', '=', 'mentorings.id')
            ->orderBy('mentorings.nama_kegiatan', 'asc') // Klasifikasi per kegiatan
            ->get();
    }

    public function headings(): array
    {
        return [
            ['DATA MENTORING KELOMPOK ' . $this->kelompok],
            [''], 
            ['Kegiatan', 'Tanggal', 'Nama Peserta', 'NIM', 'Status', 'Catatan']
        ];
    }

    public function map($row): array
    {
        return [
            $row->mentoring->nama_kegiatan,
            date('d-m-Y', strtotime($row->mentoring->tanggal)),
            $row->peserta->name,
            $row->peserta->nim,
            strtoupper($row->kehadiran),
            $row->keterangan ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style Header Biru (Baris 3)
        $sheet->getStyle('A3:F3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002F45']],
            'alignment' => ['horizontal' => 'center']
        ]);

        // Pewarnaan Baris Data
        $highestRow = $sheet->getHighestRow();
        for ($i = 4; $i <= $highestRow; $i++) {
            $statusCell = 'E' . $i;
            $status = $sheet->getCell($statusCell)->getValue();

            // Atur warna berdasarkan status
            if ($status == 'HADIR') {
                $sheet->getStyle($statusCell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DCFCE7'); // Hijau
                $sheet->getStyle($statusCell)->getFont()->getColor()->setRGB('166534');
            } elseif ($status == 'IZIN') {
                $sheet->getStyle($statusCell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF7ED'); // Oranye
                $sheet->getStyle($statusCell)->getFont()->getColor()->setRGB('9A3412');
            } else { // ALPHA
                $sheet->getStyle($statusCell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEE2E2'); // Merah
                $sheet->getStyle($statusCell)->getFont()->getColor()->setRGB('991B1B');
            }

            // Garis Border
            $sheet->getStyle("A$i:F$i")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }
    }
}