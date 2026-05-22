<?php

namespace App\Http\Controllers;

use App\Models\BarangKebutuhan;
use App\Models\PengumpulanBarang;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing; // <-- Import Drawing untuk handle foto di Excel

class BarangController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // PANITIA: Manage daftar barang (hanya divisi Logistik)
    // ─────────────────────────────────────────────────────────────

    public function manageIndex()
    {
        $this->authorizeLogistik();
        $barangs = BarangKebutuhan::orderBy('nama_barang')->get();
        return view('panitia.barang.manage', compact('barangs'));
    }

    public function manageStore(Request $request)
    {
        $this->authorizeLogistik();
        $request->validate([
            'nama_barang'       => 'required|string|max:255',
            'jumlah_kebutuhan'  => 'required|integer|min:1',
            'satuan'            => 'required|string|max:50',
            'keterangan'        => 'nullable|string|max:500',
        ]);

        BarangKebutuhan::create($request->only('nama_barang', 'jumlah_kebutuhan', 'satuan', 'keterangan'));

        return back()->with('success', 'Barang berhasil ditambahkan.');
    }

    public function manageUpdate(Request $request, $id)
    {
        $this->authorizeLogistik();
        $request->validate([
            'nama_barang'       => 'required|string|max:255',
            'jumlah_kebutuhan'  => 'required|integer|min:1',
            'satuan'            => 'required|string|max:50',
            'keterangan'        => 'nullable|string|max:500',
        ]);

        $barang = BarangKebutuhan::findOrFail($id);
        $barang->update($request->only('nama_barang', 'jumlah_kebutuhan', 'satuan', 'keterangan'));

        return back()->with('success', 'Barang berhasil diupdate.');
    }

    public function manageDestroy($id)
    {
        $this->authorizeLogistik();
        $barang = BarangKebutuhan::findOrFail($id);

        // Hapus semua foto bukti terkait
        foreach ($barang->pengumpulan as $p) {
            if ($p->foto_bukti) {
                Storage::disk('public')->delete($p->foto_bukti);
            }
        }

        $barang->delete();
        return back()->with('success', 'Barang berhasil dihapus.');
    }

    // ─────────────────────────────────────────────────────────────
    // PANITIA: Lihat list kelompok & rekap
    // ─────────────────────────────────────────────────────────────

    public function panitiaIndex()
    {
        // Hitung jumlah kelompok dari users peserta
        $kelompoks = User::where('role', 'peserta')
            ->whereNotNull('kelompok')
            ->distinct()
            ->orderBy('kelompok')
            ->pluck('kelompok');

        $barangs = BarangKebutuhan::where('aktif', true)->get();

        // Summary per kelompok: berapa barang sudah lengkap
        $summary = [];
        foreach ($kelompoks as $k) {
            $total   = $barangs->count();
            $lengkap = 0;
            foreach ($barangs as $b) {
                $p = PengumpulanBarang::where('barang_kebutuhan_id', $b->id)
                    ->where('kelompok', $k)->first();
                if ($p && $p->jumlah_terkumpul >= $b->jumlah_kebutuhan) {
                    $lengkap++;
                }
            }
            $summary[$k] = ['total' => $total, 'lengkap' => $lengkap];
        }

        return view('panitia.barang.index', compact('kelompoks', 'summary', 'barangs'));
    }

    public function panitiaKelompok($kelompok)
    {
        $barangs = BarangKebutuhan::where('aktif', true)->orderBy('nama_barang')->get();

        $data = $barangs->map(function ($b) use ($kelompok) {
            $p = PengumpulanBarang::where('barang_kebutuhan_id', $b->id)
                ->where('kelompok', $kelompok)
                ->with('updatedBy')
                ->first();

            return [
                'barang'           => $b,
                'pengumpulan'      => $p,
                'jumlah_terkumpul' => $p ? $p->jumlah_terkumpul : 0,
                'foto'             => $p && $p->foto_bukti ? Storage::url($p->foto_bukti) : null,
                'foto_url'         => $p && $p->foto_bukti ? Storage::url($p->foto_bukti) : null,
                'is_lengkap'       => $p && $p->jumlah_terkumpul >= $b->jumlah_kebutuhan,
                'updated_at'       => $p ? $p->updated_at : null,
                'updated_by_name'  => ($p && $p->updatedBy) ? $p->updatedBy->name : null,
            ];
         });

        return view('panitia.barang.kelompok', compact('kelompok', 'data'));
    }

    public function panitiaRekap()
    {
        $kelompoks = User::where('role', 'peserta')
            ->whereNotNull('kelompok')
            ->distinct()
            ->orderBy('kelompok')
            ->pluck('kelompok');

        $barangs = BarangKebutuhan::where('aktif', true)->orderBy('nama_barang')->get();

        $rekap = [];
        foreach ($kelompoks as $k) {
            $rows = [];
            foreach ($barangs as $b) {
                $p = PengumpulanBarang::where('barang_kebutuhan_id', $b->id)
                    ->where('kelompok', $k)->first();
                $rows[] = [
                    'barang'          => $b,
                    'jumlah_terkumpul' => $p ? $p->jumlah_terkumpul : 0,
                    'foto'            => $p && $p->foto_bukti ? Storage::url($p->foto_bukti) : null,
                    'is_lengkap'      => $p && $p->jumlah_terkumpul >= $b->jumlah_kebutuhan,
                    'updated_at'      => $p ? $p->updated_at : null,
                ];
            }
            $rekap[$k] = $rows;
        }

        return view('panitia.barang.rekap', compact('kelompoks', 'barangs', 'rekap'));
    }

    public function exportRekap()
    {
        // Naikkan memory limit untuk memproses gambar di spreadsheet jika datanya banyak
        ini_set('memory_limit', '256M');

        $kelompoks = User::where('role', 'peserta')
            ->whereNotNull('kelompok')
            ->distinct()
            ->orderBy('kelompok')
            ->pluck('kelompok');

        $barangs = BarangKebutuhan::where('aktif', true)->orderBy('nama_barang')->get();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        // Warna tema
        $navyHex  = '002f45';
        $tealHex  = 'bdd1d3';
        $sandHex  = 'd2c296';
        $creamHex = 'e0decd';
        $greenHex = 'd4edda';
        $redHex   = 'f8d7da';
        $whiteHex = 'FFFFFF';

        foreach ($kelompoks as $kelompok) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle("Kelompok $kelompok");

            // Header judul (Diperluas sampai G karena tambah kolom foto)
            $sheet->mergeCells('A1:G1');
            $sheet->setCellValue('A1', "REKAP PENGUMPULAN BARANG - KELOMPOK $kelompok");
            $sheet->getStyle('A1')->applyFromArray([
                'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => $whiteHex], 'name' => 'Arial'],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $navyHex]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getRowDimension(1)->setRowHeight(30);

            // Header kolom (Tambah item 'Foto Bukti' di urutan ke-7 / Kolom G)
            $headers = ['No', 'Nama Barang', 'Kebutuhan', 'Terkumpul', 'Progress', 'Status', 'Foto Bukti'];
            foreach ($headers as $i => $h) {
                $col = chr(65 + $i);
                $sheet->setCellValue("{$col}2", $h);
                $sheet->getStyle("{$col}2")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => $navyHex], 'name' => 'Arial'],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $tealHex]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $navyHex]]],
                ]);
            }
            $sheet->getRowDimension(2)->setRowHeight(22);

            // Data
            $row = 3;
            foreach ($barangs as $idx => $b) {
                $p       = PengumpulanBarang::where('barang_kebutuhan_id', $b->id)->where('kelompok', $kelompok)->first();
                $terkumpul = $p ? $p->jumlah_terkumpul : 0;
                $lengkap = $terkumpul >= $b->jumlah_kebutuhan;
                $bgColor = $lengkap ? $greenHex : ($terkumpul > 0 ? 'fff3cd' : $redHex);

                $rowData = [
                    $idx + 1,
                    $b->nama_barang,
                    $b->jumlah_kebutuhan . ' ' . $b->satuan,
                    $terkumpul . ' ' . $b->satuan,
                    $terkumpul . '/' . $b->jumlah_kebutuhan,
                    $lengkap ? 'Lengkap ✓' : ($terkumpul > 0 ? 'Sebagian' : 'Belum'),
                    '' // Kolom G sengaja dikosongkan untuk diisi Drawing Object gambar nanti
                ];

                foreach ($rowData as $ci => $val) {
                    $col = chr(65 + $ci);
                    $sheet->setCellValue("{$col}{$row}", $val);
                    $sheet->getStyle("{$col}{$row}")->applyFromArray([
                        'font'      => [
                            'name' => 'Arial',
                            'size' => 10,
                            'color' => ['rgb' => $lengkap ? '155724' : ($terkumpul > 0 ? '856404' : '721c24')]
                        ],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                        'alignment' => [
                            'horizontal' => $ci === 1 ? Alignment::HORIZONTAL_LEFT : Alignment::HORIZONTAL_CENTER,
                            'vertical'   => Alignment::VERTICAL_CENTER // Gambar rapi tepat di tengah cell
                        ],
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'cccccc']]],
                    ]);
                }

                // ─── PROSES EKSTRAK & PASTE FOTO KE EXCEL ───
                if ($p && $p->foto_bukti) {
                    $filePath = storage_path('app/public/' . $p->foto_bukti);

                    if (file_exists($filePath)) {
                        $drawing = new Drawing();
                        $drawing->setName('Foto Bukti');
                        $drawing->setDescription('Bukti upload barang');
                        $drawing->setPath($filePath);
                        $drawing->setHeight(55); // Tinggi gambar diset 55 pixel
                        $drawing->setCoordinates("G{$row}"); // Masukkan ke cell G
                        $drawing->setOffsetX(15); // Geser kanan dikit biar center
                        $drawing->setOffsetY(5);  // Geser bawah dikit biar center
                        $drawing->setWorksheet($sheet);
                    } else {
                        $sheet->setCellValue("G{$row}", "File Rusak/Hilang");
                    }
                } else {
                    $sheet->setCellValue("G{$row}", "Tidak Ada Foto");
                }

                // Naikkan tinggi baris cell supaya muat merender preview gambar
                $sheet->getRowDimension($row)->setRowHeight(50);
                $row++;
            }

            // Summary baris
            $sheet->mergeCells("A{$row}:D{$row}");
            $sheet->setCellValue("A{$row}", 'TOTAL BARANG LENGKAP');
            $sheet->setCellValue("E{$row}", "=COUNTIF(F3:F" . ($row - 1) . ",\"Lengkap ✓\")&\"/\"&COUNTA(B3:B" . ($row - 1) . ")");
            
            // Beri warna background navy kosongan di ujung baris summary agar selaras
            $sheet->setCellValue("F{$row}", "");
            $sheet->setCellValue("G{$row}", "");

            $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
                'font'      => ['bold' => true, 'name' => 'Arial', 'color' => ['rgb' => $whiteHex]],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $navyHex]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $navyHex]]],
            ]);
            $sheet->getRowDimension($row)->setRowHeight(25);

            // Lebar kolom (Kolom G dilebarkan menjadi 22 biar pas ukuran gambarnya)
            $sheet->getColumnDimension('A')->setWidth(5);
            $sheet->getColumnDimension('B')->setWidth(30);
            $sheet->getColumnDimension('C')->setWidth(15);
            $sheet->getColumnDimension('D')->setWidth(15);
            $sheet->getColumnDimension('E')->setWidth(12);
            $sheet->getColumnDimension('F')->setWidth(15);
            $sheet->getColumnDimension('G')->setWidth(22);
        }

        // Sheet rekap global (Tetap mode text statis/dashboard utama)
        $global = $spreadsheet->createSheet();
        $global->setTitle('Rekap Global');

        $global->mergeCells('A1:' . chr(65 + $barangs->count()) . '1');
        $global->setCellValue('A1', 'REKAP GLOBAL PENGUMPULAN BARANG - SELURUH KELOMPOK');
        $global->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => $whiteHex], 'name' => 'Arial'],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $navyHex]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $global->getRowDimension(1)->setRowHeight(30);

        // Header Global
        $global->setCellValue('A2', 'Kelompok');
        $global->getStyle('A2')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => $navyHex], 'name' => 'Arial'],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $tealHex]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $navyHex]]],
        ]);

        foreach ($barangs as $bi => $b) {
            $col = chr(66 + $bi);
            $global->setCellValue("{$col}2", $b->nama_barang . "\n(" . $b->jumlah_kebutuhan . ' ' . $b->satuan . ')');
            $global->getStyle("{$col}2")->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => $navyHex], 'name' => 'Arial'],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $tealHex]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $navyHex]]],
            ]);
            $global->getColumnDimension($col)->setWidth(18);
        }
        $global->getColumnDimension('A')->setWidth(12);
        $global->getRowDimension(2)->setRowHeight(35);

        $row = 3;
        foreach ($kelompoks as $k) {
            $global->setCellValue("A{$row}", "Kelompok $k");
            $global->getStyle("A{$row}")->applyFromArray([
                'font'      => ['bold' => true, 'name' => 'Arial', 'color' => ['rgb' => $navyHex]],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $sandHex]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'cccccc']]],
            ]);

            foreach ($barangs as $bi => $b) {
                $col = chr(66 + $bi);
                $p   = PengumpulanBarang::where('barang_kebutuhan_id', $b->id)->where('kelompok', $k)->first();
                $terkumpul = $p ? $p->jumlah_terkumpul : 0;
                $lengkap   = $terkumpul >= $b->jumlah_kebutuhan;
                $bgColor   = $lengkap ? $greenHex : ($terkumpul > 0 ? 'fff3cd' : $redHex);

                $global->setCellValue("{$col}{$row}", "{$terkumpul}/{$b->jumlah_kebutuhan}");
                $global->getStyle("{$col}{$row}")->applyFromArray([
                    'font'      => [
                        'name' => 'Arial',
                        'size' => 10,
                        'color' => ['rgb' => $lengkap ? '155724' : ($terkumpul > 0 ? '856404' : '721c24')]
                    ],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'cccccc']]],
                ]);
            }
            $global->getRowDimension($row)->setRowHeight(18);
            $row++;
        }

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'rekap_barang_' . date('Ymd_His') . '.xlsx';
        $tmpPath  = storage_path("app/temp/{$filename}");

        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0775, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($tmpPath);

        return response()->download($tmpPath, $filename)->deleteFileAfterSend(true);
    }

    // ─────────────────────────────────────────────────────────────
    // PESERTA: Lihat & update pengumpulan barang kelompoknya
    // ─────────────────────────────────────────────────────────────

    public function pesertaIndex()
    {
        $user     = Auth::user();
        $kelompok = $user->kelompok;

        $barangs = BarangKebutuhan::where('aktif', true)->orderBy('nama_barang')->get();

        $data = $barangs->map(function ($b) use ($kelompok) {
            $p = PengumpulanBarang::where('barang_kebutuhan_id', $b->id)
                ->where('kelompok', $kelompok)->first();
            return [
                'barang'          => $b,
                'pengumpulan'     => $p,
                'jumlah_terkumpul' => $p ? $p->jumlah_terkumpul : 0,
                'foto_url'        => $p && $p->foto_bukti ? Storage::url($p->foto_bukti) : null,
                'is_lengkap'      => $p && $p->jumlah_terkumpul >= $b->jumlah_kebutuhan,
                'updated_by_name' => $p && $p->updatedBy ? $p->updatedBy->name : null,
                'updated_at'      => $p ? $p->updated_at : null,
            ];
        });

        return view('peserta.barang', compact('kelompok', 'data'));
    }

    public function pesertaUpdate(Request $request, $barangId)
    {
        $request->validate([
            'jumlah_terkumpul' => 'required|integer|min:0',
            'foto_bukti'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $user    = Auth::user();
        $barang  = BarangKebutuhan::findOrFail($barangId);

        $pengumpulan = PengumpulanBarang::firstOrNew([
            'barang_kebutuhan_id' => $barang->id,
            'kelompok'            => $user->kelompok,
        ]);

        $pengumpulan->jumlah_terkumpul = $request->jumlah_terkumpul;
        $pengumpulan->updated_by       = $user->id;

        // Handle foto
        if ($request->hasFile('foto_bukti')) {
            // Hapus foto lama jika ada
            if ($pengumpulan->foto_bukti) {
                Storage::disk('public')->delete($pengumpulan->foto_bukti);
            }
            $path = $request->file('foto_bukti')->store('barang-bukti', 'public');
            $pengumpulan->foto_bukti = $path;
        }

        $pengumpulan->save();

        return back()->with('success', 'Data barang berhasil disimpan.');
    }

    public function pesertaHapusFoto($barangId)
    {
        $user        = Auth::user();
        $pengumpulan = PengumpulanBarang::where('barang_kebutuhan_id', $barangId)
            ->where('kelompok', $user->kelompok)
            ->firstOrFail();

        if ($pengumpulan->foto_bukti) {
            Storage::disk('public')->delete($pengumpulan->foto_bukti);
            $pengumpulan->foto_bukti = null;
            $pengumpulan->save();
        }

        return back()->with('success', 'Foto bukti berhasil dihapus.');
    }

    public function pesertaReset($barangId)
    {
        $user        = Auth::user();
        $pengumpulan = PengumpulanBarang::where('barang_kebutuhan_id', $barangId)
            ->where('kelompok', $user->kelompok)
            ->firstOrFail();

        if ($pengumpulan->foto_bukti) {
            Storage::disk('public')->delete($pengumpulan->foto_bukti);
        }

        $pengumpulan->delete();

        return back()->with('success', 'Data berhasil dihapus.');
    }

    // ─────────────────────────────────────────────────────────────
    // Helper
    // ─────────────────────────────────────────────────────────────

    private function authorizeLogistik()
    {
        $user = Auth::user();
        if ($user->role !== 'admin' && strtolower($user->divisi ?? '') !== 'logistik') {
            abort(403, 'Hanya divisi Logistik yang dapat mengelola daftar barang.');
        }
    }
}