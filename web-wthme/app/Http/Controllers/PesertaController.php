<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AbsensiPeserta;
use App\Models\RiwayatPenyakit;
use App\Models\TugasKategori;
use App\Models\TugasPengumpulan;
use App\Models\BarangKebutuhan;
use App\Models\PengumpulanBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PesertaController extends Controller
{
    /**
     * Menampilkan Dashboard Utama Sisi Peserta + Akumulasi Poin Personal
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

        // =========================================================================
        // LOGIKA PERHITUNGAN TOTAL POIN REALTIME UNTUK PESERTA YANG SEDANG LOGIN
        // =========================================================================
        
        // Aspek 1: Perhitungan Absensi (+100 poin per hadir sesuai Leaderboard terbaru)
        $jumlahHadir = AbsensiPeserta::where('user_id', $user->id)
            ->where('status', 'hadir')
            ->count();
        $poinAbsen = $jumlahHadir * 300;

        // Aspek 2: Perhitungan Keaktifan Acara
        $poinKeaktifan = DB::table('poin_keaktifan')
            ->where('peserta_id', $user->id)
            ->sum('poin') ?? 0;

        // Aspek 3: Perhitungan Kecepatan Pengumpulan Tugas (Sistem Bonus 12j & Penalti 24j)
        $kumpulanTugas = TugasPengumpulan::where('user_id', $user->id)->get();
        $poinTugas = 0;

        foreach ($kumpulanTugas as $kumpul) {
            $indukTugas = TugasKategori::find($kumpul->tugas_kategori_id);
            
            if ($indukTugas) {
                $basePoin = 80; // Poin dasar
                
                $waktuDeadline = $indukTugas->deadline ? strtotime($indukTugas->deadline) : null;
                $waktuKumpul   = strtotime($kumpul->dikumpulkan_at ?? $kumpul->created_at);

                if ($waktuDeadline) {
                    if ($waktuKumpul <= $waktuDeadline) {
                        // KONDISI SEBELUM DEADLINE (SISTEM BONUS PER 12 JAM)
                        $sisaWaktuJam = ($waktuDeadline - $waktuKumpul) / 3600;
                        $kelipatanBonus = floor($sisaWaktuJam / 12);
                        $bonusPoin = $kelipatanBonus * 10;
                        
                        $poinTugas += ($basePoin + $bonusPoin);
                    } else {
                        // KONDISI SETELAH DEADLINE (SISTEM PENALTI PER 24 JAM)
                        $waktuTerlambatJam = ($waktuKumpul - $waktuDeadline) / 3600;
                        $kelipatanPenalti = floor($waktuTerlambatJam / 24);
                        
                        if ($kelipatanPenalti == 0) {
                            $kelipatanPenalti = 1; 
                        }
                        
                        $potonganPoin = $kelipatanPenalti * 5;
                        $skorAkhir = $basePoin - $potonganPoin;
                        
                        if ($skorAkhir < 10) {
                            $skorAkhir = 10;
                        }
                        
                        $poinTugas += $skorAkhir;
                    }
                } else {
                    // JIKA TUGAS TANPA DEADLINE
                    $poinTugas += $basePoin;
                }
            }
        }

        // Akumulasi Akhir Seluruh Poin
        $totalPoin = $poinAbsen + $poinKeaktifan + $poinTugas;

        return view('peserta.index', compact(
            'user', 
            'sudahIsiRiwayat', 
            'tugasAktif', 
            'tugasBelum', 
            'barangBelum', 
            'links', 
            'pengumuman',
            'totalPoin' // <--- Variabel ini siap dipakai di blade peserta
        ));
    }

    /**
     * Menampilkan Form Riwayat Penyakit Peserta
     */
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