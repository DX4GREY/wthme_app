<?php

namespace App\Http\Controllers;

use App\Models\P3kBarangKebutuhan;
use App\Models\P3kPengumpulanBarang;
use App\Models\P3kPengumpulanKolektif;
use App\Models\P3kPengumpulanKolektifAnggota;
use App\Models\P3kPengumpulanKolektifItem;
use App\Models\P3kStokIndividu;
use App\Models\P3kStokGlobal;
use App\Models\P3kObatPribadi;
use App\Models\P3kPjKelompok;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class P3kBarangController extends Controller
{
    private const MENUS = ['logistik', 'konsumsi', 'p3k'];
    private const MENU_LABELS = ['logistik' => '🎒 Logistik', 'konsumsi' => '🥘 Konsumsi', 'p3k' => '🩹 P3K'];
    private const MENU_ICONS  = ['logistik' => '🎒', 'konsumsi' => '🥘', 'p3k' => '🩹'];

    /**
     * Mapping menu → divisi yang berwenang mengelola menu tersebut.
     * Divisi disimpan case-insensitive; perbandingan pakai strtoupper().
     */
    private const MENU_DIVISI = [
        'logistik' => 'LOGISTIK',
        'konsumsi'  => 'KONSUM',
        'p3k'       => 'P3K',
    ];

    private function validasiMenu(string $menu): void
    {
        abort_unless(in_array($menu, self::MENUS, true), 404);
    }

    // ─────────────────────────────────────────────────────────────
    // PANITIA: Manage daftar barang kebutuhan (kelompok & individu)
    // Hanya ADMIN yang boleh tambah/edit/hapus barang
    // ─────────────────────────────────────────────────────────────

    public function manageIndex()
    {
        $this->authorizeAdmin();

        $semuaBarangs = P3kBarangKebutuhan::orderBy('menu')->orderBy('tipe')->orderBy('nama_barang')->get();

        // Grouped: [menu][tipe] => Collection
        $barangsByMenuTipe = [];
        foreach (self::MENUS as $menu) {
            $barangsByMenuTipe[$menu] = [
                'kelompok' => $semuaBarangs->where('menu', $menu)->where('tipe', 'kelompok')->values(),
                'individu'  => $semuaBarangs->where('menu', $menu)->where('tipe', 'individu')->values(),
            ];
        }

        $pjKelompok  = P3kPjKelompok::with('pj')->get();
        $panitiaP3k  = User::where('divisi', 'P3K')->orderBy('name')->get();

        return view('panitia.p3k.manage', compact('barangsByMenuTipe', 'pjKelompok', 'panitiaP3k'));
    }

    public function manageStore(Request $request)
    {
        $this->authorizeAdmin();
        $request->validate([
            'nama_barang'      => 'required|string|max:255',
            'tipe'             => 'required|in:kelompok,individu',
            'menu'             => 'required|in:logistik,konsumsi,p3k',
            'jumlah_kebutuhan' => 'required|integer|min:1',
            'satuan'           => 'required|string|max:50',
            'keterangan'       => 'nullable|string|max:500',
        ]);

        P3kBarangKebutuhan::create($request->only(
            'nama_barang', 'tipe', 'menu', 'jumlah_kebutuhan', 'satuan', 'keterangan'
        ));

        return back()->with('success', 'Barang berhasil ditambahkan.');
    }

    public function manageUpdate(Request $request, $id)
    {
        $this->authorizeAdmin();
        $request->validate([
            'nama_barang'      => 'required|string|max:255',
            'tipe'             => 'required|in:kelompok,individu',
            'menu'             => 'required|in:logistik,konsumsi,p3k',
            'jumlah_kebutuhan' => 'required|integer|min:1',
            'satuan'           => 'required|string|max:50',
            'keterangan'       => 'nullable|string|max:500',
        ]);

        $barang = P3kBarangKebutuhan::findOrFail($id);
        $barang->update($request->only(
            'nama_barang', 'tipe', 'menu', 'jumlah_kebutuhan', 'satuan', 'keterangan'
        ));

        return back()->with('success', 'Barang berhasil diupdate.');
    }

    public function manageDestroy($id)
    {
        $this->authorizeAdmin();
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
    // PANITIA: Atur mapping PJ per kelompok
    // Hanya ADMIN
    // ─────────────────────────────────────────────────────────────

    public function pjStore(Request $request)
    {
        $this->authorizeAdmin();
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
    // PANITIA: Lihat list kelompok & rekap
    // Semua panitia (admin, logistik, konsumsi, p3k) boleh lihat
    // ─────────────────────────────────────────────────────────────

    public function panitiaIndex()
    {
        $this->authorizeAnyPanitia();

        $kelompoksData = User::where('role', 'peserta')
            ->whereNotNull('kelompok')
            ->distinct()
            ->pluck('kelompok')
            ->toArray();

        sort($kelompoksData, SORT_NATURAL | SORT_FLAG_CASE);

        $user = Auth::user();

        // Jika PJ salah satu divisi (bukan admin), hanya tampilkan kelompok binaannya
        if ($user->role !== 'admin') {
            $binaan = P3kPjKelompok::kelompokUntukPj($user->id);
            if (!empty($binaan)) {
                $kelompoksData = array_values(array_intersect($kelompoksData, $binaan));
            }
        }

        $kelompoks = collect($kelompoksData);
        $barangsKelompok = P3kBarangKebutuhan::kelompok()->where('aktif', true)->get();
        $barangsIndividu = P3kBarangKebutuhan::individu()->where('aktif', true)->get();

        $itemKolektifByKelompok = P3kPengumpulanKolektifItem::with('pengumpulan')
            ->get()
            ->groupBy(fn($item) => $item->pengumpulan->kelompok ?? '');

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

            $jumlahAnggota = User::where('role', 'peserta')->where('kelompok', $k)->count();
            $itemsKelompok = $itemKolektifByKelompok->get($k, collect());

            if ($jumlahAnggota > 0) {
                foreach ($barangsIndividu as $b) {
                    $totalDibawa = $itemsKelompok->where('p3k_barang_kebutuhan_id', $b->id)->sum('jumlah_dibawa');
                    if ($totalDibawa >= $b->jumlah_kebutuhan * $jumlahAnggota) {
                        $lengkapIndividu++;
                    }
                }
            }

            $obatBelum = P3kObatPribadi::where('kelompok', $k)->where('sudah_diserahkan', false)->count();
            $obatTotal = P3kObatPribadi::where('kelompok', $k)->count();

            $summary[$k] = [
                'total'      => $totalKelompok + $totalIndividu,
                'lengkap'    => $lengkapKelompok + $lengkapIndividu,
                'obat_total' => $obatTotal,
                'obat_belum' => $obatBelum,
            ];
        }

        $stokIndividuByMenu = [];
        foreach (self::MENUS as $menu) {
            $barangsMenu = $barangsIndividu->where('menu', $menu)->values();
            if ($barangsMenu->isEmpty()) continue;

            $stokIndividuByMenu[$menu] = $barangsMenu->map(function ($b) {
                $global   = P3kStokIndividu::globalSummary($b->id);
                $stokGlob = P3kStokGlobal::firstOrCreate(['p3k_barang_kebutuhan_id' => $b->id]);
                $terpakai = $stokGlob->total_terpakai;
                $sisa     = max(0, $global['total_terkumpul'] - $terpakai);

                return [
                    'barang'          => $b,
                    'total_terkumpul' => $global['total_terkumpul'],
                    'total_terpakai'  => $terpakai,
                    'total_sisa'      => $sisa,
                ];
            })->filter(fn($s) => $s['total_terkumpul'] > 0)->values();
        }

        $stokIndividu = collect($stokIndividuByMenu)->flatten(1);

        return view('panitia.p3k.index', compact('kelompoks', 'summary', 'barangsKelompok', 'barangsIndividu', 'stokIndividu', 'stokIndividuByMenu'));
    }

    public function panitiaKelompok($kelompok)
    {
        $this->authorizeAnyPanitia();

        $menus   = self::MENUS;
        $anggota = User::where('role', 'peserta')->where('kelompok', $kelompok)->orderBy('name')->get();

        // ── Barang Kelompok grouped by menu ──
        $allBarangsKelompok = P3kBarangKebutuhan::kelompok()->where('aktif', true)
            ->orderBy('menu')->orderBy('nama_barang')->get();

        $dataKelompokByMenu = [];
        foreach ($menus as $menu) {
            $dataKelompokByMenu[$menu] = $allBarangsKelompok->where('menu', $menu)->values()->map(function ($b) use ($kelompok) {
                $p = P3kPengumpulanBarang::where('p3k_barang_kebutuhan_id', $b->id)
                    ->where('kelompok', $kelompok)->with('updatedBy')->first();
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
        }

        // ── Barang Individu grouped by menu ──
        $allBarangsIndividu = P3kBarangKebutuhan::individu()->where('aktif', true)
            ->orderBy('menu')->orderBy('nama_barang')->get();
        $barangsIndividuByMenu = [];
        foreach ($menus as $menu) {
            $barangsIndividuByMenu[$menu] = $allBarangsIndividu->where('menu', $menu)->values();
        }

        // ── Pengumpulan Kolektif grouped by menu ──
        $allPengumpulan = P3kPengumpulanKolektif::where('kelompok', $kelompok)
            ->withCount('anggota')
            ->with(['perwakilan', 'anggota.peserta', 'items.barang', 'updatedBy'])
            ->orderBy('menu')->orderBy('created_at')
            ->get();
        $pengumpulanByMenu = [];
        foreach ($menus as $menu) {
            $pengumpulanByMenu[$menu] = $allPengumpulan->where('menu', $menu)->values();
        }

        // ── Per menu: anggota belum tercakup & summary individu ──
        $anggotaBelumTercakupByMenu = [];
        $summaryIndividuByMenu = [];
        foreach ($menus as $menu) {
            $pengMenu       = $pengumpulanByMenu[$menu];
            $userIdTercakup = $pengMenu->flatMap(fn($p) => $p->anggota->pluck('user_id'));
            $anggotaBelumTercakupByMenu[$menu] = $anggota->reject(fn($p) => $userIdTercakup->contains($p->id))->values();

            $barangsMenu = $barangsIndividuByMenu[$menu];
            $summaryIndividuByMenu[$menu] = $barangsMenu->map(function ($b) use ($pengMenu, $anggota, $kelompok) {
                $totalKelompok  = $pengMenu->sum(fn($p) => $p->jumlahDibawaUntuk($b->id));
                $targetKelompok = $b->jumlah_kebutuhan * $anggota->count();
                $stok = P3kStokIndividu::where('p3k_barang_kebutuhan_id', $b->id)->where('kelompok', $kelompok)->first();
                return [
                    'barang'          => $b,
                    'total_kelompok'  => $totalKelompok,
                    'target_kelompok' => $targetKelompok,
                    'is_lengkap'      => $anggota->count() > 0 && $totalKelompok >= $targetKelompok,
                    'total_terkumpul' => $stok ? $stok->total_terkumpul : $totalKelompok,
                    'total_terpakai'  => $stok ? $stok->total_terpakai : 0,
                    'total_sisa'      => $stok ? $stok->total_sisa : $totalKelompok,
                ];
            });
        }

        $obatPribadi = P3kObatPribadi::where('kelompok', $kelompok)->with('peserta', 'pj')->get();

        return view('panitia.p3k.kelompok', compact(
            'kelompok', 'menus', 'anggota',
            'dataKelompokByMenu',
            'barangsIndividuByMenu', 'pengumpulanByMenu',
            'anggotaBelumTercakupByMenu', 'summaryIndividuByMenu',
            'obatPribadi'
        ));
    }

    // ACC barang KELOMPOK — hanya divisi yang sesuai menu atau admin
    public function toggleValidasi(Request $request, $barangId, $kelompok)
    {
        $barang = P3kBarangKebutuhan::findOrFail($barangId);
        $this->authorizeMenuAccess($barang->menu, $kelompok);

        $pengumpulan = P3kPengumpulanBarang::where('p3k_barang_kebutuhan_id', $barangId)
            ->where('kelompok', $kelompok)
            ->firstOrFail();

        $pengumpulan->is_validated = !$pengumpulan->is_validated;
        $pengumpulan->updated_by   = Auth::id();
        $pengumpulan->save();

        $statusPesan = $pengumpulan->is_validated ? 'berhasil di-ACC.' : 'batal di-ACC.';
        return back()->with('success', "Status progress barang {$statusPesan}");
    }

    // Update jumlah terpakai untuk barang KELOMPOK — hanya divisi menu atau admin
    public function updateTerpakai(Request $request, $barangId, $kelompok)
    {
        $barang = P3kBarangKebutuhan::findOrFail($barangId);
        $this->authorizeMenuAccess($barang->menu, $kelompok);

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
    // PANITIA: Validasi & update barang INDIVIDU per peserta
    // ─────────────────────────────────────────────────────────────

    // Update jumlah terpakai dari STOK barang individu — per kelompok
    public function updateStokTerpakai(Request $request, $barangId, $kelompok)
    {
        $barang = P3kBarangKebutuhan::findOrFail($barangId);
        $this->authorizeMenuAccess($barang->menu, $kelompok);

        $request->validate(['total_terpakai' => 'required|integer|min:0']);

        $stok = P3kStokIndividu::firstOrCreate([
            'p3k_barang_kebutuhan_id' => $barang->id,
            'kelompok'                => $kelompok,
        ]);

        if ($request->total_terpakai > $stok->total_terkumpul) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Melebihi total terkumpul.'], 422);
            }
            return back()->withErrors(['error' => 'Jumlah terpakai tidak boleh melebihi total terkumpul kelompok ini.']);
        }

        $stok->total_terpakai = $request->total_terpakai;
        $stok->updated_by = Auth::id();
        $stok->save();

        if ($request->expectsJson()) {
            return response()->json([
                'total_terkumpul' => $stok->total_terkumpul,
                'total_terpakai'  => $stok->total_terpakai,
                'total_sisa'      => $stok->total_sisa,
            ]);
        }

        return back()->with('success', "Stok '{$barang->nama_barang}' Kelompok {$kelompok} berhasil diperbarui.");
    }

    // Tambah/kurangi stok terpakai secara cepat (increment/decrement) — per kelompok
    public function adjustStokTerpakai(Request $request, $barangId, $kelompok)
    {
        $barang = P3kBarangKebutuhan::findOrFail($barangId);
        $this->authorizeMenuAccess($barang->menu, $kelompok);

        $request->validate(['delta' => 'required|integer']);

        $stok = P3kStokIndividu::firstOrCreate([
            'p3k_barang_kebutuhan_id' => $barang->id,
            'kelompok'                => $kelompok,
        ]);

        $baru = max(0, min($stok->total_terpakai + $request->delta, $stok->total_terkumpul));
        $stok->total_terpakai = $baru;
        $stok->updated_by = Auth::id();
        $stok->save();

        if ($request->expectsJson()) {
            return response()->json([
                'total_terkumpul' => $stok->total_terkumpul,
                'total_terpakai'  => $stok->total_terpakai,
                'total_sisa'      => $stok->total_sisa,
            ]);
        }

        return back()->with('success', "Stok '{$barang->nama_barang}' Kelompok {$kelompok}: {$baru} terpakai.");
    }

    // ── Global stok terpakai (tidak per-kelompok) — hanya divisi menu atau admin ──

    public function globalAdjustStok(Request $request, $barangId)
    {
        $barang = P3kBarangKebutuhan::findOrFail($barangId);
        $this->authorizeMenuAccess($barang->menu);

        $request->validate(['delta' => 'required|integer']);

        $stok   = P3kStokGlobal::firstOrCreate(['p3k_barang_kebutuhan_id' => $barangId]);
        $global = P3kStokIndividu::globalSummary($barangId);

        $baru = max(0, min($stok->total_terpakai + (int) $request->delta, $global['total_terkumpul']));
        $stok->total_terpakai = $baru;
        $stok->updated_by     = Auth::id();
        $stok->save();

        if ($request->expectsJson()) {
            return response()->json([
                'total_terkumpul' => $global['total_terkumpul'],
                'total_terpakai'  => $baru,
                'total_sisa'      => $global['total_terkumpul'] - $baru,
            ]);
        }

        return back()->with('success', "Stok '{$barang->nama_barang}': {$baru} terpakai.");
    }

    public function globalSetStok(Request $request, $barangId)
    {
        $barang = P3kBarangKebutuhan::findOrFail($barangId);
        $this->authorizeMenuAccess($barang->menu);

        $request->validate(['total_terpakai' => 'required|integer|min:0']);

        $stok   = P3kStokGlobal::firstOrCreate(['p3k_barang_kebutuhan_id' => $barangId]);
        $global = P3kStokIndividu::globalSummary($barangId);

        if ($request->total_terpakai > $global['total_terkumpul']) {
            if ($request->expectsJson()) return response()->json(['error' => 'Melebihi total terkumpul.'], 422);
            return back()->withErrors(['error' => 'Jumlah terpakai tidak boleh melebihi total terkumpul.']);
        }

        $stok->total_terpakai = $request->total_terpakai;
        $stok->updated_by     = Auth::id();
        $stok->save();

        if ($request->expectsJson()) {
            return response()->json([
                'total_terkumpul' => $global['total_terkumpul'],
                'total_terpakai'  => $stok->total_terpakai,
                'total_sisa'      => $global['total_terkumpul'] - $stok->total_terpakai,
            ]);
        }

        return back()->with('success', "Stok '{$barang->nama_barang}' berhasil diperbarui.");
    }

    // ACC pengumpulan kolektif barang individu — hanya divisi menu tersebut atau admin
    public function toggleValidasiKolektif(Request $request, $pengumpulanId)
    {
        $pengumpulan = P3kPengumpulanKolektif::findOrFail($pengumpulanId);
        $this->authorizeMenuAccess($pengumpulan->menu, $pengumpulan->kelompok);

        $pengumpulan->is_validated = !$pengumpulan->is_validated;
        $pengumpulan->updated_by = Auth::id();
        $pengumpulan->save();

        $statusPesan = $pengumpulan->is_validated ? 'berhasil di-ACC.' : 'batal di-ACC.';
        return back()->with('success', "Pengumpulan atas nama {$pengumpulan->perwakilan->name} {$statusPesan}");
    }

    // ─────────────────────────────────────────────────────────────
    // PANITIA P3K: Obat pribadi - tandai sudah diserahkan
    // Hanya divisi P3K atau admin (obat adalah domain P3K)
    // ─────────────────────────────────────────────────────────────

    public function obatToggleDiserahkan($id)
    {
        $obat = P3kObatPribadi::findOrFail($id);
        $this->authorizeMenuAccess('p3k', $obat->kelompok);

        $obat->sudah_diserahkan = !$obat->sudah_diserahkan;
        if ($obat->sudah_diserahkan) {
            $obat->pj_p3k_id = Auth::id();
        }
        $obat->save();

        return back()->with('success', 'Status obat pribadi diperbarui.');
    }

    // ─────────────────────────────────────────────────────────────
    // PANITIA: Rekap global (semua kelompok)
    // ─────────────────────────────────────────────────────────────

    public function panitiaRekap()
    {
        $this->authorizeAnyPanitia();

        $kelompoksData = User::where('role', 'peserta')->whereNotNull('kelompok')->distinct()->pluck('kelompok')->toArray();
        sort($kelompoksData, SORT_NATURAL | SORT_FLAG_CASE);
        $kelompoks = collect($kelompoksData);

        $menus = self::MENUS;

        $allBarangsKelompok = P3kBarangKebutuhan::kelompok()->where('aktif', true)->orderBy('menu')->orderBy('nama_barang')->get();
        $allBarangsIndividu = P3kBarangKebutuhan::individu()->where('aktif', true)->orderBy('menu')->orderBy('nama_barang')->get();

        $rekapKelompokByMenu = [];
        $pengumpulanByKelompokMenu = [];
        $anggotaBelumTercakupByKelompokMenu = [];
        $summaryIndividuByKelompokMenu = [];

        foreach ($kelompoks as $k) {
            $jumlahAnggota = User::where('role', 'peserta')->where('kelompok', $k)->orderBy('name')->get();

            foreach ($menus as $menu) {
                $barangsMenu = $allBarangsKelompok->where('menu', $menu)->values();
                $rekapKelompokByMenu[$k][$menu] = $barangsMenu->map(function ($b) use ($k) {
                    $p = P3kPengumpulanBarang::where('p3k_barang_kebutuhan_id', $b->id)->where('kelompok', $k)->first();
                    return ['barang' => $b, 'jumlah_terkumpul' => $p ? $p->jumlah_terkumpul : 0, 'is_lengkap' => $p && $p->jumlah_terkumpul >= $b->jumlah_kebutuhan];
                });
            }

            $allPengKelompok = P3kPengumpulanKolektif::where('kelompok', $k)
                ->withCount('anggota')->with(['perwakilan', 'anggota.peserta', 'items.barang', 'updatedBy'])
                ->orderBy('menu')->orderBy('created_at')->get();

            foreach ($menus as $menu) {
                $pengMenu = $allPengKelompok->where('menu', $menu)->values();
                $pengumpulanByKelompokMenu[$k][$menu] = $pengMenu;

                $userIdTercakup = $pengMenu->flatMap(fn($p) => $p->anggota->pluck('user_id'));
                $anggotaBelumTercakupByKelompokMenu[$k][$menu] = $jumlahAnggota->reject(fn($p) => $userIdTercakup->contains($p->id))->values();

                $barangsMenu  = $allBarangsIndividu->where('menu', $menu)->values();
                $countAnggota = $jumlahAnggota->count();
                $summaryIndividuByKelompokMenu[$k][$menu] = $barangsMenu->map(function ($b) use ($pengMenu, $countAnggota) {
                    $total  = $pengMenu->sum(fn($p) => $p->jumlahDibawaUntuk($b->id));
                    $target = $b->jumlah_kebutuhan * $countAnggota;
                    return ['barang' => $b, 'total_kelompok' => $total, 'target_kelompok' => $target, 'is_lengkap' => $countAnggota > 0 && $total >= $target];
                });
            }
        }

        $stokIndividuByMenu = [];
        foreach ($menus as $menu) {
            $stokIndividuByMenu[$menu] = $allBarangsIndividu->where('menu', $menu)->values()->map(function ($b) use ($kelompoks) {
                $global      = P3kStokIndividu::globalSummary($b->id);
                $stokRecords = P3kStokIndividu::where('p3k_barang_kebutuhan_id', $b->id)->get()->keyBy('kelompok');
                $perKelompok = $kelompoks->map(function ($k) use ($stokRecords) {
                    $s = $stokRecords->get($k);
                    return [
                        'kelompok'        => $k,
                        'total_terkumpul' => $s ? $s->total_terkumpul : 0,
                        'total_terpakai'  => $s ? $s->total_terpakai : 0,
                        'total_sisa'      => $s ? $s->total_sisa : 0,
                    ];
                })->filter(fn($r) => $r['total_terkumpul'] > 0)->values();

                return array_merge(['barang' => $b, 'per_kelompok' => $perKelompok], $global);
            });
        }

        $obatPribadi = P3kObatPribadi::with('peserta', 'pj')->orderBy('kelompok')->get();

        return view('panitia.p3k.rekap', compact(
            'kelompoks', 'menus', 'allBarangsKelompok', 'allBarangsIndividu',
            'rekapKelompokByMenu', 'pengumpulanByKelompokMenu',
            'anggotaBelumTercakupByKelompokMenu', 'summaryIndividuByKelompokMenu',
            'stokIndividuByMenu', 'obatPribadi'
        ));
    }

    public function exportRekap()
    {
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

        $sheet1 = $spreadsheet->createSheet();
        $sheet1->setTitle('Barang Kelompok');
        $this->writeBarangSheet($sheet1, $kelompoks, $barangsKelompok, $navyHex, $tealHex, $greenHex, $redHex, $whiteHex, false);

        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Barang Individu');
        $this->writeBarangIndividuSheet($sheet2, $kelompoks, $barangsIndividu, $navyHex, $tealHex, $greenHex, $redHex, $whiteHex);

        $sheetStok = $spreadsheet->createSheet();
        $sheetStok->setTitle('Stok Global Individu');
        $this->writeStokGlobalSheet($sheetStok, $barangsIndividu, $navyHex, $tealHex, $greenHex, $redHex, $whiteHex);

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

    private function writeBarangIndividuSheet($sheet, $kelompoks, $barangsIndividu, $navyHex, $tealHex, $greenHex, $redHex, $whiteHex)
    {
        $row = 1;
        $lastColIndex = 2 + $barangsIndividu->count();
        $lastCol = chr(65 + $lastColIndex);

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

            $headerLabels = ['Perwakilan & Anggota Dititipkan', 'Jml Anggota'];
            foreach ($barangsIndividu as $b) {
                $headerLabels[] = $b->nama_barang . ' (' . $b->jumlah_kebutuhan . ' ' . $b->satuan . '/orang)';
            }
            $headerLabels[] = 'Status ACC';

            foreach ($headerLabels as $i => $h) {
                $col = chr(65 + $i);
                $sheet->setCellValue("{$col}{$row}", $h);
                $sheet->getStyle("{$col}{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => $navyHex], 'name' => 'Arial', 'size' => 9],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $tealHex]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $navyHex]]],
                ]);
            }
            $row++;

            $anggotaKelompok = User::where('role', 'peserta')->where('kelompok', $kelompok)->orderBy('name')->get();

            $pengumpulanKelompokIni = P3kPengumpulanKolektif::where('kelompok', $kelompok)
                ->withCount('anggota')
                ->with(['perwakilan', 'anggota.peserta', 'items', 'updatedBy'])
                ->orderBy('created_at')
                ->get();

            $totalPerBarang = array_fill(0, $barangsIndividu->count(), 0);

            foreach ($pengumpulanKelompokIni as $p) {
                $namaAnggotaLain = $p->anggota->pluck('peserta.name')
                    ->reject(fn($n) => $n === $p->perwakilan->name)
                    ->implode(', ');

                $labelPerwakilan = $p->perwakilan->name
                    . ($namaAnggotaLain !== '' ? " (mewakili: {$namaAnggotaLain})" : ' (mengumpulkan sendiri)');

                $sheet->setCellValue("A{$row}", $labelPerwakilan);
                $sheet->getStyle("A{$row}")->applyFromArray([
                    'font' => ['name' => 'Arial', 'size' => 9, 'bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'cccccc']]],
                ]);

                $sheet->setCellValue("B{$row}", $p->jumlah_anggota);
                $sheet->getStyle("B{$row}")->applyFromArray([
                    'font' => ['name' => 'Arial', 'size' => 9],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'cccccc']]],
                ]);

                foreach ($barangsIndividu as $i => $b) {
                    $col    = chr(67 + $i);
                    $dibawa = $p->jumlahDibawaUntuk($b->id);
                    $target = $p->targetUntuk($b);
                    $lengkap = $dibawa >= $target;
                    $bg = $lengkap ? $greenHex : ($dibawa > 0 ? 'fff3cd' : $redHex);

                    $totalPerBarang[$i] += $dibawa;

                    $sheet->setCellValue("{$col}{$row}", "{$dibawa}/{$target}");
                    $sheet->getStyle("{$col}{$row}")->applyFromArray([
                        'font' => ['name' => 'Arial', 'size' => 9],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'cccccc']]],
                    ]);
                }

                $colStatus  = chr(67 + $barangsIndividu->count());
                $statusText = $p->is_validated
                    ? 'Sudah ACC' . ($p->updatedBy ? " (oleh {$p->updatedBy->name})" : '')
                    : 'Belum ACC';
                $sheet->setCellValue("{$colStatus}{$row}", $statusText);
                $sheet->getStyle("{$colStatus}{$row}")->applyFromArray([
                    'font' => ['name' => 'Arial', 'size' => 9, 'bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $p->is_validated ? $greenHex : $redHex]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'cccccc']]],
                ]);

                $row++;
            }

            $sheet->setCellValue("A{$row}", "TOTAL KELOMPOK $kelompok");
            $sheet->mergeCells("A{$row}:B{$row}");
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => $navyHex], 'name' => 'Arial', 'size' => 9],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $tealHex]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $navyHex]]],
            ]);
            foreach ($barangsIndividu as $i => $b) {
                $col    = chr(67 + $i);
                $target = $b->jumlah_kebutuhan * $anggotaKelompok->count();
                $sheet->setCellValue("{$col}{$row}", "{$totalPerBarang[$i]}/{$target}");
                $sheet->getStyle("{$col}{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => $navyHex], 'name' => 'Arial', 'size' => 9],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $tealHex]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $navyHex]]],
                ]);
            }
            $row++;

            $userIdTercakup = $pengumpulanKelompokIni->flatMap(fn($p) => $p->anggota->pluck('user_id'));
            $belumTercakup  = $anggotaKelompok->reject(fn($p) => $userIdTercakup->contains($p->id))->pluck('name')->implode(', ');

            $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
            $sheet->setCellValue("A{$row}", 'Belum Tercakup Pengumpulan: ' . ($belumTercakup !== '' ? $belumTercakup : '— Semua anggota sudah tercakup —'));
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => ['italic' => true, 'color' => ['rgb' => $belumTercakup !== '' ? '991b1b' : '166534'], 'name' => 'Arial', 'size' => 9],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $belumTercakup !== '' ? $redHex : $greenHex]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'wrapText' => true],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'cccccc']]],
            ]);
            $row++;

            $row += 2;
        }

        $sheet->getColumnDimension('A')->setWidth(36);
        $sheet->getColumnDimension('B')->setWidth(12);
        for ($i = 0; $i < $barangsIndividu->count(); $i++) {
            $col = chr(67 + $i);
            $sheet->getColumnDimension($col)->setWidth(16);
        }
        $sheet->getColumnDimension($lastCol)->setWidth(14);
    }

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
            $stok      = P3kStokIndividu::where('p3k_barang_kebutuhan_id', $b->id)->first();
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

        $pjId = P3kPjKelompok::pjUntukKelompok($kelompok);
        $pj   = $pjId ? User::find($pjId) : null;

        $allBarangsKelompok = P3kBarangKebutuhan::kelompok()->where('aktif', true)
            ->orderBy('menu')->orderBy('nama_barang')->get();
        $dataKelompokByMenu = [];
        foreach (self::MENUS as $menu) {
            $dataKelompokByMenu[$menu] = $allBarangsKelompok->where('menu', $menu)->values()->map(function ($b) use ($kelompok) {
                $p = P3kPengumpulanBarang::where('p3k_barang_kebutuhan_id', $b->id)->where('kelompok', $kelompok)->first();
                return [
                    'barang'          => $b,
                    'pengumpulan'     => $p,
                    'jumlah_terkumpul'=> $p ? $p->jumlah_terkumpul : 0,
                    'foto_url'        => $p && $p->foto_bukti ? Storage::url($p->foto_bukti) : null,
                    'is_lengkap'      => $p && $p->jumlah_terkumpul >= $b->jumlah_kebutuhan,
                    'is_validated'    => $p ? $p->is_validated : false,
                    'updated_by_name' => $p && $p->updatedBy ? $p->updatedBy->name : null,
                    'updated_at'      => $p ? $p->updated_at : null,
                ];
            });
        }

        $allBarangsIndividu = P3kBarangKebutuhan::individu()->where('aktif', true)
            ->orderBy('menu')->orderBy('nama_barang')->get();

        $pengumpulanSayaByMenu  = [];
        $isPerwakilanSayaByMenu = [];
        $dataIndividuByMenu     = [];

        foreach (self::MENUS as $menu) {
            $anggotaSaya  = P3kPengumpulanKolektifAnggota::where('user_id', $user->id)->where('menu', $menu)->first();
            $pengumpulan  = null;
            $isPerwakilan = false;

            if ($anggotaSaya) {
                $pengumpulan = P3kPengumpulanKolektif::withCount('anggota')
                    ->with(['perwakilan', 'items'])
                    ->find($anggotaSaya->pengumpulan_kolektif_id);
                $isPerwakilan = $pengumpulan && $pengumpulan->perwakilan_user_id === $user->id;
            }

            $pengumpulanSayaByMenu[$menu]  = $pengumpulan;
            $isPerwakilanSayaByMenu[$menu] = $isPerwakilan;

            $barangsMenu = $allBarangsIndividu->where('menu', $menu)->values();
            $dataIndividuByMenu[$menu] = $barangsMenu->map(function ($b) use ($pengumpulan) {
                $dibawa = $pengumpulan ? $pengumpulan->jumlahDibawaUntuk($b->id) : 0;
                $target = $pengumpulan ? $pengumpulan->targetUntuk($b) : $b->jumlah_kebutuhan;
                return [
                    'barang'        => $b,
                    'jumlah_dibawa' => $dibawa,
                    'target'        => $target,
                    'is_lengkap'    => $pengumpulan !== null && $dibawa >= $target,
                    'is_validated'  => $pengumpulan ? $pengumpulan->is_validated : false,
                ];
            });
        }

        $menus          = self::MENUS;
        $menuLabels     = self::MENU_LABELS;
        $obatPribadiSaya = P3kObatPribadi::where('user_id', $user->id)->get();

        return view('peserta.p3k', compact(
            'kelompok', 'menus', 'menuLabels',
            'dataKelompokByMenu',
            'pengumpulanSayaByMenu', 'isPerwakilanSayaByMenu', 'dataIndividuByMenu',
            'pj', 'obatPribadiSaya'
        ));
    }

    public function pesertaUpdateKelompok(Request $request, $barangId)
    {
        $request->validate([
            'jumlah_terkumpul' => 'required|integer|min:0',
            'foto_bukti'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        $user   = Auth::user();
        $barang = P3kBarangKebutuhan::findOrFail($barangId);

        if ((int) $request->jumlah_terkumpul > $barang->jumlah_kebutuhan) {
            return back()->withErrors(['error' =>
                "Jumlah {$barang->nama_barang} tidak boleh melebihi target ({$barang->jumlah_kebutuhan} {$barang->satuan})."
            ])->withInput();
        }

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
            $pengumpulan->foto_bukti   = null;
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

    // ─────────────────────────────────────────────────────────────
    // PESERTA: Pengumpulan Kolektif barang INDIVIDU
    // ─────────────────────────────────────────────────────────────

    public function pesertaIndividuForm(string $menu)
    {
        $this->validasiMenu($menu);
        $user     = Auth::user();
        $kelompok = $user->kelompok;

        $barangsIndividu = P3kBarangKebutuhan::individu()->where('menu', $menu)->where('aktif', true)
            ->orderBy('nama_barang')->get();

        $anggotaSaya  = P3kPengumpulanKolektifAnggota::where('user_id', $user->id)->where('menu', $menu)->first();
        $pengumpulan  = null;
        $isPerwakilan = false;

        if ($anggotaSaya) {
            $pengumpulan = P3kPengumpulanKolektif::withCount('anggota')
                ->with(['perwakilan', 'anggota.peserta', 'items.barang'])
                ->find($anggotaSaya->pengumpulan_kolektif_id);
            $isPerwakilan = $pengumpulan && $pengumpulan->perwakilan_user_id === $user->id;
        }

        $rekanSekelompok = User::where('role', 'peserta')
            ->where('kelompok', $kelompok)->where('id', '!=', $user->id)->orderBy('name')->get();

        $userIdTercakupMenu = P3kPengumpulanKolektifAnggota::where('menu', $menu)->pluck('user_id');
        $idAnggotaSaya      = $pengumpulan ? $pengumpulan->anggota->pluck('user_id') : collect();

        $kandidatChecklist = $rekanSekelompok->filter(function ($p) use ($userIdTercakupMenu, $idAnggotaSaya) {
            return !$userIdTercakupMenu->contains($p->id) || $idAnggotaSaya->contains($p->id);
        })->values();

        $tercakupDiLain = $rekanSekelompok->filter(function ($p) use ($userIdTercakupMenu, $idAnggotaSaya) {
            return $userIdTercakupMenu->contains($p->id) && !$idAnggotaSaya->contains($p->id);
        })->values();

        $menuLabel = self::MENU_LABELS[$menu] ?? $menu;

        return view('peserta.p3k-individu', compact(
            'menu', 'menuLabel', 'kelompok', 'barangsIndividu',
            'pengumpulan', 'isPerwakilan', 'kandidatChecklist', 'tercakupDiLain'
        ));
    }

    public function pesertaIndividuStore(Request $request, string $menu)
    {
        $this->validasiMenu($menu);
        $user = Auth::user();

        $request->validate([
            'anggota_ids'     => 'nullable|array',
            'anggota_ids.*'   => 'integer|exists:users,id',
            'jumlah_dibawa'   => 'nullable|array',
            'jumlah_dibawa.*' => 'integer|min:0',
            'foto_bukti'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        $pengumpulan = P3kPengumpulanKolektif::where('perwakilan_user_id', $user->id)->where('menu', $menu)->first();

        if (!$pengumpulan) {
            $sudahJadiAnggota = P3kPengumpulanKolektifAnggota::where('user_id', $user->id)->where('menu', $menu)->exists();
            if ($sudahJadiAnggota) {
                return back()->withErrors(['error' => 'Anda sudah dititipkan ke pengumpulan lain di menu ini.']);
            }
        } elseif ($pengumpulan->is_validated) {
            return back()->withErrors(['error' => 'Pengumpulan Anda sudah di-ACC oleh panitia, tidak dapat diubah lagi.']);
        }

        $anggotaIdsDicheck = collect($request->input('anggota_ids', []))
            ->map(fn($v) => (int)$v)->filter(fn($v) => $v !== $user->id)->unique()->values();

        if ($anggotaIdsDicheck->isNotEmpty()) {
            $valid = User::where('role', 'peserta')->where('kelompok', $user->kelompok)
                ->whereIn('id', $anggotaIdsDicheck)->count();
            if ($valid !== $anggotaIdsDicheck->count()) {
                return back()->withErrors(['error' => 'Ada nama yang dipilih tidak valid atau bukan dari kelompok Anda.']);
            }
            $pengumpulanIdSaya = $pengumpulan?->id;
            $bentrok = P3kPengumpulanKolektifAnggota::whereIn('user_id', $anggotaIdsDicheck)
                ->where('menu', $menu)
                ->when($pengumpulanIdSaya, fn($q) => $q->where('pengumpulan_kolektif_id', '!=', $pengumpulanIdSaya))
                ->with('peserta')->get();
            if ($bentrok->isNotEmpty()) {
                $nama = $bentrok->pluck('peserta.name')->filter()->implode(', ');
                return back()->withErrors(['error' => "Sudah terdaftar di pengumpulan lain untuk menu ini: {$nama}."]);
            }
        }

        $barangsIndividu   = P3kBarangKebutuhan::individu()->where('menu', $menu)->where('aktif', true)->get();
        $jumlahAnggotaBaru = $anggotaIdsDicheck->count() + 1;

        $pesanKelebihan = [];
        foreach ($barangsIndividu as $b) {
            $input = (int)($request->input('jumlah_dibawa', [])[$b->id] ?? 0);
            $maks  = $b->jumlah_kebutuhan * $jumlahAnggotaBaru;
            if ($input > $maks) {
                $pesanKelebihan[] = "{$b->nama_barang} (maks. {$maks} {$b->satuan})";
            }
        }
        if (!empty($pesanKelebihan)) {
            return back()->withErrors(['error' => 'Jumlah dibawa melebihi target: ' . implode('; ', $pesanKelebihan) . '.'])->withInput();
        }

        DB::transaction(function () use (&$pengumpulan, $user, $menu, $anggotaIdsDicheck, $barangsIndividu, $request) {
            if (!$pengumpulan) {
                $pengumpulan = P3kPengumpulanKolektif::create([
                    'perwakilan_user_id' => $user->id,
                    'kelompok'           => $user->kelompok,
                    'menu'               => $menu,
                ]);
            }

            $idsSeharusnya = $anggotaIdsDicheck->concat([$user->id])->unique()->values();
            $pengumpulan->anggota()->whereNotIn('user_id', $idsSeharusnya)->delete();
            $idsSudahAda = $pengumpulan->anggota()->pluck('user_id');
            foreach ($idsSeharusnya->diff($idsSudahAda) as $uid) {
                $pengumpulan->anggota()->create(['user_id' => $uid, 'menu' => $menu]);
            }

            $jumlahInput = $request->input('jumlah_dibawa', []);
            foreach ($barangsIndividu as $b) {
                P3kPengumpulanKolektifItem::updateOrCreate(
                    ['pengumpulan_kolektif_id' => $pengumpulan->id, 'p3k_barang_kebutuhan_id' => $b->id],
                    ['jumlah_dibawa' => (int)($jumlahInput[$b->id] ?? 0)]
                );
            }

            if ($request->hasFile('foto_bukti')) {
                if ($pengumpulan->foto_bukti) Storage::disk('public')->delete($pengumpulan->foto_bukti);
                $pengumpulan->foto_bukti = $request->file('foto_bukti')->store("p3k-individu-bukti/{$menu}", 'public');
            }

            $pengumpulan->updated_by = $user->id;
            $pengumpulan->save();
        });

        foreach ($barangsIndividu as $b) {
            P3kStokIndividu::recalcTerkumpul($b->id, $user->kelompok);
        }

        return redirect()->route('peserta.p3k.individu', $menu)->with('success', 'Pengumpulan barang individu berhasil disimpan.');
    }

    public function pesertaIndividuHapusFoto(string $menu)
    {
        $this->validasiMenu($menu);
        $user        = Auth::user();
        $pengumpulan = P3kPengumpulanKolektif::where('perwakilan_user_id', $user->id)->where('menu', $menu)->first();

        if (!$pengumpulan)              return back()->withErrors(['error' => 'Anda belum memiliki pengumpulan.']);
        if ($pengumpulan->is_validated) return back()->withErrors(['error' => 'Pengumpulan sudah di-ACC, tidak dapat diubah.']);

        if ($pengumpulan->foto_bukti) {
            Storage::disk('public')->delete($pengumpulan->foto_bukti);
            $pengumpulan->foto_bukti = null;
            $pengumpulan->save();
        }

        return back()->with('success', 'Foto bukti berhasil dihapus.');
    }

    public function pesertaIndividuKeluar(string $menu)
    {
        $this->validasiMenu($menu);
        $user    = Auth::user();
        $anggota = P3kPengumpulanKolektifAnggota::where('user_id', $user->id)->where('menu', $menu)->first();

        if (!$anggota) return back()->withErrors(['error' => 'Anda belum terdaftar di pengumpulan manapun untuk menu ini.']);

        $pengumpulan = $anggota->pengumpulan;

        if ($pengumpulan->perwakilan_user_id === $user->id) {
            return back()->withErrors(['error' => 'Anda adalah perwakilan. Gunakan tombol "Bubarkan Pengumpulan" jika ingin keluar.']);
        }
        if ($pengumpulan->is_validated) {
            return back()->withErrors(['error' => 'Pengumpulan ini sudah di-ACC. Hubungi panitia untuk pindah.']);
        }

        $kelompok = $pengumpulan->kelompok;
        $anggota->delete();

        foreach (P3kBarangKebutuhan::individu()->where('menu', $menu)->where('aktif', true)->pluck('id') as $barangId) {
            P3kStokIndividu::recalcTerkumpul($barangId, $kelompok);
        }

        return redirect()->route('peserta.p3k.individu', $menu)->with('success', 'Anda berhasil keluar dari pengumpulan tersebut.');
    }

    public function pesertaIndividuBubarkan(string $menu)
    {
        $this->validasiMenu($menu);
        $user        = Auth::user();
        $pengumpulan = P3kPengumpulanKolektif::where('perwakilan_user_id', $user->id)->where('menu', $menu)->first();

        if (!$pengumpulan)              return back()->withErrors(['error' => 'Anda belum memiliki pengumpulan untuk menu ini.']);
        if ($pengumpulan->is_validated) return back()->withErrors(['error' => 'Pengumpulan sudah di-ACC, tidak dapat dibubarkan. Hubungi panitia.']);

        $kelompok = $pengumpulan->kelompok;
        if ($pengumpulan->foto_bukti) Storage::disk('public')->delete($pengumpulan->foto_bukti);
        $pengumpulan->delete();

        foreach (P3kBarangKebutuhan::individu()->where('menu', $menu)->where('aktif', true)->pluck('id') as $barangId) {
            P3kStokIndividu::recalcTerkumpul($barangId, $kelompok);
        }

        return redirect()->route('peserta.p3k.individu', $menu)->with('success', 'Pengumpulan berhasil dibubarkan.');
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
     * Hanya admin yang boleh mengelola master data barang
     * (tambah, edit, hapus dari halaman Kelola Barang).
     */
    private function authorizeAdmin(): void
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Hanya admin yang dapat mengelola master data barang.');
        }
    }

    /**
     * Semua panitia (role = 'panitia' atau 'admin') boleh mengakses
     * halaman view-only (index, kelompok, rekap, export).
     */
    private function authorizeAnyPanitia(): void
    {
        if (Auth::user()->role === 'peserta') {
            abort(403, 'Akses ditolak.');
        }
    }

    /**
     * Untuk aksi write pada suatu menu (validasi, ACC, update stok):
     * - Admin: boleh semua menu & semua kelompok.
     * - Divisi Logistik: hanya menu 'logistik'.
     * - Divisi Konsumsi: hanya menu 'konsumsi'.
     * - Divisi P3K: hanya menu 'p3k'.
     *
     * Jika $kelompok diberikan, juga dicek bahwa user adalah PJ
     * untuk kelompok tersebut (kecuali admin).
     *
     * @param  string       $menu     'logistik' | 'konsumsi' | 'p3k'
     * @param  string|null  $kelompok Opsional; jika ada, validasi PJ binaan juga dicek.
     */
    private function authorizeMenuAccess(string $menu, ?string $kelompok = null): void
    {
        $user = Auth::user();

        // Admin: akses penuh
        if ($user->role === 'admin') {
            return;
        }

        // Pastikan divisi user sesuai dengan menu yang diakses
        $divisiYangDibutuhkan = self::MENU_DIVISI[$menu] ?? null;
        $divisiUser           = strtoupper($user->divisi ?? '');

        if (!$divisiYangDibutuhkan || $divisiUser !== $divisiYangDibutuhkan) {
            abort(403, "Divisi {$divisiUser} tidak berwenang mengelola menu {$menu}.");
        }

        // Jika ada kelompok, cek PJ binaan
        if ($kelompok !== null) {
            $binaan = P3kPjKelompok::kelompokUntukPj($user->id);
            if (!empty($binaan) && !in_array($kelompok, $binaan)) {
                abort(403, 'Anda bukan PJ untuk kelompok ini.');
            }
        }
    }
}