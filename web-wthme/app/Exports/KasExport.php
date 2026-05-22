<?php

namespace App\Exports;

use App\Models\KasTransaksi;
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

class KasExport implements WithMultipleSheets
{
    public function __construct(private array $filters = []) {}

    public function sheets(): array
    {
        $sheets = [];
        
        // 1. Sheet Ringkasan Utama
        $sheets[] = new KasRingkasanSheet();

        // 2. Sheet Uang Masuk
        $sheets[] = new KasTransaksiSpesifikSheet('masuk', 'Uang Masuk');

        // 3. Sheet Uang Keluar (Semua Divisi)
        $sheets[] = new KasTransaksiSpesifikSheet('keluar', 'Semua Uang Keluar');

        // 4. Sheet Dinamis Per Divisi (Hanya untuk pengeluaran)
        $divisiList = KasTransaksi::whereNotNull('divisi')->distinct()->pluck('divisi');
        foreach ($divisiList as $namaDivisi) {
            $sheets[] = new KasTransaksiSpesifikSheet('keluar', "Divisi $namaDivisi", $namaDivisi);
        }

        return $sheets;
    }
}

/**
 * Sheet untuk Transaksi Spesifik (Masuk/Keluar/Per Divisi)
 */
class KasTransaksiSpesifikSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(
        private string $jenis, 
        private string $title, 
        private ?string $divisi = null
    ) {}

    public function collection()
    {
        $query = KasTransaksi::with('pencatat')
            ->where('jenis', $this->jenis)
            ->orderBy('tanggal', 'asc');

        if ($this->divisi) {
            $query->where('divisi', $this->divisi);
        }

        return $query->get()->map(fn($t, $i) => [
            $i + 1,
            $t->tanggal->format('d/m/Y'),
            $t->nominal,
            $t->divisi ?? '-',
            $t->pic,
            $t->keterangan,
            $t->pencatat->name ?? '-',
        ]);
    }

    public function headings(): array
    {
        return ['No', 'Tanggal', 'Nominal (Rp)', 'Divisi', 'PIC', 'Keterangan', 'Admin'];
    }

    public function title(): string { return $this->title; }

    public function styles(Worksheet $sheet)
    {
        // Styling Header
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002f45']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Format angka untuk kolom Nominal (Kolom C)
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("C2:C$lastRow")->getNumberFormat()->setFormatCode('#,##0');

        return [];
    }
}

/**
 * Sheet Ringkasan (Dashboard)
 */
class KasRingkasanSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function collection()
    {
        $totalMasuk = KasTransaksi::where('jenis', 'masuk')->sum('nominal');
        $totalKeluar = KasTransaksi::where('jenis', 'keluar')->sum('nominal');

        $data = collect([
            ['RINGKASAN SALDO', '', ''],
            ['Total Pemasukan', $totalMasuk, ''],
            ['Total Pengeluaran', $totalKeluar, ''],
            ['Saldo Akhir', $totalMasuk - $totalKeluar, ''],
            ['', '', ''],
            ['PENGELUARAN PER DIVISI', 'Jumlah Transaksi', 'Total Nominal']
        ]);

        $perDivisi = KasTransaksi::where('jenis', 'keluar')
            ->selectRaw('divisi, COUNT(*) as qty, SUM(nominal) as total')
            ->groupBy('divisi')
            ->get();

        foreach ($perDivisi as $row) {
            $data->push([$row->divisi, $row->qty, $row->total]);
        }

        return $data;
    }

    public function headings(): array { return []; }
    public function title(): string { return 'Ringkasan Laporan'; }

    public function styles(Worksheet $sheet)
    {
        // Styling Judul Section
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A6')->getFont()->setBold(true)->setSize(14);

        // Styling Border Table Ringkasan
        $sheet->getStyle('A2:B4')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        
        // Format Currency untuk nominal
        $sheet->getStyle('B2:B4')->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('C7:C20')->getNumberFormat()->setFormatCode('#,##0');

        return [];
    }
}