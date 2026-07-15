<?php

namespace App\Http\Controllers;

use App\Models\SuaraPeserta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SuaraPesertaController extends Controller
{
    /**
     * Tampilkan form kirim suara untuk peserta
     */
    public function create()
    {
        return view('peserta.suara.create');
    }

    /**
     * Simpan suara peserta
     */
    public function store(Request $request)
    {
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
            ->with('success', 'Suara kamu berhasil dikirim! Terima kasih atas masukannya.');
    }

    /**
     * Daftar semua suara peserta (untuk panitia)
     */
    public function index()
    {
        $suaraList = SuaraPeserta::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('panitia.suara.index', compact('suaraList'));
    }

    /**
     * Lihat detail suara peserta (untuk panitia)
     */
    public function show($id)
    {
        $suara = SuaraPeserta::with('user')->findOrFail($id);

        // Tandai sudah dibaca
        if (!$suara->dibaca) {
            $suara->update([
                'dibaca'   => true,
                'dibaca_at' => now(),
            ]);
        }

        return view('panitia.suara.show', compact('suara'));
    }

    /**
     * Hapus suara peserta
     */
    public function destroy($id)
    {
        $suara = SuaraPeserta::findOrFail($id);

        if ($suara->foto) {
            Storage::disk('public')->delete($suara->foto);
        }

        $suara->delete();

        return redirect()->route('panitia.suara.index')
            ->with('success', 'Suara berhasil dihapus.');
    }
}