<?php

namespace App\Http\Controllers;

use App\Models\AbsensiPeserta;
use App\Models\User;
use App\Models\QrSession;
use App\Services\FaceRecognitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FaceAbsensiController extends Controller
{
    public function __construct(private FaceRecognitionService $faceService) {}

    // ─── Halaman gate ───
    public function gate(Request $request)
    {
        $sesiList = QrSession::where('aktif', true)
            ->where('untuk', 'peserta')
            ->latest()
            ->get();

        return view('panitia.face-gate', compact('sesiList'));
    }

    // ─── Proses identifikasi + simpan absen (multi wajah per frame) ───
    public function gateProcess(Request $request)
    {
        $request->validate([
            'photo'      => 'required|image|max:5120',
            'session_id' => 'required|integer',
        ]);

        // 1. Identifikasi semua wajah via FastAPI
        $result = $this->faceService->identifyFace($request->file('photo'));

        // Tidak ada wajah / tidak ada yang cocok sama sekali
        if (empty($result['found']) || !$result['found']) {
            return response()->json([
                'success'  => false,
                'fallback' => true,
                'message'  => $result['reason'] ?? 'Wajah tidak dikenali',
            ]);
        }

        // 2. Cari sesi
        $session = QrSession::find($request->session_id);

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi absensi tidak ditemukan.',
            ]);
        }

        $matches = $result['matches'] ?? [];
        $results = [];

        // 3. Proses tiap wajah yang cocok satu per satu
        foreach ($matches as $match) {
            $user = User::find($match['user_id'] ?? null);

            if (!$user || !$user->isPeserta()) {
                continue; // skip diam-diam, jangan ganggu wajah lain yang valid
            }

            // Cek sudah absen di sesi ini
            $sudahAbsen = AbsensiPeserta::where('user_id', $user->id)
                ->where('qr_session_id', $session->id)
                ->exists();

            if ($sudahAbsen) {
                $results[] = [
                    'already'    => true,
                    'user_name'  => $user->name,
                    'kelompok'   => $user->kelompok ?? '-',
                    'nim'        => $user->nim ?? '-',
                    'confidence' => $match['confidence'] ?? null,
                ];
                continue;
            }

            // Simpan absen — firstOrCreate (idealnya dibarengi unique index di DB,
            // tapi TIDAK wajib untuk membuat fitur ini jalan; itu cuma pengaman tambahan)
            try {
                AbsensiPeserta::firstOrCreate(
                    [
                        'user_id'       => $user->id,
                        'qr_session_id' => $session->id,
                    ],
                    [
                        'metode'      => 'face',
                        'nama'        => $user->name,
                        'nim'         => $user->nim ?? '-',
                        'angkatan'    => $user->angkatan ?? '-',
                        'kelompok'    => $user->kelompok ?? '-',
                        'status'      => 'hadir',
                        'ip_address'  => $request->ip(),
                        'waktu_absen' => now(),
                    ]
                );

                $results[] = [
                    'already'    => false,
                    'user_name'  => $user->name,
                    'kelompok'   => $user->kelompok ?? '-',
                    'nim'        => $user->nim ?? '-',
                    'confidence' => $match['confidence'] ?? null,
                ];
            } catch (\Exception $e) {
                Log::error('Gagal simpan absen face (user_id ' . $user->id . '): ' . $e->getMessage());
                // tetap lanjut proses wajah lain, jangan sampai 1 gagal ngerusak semua
            }
        }

        if (empty($results)) {
            return response()->json([
                'success'  => false,
                'fallback' => true,
                'message'  => 'Wajah dikenali tapi tidak ada yang valid untuk diabsen.',
            ]);
        }

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }
}