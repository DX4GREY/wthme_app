<?php

namespace App\Http\Controllers;

use App\Models\CaptureMoment;
use App\Models\CaptureMomentReaction;
use App\Models\CaptureMomentSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CaptureMomentController extends Controller
{
    // Poin berdasarkan urutan ranking total_skor (juara 4 dst rata = 150)
    protected function poinUntukJuara(int $juara): int
    {
        return match (true) {
            $juara === 1 => 200,
            $juara === 2 => 190,
            $juara === 3 => 180,
            default      => 150,
        };
    }

    /* =========================================================
     |  PESERTA
     * ========================================================= */

    public function pesertaIndex()
    {
        $user     = Auth::user();
        $setting  = CaptureMomentSetting::current();

        // Foto kelompok sendiri (kalau sudah pernah upload)
        $milikKelompok = CaptureMoment::where('kelompok', $user->kelompok)->first();

        // Galeri semua kelompok, urut dari yang paling baru update-nya
        $semuaFoto = CaptureMoment::with(['uploader', 'reactions'])
            ->orderByDesc('updated_at')
            ->get();

        // Reaction milik user sendiri, supaya view tahu emoji apa yang sudah dipilih per foto
        $reaksiSaya = CaptureMomentReaction::where('user_id', $user->id)
            ->pluck('emoji', 'capture_moment_id');

        return view('peserta.capture-moment.index', [
            'setting'       => $setting,
            'milikKelompok' => $milikKelompok,
            'semuaFoto'     => $semuaFoto,
            'reaksiSaya'    => $reaksiSaya,
        ]);
    }

    public function pesertaStore(Request $request)
    {
        $setting = CaptureMomentSetting::current();

        if (!$setting->sedangBerjalan()) {
            return back()->with('error', 'Periode upload Capture Moment belum dibuka / sudah ditutup.');
        }

        $request->validate([
            'foto'    => 'required|image|max:15360', // max 15MB
            'caption' => 'nullable|string|max:255',
        ]);

        $user     = Auth::user();
        $kelompok = $user->kelompok;

        // ✅ Validasi: user harus punya kelompok
        if (blank($kelompok)) {
            return back()->with('error', 'Akun kamu belum memiliki kelompok. Hubungi panitia.');
        }

        $existing = CaptureMoment::where('kelompok', $kelompok)->first();

        // Hapus foto lama dari storage kalau ada
        if ($existing) {
            $oldPath = $existing->getOriginal('foto_path') ?? $existing->foto_path;
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $path = $request->file('foto')->store('capture-moments', 'public');

        CaptureMoment::updateOrCreate(
            ['kelompok' => $kelompok],
            [
                'foto_path'   => $path,
                'caption'     => $request->caption ?? '',
                'uploaded_by' => $user->id,
            ]
        );

        return back()->with('success', 'Foto Capture Moment kelompok ' . $kelompok . ' berhasil disimpan!');
    }

    public function react(Request $request, $id)
    {
        $setting = CaptureMomentSetting::current();

        if (!$setting->sedangBerjalan()) {
            return back()->with('error', 'Periode reaction sudah ditutup.');
        }

        $request->validate([
            'emoji' => 'required|string|max:10',
        ]);

        $foto = CaptureMoment::findOrFail($id);

        CaptureMomentReaction::updateOrCreate(
            [
                'capture_moment_id' => $foto->id,
                'user_id'           => Auth::id(),
            ],
            [
                'emoji' => $request->emoji,
            ]
        );

        return back()->with('success', 'Reaction terkirim!');
    }

    /* =========================================================
     |  PANITIA
     * ========================================================= */

    public function panitiaIndex()
    {
        $setting = CaptureMomentSetting::current();

        $foto = CaptureMoment::with(['uploader', 'penilai', 'reactions'])
            ->orderByDesc('total_skor')
            ->orderBy('created_at')
            ->get();

        return view('panitia.capture-moment.index', [
            'setting' => $setting,
            'foto'    => $foto,
        ]);
    }

    public function nilai(Request $request, $id)
    {
        $request->validate([
            'skor_kelengkapan' => 'required|integer|min:0|max:100',
            'skor_tema'        => 'required|integer|min:0|max:100',
            'skor_estetika'    => 'required|integer|min:0|max:100',
        ]);

        $foto = CaptureMoment::findOrFail($id);

        $total = $request->skor_kelengkapan + $request->skor_tema + $request->skor_estetika;

        $foto->update([
            'skor_kelengkapan' => $request->skor_kelengkapan,
            'skor_tema'        => $request->skor_tema,
            'skor_estetika'    => $request->skor_estetika,
            'total_skor'       => $total,
            'dinilai_oleh'     => Auth::id(),
            'dinilai_at'       => now(),
        ]);

        $this->hitungUlangRanking();

        return back()->with('success', 'Penilaian kelompok ' . $foto->kelompok . ' tersimpan & ranking diperbarui.');
    }

    // Dipanggil otomatis tiap kali ada penilaian baru: urutkan ulang juara & poin
    protected function hitungUlangRanking(): void
    {
        $sudahDinilai = CaptureMoment::whereNotNull('total_skor')
            ->orderByDesc('total_skor')
            ->orderBy('dinilai_at') // tie-breaker: siapa duluan dinilai
            ->get();

        $rank = 1;
        foreach ($sudahDinilai as $foto) {
            $foto->update([
                'juara' => $rank,
                'poin'  => $this->poinUntukJuara($rank),
            ]);
            $rank++;
        }
    }

    public function settingsUpdate(Request $request)
    {
        $request->validate([
            'mulai_at'   => 'nullable|date',
            'selesai_at' => 'nullable|date|after_or_equal:mulai_at',
        ]);

        $setting = CaptureMomentSetting::current();
        $setting->update([
            'mulai_at'   => $request->mulai_at,
            'selesai_at' => $request->selesai_at,
        ]);

        return back()->with('success', 'Periode Capture Moment berhasil diperbarui.');
    }
}
