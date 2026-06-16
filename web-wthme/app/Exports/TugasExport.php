<?php

namespace App\Exports;

use App\Models\TugasKategori;
use App\Models\TugasPengumpulan;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class TugasExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        $sheets = [];
        
        // 1. Sheet Ringkasan di depan
        $sheets[] = new TugasRingkasanSheet();

        // 2. Sheet per kelompok (Diurutkan secara numerik/angka)
        $kelompoks = User::where('role', 'peserta')
            ->whereNotNull('kelompok')
            ->distinct()
            ->orderByRaw('CAST(kelompok AS UNSIGNED) ASC') // Mengurutkan nomor kelompok (1, 2, 3... dst)
            ->pluck('kelompok');

        foreach ($kelompoks as $kelompok) {
            $sheets[] = new TugasPerKelompokSheet($kelompok);
        }

        return $sheets;
    }
}

class TugasPerKelompokSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    private $tugasList;

    public function __construct(private $kelompok) {
        $this->tugasList = TugasKategori::orderBy('urutan')->orderBy('created_at')->get();
    }

    public function collection()
    {
        $peserta = User::where('role', 'peserta')
            ->where('kelompok', $this->kelompok)
            ->orderBy('name')
            ->get();

        $pengumpulanMap = TugasPengumpulan::whereIn('user_id', $peserta->pluck('id'))
            ->get()
            ->groupBy('user_id')
            ->map(fn($items) => $items->keyBy('tugas_kategori_id'));

        return $peserta->map(function ($p, $i) use ($pengumpulanMap) {
            $row = [
                $i + 1,
                $p->name,
                $p->nim,
                'Kelompok ' . $p->kelompok,
            ];

            foreach ($this->tugasList as $tugas) {
                $kumpul = $pengumpulanMap[$p->id][$tugas->id] ?? null;
                if ($kumpul) {
                    $row[] = strtoupper(str_replace('_', ' ', $kumpul->status)); 
                } else {
                    $row[] = 'BELUM';
                }
            }

            return $row;
        });
    }

    public function headings(): array
    {
        $titles = $this->tugasList->pluck('nama_tugas')->toArray();
        return array_merge(['No', 'Nama Peserta', 'NIM', 'Unit/Kelompok'], $titles);
    }

    public function title(): string { return 'Kel. ' . $this->kelompok; }

    public function styles(Worksheet $sheet)
    {
        $lastCol = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();

        // Header Styling
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002f45']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Tambahkan Hyperlink ke Bukti File & Warna Status
        $peserta = User::where('role', 'peserta')->where('kelompok', $this->kelompok)->orderBy('name')->get();
        $pengumpulanMap = TugasPengumpulan::whereIn('user_id', $peserta->pluck('id'))
            ->get()->groupBy('user_id')->map(fn($items) => $items->keyBy('tugas_kategori_id'));

        $currentRow = 2;
        foreach ($peserta as $p) {
            $currentCol = 'E'; // Kolom tugas pertama dimulai dari E
            foreach ($this->tugasList as $tugas) {
                $kumpul = $pengumpulanMap[$p->id][$tugas->id] ?? null;
                $cellCoord = $currentCol . $currentRow;
                
                if ($kumpul) {
                    // Fix: Sesuaikan string status 'tepat_waktu' dengan database Anda
                    if ($kumpul->status === 'tepat_waktu') {
                        $sheet->getStyle($cellCoord)->getFont()->getColor()->setRGB('166534');
                    } else {
                        $sheet->getStyle($cellCoord)->getFont()->getColor()->setRGB('991b1b');
                    }

                    // Tambahkan LINK jika ada file (Mendukung format JSON array berkas)
                    if ($kumpul->file_path) {
                        $files = json_decode($kumpul->file_path, true);
                        $pathUntukLink = null;

                        if (is_array($files) && isset($files[0]['path'])) {
                            $pathUntukLink = $files[0]['path']; // Ambil file pertama jika ada banyak file
                        } elseif (!is_array($files)) {
                            $pathUntukLink = $kumpul->file_path; // Jika format lama berupa string path biasa
                        }

                        if ($pathUntukLink) {
                            $url = url(Storage::url($pathUntukLink));
                            $sheet->getCell($cellCoord)->getHyperlink()->setUrl($url);
                            $sheet->getStyle($cellCoord)->getFont()->setUnderline(true);
                        }
                    }
                } else {
                    $sheet->getStyle($cellCoord)->getFont()->getColor()->setRGB('94a3b8'); // Abu-abu untuk BELUM
                }
                $currentCol++;
            }
            $currentRow++;
        }

        return [
            "A1:{$lastCol}{$lastRow}" => [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BDD1D3']]],
            ],
        ];
    }
}

class TugasRingkasanSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function collection()
    {
        $tugasList    = TugasKategori::orderBy('urutan')->get();
        $totalPeserta = User::where('role', 'peserta')->count();

        return $tugasList->map(function ($tugas, $i) use ($totalPeserta) {
            $sudah     = TugasPengumpulan::where('tugas_kategori_id', $tugas->id)->count();
            $terlambat = TugasPengumpulan::where('tugas_kategori_id', $tugas->id)
                ->where('status', 'terlambat')->count();
            $belum      = $totalPeserta - $sudah;
            $pct        = $totalPeserta > 0 ? round(($sudah / $totalPeserta) * 100, 1) : 0;

            return [
                $i + 1,
                $tugas->nama_tugas,
                $tugas->deadline ? date('d/m/Y H:i', strtotime($tugas->deadline)) : '-',
                $tugas->aktif ? 'AKTIF' : 'NONAKTIF',
                $sudah,
                $sudah - $terlambat,
                $terlambat,
                $belum,
                $pct . '%',
            ];
        });
    }

    public function headings(): array
    {
        return ['No', 'Nama Tugas', 'Deadline', 'Status', 'Total Masuk', 'Tepat Waktu', 'Terlambat', 'Belum Kumpul', '% Progress'];
    }

    public function title(): string { return 'Ringkasan Progress'; }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002f45']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $lastRow = $sheet->getHighestRow();
        for ($i = 2; $i <= $lastRow; $i++) {
            $val = (float) str_replace('%', '', $sheet->getCell("I$i")->getValue());
            if ($val == 100) {
                $sheet->getStyle("I$i")->getFont()->getColor()->setRGB('166534');
                $sheet->getStyle("I$i")->getFont()->setBold(true);
            }
        }

        return [
            "A1:I{$lastRow}" => [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BDD1D3']]],
            ],
        ];
    }
}