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
        // 1. Ambil data kelompok unik dari database
        $kelompoksData = User::where('role', 'peserta')
            ->whereNotNull('kelompok')
            ->distinct()
            ->pluck('kelompok')
            ->toArray();

        // 2. Urutkan menggunakan aturan "Natural Sorting" (Kelompok 1, 2, ... 10, 11)
        sort($kelompoksData, SORT_NATURAL | SORT_FLAG_CASE);

        $kelompoks = collect($kelompoksData);
        $barangs = BarangKebutuhan::where('aktif', true)->get();

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
                'is_lengkap'       => $p && $p->jumlah_terkumpul >= $b->jumlah_kebutuhan,
                'is_validated'     => $p ? $p->is_validated : false,
                'updated_at'       => $p ? $p->updated_at : null,
                'updated_by_name'  => ($p && $p->updatedBy) ? $p->updatedBy->name : null,
            ];
        });

        return view('panitia.barang.kelompok', compact('kelompok', 'data'));
    }

    public function toggleValidasi(Request $request, $barangId, $kelompok)
    {
        $this->authorizeLogistik();

        $pengumpulan = PengumpulanBarang::where('barang_kebutuhan_id', $barangId)
            ->where('kelompok', $kelompok)
            ->firstOrFail();

        $pengumpulan->is_validated = !$pengumpulan->is_validated;
        $pengumpulan->save();

        $statusPesan = $pengumpulan->is_validated ? 'berhasil di-ACC.' : 'batal di-ACC.';
        return back()->with('success', "Status progress barang {$statusPesan}");
    }

    public function panitiaRekap()
    {
        // FIX: Menggunakan Natural Sorting agar nomor kelompok berurutan rapi di web rekap global
        $kelompoksData = User::where('role', 'peserta')
            ->whereNotNull('kelompok')
            ->distinct()
            ->pluck('kelompok')
            ->toArray();

        sort($kelompoksData, SORT_NATURAL | SORT_FLAG_CASE);
        $kelompoks = collect($kelompoksData);

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
        // 1. Ambil data kelompok unik dan urutkan dengan Natural Sorting (1, 2, ... 10, 11)
        $kelompoksData = User::where('role', 'peserta')
            ->whereNotNull('kelompok')
            ->distinct()
            ->pluck('kelompok')
            ->toArray();

        sort($kelompoksData, SORT_NATURAL | SORT_FLAG_CASE);
        $kelompoks = collect($kelompoksData);

        // 2. Ambil data barang yang aktif
        $barangs = BarangKebutuhan::where('aktif', true)->orderBy('nama_barang')->get();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0); // Hapus sheet default

        // Buat SATU-SATUNYA Sheet: Rekap Global
        $global = $spreadsheet->createSheet();
        $global->setTitle('Rekap Global');

        // Definisikan Kode Warna (Palette)
        $navyHex  = '002f45';
        $tealHex  = 'bdd1d3';
        $sandHex  = 'd2c296';
        $greenHex = 'd4edda';
        $redHex   = 'f8d7da';
        $whiteHex = 'FFFFFF';

        // --- Judul Atas ---
        $lastColLetter = chr(65 + $barangs->count());
        $global->mergeCells("A1:{$lastColLetter}1");
        $global->setCellValue('A1', 'REKAP GLOBAL PENGUMPULAN BARANG - SELURUH KELOMPOK');
        $global->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => $whiteHex], 'name' => 'Arial'],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $navyHex]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $global->getRowDimension(1)->setRowHeight(35);

        // --- Header Kolom Nama Kelompok ---
        $global->setCellValue('A2', 'Kelompok');
        $global->getStyle('A2')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => $navyHex], 'name' => 'Arial'],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $tealHex]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $navyHex]]],
        ]);

        // --- Header Kolom Nama Barang ---
        foreach ($barangs as $bi => $b) {
            $col = chr(66 + $bi);
            $global->setCellValue("{$col}2", $b->nama_barang . "\n(" . $b->jumlah_kebutuhan . ' ' . $b->satuan . ')');
            $global->getStyle("{$col}2")->applyFromArray([
                'font'      => ['bold' => true, 'size' => 10, 'color' => ['rgb' => $navyHex], 'name' => 'Arial'],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $tealHex]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $navyHex]]],
            ]);
            $global->getColumnDimension($col)->setWidth(20);
        }
        $global->getColumnDimension('A')->setWidth(15);
        $global->getRowDimension(2)->setRowHeight(40);

        // --- Isi Data Baris Kelompok (Berurutan) ---
        $row = 3;
        foreach ($kelompoks as $k) {
            // Kolom Kelompok
            $global->setCellValue("A{$row}", "Kelompok $k");
            $global->getStyle("A{$row}")->applyFromArray([
                'font'      => ['bold' => true, 'name' => 'Arial', 'color' => ['rgb' => $navyHex]],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $sandHex]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'cccccc']]],
            ]);

            // Kolom Progress Barang per Kelompok
            foreach ($barangs as $bi => $b) {
                $col = chr(66 + $bi);
                $p   = PengumpulanBarang::where('barang_kebutuhan_id', $b->id)->where('kelompok', $k)->first();

                $terkumpul = $p ? $p->jumlah_terkumpul : 0;
                $lengkap   = $terkumpul >= $b->jumlah_kebutuhan;

                // Warnai background cell: Hijau (Lengkap), Kuning (Sebagian), Merah (Belum)
                $bgColor   = $lengkap ? $greenHex : ($terkumpul > 0 ? 'fff3cd' : $redHex);
                // Warna teks menyesuaikan status progress
                $textColor = $lengkap ? '155724' : ($terkumpul > 0 ? '856404' : '721c24');

                $global->setCellValue("{$col}{$row}", "{$terkumpul}/{$b->jumlah_kebutuhan}");
                $global->getStyle("{$col}{$row}")->applyFromArray([
                    'font'      => ['name' => 'Arial', 'size' => 10, 'bold' => true, 'color' => ['rgb' => $textColor]],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'cccccc']]],
                ]);
            }
            $global->getRowDimension($row)->setRowHeight(22);
            $row++;
        }

        // Set sheet pertama aktif secara default saat dibuka
        $spreadsheet->setActiveSheetIndex(0);

        // Proses download file
        $filename = 'rekap_global_barang_' . date('Ymd_His') . '.xlsx';
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

        $panitiaLogistik = User::where('role', 'panitia')
            ->where('divisi', 'logistik')
            ->orderBy('name')
            ->get();

        $data = $barangs->map(function ($b) use ($kelompok) {
            $p = PengumpulanBarang::where('barang_kebutuhan_id', $b->id)
                ->where('kelompok', $kelompok)->first();
            return [
                'barang'           => $b,
                'pengumpulan'      => $p,
                'jumlah_terkumpul' => $p ? $p->jumlah_terkumpul : 0,
                'foto_url'         => $p && $p->foto_bukti ? Storage::url($p->foto_bukti) : null,
                'is_lengkap'       => $p && $p->jumlah_terkumpul >= $b->jumlah_kebutuhan,
                'is_validated'     => $p ? $p->is_validated : false,
                'updated_by_name'  => $p && $p->updatedBy ? $p->updatedBy->name : null,
                'updated_at'       => $p ? $p->updated_at : null,
            ];
        });

        return view('peserta.barang', compact('kelompok', 'data', 'panitiaLogistik'));
    }

    public function pesertaUpdate(Request $request, $barangId)
    {
        $request->validate([
            'jumlah_terkumpul'    => 'required|integer|min:0',
            'panitia_penerima_id' => 'required|exists:users,id',
            'foto_bukti'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        $user    = Auth::user();
        $barang  = BarangKebutuhan::findOrFail($barangId);

        $pengumpulan = PengumpulanBarang::firstOrNew([
            'barang_kebutuhan_id' => $barang->id,
            'kelompok'            => $user->kelompok,
        ]);

        if ($pengumpulan->is_validated && $pengumpulan->jumlah_terkumpul >= $barang->jumlah_kebutuhan) {
            return back()->withErrors(['error' => 'Data sudah lengkap and di-ACC panitia, tidak dapat diubah lagi.']);
        }

        if ($pengumpulan->is_validated && $request->jumlah_terkumpul < $pengumpulan->jumlah_terkumpul) {
            return back()->withErrors(['error' => 'Jumlah barang tidak boleh lebih kecil dari jumlah yang sudah di-ACC sebelumnya.']);
        }

        $pengumpulan->jumlah_terkumpul    = $request->jumlah_terkumpul;
        $pengumpulan->panitia_penerima_id = $request->panitia_penerima_id;
        $pengumpulan->updated_by          = $user->id;

        if ($pengumpulan->is_validated && $request->jumlah_terkumpul > $pengumpulan->getOriginal('jumlah_terkumpul')) {
            $pengumpulan->is_validated = false;
        }

        if ($request->hasFile('foto_bukti')) {
            if ($pengumpulan->foto_bukti) {
                Storage::disk('public')->delete($pengumpulan->foto_bukti);
            }
            $path = $request->file('foto_bukti')->store('barang-bukti', 'public');
            $pengumpulan->foto_bukti = $path;
        }

        $pengumpulan->save();

        return back()->with('success', 'Data barang berhasil diperbarui.');
    }

    public function pesertaHapusFoto($barangId)
    {
        $user        = Auth::user();
        $barang      = BarangKebutuhan::findOrFail($barangId);
        $pengumpulan = PengumpulanBarang::where('barang_kebutuhan_id', $barangId)
            ->where('kelompok', $user->kelompok)
            ->firstOrFail();

        if ($pengumpulan->is_validated && $pengumpulan->jumlah_terkumpul >= $barang->jumlah_kebutuhan) {
            return back()->withErrors(['error' => 'Data sudah di-ACC panitia sepenuhnya.']);
        }

        if ($pengumpulan->foto_bukti) {
            Storage::disk('public')->delete($pengumpulan->foto_bukti);
            $pengumpulan->foto_bukti = null;
            $pengumpulan->is_validated = false;
            $pengumpulan->save();
        }

        return back()->with('success', 'Foto bukti berhasil dihapus.');
    }

    public function pesertaReset($barangId)
    {
        $user        = Auth::user();
        $barang      = BarangKebutuhan::findOrFail($barangId);
        $pengumpulan = PengumpulanBarang::where('barang_kebutuhan_id', $barangId)
            ->where('kelompok', $user->kelompok)
            ->firstOrFail();

        if ($pengumpulan->is_validated && $pengumpulan->jumlah_terkumpul >= $barang->jumlah_kebutuhan) {
            return back()->withErrors(['error' => 'Data sudah di-ACC panitia sepenuhnya.']);
        }

        if ($pengumpulan->foto_bukti) {
            Storage::disk('public')->delete($pengumpulan->foto_bukti);
        }

        $pengumpulan->delete();

        return back()->with('success', 'Data berhasil direset.');
    }

    private function authorizeLogistik()
    {
        $user = Auth::user();
        if ($user->role !== 'admin' && strtolower($user->divisi ?? '') !== 'logistik') {
            abort(403, 'Hanya divisi Logistik yang dapat mengelola daftar barang.');
        }
    }
}
