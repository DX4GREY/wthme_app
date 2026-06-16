<?php

namespace App\Http\Controllers;

use App\Models\P3kBarangKebutuhan;
use App\Models\P3kPengumpulanBarang;
use App\Models\P3kPengumpulanIndividu;
use App\Models\P3kStokIndividu;
use App\Models\P3kObatPribadi;
use App\Models\P3kPjKelompok;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class P3kBarangController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // PANITIA P3K: Manage daftar barang kebutuhan (kelompok & individu)
    // ─────────────────────────────────────────────────────────────

    public function manageIndex()
    {
        $this->authorizeP3k();
        $barangsKelompok = P3kBarangKebutuhan::kelompok()->orderBy('nama_barang')->get();
        $barangsIndividu = P3kBarangKebutuhan::individu()->orderBy('nama_barang')->get();

        // Mapping PJ per kelompok (untuk ditampilkan/diatur)
        $pjKelompok = P3kPjKelompok::with('pj')->get();
        $panitiaP3k = User::where('divisi', 'P3K')->orderBy('name')->get();

        return view('panitia.p3k.manage', compact('barangsKelompok', 'barangsIndividu', 'pjKelompok', 'panitiaP3k'));
    }

    public function manageStore(Request $request)
    {
        $this->authorizeP3k();
        $request->validate([
            'nama_barang'      => 'required|string|max:255',
            'kategori'         => 'required|in:kelompok,individu',
            'jumlah_kebutuhan' => 'required|integer|min:1',
            'satuan'           => 'required|string|max:50',
            'keterangan'       => 'nullable|string|max:500',
        ]);

        P3kBarangKebutuhan::create($request->only(
            'nama_barang', 'kategori', 'jumlah_kebutuhan', 'satuan', 'keterangan'
        ));

        return back()->with('success', 'Barang P3K berhasil ditambahkan.');
    }

    public function manageUpdate(Request $request, $id)
    {
        $this->authorizeP3k();
        $request->validate([
            'nama_barang'      => 'required|string|max:255',
            'kategori'         => 'required|in:kelompok,individu',
            'jumlah_kebutuhan' => 'required|integer|min:1',
            'satuan'           => 'required|string|max:50',
            'keterangan'       => 'nullable|string|max:500',
        ]);

        $barang = P3kBarangKebutuhan::findOrFail($id);
        $barang->update($request->only(
            'nama_barang', 'kategori', 'jumlah_kebutuhan', 'satuan', 'keterangan'
        ));

        return back()->with('success', 'Barang P3K berhasil diupdate.');
    }

    public function manageDestroy($id)
    {
        $this->authorizeP3k();
        $barang = P3kBarangKebutuhan::findOrFail($id);

        foreach ($barang->pengumpulan as $p) {
            if ($p->foto_bukti) {
                Storage::disk('public')->delete($p->foto_bukti);
            }
        }

        $barang->delete();
        return back()->with('success', 'Barang P3K berhasil dihapus.');
    }

    // ─────────────────────────────────────────────────────────────
    // PANITIA P3K: Atur mapping PJ per kelompok
    // ─────────────────────────────────────────────────────────────

    public function pjStore(Request $request)
    {
        $this->authorizeP3k();
        $request->validate([
            'kelompok'  => 'required|string',
            'pj_p3k_id' => 'required|exists:users,id',
        ]);

        P3kPjKelompok::updateOrCreate(
            ['kelompok' => $request->kelompok],
            ['pj_p3k_id' => $request->pj_p3k_id]
        );

        return back()->with('success', "PJ untuk Kelompok {$request->kelompok} berhasil diatur.");
    }

    // ─────────────────────────────────────────────────────────────
    // PANITIA P3K: Lihat list kelompok & rekap
    // ─────────────────────────────────────────────────────────────

    public function panitiaIndex()
    {
        // Semua panitia boleh melihat halaman ini
        $this->authorizeAnyPanitia();

        $kelompoksData = User::where('role', 'peserta')
            ->whereNotNull('kelompok')
            ->distinct()
            ->pluck('kelompok')
            ->toArray();

        sort($kelompoksData, SORT_NATURAL | SORT_FLAG_CASE);

        $user = Auth::user();

        // Jika PJ P3K (bukan admin), hanya tampilkan kelompok binaannya
        if ($user->role !== 'admin' && strtoupper($user->divisi ?? '') === 'P3K') {
            $binaan = P3kPjKelompok::kelompokUntukPj($user->id);
            if (!empty($binaan)) {
                $kelompoksData = array_values(array_intersect($kelompoksData, $binaan));
            }
        }

        $kelompoks = collect($kelompoksData);
        $barangsKelompok = P3kBarangKebutuhan::kelompok()->where('aktif', true)->get();
        $barangsIndividu = P3kBarangKebutuhan::individu()->where('aktif', true)->get();

        $summary = [];
        foreach ($kelompoks as $k) {
            $totalKelompok = $barangsKelompok->count();
            $lengkapKelompok = 0;
            foreach ($barangsKelompok as $b) {
                $p = P3kPengumpulanBarang::where('p3k_barang_kebutuhan_id', $b->id)
                    ->where('kelompok', $k)->first();
                if ($p && $p->jumlah_terkumpul >= $b->jumlah_kebutuhan) {
                    $lengkapKelompok++;
                }
            }

            $totalIndividu = $barangsIndividu->count();
            $lengkapIndividu = 0;

            // Anggota kelompok ini
            $anggotaIds = User::where('role', 'peserta')->where('kelompok', $k)->pluck('id');
            $jumlahAnggota = $anggotaIds->count();

            foreach ($barangsIndividu as $b) {
                if ($jumlahAnggota === 0) continue;
                $jumlahSudahBawa = P3kPengumpulanIndividu::where('p3k_barang_kebutuhan_id', $b->id)
                    ->whereIn('user_id', $anggotaIds)
                    ->where('jumlah_dibawa', '>=', $b->jumlah_kebutuhan)
                    ->count();
                if ($jumlahSudahBawa >= $jumlahAnggota) {
                    $lengkapIndividu++;
                }
            }

            // Obat pribadi dari kelompok ini yang belum diserahkan
            $obatBelum = P3kObatPribadi::where('kelompok', $k)->where('sudah_diserahkan', false)->count();
            $obatTotal = P3kObatPribadi::where('kelompok', $k)->count();

            $summary[$k] = [
                'total'    => $totalKelompok + $totalIndividu,
                'lengkap'  => $lengkapKelompok + $lengkapIndividu,
                'obat_total' => $obatTotal,
                'obat_belum' => $obatBelum,
            ];
        }

        // Stok global barang individu — aggregat lintas semua kelompok, hanya untuk tampilan
        $stokIndividu = $barangsIndividu->map(function ($b) {
            $global = P3kStokIndividu::globalSummary($b->id);
            return [
                'barang'          => $b,
                'total_terkumpul' => $global['total_terkumpul'],
                'total_terpakai'  => $global['total_terpakai'],
                'total_sisa'      => $global['total_sisa'],
            ];
        });

        return view('panitia.p3k.index', compact('kelompoks', 'summary', 'barangsKelompok', 'barangsIndividu', 'stokIndividu'));
    }

    public function panitiaKelompok($kelompok)
    {
        // Semua panitia boleh melihat halaman kelompok
        // Aksi write (validasi, terpakai) di method masing-masing sudah dilindungi authorizeKelompokAccess()
        $this->authorizeAnyPanitia();

        $barangsKelompok = P3kBarangKebutuhan::kelompok()->where('aktif', true)->orderBy('nama_barang')->get();
        $barangsIndividu = P3kBarangKebutuhan::individu()->where('aktif', true)->orderBy('nama_barang')->get();

        // ── Barang Kelompok (agregat per kelompok, model lama) ──
        $dataKelompok = $barangsKelompok->map(function ($b) use ($kelompok) {
            $p = P3kPengumpulanBarang::where('p3k_barang_kebutuhan_id', $b->id)
                ->where('kelompok', $kelompok)
                ->with('updatedBy')
                ->first();

            return [
                'barang'           => $b,
                'pengumpulan'      => $p,
                'jumlah_terkumpul' => $p ? $p->jumlah_terkumpul : 0,
                'jumlah_terpakai'  => $p ? $p->jumlah_terpakai : 0,
                'jumlah_sisa'      => $p ? $p->jumlah_sisa : 0,
                'foto'             => $p && $p->foto_bukti ? Storage::url($p->foto_bukti) : null,
                'is_lengkap'       => $p && $p->jumlah_terkumpul >= $b->jumlah_kebutuhan,
                'is_validated'     => $p ? $p->is_validated : false,
                'updated_at'       => $p ? $p->updated_at : null,
                'updated_by_name'  => ($p && $p->updatedBy) ? $p->updatedBy->name : null,
            ];
        });

        // ── Barang Individu (per peserta dalam kelompok) ──
        $anggota = User::where('role', 'peserta')->where('kelompok', $kelompok)->orderBy('name')->get();

        // peserta x barang individu -> data pengumpulan
        $dataIndividu = $anggota->map(function ($peserta) use ($barangsIndividu) {
            $items = $barangsIndividu->map(function ($b) use ($peserta) {
                $p = P3kPengumpulanIndividu::where('p3k_barang_kebutuhan_id', $b->id)
                    ->where('user_id', $peserta->id)
                    ->first();

                return [
                    'barang'           => $b,
                    'pengumpulan'      => $p,
                    'jumlah_dibawa'    => $p ? $p->jumlah_dibawa : 0,
                    'foto'             => $p && $p->foto_bukti ? Storage::url($p->foto_bukti) : null,
                    'is_lengkap'       => $p && $p->jumlah_dibawa >= $b->jumlah_kebutuhan,
                    'is_validated'     => $p ? $p->is_validated : false,
                ];
            });

            return [
                'peserta' => $peserta,
                'items'   => $items,
            ];
        });

        // ── Summary Barang Individu per KELOMPOK (total terkumpul + stok terpakai kelompok ini) ──
        $summaryIndividuKelompok = $barangsIndividu->map(function ($b) use ($anggota, $kelompok) {
            $anggotaIds = $anggota->pluck('id');
            $totalKelompok = P3kPengumpulanIndividu::where('p3k_barang_kebutuhan_id', $b->id)
                ->whereIn('user_id', $anggotaIds)
                ->sum('jumlah_dibawa');

            $targetKelompok = $b->jumlah_kebutuhan * $anggota->count();

            // Stok per kelompok ini (terpakai dikontrol di halaman kelompok)
            $stok = P3kStokIndividu::where('p3k_barang_kebutuhan_id', $b->id)
                ->where('kelompok', $kelompok)
                ->first();

            return [
                'barang'           => $b,
                'total_kelompok'   => $totalKelompok,
                'target_kelompok'  => $targetKelompok,
                'is_lengkap'       => $totalKelompok >= $targetKelompok,
                'total_terkumpul'  => $stok ? $stok->total_terkumpul : $totalKelompok,
                'total_terpakai'   => $stok ? $stok->total_terpakai : 0,
                'total_sisa'       => $stok ? $stok->total_sisa : $totalKelompok,
            ];
        });

        $obatPribadi = P3kObatPribadi::where('kelompok', $kelompok)->with('peserta', 'pj')->get();

        return view('panitia.p3k.kelompok', compact(
            'kelompok', 'dataKelompok', 'dataIndividu', 'barangsIndividu', 'summaryIndividuKelompok', 'obatPribadi'
        ));
    }

    public function toggleValidasi(Request $request, $barangId, $kelompok)
    {
        $this->authorizeKelompokAccess($kelompok);

        $pengumpulan = P3kPengumpulanBarang::where('p3k_barang_kebutuhan_id', $barangId)
            ->where('kelompok', $kelompok)
            ->firstOrFail();

        $pengumpulan->is_validated = !$pengumpulan->is_validated;
        $pengumpulan->save();

        $statusPesan = $pengumpulan->is_validated ? 'berhasil di-ACC.' : 'batal di-ACC.';
        return back()->with('success', "Status progress barang {$statusPesan}");
    }

    // Update jumlah terpakai untuk barang KELOMPOK (agregat) selama acara berjalan
    public function updateTerpakai(Request $request, $barangId, $kelompok)
    {
        $this->authorizeKelompokAccess($kelompok);

        $request->validate([
            'jumlah_terpakai' => 'required|integer|min:0',
        ]);

        $pengumpulan = P3kPengumpulanBarang::where('p3k_barang_kebutuhan_id', $barangId)
            ->where('kelompok', $kelompok)
            ->firstOrFail();

        if ($request->jumlah_terpakai > $pengumpulan->jumlah_terkumpul) {
            return back()->withErrors(['error' => 'Jumlah terpakai tidak boleh melebihi jumlah terkumpul.']);
        }

        $pengumpulan->jumlah_terpakai = $request->jumlah_terpakai;
        $pengumpulan->updated_by = Auth::id();
        $pengumpulan->save();

        return back()->with('success', 'Jumlah terpakai berhasil diperbarui.');
    }

    // ─────────────────────────────────────────────────────────────
    // PANITIA P3K: Validasi & update barang INDIVIDU per peserta
    // ─────────────────────────────────────────────────────────────

    // Update jumlah terpakai dari STOK barang individu — per kelompok
    public function updateStokTerpakai(Request $request, $barangId, $kelompok)
    {
        $this->authorizeKelompokAccess($kelompok);

        $request->validate([
            'total_terpakai' => 'required|integer|min:0',
        ]);

        $barang = P3kBarangKebutuhan::findOrFail($barangId);
        $stok = P3kStokIndividu::firstOrCreate([
            'p3k_barang_kebutuhan_id' => $barang->id,
            'kelompok'                => $kelompok,
        ]);

        if ($request->total_terpakai > $stok->total_terkumpul) {
            return back()->withErrors(['error' => 'Jumlah terpakai tidak boleh melebihi total terkumpul kelompok ini.']);
        }

        $stok->total_terpakai = $request->total_terpakai;
        $stok->updated_by = Auth::id();
        $stok->save();

        return back()->with('success', "Stok '{$barang->nama_barang}' Kelompok {$kelompok} berhasil diperbarui.");
    }

    // Tambah/kurangi stok terpakai secara cepat (increment/decrement) — per kelompok
    public function adjustStokTerpakai(Request $request, $barangId, $kelompok)
    {
        $this->authorizeKelompokAccess($kelompok);

        $request->validate([
            'delta' => 'required|integer', // bisa positif (pakai) atau negatif (batal pakai)
        ]);

        $barang = P3kBarangKebutuhan::findOrFail($barangId);
        $stok = P3kStokIndividu::firstOrCreate([
            'p3k_barang_kebutuhan_id' => $barang->id,
            'kelompok'                => $kelompok,
        ]);

        $baru = $stok->total_terpakai + $request->delta;
        $baru = max(0, min($baru, $stok->total_terkumpul));

        $stok->total_terpakai = $baru;
        $stok->updated_by = Auth::id();
        $stok->save();

        return back()->with('success', "Stok '{$barang->nama_barang}' Kelompok {$kelompok}: {$baru} terpakai.");
    }

    // ACC barang individu milik satu peserta (saat pengumpulan)
    public function toggleValidasiIndividu(Request $request, $barangId, $userId)
    {
        $pengumpulan = P3kPengumpulanIndividu::where('p3k_barang_kebutuhan_id', $barangId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $peserta = User::findOrFail($userId);
        $this->authorizeKelompokAccess($peserta->kelompok);

        $pengumpulan->is_validated = !$pengumpulan->is_validated;
        $pengumpulan->save();

        $statusPesan = $pengumpulan->is_validated ? 'berhasil di-ACC.' : 'batal di-ACC.';
        return back()->with('success', "Status barang individu {$statusPesan}");
    }

    // ─────────────────────────────────────────────────────────────
    // PANITIA P3K: Obat pribadi - tandai sudah diserahkan
    // ─────────────────────────────────────────────────────────────

    public function obatToggleDiserahkan($id)
    {
        $obat = P3kObatPribadi::findOrFail($id);
        $this->authorizeKelompokAccess($obat->kelompok);

        $obat->sudah_diserahkan = !$obat->sudah_diserahkan;
        if ($obat->sudah_diserahkan) {
            $obat->pj_p3k_id = Auth::id();
        }
        $obat->save();

        return back()->with('success', 'Status obat pribadi diperbarui.');
    }

    // ─────────────────────────────────────────────────────────────
    // PANITIA P3K: Rekap global (semua kelompok) - khusus admin/koordinator
    // ─────────────────────────────────────────────────────────────

    public function panitiaRekap()
    {
        // Semua panitia boleh melihat rekap
        $this->authorizeAnyPanitia();

        $kelompoksData = User::where('role', 'peserta')
            ->whereNotNull('kelompok')
            ->distinct()
            ->pluck('kelompok')
            ->toArray();

        sort($kelompoksData, SORT_NATURAL | SORT_FLAG_CASE);
        $kelompoks = collect($kelompoksData);

        $barangsKelompok = P3kBarangKebutuhan::kelompok()->where('aktif', true)->orderBy('nama_barang')->get();
        $barangsIndividu = P3kBarangKebutuhan::individu()->where('aktif', true)->orderBy('nama_barang')->get();

        $rekapKelompok = [];
        $rekapIndividu = []; // per kelompok -> list peserta dengan status tiap barang individu

        foreach ($kelompoks as $k) {
            $rowsK = [];
            foreach ($barangsKelompok as $b) {
                $p = P3kPengumpulanBarang::where('p3k_barang_kebutuhan_id', $b->id)->where('kelompok', $k)->first();
                $rowsK[] = [
                    'barang'           => $b,
                    'jumlah_terkumpul' => $p ? $p->jumlah_terkumpul : 0,
                    'is_lengkap'       => $p && $p->jumlah_terkumpul >= $b->jumlah_kebutuhan,
                ];
            }
            $rekapKelompok[$k] = $rowsK;

            // Rekap individu: per peserta di kelompok ini
            $anggota = User::where('role', 'peserta')->where('kelompok', $k)->orderBy('name')->get();
            $rowsI = [];
            foreach ($anggota as $peserta) {
                $itemBarang = [];
                foreach ($barangsIndividu as $b) {
                    $p = P3kPengumpulanIndividu::where('p3k_barang_kebutuhan_id', $b->id)
                        ->where('user_id', $peserta->id)->first();
                    $itemBarang[] = [
                        'barang'          => $b,
                        'jumlah_dibawa'   => $p ? $p->jumlah_dibawa : 0,
                        'is_lengkap'      => $p && $p->jumlah_dibawa >= $b->jumlah_kebutuhan,
                    ];
                }
                $rowsI[] = [
                    'peserta' => $peserta,
                    'items'   => $itemBarang,
                ];
            }
            $rekapIndividu[$k] = $rowsI;
        }

        // Summary barang individu per kelompok (total terkumpul vs target kelompok)
        $summaryIndividuPerKelompok = [];
        foreach ($kelompoks as $k) {
            $anggotaIds = User::where('role', 'peserta')->where('kelompok', $k)->pluck('id');
            $jumlahAnggota = $anggotaIds->count();

            $rows = [];
            foreach ($barangsIndividu as $b) {
                $totalKelompok = P3kPengumpulanIndividu::where('p3k_barang_kebutuhan_id', $b->id)
                    ->whereIn('user_id', $anggotaIds)
                    ->sum('jumlah_dibawa');
                $targetKelompok = $b->jumlah_kebutuhan * $jumlahAnggota;

                $rows[] = [
                    'barang'          => $b,
                    'total_kelompok'  => $totalKelompok,
                    'target_kelompok' => $targetKelompok,
                    'is_lengkap'      => $jumlahAnggota > 0 && $totalKelompok >= $targetKelompok,
                ];
            }
            $summaryIndividuPerKelompok[$k] = $rows;
        }

        // Stok global barang individu — aggregat dari semua kelompok (per-kelompok disimpan terpisah)
        $stokIndividu = $barangsIndividu->map(function ($b) {
            $global = P3kStokIndividu::globalSummary($b->id);
            return [
                'barang'          => $b,
                'total_terkumpul' => $global['total_terkumpul'],
                'total_terpakai'  => $global['total_terpakai'],
                'total_sisa'      => $global['total_sisa'],
            ];
        });

        $obatPribadi = P3kObatPribadi::with('peserta', 'pj')->orderBy('kelompok')->get();

        return view('panitia.p3k.rekap', compact(
            'kelompoks', 'barangsKelompok', 'barangsIndividu', 'rekapKelompok', 'rekapIndividu',
            'summaryIndividuPerKelompok', 'stokIndividu', 'obatPribadi'
        ));
    }

    public function exportRekap()
    {
        // Semua panitia boleh export rekap
        $this->authorizeAnyPanitia();

        $kelompoksData = User::where('role', 'peserta')
            ->whereNotNull('kelompok')
            ->distinct()
            ->pluck('kelompok')
            ->toArray();

        sort($kelompoksData, SORT_NATURAL | SORT_FLAG_CASE);
        $kelompoks = collect($kelompoksData);

        $barangsKelompok = P3kBarangKebutuhan::kelompok()->where('aktif', true)->orderBy('nama_barang')->get();
        $barangsIndividu = P3kBarangKebutuhan::individu()->where('aktif', true)->orderBy('nama_barang')->get();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $navyHex  = '002f45';
        $tealHex  = 'bdd1d3';
        $greenHex = 'd4edda';
        $redHex   = 'f8d7da';
        $whiteHex = 'FFFFFF';

        // ── Sheet 1: Barang Kelompok ──
        $sheet1 = $spreadsheet->createSheet();
        $sheet1->setTitle('Barang Kelompok');
        $this->writeBarangSheet($sheet1, $kelompoks, $barangsKelompok, $navyHex, $tealHex, $greenHex, $redHex, $whiteHex, false);

        // ── Sheet 2: Barang Individu (per peserta, terkumpul/terpakai/sisa) ──
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Barang Individu');
        $this->writeBarangIndividuSheet($sheet2, $kelompoks, $barangsIndividu, $navyHex, $tealHex, $greenHex, $redHex, $whiteHex);

        // ── Sheet 2b: Stok Global Barang Individu (pool, lintas kelompok) ──
        $sheetStok = $spreadsheet->createSheet();
        $sheetStok->setTitle('Stok Global Individu');
        $this->writeStokGlobalSheet($sheetStok, $barangsIndividu, $navyHex, $tealHex, $greenHex, $redHex, $whiteHex);

        // ── Sheet 3: Obat Pribadi ──
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Obat Pribadi');
        $sheet3->mergeCells('A1:F1');
        $sheet3->setCellValue('A1', 'PENDATAAN OBAT PRIBADI');
        $sheet3->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => $whiteHex], 'name' => 'Arial'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $navyHex]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $headers = ['No', 'Nama Peserta', 'Kelompok', 'Penyakit/Kondisi', 'Nama Obat', 'Status'];
        foreach ($headers as $i => $h) {
            $col = chr(65 + $i);
            $sheet3->setCellValue("{$col}2", $h);
            $sheet3->getStyle("{$col}2")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => $navyHex], 'name' => 'Arial'],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $tealHex]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
        }

        $obatPribadi = P3kObatPribadi::with('peserta')->orderBy('kelompok')->get();
        $r = 3;
        foreach ($obatPribadi as $idx => $o) {
            $bg = $o->sudah_diserahkan ? $greenHex : $redHex;
            $rowData = [
                $idx + 1,
                $o->peserta->name ?? '-',
                $o->kelompok,
                $o->penyakit,
                $o->nama_obat ?? '-',
                $o->sudah_diserahkan ? 'Sudah Diserahkan' : 'Belum',
            ];
            foreach ($rowData as $ci => $val) {
                $col = chr(65 + $ci);
                $sheet3->setCellValue("{$col}{$r}", $val);
                $sheet3->getStyle("{$col}{$r}")->applyFromArray([
                    'font' => ['name' => 'Arial', 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'cccccc']]],
                ]);
            }
            $r++;
        }

        foreach (['A','B','C','D','E','F'] as $col) {
            $sheet3->getColumnDimension($col)->setWidth(20);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'rekap_p3k_' . date('Ymd_His') . '.xlsx';
        $tmpPath  = storage_path("app/temp/{$filename}");

        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0775, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($tmpPath);

        return response()->download($tmpPath, $filename)->deleteFileAfterSend(true);
    }

    private function writeBarangSheet($sheet, $kelompoks, $barangs, $navyHex, $tealHex, $greenHex, $redHex, $whiteHex)
    {
        $row = 1;

        foreach ($kelompoks as $kelompok) {
            $sheet->mergeCells("A{$row}:F{$row}");
            $sheet->setCellValue("A{$row}", "REKAP BARANG - KELOMPOK $kelompok");
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => $whiteHex], 'name' => 'Arial'],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $navyHex]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getRowDimension($row)->setRowHeight(28);
            $row++;

            $headers = ['No', 'Nama Barang', 'Kebutuhan', 'Terkumpul', 'Progress', 'Status'];

            foreach ($headers as $i => $h) {
                $col = chr(65 + $i);
                $sheet->setCellValue("{$col}{$row}", $h);
                $sheet->getStyle("{$col}{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => $navyHex], 'name' => 'Arial'],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $tealHex]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $navyHex]]],
                ]);
            }
            $row++;

            foreach ($barangs as $idx => $b) {
                $p = P3kPengumpulanBarang::where('p3k_barang_kebutuhan_id', $b->id)->where('kelompok', $kelompok)->first();
                $terkumpul = $p ? $p->jumlah_terkumpul : 0;
                $lengkap   = $terkumpul >= $b->jumlah_kebutuhan;
                $bg = $lengkap ? $greenHex : ($terkumpul > 0 ? 'fff3cd' : $redHex);

                $rowData = [
                    $idx + 1,
                    $b->nama_barang,
                    $b->jumlah_kebutuhan . ' ' . $b->satuan,
                    $terkumpul . ' ' . $b->satuan,
                    $terkumpul . '/' . $b->jumlah_kebutuhan,
                    $lengkap ? 'Lengkap' : ($terkumpul > 0 ? 'Sebagian' : 'Belum'),
                ];

                foreach ($rowData as $ci => $val) {
                    $col = chr(65 + $ci);
                    $sheet->setCellValue("{$col}{$row}", $val);
                    $sheet->getStyle("{$col}{$row}")->applyFromArray([
                        'font' => ['name' => 'Arial', 'size' => 10],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
                        'alignment' => ['horizontal' => $ci === 1 ? Alignment::HORIZONTAL_LEFT : Alignment::HORIZONTAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'cccccc']]],
                    ]);
                }
                $row++;
            }

            $row += 2;
        }

        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(28);
        foreach (['C','D','E','F'] as $col) {
            $sheet->getColumnDimension($col)->setWidth(14);
        }
    }

    // Sheet barang individu: per peserta (1 blok per kelompok, baris = peserta x barang)
    private function writeBarangIndividuSheet($sheet, $kelompoks, $barangsIndividu, $navyHex, $tealHex, $greenHex, $redHex, $whiteHex)
    {
        $row = 1;
        $lastCol = chr(65 + 1 + $barangsIndividu->count()); // kolom: Nama Peserta + tiap barang

        foreach ($kelompoks as $kelompok) {
            $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
            $sheet->setCellValue("A{$row}", "BARANG INDIVIDU - KELOMPOK $kelompok");
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => $whiteHex], 'name' => 'Arial'],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $navyHex]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getRowDimension($row)->setRowHeight(28);
            $row++;

            // Header: Nama Peserta | Barang1 (target) | Barang2 (target) | ...
            $sheet->setCellValue("A{$row}", 'Nama Peserta');
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => $navyHex], 'name' => 'Arial'],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $tealHex]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $navyHex]]],
            ]);

            foreach ($barangsIndividu as $i => $b) {
                $col = chr(65 + 1 + $i);
                $sheet->setCellValue("{$col}{$row}", $b->nama_barang . ' (' . $b->jumlah_kebutuhan . ' ' . $b->satuan . ')');
                $sheet->getStyle("{$col}{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => $navyHex], 'name' => 'Arial', 'size' => 9],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $tealHex]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $navyHex]]],
                ]);
            }
            $row++;

            $anggota = User::where('role', 'peserta')->where('kelompok', $kelompok)->orderBy('name')->get();

            $totalPerBarang = array_fill(0, $barangsIndividu->count(), 0);

            foreach ($anggota as $peserta) {
                $sheet->setCellValue("A{$row}", $peserta->name);
                $sheet->getStyle("A{$row}")->applyFromArray([
                    'font' => ['name' => 'Arial', 'size' => 10, 'bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'cccccc']]],
                ]);

                foreach ($barangsIndividu as $i => $b) {
                    $col = chr(65 + 1 + $i);
                    $p = P3kPengumpulanIndividu::where('p3k_barang_kebutuhan_id', $b->id)
                        ->where('user_id', $peserta->id)->first();
                    $dibawa  = $p ? $p->jumlah_dibawa : 0;
                    $lengkap = $dibawa >= $b->jumlah_kebutuhan;
                    $bg = $lengkap ? $greenHex : ($dibawa > 0 ? 'fff3cd' : $redHex);

                    $totalPerBarang[$i] += $dibawa;

                    $sheet->setCellValue("{$col}{$row}", "{$dibawa}/{$b->jumlah_kebutuhan}");
                    $sheet->getStyle("{$col}{$row}")->applyFromArray([
                        'font' => ['name' => 'Arial', 'size' => 9],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'cccccc']]],
                    ]);
                }
                $row++;
            }

            // Baris TOTAL KELOMPOK
            $sheet->setCellValue("A{$row}", "TOTAL KELOMPOK $kelompok");
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => $navyHex], 'name' => 'Arial', 'size' => 10],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $tealHex]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $navyHex]]],
            ]);
            foreach ($barangsIndividu as $i => $b) {
                $col = chr(65 + 1 + $i);
                $target = $b->jumlah_kebutuhan * $anggota->count();
                $sheet->setCellValue("{$col}{$row}", "{$totalPerBarang[$i]}/{$target}");
                $sheet->getStyle("{$col}{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => $navyHex], 'name' => 'Arial', 'size' => 9],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $tealHex]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $navyHex]]],
                ]);
            }
            $row++;

            $row += 2;
        }

        $sheet->getColumnDimension('A')->setWidth(28);
        for ($i = 0; $i < $barangsIndividu->count(); $i++) {
            $col = chr(65 + 1 + $i);
            $sheet->getColumnDimension($col)->setWidth(18);
        }
    }

    // Sheet stok global barang individu: pool lintas kelompok (terkumpul/terpakai/sisa)
    private function writeStokGlobalSheet($sheet, $barangsIndividu, $navyHex, $tealHex, $greenHex, $redHex, $whiteHex)
    {
        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', 'STOK GLOBAL BARANG INDIVIDU (POOL LINTAS KELOMPOK)');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => $whiteHex], 'name' => 'Arial'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $navyHex]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $headers = ['No', 'Nama Barang', 'Total Terkumpul', 'Total Terpakai', 'Sisa Stok'];
        foreach ($headers as $i => $h) {
            $col = chr(65 + $i);
            $sheet->setCellValue("{$col}2", $h);
            $sheet->getStyle("{$col}2")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => $navyHex], 'name' => 'Arial'],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $tealHex]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $navyHex]]],
            ]);
        }

        $row = 3;
        foreach ($barangsIndividu as $idx => $b) {
            $stok = P3kStokIndividu::where('p3k_barang_kebutuhan_id', $b->id)->first();
            $terkumpul = $stok ? $stok->total_terkumpul : 0;
            $terpakai  = $stok ? $stok->total_terpakai : 0;
            $sisa      = $stok ? $stok->total_sisa : 0;

            $bg = $sisa > 0 ? $greenHex : ($terkumpul > 0 ? 'fff3cd' : $redHex);

            $rowData = [
                $idx + 1,
                $b->nama_barang . ' (' . $b->satuan . ')',
                $terkumpul,
                $terpakai,
                $sisa,
            ];

            foreach ($rowData as $ci => $val) {
                $col = chr(65 + $ci);
                $sheet->setCellValue("{$col}{$row}", $val);
                $sheet->getStyle("{$col}{$row}")->applyFromArray([
                    'font' => ['name' => 'Arial', 'size' => 10, 'bold' => $ci === 4],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
                    'alignment' => ['horizontal' => $ci === 1 ? Alignment::HORIZONTAL_LEFT : Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'cccccc']]],
                ]);
            }
            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(28);
        foreach (['C','D','E'] as $col) {
            $sheet->getColumnDimension($col)->setWidth(16);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // PESERTA: Lihat & update pengumpulan barang P3K kelompoknya
    // ─────────────────────────────────────────────────────────────

    public function pesertaIndex()
    {
        $user     = Auth::user();
        $kelompok = $user->kelompok;

        $barangsKelompok = P3kBarangKebutuhan::kelompok()->where('aktif', true)->orderBy('nama_barang')->get();
        $barangsIndividu = P3kBarangKebutuhan::individu()->where('aktif', true)->orderBy('nama_barang')->get();

        // PJ P3K otomatis berdasarkan mapping kelompok
        $pjId = P3kPjKelompok::pjUntukKelompok($kelompok);
        $pj   = $pjId ? User::find($pjId) : null;

        // Barang kelompok (agregat per kelompok)
        $dataKelompok = $barangsKelompok->map(function ($b) use ($kelompok) {
            $p = P3kPengumpulanBarang::where('p3k_barang_kebutuhan_id', $b->id)
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

        // Barang individu (milik peserta yang login)
        $dataIndividu = $barangsIndividu->map(function ($b) use ($user) {
            $p = P3kPengumpulanIndividu::where('p3k_barang_kebutuhan_id', $b->id)
                ->where('user_id', $user->id)->first();
            return [
                'barang'          => $b,
                'pengumpulan'     => $p,
                'jumlah_dibawa'   => $p ? $p->jumlah_dibawa : 0,
                'foto_url'        => $p && $p->foto_bukti ? Storage::url($p->foto_bukti) : null,
                'is_lengkap'      => $p && $p->jumlah_dibawa >= $b->jumlah_kebutuhan,
                'is_validated'    => $p ? $p->is_validated : false,
                'updated_by_name' => $p && $p->updatedBy ? $p->updatedBy->name : null,
                'updated_at'      => $p ? $p->updated_at : null,
            ];
        });

        $obatPribadiSaya = P3kObatPribadi::where('user_id', $user->id)->get();

        return view('peserta.p3k', compact('kelompok', 'dataKelompok', 'dataIndividu', 'pj', 'obatPribadiSaya'));
    }

    // ── Barang KELOMPOK (agregat per kelompok) ──

    public function pesertaUpdateKelompok(Request $request, $barangId)
    {
        $request->validate([
            'jumlah_terkumpul' => 'required|integer|min:0',
            'foto_bukti'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        $user    = Auth::user();
        $barang  = P3kBarangKebutuhan::findOrFail($barangId);

        // PJ ditentukan otomatis dari mapping kelompok, peserta tidak memilih
        $pjId = P3kPjKelompok::pjUntukKelompok($user->kelompok);

        $pengumpulan = P3kPengumpulanBarang::firstOrNew([
            'p3k_barang_kebutuhan_id' => $barang->id,
            'kelompok'                => $user->kelompok,
        ]);

        if ($pengumpulan->is_validated && $pengumpulan->jumlah_terkumpul >= $barang->jumlah_kebutuhan) {
            return back()->withErrors(['error' => 'Data sudah lengkap dan di-ACC P3K, tidak dapat diubah lagi.']);
        }

        if ($pengumpulan->is_validated && $request->jumlah_terkumpul < $pengumpulan->jumlah_terkumpul) {
            return back()->withErrors(['error' => 'Jumlah barang tidak boleh lebih kecil dari jumlah yang sudah di-ACC sebelumnya.']);
        }

        $pengumpulan->jumlah_terkumpul = $request->jumlah_terkumpul;
        $pengumpulan->pj_p3k_id        = $pjId;
        $pengumpulan->updated_by       = $user->id;

        if ($pengumpulan->is_validated && $request->jumlah_terkumpul > $pengumpulan->getOriginal('jumlah_terkumpul')) {
            $pengumpulan->is_validated = false;
        }

        if ($request->hasFile('foto_bukti')) {
            if ($pengumpulan->foto_bukti) {
                Storage::disk('public')->delete($pengumpulan->foto_bukti);
            }
            $path = $request->file('foto_bukti')->store('p3k-bukti', 'public');
            $pengumpulan->foto_bukti = $path;
        }

        $pengumpulan->save();

        return back()->with('success', 'Data barang P3K berhasil diperbarui.');
    }

    public function pesertaHapusFotoKelompok($barangId)
    {
        $user        = Auth::user();
        $barang      = P3kBarangKebutuhan::findOrFail($barangId);
        $pengumpulan = P3kPengumpulanBarang::where('p3k_barang_kebutuhan_id', $barangId)
            ->where('kelompok', $user->kelompok)
            ->firstOrFail();

        if ($pengumpulan->is_validated && $pengumpulan->jumlah_terkumpul >= $barang->jumlah_kebutuhan) {
            return back()->withErrors(['error' => 'Data sudah di-ACC P3K sepenuhnya.']);
        }

        if ($pengumpulan->foto_bukti) {
            Storage::disk('public')->delete($pengumpulan->foto_bukti);
            $pengumpulan->foto_bukti = null;
            $pengumpulan->is_validated = false;
            $pengumpulan->save();
        }

        return back()->with('success', 'Foto bukti berhasil dihapus.');
    }

    public function pesertaResetKelompok($barangId)
    {
        $user        = Auth::user();
        $barang      = P3kBarangKebutuhan::findOrFail($barangId);
        $pengumpulan = P3kPengumpulanBarang::where('p3k_barang_kebutuhan_id', $barangId)
            ->where('kelompok', $user->kelompok)
            ->firstOrFail();

        if ($pengumpulan->is_validated && $pengumpulan->jumlah_terkumpul >= $barang->jumlah_kebutuhan) {
            return back()->withErrors(['error' => 'Data sudah di-ACC P3K sepenuhnya.']);
        }

        if ($pengumpulan->foto_bukti) {
            Storage::disk('public')->delete($pengumpulan->foto_bukti);
        }

        $pengumpulan->delete();

        return back()->with('success', 'Data berhasil direset.');
    }

    // ── Barang INDIVIDU (milik peserta sendiri) ──

    public function pesertaUpdateIndividu(Request $request, $barangId)
    {
        $request->validate([
            'jumlah_dibawa' => 'required|integer|min:0',
            'foto_bukti'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        $user   = Auth::user();
        $barang = P3kBarangKebutuhan::findOrFail($barangId);

        $pengumpulan = P3kPengumpulanIndividu::firstOrNew([
            'p3k_barang_kebutuhan_id' => $barang->id,
            'user_id'                 => $user->id,
        ]);

        if ($pengumpulan->is_validated && $pengumpulan->jumlah_dibawa >= $barang->jumlah_kebutuhan) {
            return back()->withErrors(['error' => 'Data sudah lengkap dan di-ACC P3K, tidak dapat diubah lagi.']);
        }

        if ($pengumpulan->is_validated && $request->jumlah_dibawa < $pengumpulan->jumlah_dibawa) {
            return back()->withErrors(['error' => 'Jumlah barang tidak boleh lebih kecil dari jumlah yang sudah di-ACC sebelumnya.']);
        }

        $pengumpulan->jumlah_dibawa = $request->jumlah_dibawa;
        $pengumpulan->updated_by    = $user->id;

        if ($pengumpulan->is_validated && $request->jumlah_dibawa > $pengumpulan->getOriginal('jumlah_dibawa')) {
            $pengumpulan->is_validated = false;
        }

        if ($request->hasFile('foto_bukti')) {
            if ($pengumpulan->foto_bukti) {
                Storage::disk('public')->delete($pengumpulan->foto_bukti);
            }
            $path = $request->file('foto_bukti')->store('p3k-individu-bukti', 'public');
            $pengumpulan->foto_bukti = $path;
        }

        $pengumpulan->save();

        // Recalculate stok per kelompok peserta ini
        P3kStokIndividu::recalcTerkumpul($barang->id, $user->kelompok);

        return back()->with('success', 'Data barang individu berhasil diperbarui.');
    }

    public function pesertaHapusFotoIndividu($barangId)
    {
        $user        = Auth::user();
        $barang      = P3kBarangKebutuhan::findOrFail($barangId);
        $pengumpulan = P3kPengumpulanIndividu::where('p3k_barang_kebutuhan_id', $barangId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($pengumpulan->is_validated && $pengumpulan->jumlah_dibawa >= $barang->jumlah_kebutuhan) {
            return back()->withErrors(['error' => 'Data sudah di-ACC P3K sepenuhnya.']);
        }

        if ($pengumpulan->foto_bukti) {
            Storage::disk('public')->delete($pengumpulan->foto_bukti);
            $pengumpulan->foto_bukti = null;
            $pengumpulan->is_validated = false;
            $pengumpulan->save();
        }

        return back()->with('success', 'Foto bukti berhasil dihapus.');
    }

    public function pesertaResetIndividu($barangId)
    {
        $user        = Auth::user();
        $barang      = P3kBarangKebutuhan::findOrFail($barangId);
        $pengumpulan = P3kPengumpulanIndividu::where('p3k_barang_kebutuhan_id', $barangId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($pengumpulan->is_validated && $pengumpulan->jumlah_dibawa >= $barang->jumlah_kebutuhan) {
            return back()->withErrors(['error' => 'Data sudah di-ACC P3K sepenuhnya.']);
        }

        if ($pengumpulan->foto_bukti) {
            Storage::disk('public')->delete($pengumpulan->foto_bukti);
        }

        $pengumpulan->delete();

        // Recalculate stok per kelompok setelah data peserta dihapus
        P3kStokIndividu::recalcTerkumpul($barang->id, $user->kelompok);

        return back()->with('success', 'Data berhasil direset.');
    }

    // ─────────────────────────────────────────────────────────────
    // PESERTA: Lapor obat pribadi
    // ─────────────────────────────────────────────────────────────

    public function obatStore(Request $request)
    {
        $request->validate([
            'penyakit'   => 'required|string|max:255',
            'nama_obat'  => 'nullable|string|max:255',
            'catatan'    => 'nullable|string|max:500',
            'foto_bukti' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        $user = Auth::user();
        $pjId = P3kPjKelompok::pjUntukKelompok($user->kelompok);

        $data = [
            'user_id'   => $user->id,
            'kelompok'  => $user->kelompok,
            'penyakit'  => $request->penyakit,
            'nama_obat' => $request->nama_obat,
            'catatan'   => $request->catatan,
            'pj_p3k_id' => $pjId,
        ];

        if ($request->hasFile('foto_bukti')) {
            $data['foto_bukti'] = $request->file('foto_bukti')->store('p3k-obat-bukti', 'public');
        }

        P3kObatPribadi::create($data);

        return back()->with('success', 'Data obat pribadi berhasil dilaporkan ke P3K.');
    }

    public function obatDestroy($id)
    {
        $user = Auth::user();
        $obat = P3kObatPribadi::where('id', $id)->where('user_id', $user->id)->firstOrFail();

        if ($obat->sudah_diserahkan) {
            return back()->withErrors(['error' => 'Data sudah diterima P3K, tidak dapat dihapus.']);
        }

        if ($obat->foto_bukti) {
            Storage::disk('public')->delete($obat->foto_bukti);
        }

        $obat->delete();

        return back()->with('success', 'Data obat pribadi dihapus.');
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers: Authorization
    // ─────────────────────────────────────────────────────────────

    /**
     * Hanya admin atau divisi P3K yang boleh melakukan aksi tulis
     * (manage barang, validasi, update terpakai, stok, obat, dsb.)
     */
    private function authorizeP3k()
    {
        $user = Auth::user();
        if ($user->role !== 'admin' && strtoupper($user->divisi ?? '') !== 'P3K') {
            abort(403, 'Hanya admin atau divisi P3K yang dapat mengelola data ini.');
        }
    }

    /**
     * Semua panitia (role = 'panitia' atau 'admin') boleh mengakses
     * halaman view-only (index, kelompok, rekap).
     * Middleware 'panitia' sudah memastikan user adalah panitia,
     * jadi cukup pastikan bukan peserta yang bypass.
     */
    private function authorizeAnyPanitia()
    {
        $user = Auth::user();
        // Middleware 'panitia' sudah handle ini, tapi double-check role
        if ($user->role === 'peserta') {
            abort(403, 'Akses ditolak.');
        }
    }

    /**
     * Untuk aksi write per kelompok: hanya admin atau divisi P3K,
     * dan jika PJ P3K, hanya untuk kelompok binaannya.
     */
    private function authorizeKelompokAccess($kelompok)
    {
        $user = Auth::user();

        // Admin: akses penuh ke semua kelompok
        if ($user->role === 'admin') {
            return;
        }

        // Harus divisi P3K untuk bisa write per kelompok
        if (strtoupper($user->divisi ?? '') !== 'P3K') {
            abort(403, 'Hanya admin atau divisi P3K yang dapat mengakses data ini.');
        }

        // PJ P3K: hanya boleh akses kelompok binaannya
        $binaan = P3kPjKelompok::kelompokUntukPj($user->id);
        if (!empty($binaan) && !in_array($kelompok, $binaan)) {
            abort(403, 'Anda bukan PJ P3K untuk kelompok ini.');
        }
    }
}
