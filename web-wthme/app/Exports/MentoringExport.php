<?php

namespace App\Exports;

use App\Models\Mentoring;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class MentoringExport implements FromCollection, WithStyles, ShouldAutoSize, WithTitle
{
    protected $kelompok;

    public function __construct($kelompok)
    {
        $this->kelompok = $kelompok;
    }

    public function title(): string
    {
        return 'Kelompok ' . $this->kelompok;
    }

    /**
    * Kita susun datanya secara manual agar ada jarak antar kegiatan
    */
    public function collection()
    {
        $data = collect();
        
        // Ambil semua mentoring untuk kelompok ini
        $mentorings = Mentoring::where('kelompok', $this->kelompok)
            ->with('details.peserta')
            ->orderBy('tanggal', 'asc')
            ->get();

        foreach ($mentorings as $m) {
            // Tambahkan Header Kegiatan
            $data->push([
                'KEGIATAN: ' . strtoupper($m->nama_kegiatan) . ' (' . date('d/m/Y', strtotime($m->tanggal)) . ')',
                '', '', '', '', '', '' // Kosongkan kolom lain untuk merger
            ]);

            // Tambahkan Header Tabel untuk kegiatan ini
            $data->push(['Nama', 'NIM', 'Gender', 'Kehadiran', 'Catatan', '', '']);

            // Tambahkan Data Peserta
            foreach ($m->details as $d) {
                $data->push([
                    $d->peserta->name ?? 'N/A',
                    $d->peserta->nim ?? '-',
                    $d->peserta->gender ?? '-',
                    strtoupper($d->kehadiran),
                    $d->keterangan ?? '-',
                    '', ''
                ]);
            }

            // Tambahkan 2 Baris Kosong sebagai Jarak/Pemisah antar kegiatan
            $data->push(['', '', '', '', '', '', '']);
            $data->push(['', '', '', '', '', '', '']);
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('A1:E' . $highestRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        for ($i = 1; $i <= $highestRow; $i++) {
            $rowValue = $sheet->getCell('A' . $i)->getValue();
            $kehadiranValue = $sheet->getCell('D' . $i)->getValue();

            // 1. STYLE HEADER KEGIATAN (Yang ada teks "KEGIATAN:")
            if (str_contains($rowValue, 'KEGIATAN:')) {
                $sheet->mergeCells("A{$i}:E{$i}");
                $sheet->getStyle("A{$i}:E{$i}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'D2C296']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002F45']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
            }

            // 2. STYLE HEADER TABEL (Nama, NIM, dll)
            if ($rowValue == 'Nama') {
                $sheet->getStyle("A{$i}:E{$i}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0DECD']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                ]);
            }

            // 3. WARNA OTOMATIS BERDASARKAN STATUS (IZIN/ALPHA)
            if (in_array($kehadiranValue, ['HADIR', 'IZIN', 'ALPHA'])) {
                // Beri border untuk baris data
                $sheet->getStyle("A{$i}:E{$i}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                
                // Cek Status Kehadiran untuk warna sel
                if ($kehadiranValue == 'IZIN') {
                    $sheet->getStyle('D' . $i)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00'); // Kuning
                } elseif ($kehadiranValue == 'ALPHA') {
                    $sheet->getStyle('D' . $i)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FF0000'); // Merah
                    $sheet->getStyle('D' . $i)->getFont()->getColor()->setRGB('FFFFFF'); // Teks Putih agar kontras
                }
            }
        }
    }
}