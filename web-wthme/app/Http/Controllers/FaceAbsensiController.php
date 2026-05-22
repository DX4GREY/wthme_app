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
        $activeSessions = QrSession::where('aktif', true)
            ->where('untuk', 'peserta')
            ->latest()
            ->get();

        return view('panitia.face-gate', compact('activeSessions'));
    }

    // ─── Proses identifikasi + simpan absen ───
    public function gateProcess(Request $request)
    {
        $request->validate([
            'photo'      => 'required|image|max:5120',
            'session_id' => 'required|integer',
        ]);

        // 1. Identifikasi wajah via FastAPI
        $result = $this->faceService->identifyFace($request->file('photo'));

        // Wajah tidak terdeteksi / tidak cocok
        if (empty($result['found']) || !$result['found']) {
            return response()->json([
                'success'  => false,
                'fallback' => true,
                'message'  => $result['reason'] ?? 'Wajah tidak dikenali',
            ]);
        }

        // 2. Cari user
        $user = User::find($result['user_id']);

        if (!$user || !$user->isPeserta()) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan atau bukan peserta.',
            ]);
        }

        // 3. Cari sesi
        $session = QrSession::find($request->session_id);

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi absensi tidak ditemukan.',
            ]);
        }

        // 4. Cek sudah absen
        $sudahAbsen = AbsensiPeserta::where('user_id', $user->id)
            ->where('qr_session_id', $session->id)
            ->exists();

        if ($sudahAbsen) {
            return response()->json([
                'success'    => true,
                'already'    => true,
                'user_name'  => $user->name,
                'kelompok'   => $user->kelompok,
                'nim'        => $user->nim ?? '-',
                'message'    => "{$user->name} sudah absen sebelumnya",
            ]);
        }

        // 5. Simpan absen
        try {
            AbsensiPeserta::create([
                'user_id'       => $user->id,
                'qr_session_id' => $session->id,
                'metode'        => 'face',
                'nama'          => $user->name,
                'nim'           => $user->nim    ?? '-',
                'angkatan'      => $user->angkatan ?? '-',
                'kelompok'      => $user->kelompok ?? '-',
                'status'        => 'hadir',
                'ip_address'    => $request->ip(),
                'waktu_absen'   => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal simpan absen face: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan absen: ' . $e->getMessage(),
            ]);
        }

        return response()->json([
            'success'    => true,
            'already'    => false,
            'user_name'  => $user->name,
            'kelompok'   => $user->kelompok ?? '-',
            'nim'        => $user->nim ?? '-',
            'confidence' => $result['confidence'] ?? null,
            'message'    => "Selamat datang, {$user->name}!",
        ]);
    }
}