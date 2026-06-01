<?php

namespace App\Exports;

use App\Models\AbsensiPeserta;
use App\Models\QrSession;
use App\Models\User;
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
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class AbsensiPesertaExport implements WithMultipleSheets
{
    public function __construct($sessionId = null)
    {
        // Diabaikan karena kita memaksakan rekap Global Matriks Keseluruhan Sesi
    }

    public function sheets(): array
    {
        return [
            new AbsensiMatrixGrupKelompokSheet()
        ];
    }
}

class AbsensiMatrixGrupKelompokSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize, WithEvents
{
    private $listSesi;
    private $jumlahSesi = 0;
    private $barisPembatas = [];  // Menyimpan indeks baris judul kelompok (misal: KELOMPOK 1)
    private $barisData = [];      // Menyimpan indeks baris data peserta (untuk dropdown & border)

    public function __construct()
    {
        // Ambil semua sesi peserta diurutkan dari yang paling awal
        $this->listSesi = QrSession::where('untuk', 'peserta')->orderBy('created_at', 'asc')->get();
        $this->jumlahSesi = $this->listSesi->count();
    }

    public function collection()
    {
        $output = collect();

        // 1. Ambil semua kelompok unik dari master user
        $listKelompok = User::whereNotNull('kelompok')->distinct()->pluck('kelompok')->sort();

        // 2. Ambil seluruh log absensi, dikelompokkan berdasarkan user_id dan qr_session_id
        $logAbsensi = AbsensiPeserta::get()->groupBy(['user_id', 'qr_session_id']);

        // Baris data pertama dimulai pada Baris 3 (karena baris 1 & 2 dipakai untuk Header Sesi & Header Kolom)
        $currentLine = 3; 

        foreach ($listKelompok as $kelompok) {
            
            // A. Sisipkan Baris Sekat Pembatas Kelompok (Sama seperti gaya sekat Divisi di filemu)
            // Buat array kosong sepanjang jumlah kolom yang ada agar tidak merusak baris
            $sekatData = ['KELOMPOK ' . $kelompok];
            for ($i = 0; $i < (4 + $this->jumlahSesi); $i++) {
                $sekatData[] = '';
            }
            $output->push($sekatData);
            $this->barisPembatas[] = $currentLine;
            $currentLine++;

            // B. Ambil Peserta di Kelompok Terkait
            $masterPeserta = User::where('kelompok', $kelompok)->orderBy('name', 'asc')->get();
            
            foreach ($masterPeserta as $index => $user) {
                // Kolom identitas dasar peserta
                $rowData = [
                    $index + 1, // Reset nomor urut dari 1 di setiap kelompok baru
                    $user->name,
                    $user->nim,
                    $user->angkatan,
                    'Kelompok ' . $user->kelompok
                ];

                // Kolom dinamis ke kanan: Isi status kehadiran di tiap sesi
                foreach ($this->listSesi as $sesi) {
                    $log = $logAbsensi->get($user->id)?->get($sesi->id)?->first();

                    $status = 'TANPA KETERANGAN';
                    if ($log) {
                        if ($log->status === 'hadir') {
                            $status = $log->waktu_absen ? 'HADIR' : 'IZIN';
                        } else {
                            $status = 'TANPA KETERANGAN';
                        }
                    }
                    $rowData[] = $status;
                }

                $output->push($rowData);
                $this->barisData[] = $currentLine;
                $currentLine++;
            }

            // C. Beri jarak 1 baris kosong antar kelompok sebelum masuk kelompok berikutnya
            $jarakData = [];
            for ($i = 0; $i < (5 + $this->jumlahSesi); $i++) {
                $jarakData[] = '';
            }
            $output->push($jarakData);
            $currentLine++;
        }

        return $output;
    }

    public function headings(): array
    {
        // Membuat struktur header dua baris. 
        // Baris pertama diisi judul utama, Baris kedua diisi sub-judul kolom.
        $rowHeader = ['NO', 'NAMA LENGKAP', 'NIM', 'ANGKATAN', 'KELOMPOK'];
        foreach ($this->listSesi as $sesi) {
            $rowHeader[] = strtoupper($sesi->nama_sesi);
        }

        return [
            ['REKAPITULASI KEHADIRAN PESERETA GLOBAL'], // Baris 1: Judul lembar
            $rowHeader                                  // Baris 2: Judul kolom matriks
        ];
    }

    public function title(): string
    {
        return 'REKAP GLOBAL MATRIKS';
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // Merge Judul Utama di Baris 1
        $sheet->mergeCells('A1:' . $highestColumn . '1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '002f45']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Styling Baris 2 (Header Kolom Matriks) -> Navy Gelap Premium
        $sheet->getStyle('A2:' . $highestColumn . '2')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '002f45'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
            ]
        ]);
        $sheet->getRowDimension(2)->setRowHeight(28);

        // Styling Baris Sekat Pembatas Kelompok (Gabung kolom & cetak tebal)
        foreach ($this->barisPembatas as $row) {
            $sheet->mergeCells("A{$row}:" . $highestColumn . $row);
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '000000']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
            ]);
            $sheet->getRowDimension($row)->setRowHeight(26);
        }

        // Styling Baris Data Utama (Borders tipis & Alignment Tengah)
        foreach ($this->barisData as $row) {
            $sheet->getStyle("A{$row}:" . $highestColumn . $row)->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D3D3D3']]
                ],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER]
            ]);

            // Set Center untuk data identitas (Kecuali Nama Lengkap di kolom B)
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}:E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            // Set Center untuk seluruh kolom status ke kanan (Mulai kolom F)
            $sheet->getStyle("F{$row}:" . $highestColumn . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getRowDimension($row)->setRowHeight(22);
        }
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                if (empty($this->barisData) || $this->jumlahSesi == 0) return;

                $kolomMulaiIndex = 6; // Kolom F
                $kolomAkhirIndex = 5 + $this->jumlahSesi;

                // 1. BUAT FORMATTING PILLS WARNA
                $formatHadir = new Conditional();
                $formatHadir->setConditionType(Conditional::CONDITION_CELLIS)
                    ->setOperatorType(Conditional::OPERATOR_EQUAL)
                    ->addCondition('"HADIR"');
                $formatHadir->getStyle()->getFont()->setBold(true)->getColor()->setRGB('1E4620');
                $formatHadir->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D1E7DD');

                $formatIzin = new Conditional();
                $formatIzin->setConditionType(Conditional::CONDITION_CELLIS)
                    ->setOperatorType(Conditional::OPERATOR_EQUAL)
                    ->addCondition('"IZIN"');
                $formatIzin->getStyle()->getFont()->setBold(true)->getColor()->setRGB('664D03');
                $formatIzin->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF3CD');

                $formatAlfa = new Conditional();
                $formatAlfa->setConditionType(Conditional::CONDITION_CELLIS)
                    ->setOperatorType(Conditional::OPERATOR_EQUAL)
                    ->addCondition('"TANPA KETERANGAN"');
                $formatAlfa->getStyle()->getFont()->setBold(true)->getColor()->setRGB('842029');
                $formatAlfa->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8D7DA');

                $stylesCollection = [$formatHadir, $formatIzin, $formatAlfa];

                // 2. LOOP TERTARGET: Hanya inject dropdown & warna di cell data absensi nyata
                for ($col = $kolomMulaiIndex; $col <= $kolomAkhirIndex; $col++) {
                    $colString = Coordinate::stringFromColumnIndex($col);
                    
                    foreach ($this->barisData as $row) {
                        $cellCoordinate = $colString . $row;
                        
                        // Pasang Dropdown
                        $validation = $sheet->getCell($cellCoordinate)->getDataValidation();
                        $validation->setType(DataValidation::TYPE_LIST);
                        $validation->setErrorStyle(DataValidation::STYLE_STOP);
                        $validation->setAllowBlank(false);
                        $validation->setShowDropDown(true);
                        $validation->setErrorTitle('Input Salah');
                        $validation->setError('Pilih status dari dropdown yang valid.');
                        $validation->setFormula1('"HADIR,IZIN,TANPA KETERANGAN"');

                        // Pasang Warna Pill
                        $sheet->getStyle($cellCoordinate)->setConditionalStyles($stylesCollection);
                    }
                }
            },
        ];
    }
}