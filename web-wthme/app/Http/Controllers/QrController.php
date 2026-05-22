<?php

namespace App\Http\Controllers;

use App\Models\QrSession;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrController extends Controller
{
    public function create()
    {
        return view('panitia.qr.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_sesi'        => 'required|string|max:255',
            'untuk'            => 'required|in:peserta,panitia',
            'berlaku_hingga'   => 'nullable|date|after:now',
            'rotating'         => 'nullable|boolean',
            'rotate_interval'  => 'nullable|integer|min:15|max:300',
        ]);

        $sessionCode = Str::upper(Str::random(8));
        $isRotating  = $request->boolean('rotating') && $request->untuk === 'peserta';

        $qr = QrSession::create([
            'session_code'    => $sessionCode,
            'nama_sesi'       => $request->nama_sesi,
            'untuk'           => $request->untuk,
            'aktif'           => true,
            'berlaku_hingga'  => $request->berlaku_hingga,
            'dibuat_oleh'     => auth()->id(),
            'rotating'        => $isRotating,
            'rotate_interval' => $isRotating ? (int)($request->rotate_interval ?? 30) : 30,
        ]);

        // Generate token pertama kalau rotating
        if ($isRotating) {
            $qr->regenerateToken();
        }

        return redirect()->route('panitia.qr.show', $qr->session_code)
            ->with('success', 'QR Code berhasil dibuat!');
    }

    public function show($code)
    {
        $qrSession = QrSession::where('session_code', $code)->firstOrFail();

        // Kalau rotating, pakai token; kalau tidak, pakai session_code biasa
        if ($qrSession->rotating) {
            // Regenerate kalau token sudah expired
            if (!$qrSession->token_expires_at || now()->isAfter($qrSession->token_expires_at)) {
                $qrSession->regenerateToken();
                $qrSession->refresh();
            }
            $absenUrl = url('/peserta/absen?code=' . $qrSession->session_code . '&token=' . $qrSession->current_token);
        } else {
            $absenUrl = $qrSession->untuk === 'panitia'
                ? url('/panitia/absen?code=' . $code)
                : url('/peserta/absen?code=' . $code);
        }

        $qrCode = QrCode::format('svg')->size(280)->errorCorrection('H')->generate($absenUrl);

        return view('panitia.qr.show', compact('qrSession', 'qrCode', 'absenUrl'));
    }

    // Endpoint AJAX — dipanggil halaman videotron tiap X detik
    public function refreshToken($code)
    {
        $qrSession = QrSession::where('session_code', $code)
            ->where('aktif', true)
            ->firstOrFail();

        if (!$qrSession->rotating) {
            return response()->json(['error' => 'Bukan sesi rotating'], 400);
        }

        // Regenerate kalau sudah expired atau hampir expired (< 3 detik)
        if (!$qrSession->token_expires_at || now()->addSeconds(3)->isAfter($qrSession->token_expires_at)) {
            $qrSession->regenerateToken();
            $qrSession->refresh();
        }

        $absenUrl = url('/peserta/absen?code=' . $qrSession->session_code . '&token=' . $qrSession->current_token);
        $qrSvg    = QrCode::format('svg')->size(280)->errorCorrection('H')->generate($absenUrl);

        return response()->json([
            'qr_svg'      => $qrSvg,
            'token'       => $qrSession->current_token,
            'expires_at'  => $qrSession->token_expires_at->timestamp,
            'interval'    => $qrSession->rotate_interval,
            'absen_url'   => $absenUrl,
        ]);
    }

    public function toggle($id)
    {
        $qr = QrSession::findOrFail($id);
        $qr->update(['aktif' => !$qr->aktif]);
        return back()->with('success', 'Status QR berhasil diubah.');
    }
}