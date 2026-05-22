<?php

namespace App\Exports;

use App\Models\AbsensiPeserta;
use App\Models\QrSession;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AbsensiPesertaExport implements WithMultipleSheets
{
    protected $sessionId;

    public function __construct($sessionId = null)
    {
        $this->sessionId = $sessionId;
    }

    public function sheets(): array
    {
        $sheets = [];
        
        if ($this->sessionId) {
            $kelompoks = AbsensiPeserta::where('qr_session_id', $this->sessionId)
                ->distinct()->pluck('kelompok')->sort();
            
            foreach ($kelompoks as $kelompok) {
                $sheets[] = new AbsensiPerKelompokSheet($kelompok, $this->sessionId);
            }
            $sheets[] = new AbsensiRingkasanSheet($this->sessionId);
        } else {
            $sesis = QrSession::where('untuk', 'peserta')->orderBy('created_at', 'asc')->get();
            foreach ($sesis as $sesi) {
                $sheets[] = new AbsensiPerSesiSheet($sesi);
            }
            $sheets[] = new AbsensiRingkasanSheet(null);
        }
        
        return $sheets;
    }
}

/**
 * Trait Helper untuk Styling Tabel agar kode tidak berulang
 */
trait ExportStylingHelper {
    public function applyCommonStyles(Worksheet $sheet) {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $range = 'A1:' . $highestColumn . $highestRow;

        // 1. Header Style
        $sheet->getStyle('A1:' . $highestColumn . '1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '002f45'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // 2. Garis Tabel (Borders)
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // 3. Center Alignment untuk Kolom Tertentu (No, NIM, Kelompok, Status)
        $sheet->getStyle('A2:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C2:E' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F2:F' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Tinggi baris header
        $sheet->getRowDimension(1)->setRowHeight(25);
    }
}

class AbsensiPerKelompokSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    use ExportStylingHelper;
    private $kelompok;
    private $sessionId;
    
    public function __construct($kelompok, $sessionId) {
        $this->kelompok = $kelompok;
        $this->sessionId = $sessionId;
    }
    
    public function collection() {
        return AbsensiPeserta::where('qr_session_id', $this->sessionId)
            ->where('kelompok', $this->kelompok)->orderBy('nama')->get()
            ->map(fn($row, $index) => [
                $index + 1, $row->nama, $row->nim, $row->angkatan, $row->kelompok, 
                strtoupper($row->status), \Carbon\Carbon::parse($row->waktu_absen)->format('H:i:s'), $row->qrSession->nama_sesi ?? '-'
            ]);
    }
    
    public function headings(): array { return ['NO', 'NAMA LENGKAP', 'NIM', 'ANGKATAN', 'KELOMPOK', 'STATUS', 'JAM ABSEN', 'SESI']; }
    public function title(): string { return 'Kelompok ' . ($this->kelompok ?? 'N-A'); }
    public function styles(Worksheet $sheet) { $this->applyCommonStyles($sheet); }
}

class AbsensiPerSesiSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    use ExportStylingHelper;
    private $sesi;
    
    public function __construct($sesi) { $this->sesi = $sesi; }
    
    public function collection() {
        return AbsensiPeserta::where('qr_session_id', $this->sesi->id)
            ->orderBy('kelompok')->orderBy('nama')->get()
            ->map(fn($row, $index) => [
                $index + 1, $row->nama, $row->nim, $row->angkatan, $row->kelompok, 
                strtoupper($row->status), \Carbon\Carbon::parse($row->waktu_absen)->format('d/m/Y H:i')
            ]);
    }
    
    public function headings(): array { return ['NO', 'NAMA LENGKAP', 'NIM', 'ANGKATAN', 'KELOMPOK', 'STATUS', 'WAKTU PRESENSI']; }
    public function title(): string { return substr($this->sesi->nama_sesi, 0, 30); }
    public function styles(Worksheet $sheet) { $this->applyCommonStyles($sheet); }
}

class AbsensiRingkasanSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    use ExportStylingHelper;
    private $sessionId;

    public function __construct($sessionId = null) { $this->sessionId = $sessionId; }

    public function collection() {
        $query = AbsensiPeserta::selectRaw('kelompok, COUNT(*) as total_hadir')->where('status', 'hadir');
        if ($this->sessionId) $query->where('qr_session_id', $this->sessionId);

        return $query->groupBy('kelompok')->orderBy('kelompok')->get()
            ->map(fn($row) => [$row->kelompok, $row->total_hadir . ' Orang']);
    }
    
    public function headings(): array { return ['NOMOR KELOMPOK', 'TOTAL KEHADIRAN']; }
    public function title(): string { return $this->sessionId ? 'Ringkasan' : 'Global Rekap'; }
    public function styles(Worksheet $sheet) {
        $this->applyCommonStyles($sheet);
        // Custom: buat kolom Ringkasan jadi Center semua
        $sheet->getStyle('A1:B'.$sheet->getHighestRow())->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
}