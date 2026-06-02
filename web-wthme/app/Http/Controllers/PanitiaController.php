<?php

namespace App\Http\Controllers;

use App\Models\LinkResource;
use App\Models\QrSession;
use App\Models\AbsensiPeserta;
use App\Models\AbsensiPanitia;
use App\Models\User;
use App\Models\Link;
use App\Models\InformasiPeserta;
use Illuminate\Http\Request;

class PanitiaController extends Controller
{
    public function index()
    {
        $links = LinkResource::where('aktif', true)
            ->orderBy('urutan')
            ->get();

        $qrSessions = QrSession::with('pembuatOleh')
            ->orderBy('created_at', 'desc')
            ->get();

        // 1. Ambil Sesi Terakhir yang baru saja dibuat
        $latestSession = QrSession::orderBy('created_at', 'desc')->first();

        // 2. Logika Hitung (Hanya jika ada sesi, jika tidak ada set ke 0)
        if ($latestSession) {
            // Hitung total peserta Laki-laki yang hadir di sesi ini
            $pesertaHadirL = AbsensiPeserta::where('qr_session_id', $latestSession->id)
                ->where('status', 'hadir')
                ->whereHas('user', function ($query) {
                    $query->where('gender', 'L');
                })->count();

            // Hitung total peserta Perempuan yang hadir di sesi ini
            $pesertaHadirP = AbsensiPeserta::where('qr_session_id', $latestSession->id)
                ->where('status', 'hadir')
                ->whereHas('user', function ($query) {
                    $query->where('gender', 'P');
                })->count();

            // Total keseluruhan peserta hadir
            $totalPesertaHadir = $pesertaHadirL + $pesertaHadirP;

            $totalPanitiaHadir = AbsensiPanitia::where('qr_session_id', $latestSession->id)
                ->where('status', 'hadir')
                ->count();

            $namaSesiAktif = $latestSession->nama_sesi;
        } else {
            $pesertaHadirL = 0;
            $pesertaHadirP = 0;
            $totalPesertaHadir = 0;
            $totalPanitiaHadir = 0;
            $namaSesiAktif = "Belum ada sesi";
        }

        $totalSeluruhPeserta = User::where('role', 'peserta')->count();

        $totalSeluruhPanitia = User::whereIn('role', [
            'admin',
            'panitia',
            'korlap',
            'mentor',
            'bendahara',
            'ketuplak'
        ])->count();

        $links = \App\Models\Link::all();

        return view('panitia.index', compact(
            'links',
            'qrSessions',
            'totalPesertaHadir',
            'totalPanitiaHadir',
            'totalSeluruhPeserta',
            'pesertaHadirL', // <--- Pastikan ini ditambahkan
            'pesertaHadirP',
            'totalSeluruhPanitia',
            'namaSesiAktif' // Tambahkan ini agar di view bisa muncul nama sesinya

        ));
    }

    // Tambahkan method ini di dalam class
    public function storeLink(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'url' => 'required|url',
            'ikon' => 'required'
        ]);

        Link::create($request->all());
        return back()->with('success', 'Link berhasil ditambahkan!');
    }

    public function destroyLink($id)
    {
        Link::findOrFail($id)->delete();
        return back()->with('success', 'Link berhasil dihapus!');
    }

    // Fungsi untuk menyimpan pengumuman buat peserta
    public function storeInfoPeserta(Request $request)
    {
        $request->validate([
            'judul' => 'required',
            'kategori' => 'required',
            'konten' => 'nullable|string',
            'url_link' => 'nullable|url',
        ]);

        InformasiPeserta::create($request->all());
        return back()->with('success', 'Pengumuman telah terkirim ke portal peserta!');
    }

    // Fungsi untuk menghapus pengumuman
    public function destroyInfoPeserta($id)
    {
        InformasiPeserta::findOrFail($id)->delete();
        return back()->with('success', 'Pengumuman dihapus.');
    }
    public function indexInfoPeserta()
    {
        $infos = \App\Models\InformasiPeserta::latest()->get();
        return view('panitia.informasi_peserta', compact('infos'));
    }
}
