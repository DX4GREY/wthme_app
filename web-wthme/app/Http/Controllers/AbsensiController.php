<?php

namespace App\Http\Controllers;

use App\Models\AbsensiPeserta;
use App\Models\AbsensiPanitia;
use App\Models\QrSession;
use App\Models\User;
use Illuminate\Http\Request;

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
        $sudahAbsen = AbsensiPeserta::where('user_id', auth()->id())
            ->where('qr_session_id', $qrSession->id)
            ->exists();

        if ($sudahAbsen) {
            return back()->with('error', 'Kamu sudah melakukan absensi untuk sesi ini.');
        }

        $user = auth()->user();

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


    // ===== DATA ABSENSI (untuk panitia lihat) =====

    public function dataPeserta()
    {
        $absensi = AbsensiPeserta::with('user', 'qrSession')
            ->orderBy('kelompok')
            ->orderBy('nama')
            ->get()
            ->groupBy('kelompok');

        $sesiList = QrSession::where('untuk', 'peserta')->get();

        return view('panitia.data-absensi-peserta', compact('absensi', 'sesiList'));
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
                'message' => 'Wajah tidak dikenali',
                'fallback_qr' => true,
            ]);
        }

        $userId = $result['user_id'];
        $user   = \App\Models\User::find($userId);

        return response()->json([
            'success'    => true,
            'user_name'  => $user->name,
            'kelompok'   => $user->kelompok,
            'confidence' => $result['confidence'],
            'message'    => "Selamat datang, {$user->name}!",
        ]);
    }
}