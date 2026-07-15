<?php

namespace App\Http\Controllers;

use App\Models\AbsensiPeserta;
use App\Models\AbsensiPanitia;
use App\Models\QrSession;
use App\Models\User;
use App\Models\DailyAbsensiPassword;
use App\Services\FaceRecognitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AbsensiController extends Controller
{
    // ===== ABSENSI PESERTA =====

    public function formPeserta(Request $request)
    {
        $code  = $request->query('code');
        $token = $request->query('token');
        $qrSession  = null;
        $sudahAbsen = false;
        $error      = null;

        if ($code) {
            $qrSession = QrSession::where('session_code', $code)
                ->where('untuk', 'peserta')
                ->first();

            if (!$qrSession) {
                $error = 'QR Code tidak valid.';
            } elseif (!$qrSession->aktif) {
                $error = 'Sesi absensi sudah ditutup.';
            } elseif ($qrSession->berlaku_hingga && now()->isAfter($qrSession->berlaku_hingga)) {
                $error = 'QR Code sudah kadaluarsa.';
            } elseif ($qrSession->rotating && !$qrSession->isTokenValid($token)) {
                $error = 'QR Code sudah tidak berlaku (expired). Scan QR terbaru dari layar.';
            } else {
                $sudahAbsen = AbsensiPeserta::where('user_id', auth()->id())
                    ->where('qr_session_id', $qrSession->id)
                    ->exists();
            }
        }

        return view('peserta.absen', compact('qrSession', 'sudahAbsen', 'error', 'code', 'token'));
    }

    public function storePeserta(Request $request)
    {
        $request->validate([
            'session_code' => 'required|string',
            'token'        => 'nullable|string',
            'latitude'     => 'nullable|numeric',
            'longitude'    => 'nullable|numeric',
            'fingerprint'  => 'required|string',
        ]);

        $ip = $request->ip();

        // === CEK 1: QR Session valid ===
        $qrSession = QrSession::where('session_code', $request->session_code)
            ->where('untuk', 'peserta')
            ->where('aktif', true)
            ->first();

        if (!$qrSession) {
            return back()->with('error', 'Sesi absensi tidak ditemukan atau sudah ditutup.');
        }

        // === CEK 2: Token rotating masih valid ===
        if ($qrSession->rotating && !$qrSession->isTokenValid($request->token)) {
            return back()->with('error', 'QR Code sudah expired. Silakan scan ulang QR terbaru dari layar.');
        }

        // === CEK 3: Fingerprint device ===
        $user = auth()->user();
        $fp   = $request->fingerprint;

        // Jika user sudah punya fingerprint → harus sama
        if ($user->device_fingerprint && $user->device_fingerprint !== $fp) {
            return back()->with('error', 'Perangkat berbeda terdeteksi. Gunakan device yang sama.');
        }

        // Jika fingerprint dipakai akun lain → blok
        $ownerLain = \App\Models\User::where('device_fingerprint', $fp)
            ->where('id', '!=', $user->id)
            ->first();

        if ($ownerLain) {
            return back()->with('error', 'Perangkat ini sudah digunakan akun lain.');
        }

        // Simpan fingerprint pertama kali
        if (!$user->device_fingerprint) {
            $user->update([
                'device_fingerprint' => $fp,
                'fingerprint_set_at' => now(),
            ]);
        }

        // === CEK 4: Sudah absen sebelumnya ===
        $sudahAbsen = AbsensiPeserta::where('user_id', $user->id)
            ->where('qr_session_id', $qrSession->id)
            ->exists();

        if ($sudahAbsen) {
            return back()->with('error', 'Kamu sudah melakukan absensi untuk sesi ini.');
        }

        AbsensiPeserta::create([
            'user_id'       => $user->id,
            'qr_session_id' => $qrSession->id,
            'nama'          => $user->name,
            'nim'           => $user->nim,
            'angkatan'      => $user->angkatan,
            'kelompok'      => $user->kelompok,
            'status'        => 'hadir',
            'ip_address'    => $ip,
            'waktu_absen'   => now(),
        ]);

        return redirect()->route('peserta.index')
            ->with('success', 'Absensi berhasil! Terima kasih ' . $user->name . ' 🎉');
    }


    // ===== ABSENSI PANITIA =====

    public function formPanitia(Request $request)
    {
        $code = $request->query('code');
        $qrSession = null;
        $sudahAbsen = false;
        $error = null;

        if ($code) {
            $qrSession = QrSession::where('session_code', $code)
                ->where('untuk', 'panitia')
                ->first();

            if (!$qrSession) {
                $error = 'QR Code tidak valid atau bukan untuk panitia.';
            } elseif (!$qrSession->aktif) {
                $error = 'Sesi absensi sudah ditutup.';
            } else {
                $sudahAbsen = AbsensiPanitia::where('user_id', auth()->id())
                    ->where('qr_session_id', $qrSession->id)
                    ->exists();
            }
        }

        return view('panitia.absen', compact('qrSession', 'sudahAbsen', 'error', 'code'));
    }

    public function storePanitia(Request $request)
    {
        $request->validate([
            'session_code' => 'required|string',
        ]);

        $ip = $request->ip();

        $qrSession = QrSession::where('session_code', $request->session_code)
            ->where('untuk', 'panitia')
            ->where('aktif', true)
            ->first();

        if (!$qrSession) {
            return back()->with('error', 'Sesi absensi tidak ditemukan.');
        }

        $sudahAbsen = AbsensiPanitia::where('user_id', auth()->id())
            ->where('qr_session_id', $qrSession->id)
            ->exists();

        if ($sudahAbsen) {
            return back()->with('error', 'Kamu sudah melakukan absensi untuk sesi ini.');
        }

        $user = auth()->user();

        AbsensiPanitia::create([
            'user_id'       => $user->id,
            'qr_session_id' => $qrSession->id,
            'nama'          => $user->name,
            'nim'           => $user->nim,
            'divisi'        => $user->divisi,
            'status'        => 'hadir',
            'ip_address'    => $ip,
            'waktu_absen'   => now(),
        ]);

        return redirect()->route('panitia.index')
            ->with('success', 'Absensi berhasil!');
    }


    // ===== DATA ABSENSI (Tampilan Matriks Global Mirip Excel) =====

    public function dataPeserta(Request $request)
    {
        // 1. Ambil semua list sesi khusus peserta, diurutkan dari yang paling lama/awal
        $sesiList = QrSession::where('untuk', 'peserta')
            ->orderBy('created_at', 'asc')
            ->get();

        // 2. Ambil master data user peserta, urutkan nama secara alfabetis dahulu
        $rawUsers = User::whereNotNull('kelompok')
            ->orderBy('name', 'asc')
            ->get();

        // 3. Grouping berdasarkan kelompok
        $groupedData = $rawUsers->groupBy('kelompok');

        // 4. Urutkan key kelompok secara "Natural" (1, 2, 3... bukan 1, 10, 2)
        $matrixData = $groupedData->sortKeysUsing(function ($a, $b) {
            return strnatcasecmp($a, $b);
        });

        // 5. Ambil log riwayat absensi mentah, kemudian di-grouping bertingkat
        $logAbsensi = AbsensiPeserta::get()->groupBy(['user_id', 'qr_session_id']);

        // 6. Kirim data matriks global ke view rekap utama
        return view('panitia.data-absensi-peserta', compact('sesiList', 'matrixData', 'logAbsensi'));
    }

    // ===== AJAX UPDATE STATUS ABSENSI PESERTA =====

    public function updateStatusPeserta(Request $request)
    {
        $userLogin = auth()->user();

        // Safety check - ensure user is authenticated
        if (!$userLogin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Please login first.'
            ], 401);
        }

        // =============== KUNCI PENGAMAN AKSES ===============
        $isAdmin   = ($userLogin->role === 'admin' || strtoupper($userLogin->divisi ?? '') === 'ADMIN');
        $isAcara   = (strtoupper($userLogin->divisi ?? '') === 'ACARA');
        $isKomdis  = (strtoupper($userLogin->divisi ?? '') === 'KOMDIS');
        $isMentor  = (strtoupper($userLogin->divisi ?? '') === 'MENTOR');

        if (!$isAdmin && !$isAcara && !$isKomdis && !$isMentor) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak! Anda tidak memiliki otoritas mengubah absensi.'
            ], 403);
        }
        // ====================================================

        $request->validate([
            'user_id'        => 'required|exists:users,id',
            'qr_session_id'  => 'required|exists:qr_sessions,id',
            'status'         => 'required|in:hadir,izin,tidak_hadir'
        ]);

        // Cari log absensi yang sudah ada berdasarkan user dan sesi terkait
        $absensi = AbsensiPeserta::where('user_id', $request->user_id)
            ->where('qr_session_id', $request->qr_session_id)
            ->first();

        if ($request->status === 'tidak_hadir') {
            // Jika diubah menjadi Tidak Hadir (Alfa), hapus log record dari database jika ada
            if ($absensi) {
                $absensi->delete();
            }
        } else { // <--- PERBAIKAN: Sekarang menggunakan 'else' yang benar
            // Jika status adalah 'hadir' atau 'izin', buat record baru jika belum ada
            if (!$absensi) {
                $user = User::find($request->user_id);
                
                // Safety check - user must exist
                if (!$user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User not found.'
                    ], 404);
                }
                
                $absensi = new AbsensiPeserta();
                $absensi->user_id       = $user->id;
                $absensi->qr_session_id = $request->qr_session_id;
                $absensi->nama          = $user->name;
                $absensi->nim           = $user->nim;
                $absensi->angkatan      = $user->angkatan;
                $absensi->kelompok      = $user->kelompok;
                $absensi->ip_address    = $request->ip();
            }

            // Simpan status asli sesuai yang dipilih dari dropdown ('hadir' atau 'izin')
            $absensi->status = $request->status;

            // Waktu absen tetap dicatat sebagai penanda kapan admin mengubah datanya
            $absensi->waktu_absen = now();
            $absensi->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diperbarui!'
        ]);
    }

    public function dataPanitia()
    {
        $absensi = AbsensiPanitia::with('user', 'qrSession')
            ->orderBy('divisi')
            ->orderBy('nama')
            ->get()
            ->groupBy('divisi');

        return view('panitia.data-absensi-panitia', compact('absensi'));
    }

    public function faceGate()
    {
        return view('panitia.face-gate');
    }

    // ===== PASSWORD VERIFICATION FOR ATTENDANCE DATA =====

    public function showPasswordForm()
    {
        return view('panitia.absensi-password');
    }

    public function verifyPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $todayPassword = DailyAbsensiPassword::getTodayPassword();

        if (!$todayPassword) {
            return back()->with('error', 'Password akses absensi hari ini belum dibuat oleh admin.');
        }

        if (!Hash::check($request->password, $todayPassword->password)) {
            return back()->with('error', 'Password salah!');
        }

        // Set session untuk 24 jam
        $request->session()->put('absensi_password_verified', true);
        $request->session()->put('absensi_password_verified_at', now());

        // Redirect ke URL yang dituju sebelum verifikasi, atau ke dashboard panitia
        $intendedUrl = $request->session()->get('url.intended');
        
        return redirect($intendedUrl ?: route('panitia.index'))
            ->with('success', 'Password berhasil diverifikasi! Akses absensi diberikan.');
    }

    public function logoutPassword(Request $request)
    {
        $request->session()->forget('absensi_password_verified');
        $request->session()->forget('absensi_password_verified_at');

        return redirect()->route('panitia.index')
            ->with('success', 'Session password absensi telah dihapus.');
    }

    public function faceGateProcess(Request $request)
    {
        $request->validate([
            'photo'   => 'required|image|max:5120',
            'qr_code' => 'required|string',
        ]);

        $faceService = app(FaceRecognitionService::class);
        $result      = $faceService->identifyFace($request->file('photo'));

        if (!$result['found']) {
            return response()->json([
                'success' => false,
                'message' => $result['reason'] ?? 'Wajah tidak dikenali',
                'fallback_qr' => true,
            ]);
        }

        $userId = $result['user_id'];
        $user   = \App\Models\User::find($userId);

        // Safety check if user not found in database
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan di database.',
                'fallback_qr' => true,
            ]);
        }

        return response()->json([
            'success'    => true,
            'user_name'  => $user->name,
            'kelompok'   => $user->kelompok,
            'confidence' => $result['confidence'],
            'message'    => "Selamat datang, {$user->name}!",
        ]);
    }
}
