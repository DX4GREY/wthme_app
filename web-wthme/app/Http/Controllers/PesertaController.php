<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AbsensiPeserta;
use App\Models\RiwayatPenyakit;
use App\Models\TugasKategori;
use App\Models\TugasPengumpulan;
use App\Models\BarangKebutuhan;
use App\Models\PengumpulanBarang;
use App\Models\CaptureMoment;
use App\Models\PersonalBroadcastRecipient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PesertaController extends Controller
{
    /**
     * Menampilkan Dashboard Utama Sisi Peserta + Akumulasi Poin & Peringkat Personal
     */
    public function index()
    {
        $user = auth()->user();
        
        // 1. DATA VALIDASI FORM DASHBOARD
        $sudahIsiRiwayat = RiwayatPenyakit::where('user_id', $user->id)->exists();
        $tugasAktif      = TugasKategori::where('aktif', true)->count();
        $sudahKumpul     = TugasPengumpulan::where('user_id', $user->id)->count();
        $tugasBelum      = max(0, $tugasAktif - $sudahKumpul);

        $idBarangAktif    = BarangKebutuhan::where('aktif', true)->pluck('id');
        $totalBarangAktif = $idBarangAktif->count();

        $barangLengkap = PengumpulanBarang::where('kelompok', $user->kelompok)
            ->whereIn('barang_kebutuhan_id', $idBarangAktif)
            ->whereHas('barang', function ($query) {
                $query->whereColumn('pengumpulan_barang.jumlah_terkumpul', '>=', 'barang_kebutuhan.jumlah_kebutuhan');
            })
            ->count();

        $barangBelum = max(0, $totalBarangAktif - $barangLengkap);
        $links       = \App\Models\Link::all();
        $pengumuman  = \App\Models\InformasiPeserta::latest()->get();
        $personalBroadcasts = collect();

        try {
            if (Schema::hasTable('personal_broadcasts') && Schema::hasTable('personal_broadcast_recipients')) {
                $personalBroadcasts = PersonalBroadcastRecipient::where('user_id', $user->id)
                    ->with('broadcast')
                    ->latest()
                    ->get()
                    ->groupBy('personal_broadcast_id')
                    ->filter(function ($group) {
                        return !$group->contains(function ($recipient) {
                            return !is_null($recipient->viewed_at);
                        });
                    })
                    ->map(function ($group) {
                        return $group->first();
                    })
                    ->values();
            }
        } catch (\Throwable $e) {
            report($e);
            $personalBroadcasts = collect();
        }

        // =========================================================================
        // LOGIKA PERHITUNGAN TOTAL POIN REALTIME UNTUK SELURUH PESERTA (DAPAT RANKING)
        // =========================================================================
        
        $allPesertas = User::where('role', 'peserta')->get();
        $rankList = [];
        $totalPoin = 0; // Default penampung poin user login

        // 🟢 POIN CAPTURE MOMENT — per kelompok (sudah dinilai panitia saja yang punya poin)
        $poinCaptureMomentPerKelompok = CaptureMoment::whereNotNull('poin')
            ->pluck('poin', 'kelompok');

        foreach ($allPesertas as $peserta) {
            // Aspek 1: Absensi (+300 poin per hadir)
            $jumlahHadir = AbsensiPeserta::where('user_id', $peserta->id)
                ->where('status', 'hadir')
                ->count();
            $poinAbsen = $jumlahHadir * 300;

            // Aspek 2: Keaktifan Acara
            $poinKeaktifan = DB::table('poin_keaktifan')
                ->where('peserta_id', $peserta->id)
                ->sum('poin') ?? 0;

            // Aspek 3: Tugas (Bonus 12j & Penalti 24j)
            $kumpulanTugas = TugasPengumpulan::where('user_id', $peserta->id)->get();
            $poinTugas = 0;

            foreach ($kumpulanTugas as $kumpul) {
                $indukTugas = TugasKategori::find($kumpul->tugas_kategori_id);
                if ($indukTugas) {
                    $basePoin = 80;
                    $waktuDeadline = $indukTugas->deadline ? strtotime($indukTugas->deadline) : null;
                    $waktuKumpul   = strtotime($kumpul->dikumpulkan_at ?? $kumpul->created_at);

                    if ($waktuDeadline) {
                        if ($waktuKumpul <= $waktuDeadline) {
                            $sisaWaktuJam = ($waktuDeadline - $waktuKumpul) / 3600;
                            $kelipatanBonus = floor($sisaWaktuJam / 12);
                            $poinTugas += ($basePoin + ($kelipatanBonus * 10));
                        } else {
                            $waktuTerlambatJam = ($waktuKumpul - $waktuDeadline) / 3600;
                            $kelipatanPenalti = floor($waktuTerlambatJam / 24);
                            if ($kelipatanPenalti == 0) $kelipatanPenalti = 1; 
                            
                            $skorAkhir = $basePoin - ($kelipatanPenalti * 5);
                            $poinTugas += ($skorAkhir < 10) ? 10 : $skorAkhir;
                        }
                    } else {
                        $poinTugas += $basePoin;
                    }
                }
            }

            // Aspek 4: 🟢 Capture Moment — diwariskan ke semua anggota kelompoknya
            $poinCapture = $poinCaptureMomentPerKelompok[$peserta->kelompok] ?? 0;

            $totalSkor = $poinAbsen + $poinKeaktifan + $poinTugas + $poinCapture;

            // Simpan data ke array untuk disorting
            $rankList[] = [
                'user_id' => $peserta->id,
                'total'   => $totalSkor
            ];

            // Jika loop sedang memproses user yang login, ikat nilainya ke $totalPoin
            if ($peserta->id === $user->id) {
                $totalPoin = $totalSkor;
            }
        }

        // Urutkan seluruh peserta berdasarkan total skor tertinggi
        usort($rankList, function ($a, $b) {
            return $b['total'] <=> $a['total'];
        });

        // Cari posisi index ke berapa user_id yang sedang login berada di dalam array list
        $myRank = 0;
        foreach ($rankList as $index => $item) {
            if ($item['user_id'] === $user->id) {
                $myRank = $index + 1; // Ditambah 1 karena array dimulai dari angka 0
                break;
            }
        }

        return view('peserta.index', compact(
            'user', 
            'sudahIsiRiwayat', 
            'tugasAktif', 
            'tugasBelum', 
            'barangBelum', 
            'links', 
            'pengumuman',
            'personalBroadcasts',
            'totalPoin',
            'myRank' // Variabel peringkat siap dipakai di Blade
        ));
    }

    /**
     * Menampilkan Form Riwayat Penyakit Peserta
     */
    public function markPersonalBroadcastViewed($id)
    {
        if (!Schema::hasTable('personal_broadcasts') || !Schema::hasTable('personal_broadcast_recipients')) {
            return response()->json(['success' => true]);
        }

        PersonalBroadcastRecipient::where('user_id', auth()->id())
            ->where('personal_broadcast_id', $id)
            ->whereNull('viewed_at')
            ->update(['viewed_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function riwayatPenyakit()
    {
        $user = auth()->user();
        $data = RiwayatPenyakit::where('user_id', $user->id)->first();
        return view('peserta.riwayat-penyakit', compact('user', 'data'));
    }

    /**
     * Menyimpan / Memperbarui Form Riwayat Kesehatan Peserta
     */
    public function simpanRiwayat(Request $request)
    {
        $request->validate([
            'no_telp_ortu'        => 'required|string|max:20',
            'alamat_rumah'        => 'required|string',
            'riwayat_penyakit'    => 'required|string',
            'obat_rutin'          => 'required|string',
            'riwayat_cedera'      => 'required|string',
            'alergi_makanan'      => 'required|string',
            'keterangan_tambahan' => 'required|string',
            'bukti_kesehatan'     => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $user = auth()->user();
        $riwayat = RiwayatPenyakit::where('user_id', $user->id)->first();

        $dataInput = [
            'nama'                => $user->name,
            'nim'                 => $user->nim,
            'kelompok'            => $user->kelompok,
            'no_telp'             => $request->no_telp,
            'no_telp_ortu'        => $request->no_telp_ortu,
            'alamat_rumah'        => $request->alamat_rumah,
            'riwayat_penyakit'    => $request->riwayat_penyakit,
            'alergi'              => $request->alergi ?? '-', 
            'obat_rutin'          => $request->obat_rutin,
            'riwayat_cedera'      => $request->riwayat_cedera,
            'alergi_makanan'      => $request->alergi_makanan,
            'keterangan_tambahan' => $request->keterangan_tambahan,
        ];

        if ($request->hasFile('bukti_kesehatan')) {
            if ($riwayat && $riwayat->bukti_kesehatan) {
                Storage::disk('public')->delete($riwayat->bukti_kesehatan);
            }
            $path = $request->file('bukti_kesehatan')->store('bukti_kesehatan', 'public');
            $dataInput['bukti_kesehatan'] = $path;
        }

        RiwayatPenyakit::updateOrCreate(
            ['user_id' => $user->id],
            $dataInput
        );

        return redirect()->route('peserta.index')
            ->with('success', 'Data riwayat kesehatan berhasil disimpan!');
    }
}