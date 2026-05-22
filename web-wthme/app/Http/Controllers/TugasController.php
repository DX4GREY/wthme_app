<?php

namespace App\Http\Controllers;

use App\Exports\TugasExport;
use App\Models\TugasKategori;
use App\Models\TugasPengumpulan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class TugasController extends Controller
{
    // ===== SISI PANITIA =====

    /** Halaman kelola tugas: daftar kategori + buat baru */
    public function indexPanitia()
    {
        $tugasList = TugasKategori::withCount('pengumpulan')
            ->orderBy('urutan')
            ->orderBy('created_at')
            ->get();

        $totalPeserta = User::where('role', 'peserta')->count();

        return view('panitia.tugas.index', compact('tugasList', 'totalPeserta'));
    }

    /** Simpan kategori tugas baru */
    public function storeTugas(Request $request)
    {
        $request->validate([
            'nama_tugas'    => 'required|string|max:255',
            'deskripsi'     => 'nullable|string|max:1000',
            'file_petunjuk' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240', // Max 10MB
            'deadline'      => 'nullable|date',
            'tipe_file'     => 'required|in:semua,pdf,gambar',
            'maks_ukuran'   => 'required|integer|min:512|max:51200',
            'urutan'        => 'nullable|integer|min:0',
        ]);

        $pathPetunjuk = null;
        if ($request->hasFile('file_petunjuk')) {
            // Simpan ke storage/app/public/petunjuk-tugas
            $pathPetunjuk = $request->file('file_petunjuk')->store('petunjuk-tugas', 'public');
        }

        \App\Models\TugasKategori::create([
            'nama_tugas'    => $request->nama_tugas,
            'deskripsi'     => $request->deskripsi,
            'file_petunjuk' => $pathPetunjuk,
            'deadline'      => $request->deadline,
            'aktif'         => true,
            'tipe_file'     => $request->tipe_file,
            'maks_ukuran'   => $request->maks_ukuran,
            'urutan'        => $request->urutan ?? \App\Models\TugasKategori::max('urutan') + 1,
            'dibuat_oleh'   => auth()->id(),
        ]);

        return back()->with('success', 'Tugas berhasil dibuat!');
    }

    /** Toggle aktif/nonaktif tugas */
    public function toggleTugas($id)
    {
        $tugas = TugasKategori::findOrFail($id);
        $tugas->update(['aktif' => !$tugas->aktif]);
        return back()->with('success', 'Status tugas diperbarui.');
    }

    /** Hapus kategori tugas (dan semua file pengumpulan) */
    public function destroyTugas($id)
    {
        $tugas = TugasKategori::findOrFail($id);

        // Hapus semua file
        foreach ($tugas->pengumpulan as $p) {
            Storage::disk('public')->delete($p->file_path);
        }

        $tugas->delete();

        return back()->with('success', 'Tugas berhasil dihapus.');
    }

    /** Rekap pengumpulan: tabel peserta × tugas, dikelompokkan per kelompok */
    public function rekap(Request $request)
    {
        $tugasList = TugasKategori::orderBy('urutan')->orderBy('created_at')->get();

        // Ambil semua peserta, dikelompokkan per kelompok
        $pesertaQuery = User::where('role', 'peserta')
            ->orderBy('kelompok')
            ->orderBy('name')
            ->get();

        // Map: user_id → [tugas_kategori_id => TugasPengumpulan]
        $pengumpulanMap = TugasPengumpulan::all()
            ->groupBy('user_id')
            ->map(fn($items) => $items->keyBy('tugas_kategori_id'));

        // Filter kelompok
        $filterKelompok = $request->kelompok;
        if ($filterKelompok) {
            $pesertaQuery = $pesertaQuery->where('kelompok', $filterKelompok);
        }

        $pesertaPerKelompok = $pesertaQuery->groupBy('kelompok');
        $kelompokList       = User::where('role', 'peserta')
            ->distinct()
            ->orderBy('kelompok')
            ->pluck('kelompok');

        // Statistik per tugas
        $statsPerTugas = $tugasList->map(function ($tugas) use ($pesertaQuery) {
            $sudahKumpul = TugasPengumpulan::where('tugas_kategori_id', $tugas->id)->count();
            return [
                'id'           => $tugas->id,
                'sudah_kumpul' => $sudahKumpul,
                'terlambat'    => TugasPengumpulan::where('tugas_kategori_id', $tugas->id)
                    ->where('status', 'terlambat')->count(),
            ];
        })->keyBy('id');

        return view('panitia.tugas.rekap', compact(
            'tugasList',
            'pesertaPerKelompok',
            'pengumpulanMap',
            'kelompokList',
            'filterKelompok',
            'statsPerTugas'
        ));
    }

    /** Download file tugas peserta */
    public function downloadFile($id)
    {
        $p = TugasPengumpulan::findOrFail($id);

        if (!Storage::disk('public')->exists($p->file_path)) {
            return back()->with('error', 'File tidak ditemukan.');
        }

        return Storage::disk('public')->download($p->file_path, $p->file_nama_asli);
    }

    /** Export rekap ke Excel */
    public function exportRekap()
    {
        return Excel::download(
            new TugasExport(),
            'rekap-tugas-' . date('Ymd') . '.xlsx'
        );
    }

    // ===== SISI PESERTA =====

    /** Halaman daftar tugas + status pengumpulan peserta */
    public function indexPeserta()
    {
        $user      = auth()->user();
        $tugasList = TugasKategori::where('aktif', true)
            ->orderBy('urutan')
            ->orderBy('created_at')
            ->get();

        // Pengumpulan peserta ini
        $sudahKumpul = TugasPengumpulan::where('user_id', $user->id)
            ->get()
            ->keyBy('tugas_kategori_id');

        return view('peserta.tugas', compact('tugasList', 'sudahKumpul', 'user'));
    }

    /** Upload / replace file tugas peserta */
    public function uploadTugas(Request $request)
    {
        $user      = auth()->user();
        $kategoriId = $request->tugas_kategori_id;
        $tugas     = TugasKategori::where('id', $kategoriId)
            ->where('aktif', true)
            ->firstOrFail();

        // Validasi ekstensi dinamis
        $ekstensiOke = implode(',', $tugas->ekstensiDiizinkan());
        $maksKb      = $tugas->maks_ukuran; // dalam KB

        $request->validate([
            'tugas_kategori_id' => 'required|exists:tugas_kategori,id',
            'file_tugas'        => "required|file|mimes:{$ekstensiOke}|max:{$maksKb}",
            'catatan'           => 'nullable|string|max:500',
        ], [
            'file_tugas.mimes' => 'Format file tidak diizinkan. File yang boleh: ' . strtoupper($ekstensiOke),
            'file_tugas.max'   => 'Ukuran file melebihi batas (' . round($maksKb / 1024, 1) . ' MB).',
        ]);

        $file       = $request->file('file_tugas');
        $ekstensi   = strtolower($file->getClientOriginalExtension());
        $namaFile   = $user->nim . '_' . $tugas->id . '_' . time() . '.' . $ekstensi;
        $filePath   = $file->storeAs('tugas-pengumpulan', $namaFile, 'public');

        // Cek sudah pernah kumpul
        $existing = TugasPengumpulan::where('user_id', $user->id)
            ->where('tugas_kategori_id', $kategoriId)
            ->first();

        $status = $tugas->isTerlambat() ? 'terlambat' : 'tepat_waktu';

        if ($existing) {
            // Hapus file lama
            Storage::disk('public')->delete($existing->file_path);

            $existing->update([
                'file_path'       => $filePath,
                'file_nama_asli'  => $file->getClientOriginalName(),
                'file_ekstensi'   => $ekstensi,
                'file_ukuran'     => $file->getSize(),
                'status'          => $status,
                'catatan'         => $request->catatan,
                'dikumpulkan_at'  => now(),
            ]);

            $msg = 'Tugas berhasil diperbarui!';
        } else {
            TugasPengumpulan::create([
                'user_id'           => $user->id,
                'tugas_kategori_id' => $kategoriId,
                'nama'              => $user->name,
                'nim'               => $user->nim,
                'kelompok'          => $user->kelompok,
                'file_path'         => $filePath,
                'file_nama_asli'    => $file->getClientOriginalName(),
                'file_ekstensi'     => $ekstensi,
                'file_ukuran'       => $file->getSize(),
                'status'            => $status,
                'catatan'           => $request->catatan,
                'dikumpulkan_at'    => now(),
            ]);

            $msg = 'Tugas berhasil dikumpulkan! 🎉';
        }

        return back()->with('success', $msg);
    }
}
