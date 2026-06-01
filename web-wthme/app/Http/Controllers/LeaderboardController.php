<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AbsensiPeserta;
use App\Models\TugasKategori;
use App\Models\TugasPengumpulan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    /**
     * Menampilkan Halaman Leaderboard Global
     */
    public function index()
    {
        $pesertas = User::where('role', 'peserta')->get();
        $rankData = [];

        foreach ($pesertas as $peserta) {
            // 1. HITUNG ABSENSI (+20 poin per hadir)
            $jumlahHadir = AbsensiPeserta::where('user_id', $peserta->id)
                ->where('status', 'hadir')
                ->count();
            $poinAbsen = $jumlahHadir * 300;

            // 2. HITUNG KEAKTIFAN ACARA (Hasil input dari KeaktifanController)
            $poinKeaktifan = DB::table('poin_keaktifan')
                ->where('peserta_id', $peserta->id)
                ->sum('poin') ?? 0;

            // 3. HITUNG KECEPATAN TUGAS (Bonus Kecepatan 12j & Penalti Keterlambatan 24j)
            $kumpulanTugas = TugasPengumpulan::where('user_id', $peserta->id)->get();
            $poinTugas = 0;

            foreach ($kumpulanTugas as $kumpul) {
                $indukTugas = TugasKategori::find($kumpul->tugas_kategori_id);
                
                if ($indukTugas) {
                    $basePoin = 80; // Poin dasar tugas normal jika pas deadline
                    
                    $waktuRilis    = strtotime($indukTugas->created_at);
                    $waktuDeadline = $indukTugas->deadline ? strtotime($indukTugas->deadline) : null;
                    $waktuKumpul   = strtotime($kumpul->dikumpulkan_at ?? $kumpul->created_at);

                    // JIKA ADA DEADLINE YANG DITETAPKAN PANITIA
                    if ($waktuDeadline) {
                        
                        if ($waktuKumpul <= $waktuDeadline) {
                            // --- KONDISI TEPAT WAKTU / SEBELUM DEADLINE (SISTEM BONUS) ---
                            // Hitung sisa waktu menuju deadline (dalam satuan jam)
                            $sisaWaktuJam = ($waktuDeadline - $waktuKumpul) / 3600;
                            
                            // Setiap kelipatan 12 jam sisa waktu, dapat bonus +5 poin
                            $kelipatanBonus = floor($sisaWaktuJam / 12);
                            $bonusPoin = $kelipatanBonus * 10;
                            
                            $poinTugas += ($basePoin + $bonusPoin);

                        } else {
                            // --- KONDISI TERLAMBAT / SETELAH DEADLINE (SISTEM PENALTI) ---
                            // Hitung berapa jam keterlambatan dari batas deadline
                            $waktuTerlambatJam = ($waktuKumpul - $waktuDeadline) / 3600;
                            
                            // Setiap kelipatan 24 jam keterlambatan, dipotong -5 poin
                            $kelipatanPenalti = floor($waktuTerlambatJam / 24);
                            
                            // Berikan penalti minimal 1x potong karena statusnya sudah fix melewati deadline
                            if ($kelipatanPenalti == 0) {
                                $kelipatanPenalti = 1; 
                            }
                            
                            $potonganPoin = $kelipatanPenalti * 5;
                            $skorAkhir = $basePoin - $potonganPoin;
                            
                            // Batas aman minimal skor biar tidak minus (mentok di 10 poin semundur apa pun)
                            if ($skorAkhir < 10) {
                                $skorAkhir = 10;
                            }
                            
                            $poinTugas += $skorAkhir;
                        }

                    } else {
                        // JIKA TUGAS TIDAK MEMILIKI DEADLINE (DAPAT POIN NORMAL)
                        $poinTugas += $basePoin;
                    }
                }
            }

            // TOTAL AKUMULASI SKOR SELURUH ASPEK
            $totalSkor = $poinAbsen + $poinKeaktifan + $poinTugas;

            $rankData[] = [
                'name'           => $peserta->name,
                'nim'            => $peserta->nim,
                'kelompok'       => $peserta->kelompok ?? '-',
                'poin_absen'     => $poinAbsen,
                'poin_keaktifan' => $poinKeaktifan,
                'poin_tugas'     => $poinTugas,
                'total'          => $totalSkor
            ];
        }

        // URUTKAN DARI SKOR TERTINGGI KE TERENDAH
        usort($rankData, function ($a, $b) {
            return $b['total'] <=> $a['total'];
        });

        // PROTEKSI LOGIKA AKSES: Jika role user yang login adalah 'peserta', potong array menjadi hanya 10 baris teratas
        if (auth()->check() && auth()->user()->role === 'peserta') {
            $rankData = array_slice($rankData, 0, 10);
        }

        return view('leaderboard.index', compact('rankData'));
    }

    /**
     * Menampilkan Form Input Poin oleh Panitia
     */
    public function inputPoint()
    {
        // 1. Mengambil daftar peserta untuk dropdown form
        $pesertas = User::where('role', 'peserta')
            ->orderBy('kelompok', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        // 2. Mengambil data riwayat log keaktifan dari tabel poin_keaktifan
        $riwayatLog = DB::table('poin_keaktifan')
            ->join('users as peserta', 'poin_keaktifan.peserta_id', '=', 'peserta.id')
            ->leftJoin('users as panitia', 'poin_keaktifan.panitia_id', '=', 'panitia.id')
            ->select(
                'poin_keaktifan.*',
                'peserta.name as nama_peserta',
                'peserta.kelompok as kelompok_peserta',
                'panitia.name as nama_panitia'
            )
            ->orderBy('poin_keaktifan.created_at', 'desc')
            ->paginate(25);

        return view('panitia.keaktifan.index', compact('pesertas', 'riwayatLog'));
    }
}