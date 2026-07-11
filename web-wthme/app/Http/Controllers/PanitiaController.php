<?php

namespace App\Http\Controllers;

use App\Models\LinkResource;
use App\Models\QrSession;
use App\Models\AbsensiPeserta;
use App\Models\AbsensiPanitia;
use App\Models\User;
use App\Models\Link;
use App\Models\InformasiPeserta;
use App\Models\PersonalBroadcast;
use App\Models\PersonalBroadcastRecipient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

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

            // 🟢 LOGIKA BARU: Ambil rincian data kelompok yang HADIR PADA SESI INI
            // Ambil semua data absensi peserta yang hadir di sesi ini beserta data relasi usernya
            $absensiSesiAktif = AbsensiPeserta::where('qr_session_id', $latestSession->id)
                ->where('status', 'hadir')
                ->with('user') // Pastikan model AbsensiPeserta memiliki relasi function user()
                ->get();

            // Kelompokkan user yang hadir berdasarkan kolom 'kelompok' mereka
            $detailKelompok = $absensiSesiAktif->groupBy(function($absensi) {
                    return $absensi->user->kelompok ?? 'Tanpa Kelompok';
                })
                ->map(function ($items, $namaKelompok) {
                    return [
                        'nama'  => is_numeric($namaKelompok) ? 'Kelompok ' . $namaKelompok : $namaKelompok,
                        'L'     => $items->where('user.gender', 'L')->count(),
                        'P'     => $items->where('user.gender', 'P')->count(),
                        'total' => $items->count()
                    ];
                })
                ->sortBy('nama') // Urutkan alfabetis Kelompok 1, Kelompok 2, dst.
                ->values();

        } else {
            $pesertaHadirL = 0;
            $pesertaHadirP = 0;
            $totalPesertaHadir = 0;
            $totalPanitiaHadir = 0;
            $namaSesiAktif = "Belum ada sesi";
            $detailKelompok = collect(); // Kosongkan jika belum ada sesi
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
            'pesertaHadirL', 
            'pesertaHadirP',
            'totalSeluruhPanitia',
            'namaSesiAktif',
            'detailKelompok' // 🟢 Pastikan variabel detailKelompok di-passing ke View
        ));
    }

    // ... method storeLink, destroyLink, dll ke bawah tetap sama dan tidak perlu diubah ...
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

    public function storePersonalBroadcast(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Hanya admin yang dapat membuat broadcast personal.');
        }

        if (!Schema::hasTable('personal_broadcasts') || !Schema::hasTable('personal_broadcast_recipients')) {
            return back()->with('error', 'Broadcast personal belum siap karena migrasi database belum dijalankan.');
        }

        $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'recipient_ids' => 'nullable|array',
            'recipient_ids.*' => 'exists:users,id',
        ]);

        $broadcast = PersonalBroadcast::create([
            'judul' => $request->judul,
            'konten' => $request->konten,
            'created_by' => auth()->id(),
        ]);

        $recipientIds = collect($request->input('recipient_ids', []))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => ['user_id' => (int) $id])
            ->all();

        if (!empty($recipientIds)) {
            $broadcast->recipients()->createMany($recipientIds);
        }

        return back()->with('success', 'Broadcast personal berhasil dikirim.');
    }

    public function updatePersonalBroadcast(Request $request, $id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Hanya admin yang dapat mengubah broadcast personal.');
        }

        if (!Schema::hasTable('personal_broadcasts') || !Schema::hasTable('personal_broadcast_recipients')) {
            return back()->with('error', 'Broadcast personal belum siap karena migrasi database belum dijalankan.');
        }

        $broadcast = PersonalBroadcast::findOrFail($id);

        $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'recipient_ids' => 'nullable|array',
            'recipient_ids.*' => 'exists:users,id',
        ]);

        $broadcast->update([
            'judul' => $request->judul,
            'konten' => $request->konten,
        ]);

        $newRecipientIds = collect($request->input('recipient_ids', []))
            ->filter(fn ($value) => is_numeric($value))
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values()
            ->all();

        $existingRecipients = $broadcast->recipients()->get()->keyBy('user_id');

        // Hapus peserta yang tidak lagi menjadi target
        $toDelete = $existingRecipients->keys()->diff($newRecipientIds);
        if ($toDelete->isNotEmpty()) {
            PersonalBroadcastRecipient::where('personal_broadcast_id', $broadcast->id)
                ->whereIn('user_id', $toDelete->all())
                ->delete();
        }

        // Pertahankan viewed_at untuk peserta yang masih ada,
        // dan tambahkan peserta baru tanpa viewed_at.
        $toKeep = $existingRecipients->keys()->intersect($newRecipientIds);
        $toCreate = collect($newRecipientIds)->diff($toKeep)->map(fn ($value) => ['user_id' => $value])->all();

        if (!empty($toCreate)) {
            $broadcast->recipients()->createMany($toCreate);
        }

        return redirect()->route('panitia.info.peserta.index', ['edit' => $broadcast->id])
            ->with('success', 'Broadcast personal berhasil diperbarui.');
    }

    public function destroyInfoPeserta($id)
    {
        InformasiPeserta::findOrFail($id)->delete();
        return back()->with('success', 'Pengumuman dihapus.');
    }

    public function destroyPersonalBroadcast($id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Hanya admin yang dapat menghapus broadcast personal.');
        }

        if (!Schema::hasTable('personal_broadcasts') || !Schema::hasTable('personal_broadcast_recipients')) {
            return back()->with('error', 'Broadcast personal belum siap karena migrasi database belum dijalankan.');
        }

        PersonalBroadcast::findOrFail($id)->delete();
        return back()->with('success', 'Broadcast personal dihapus.');
    }

    public function indexInfoPeserta(Request $request)
    {
        $infos = InformasiPeserta::latest()->get();
        $participants = User::where('role', 'peserta')->orderBy('name')->get();

        $broadcasts = collect();
        $editingBroadcast = null;

        if (Schema::hasTable('personal_broadcasts') && Schema::hasTable('personal_broadcast_recipients')) {
            $broadcasts = PersonalBroadcast::with('recipients.user')->latest()->get();
        }

        if ($request->filled('edit')) {
            $editingBroadcast = PersonalBroadcast::with('recipients')->findOrFail($request->query('edit'));
        }

        return view('panitia.informasi_peserta', compact('infos', 'participants', 'broadcasts', 'editingBroadcast'));
    }
}