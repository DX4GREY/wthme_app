<?php

namespace App\Http\Controllers;

use App\Models\SuaraPeserta;
use App\Models\SuaraPesertaRead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SuaraPesertaController extends Controller
{
    /**
     * Tampilkan form kirim suara untuk peserta
     * Jika sudah pernah kirim, tampilkan status suara
     */
    public function create()
    {
        $suara = SuaraPeserta::with(['reads.user'])
            ->where('user_id', Auth::id())
            ->first();

        if ($suara) {
            return view('peserta.suara.status', compact('suara'));
        }

        return view('peserta.suara.create');
    }

    /**
     * Simpan suara peserta (hanya bisa sekali)
     */
    public function store(Request $request)
    {
        // Cek apakah sudah pernah kirim
        $exists = SuaraPeserta::where('user_id', Auth::id())->exists();
        if ($exists) {
            return redirect()->route('peserta.suara.create')
                ->with('error', 'Kamu sudah mengirimkan suara sebelumnya. Tunggu hingga suara kamu dihapus oleh panitia.');
        }

        $request->validate([
            'pesan' => 'required|string|max:5000',
            'foto'  => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'anonim' => 'nullable|boolean',
        ]);

        $data = [
            'user_id' => Auth::id(),
            'pesan'   => $request->pesan,
            'anonim'  => $request->boolean('anonim'),
        ];

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('suara-peserta', 'public');
        }

        SuaraPeserta::create($data);

        return redirect()->route('peserta.suara.create')
            ->with('success', 'Suara kamu berhasil dikirim! Pantau status suara kamu di sini.');
    }

    /**
     * Hapus suara milik peserta sendiri (jika sudah dibaca, biarkan panitia yang hapus)
     */
    public function destroyOwn()
    {
        $suara = SuaraPeserta::where('user_id', Auth::id())->first();
        if (!$suara) {
            return redirect()->route('peserta.suara.create')
                ->with('error', 'Kamu belum mengirimkan suara.');
        }

        if ($suara->foto) {
            Storage::disk('public')->delete($suara->foto);
        }

        $suara->reads()->delete();
        $suara->delete();

        return redirect()->route('peserta.suara.create')
            ->with('success', 'Suara kamu berhasil dihapus. Kamu bisa mengirim suara baru.');
    }

    /**
     * Daftar semua suara peserta (untuk panitia)
     */
    public function index()
    {
        $suaraList = SuaraPeserta::with('user')
            ->withCount('reads')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('panitia.suara.index', compact('suaraList'));
    }

    /**
     * Lihat detail suara peserta (untuk panitia)
     * Catat siapa yang membaca
     */
    public function show($id)
    {
        $suara = SuaraPeserta::with(['reads.user'])->findOrFail($id);

        // Tandai sudah dibaca dan catat pembaca
        if (!$suara->dibaca) {
            $suara->update([
                'dibaca'   => true,
                'dibaca_at' => now(),
            ]);
        }

        // Catat pembaca (panitia/admin yang lihat)
        $alreadyRead = SuaraPesertaRead::where('suara_peserta_id', $suara->id)
            ->where('user_id', Auth::id())
            ->exists();

        if (!$alreadyRead) {
            SuaraPesertaRead::create([
                'suara_peserta_id' => $suara->id,
                'user_id' => Auth::id(),
            ]);

            // Refresh reads
            $suara->load('reads.user');
        }

        return view('panitia.suara.show', compact('suara'));
    }

    /**
     * Hapus suara peserta (panitia/admin)
     */
    public function destroy($id)
    {
        $suara = SuaraPeserta::findOrFail($id);

        if ($suara->foto) {
            Storage::disk('public')->delete($suara->foto);
        }

        $suara->reads()->delete();
        $suara->delete();

        return redirect()->route('panitia.suara.index')
            ->with('success', 'Suara berhasil dihapus.');
    }
}