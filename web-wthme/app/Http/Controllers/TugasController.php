<?php

namespace App\Http\Controllers;

use App\Exports\TugasExport;
use App\Models\TugasKategori;
use App\Models\TugasPengumpulan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;

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
            'file_petunjuk' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'deadline'      => 'nullable|date',
            'tipe_file'     => 'required|in:semua,pdf,gambar',
            'maks_ukuran'   => 'required|integer|min:512|max:51200',
            'urutan'        => 'nullable|integer|min:0',
        ]);

        $pathPetunjuk = null;
        if ($request->hasFile('file_petunjuk')) {
            $pathPetunjuk = $request->file('file_petunjuk')->store('petunjuk-tugas', 'public');
        }

        TugasKategori::create([
            'nama_tugas'    => $request->nama_tugas,
            'deskripsi'     => $request->deskripsi,
            'file_petunjuk' => $pathPetunjuk,
            'deadline'      => $request->deadline,
            'aktif'         => true,
            'tipe_file'     => $request->tipe_file,
            'maks_ukuran'   => $request->maks_ukuran,
            'urutan'        => $request->urutan ?? (TugasKategori::max('urutan') + 1),
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
        $tugas = TugasKategori::with('pengumpulan')->findOrFail($id);

        foreach ($tugas->pengumpulan as $p) {
            $files = json_decode($p->file_path, true);
            if (is_array($files)) {
                foreach ($files as $file) {
                    if (isset($file['path'])) {
                        Storage::disk('public')->delete($file['path']);
                    }
                }
            } else {
                Storage::disk('public')->delete($p->file_path);
            }
        }

        $tugas->delete();

        return back()->with('success', 'Tugas berhasil dihapus.');
    }

    /** REKAP OPTIMIZED: Diurutkan berdasarkan kelompok secara natural numerik */
    public function rekap(Request $request)
    {
        // 1. Ambil semua tugas
        $tugasList = TugasKategori::orderBy('urutan')->orderBy('created_at')->get();

        // 2. Filter & Ambil data peserta
        $filterKelompok = $request->kelompok;
        $pesertaQuery = User::where('role', 'peserta');
        
        if ($filterKelompok) {
            $pesertaQuery->where('kelompok', $filterKelompok);
        }

        // MENGGUNAKAN orderByRaw AGAR URUTAN KELOMPOK TIDAK NGACAK (Urutan Alami: 1, 2, 3... dst)
        $pesertaData = $pesertaQuery->orderByRaw('CAST(kelompok AS UNSIGNED) ASC')
            ->orderBy('name')
            ->get();
            
        $pesertaPerKelompok = $pesertaData->groupBy('kelompok');

        // 3. Eager loading peta pengumpulan agar tidak query berulang di baris tabel
        $pesertaIds = $pesertaData->pluck('id')->toArray();
        $pengumpulanMap = TugasPengumpulan::whereIn('user_id', $pesertaIds)
            ->get()
            ->groupBy('user_id')
            ->map(fn($items) => $items->keyBy('tugas_kategori_id'));

        // 4. Urutan Kelompok pada Dropdown Filter disesuaikan agar rapi secara numerik
        $kelompokList = User::where('role', 'peserta')
            ->distinct()
            ->orderByRaw('CAST(kelompok AS UNSIGNED) ASC')
            ->pluck('kelompok');

        // 5. Total seluruh peserta (untuk indikator card statistik)
        $totalPeserta = User::where('role', 'peserta')->count();

        // 6. OPTIMASI STATISTIK: Agregasi langsung via Group By DB
        $globalStats = TugasPengumpulan::select('tugas_kategori_id')
            ->selectRaw('count(*) as sudah_kumpul')
            ->selectRaw("sum(case when status = 'terlambat' then 1 else 0 end) as terlambat")
            ->groupBy('tugas_kategori_id')
            ->get()
            ->keyBy('tugas_kategori_id');

        $statsPerTugas = $tugasList->mapWithKeys(function ($tugas) use ($globalStats) {
            $stat = $globalStats->get($tugas->id);
            return [
                $tugas->id => [
                    'id'           => $tugas->id,
                    'sudah_kumpul' => $stat ? $stat->sudah_kumpul : 0,
                    'terlambat'    => $stat ? (int)$stat->terlambat : 0,
                ]
            ];
        });

        return view('panitia.tugas.rekap', compact(
            'tugasList',
            'pesertaPerKelompok',
            'pengumpulanMap',
            'kelompokList',
            'filterKelompok',
            'statsPerTugas',
            'totalPeserta'
        ));
    }

    /** MENGAMBIL LIST BERKAS JAVASCRIPT: Endpoint API internal untuk mengambil detail file di modal */
    public function getFilesJson($id)
    {
        $p = TugasPengumpulan::findOrFail($id);
        $files = json_decode($p->file_path, true);
        
        $formattedFiles = [];
        
        if (is_array($files)) {
            foreach ($files as $index => $file) {
                $sizeInBytes = $file['ukuran'] ?? 0;
                if ($sizeInBytes >= 1048576) {
                    $formattedSize = round($sizeInBytes / 1048576, 2) . ' MB';
                } else {
                    $formattedSize = round($sizeInBytes / 1024, 1) . ' KB';
                }

                $formattedFiles[] = [
                    'id'        => $index,
                    'nama_asli' => $file['nama_asli'] ?? 'File_' . ($index + 1),
                    'ekstensi'  => $file['ekstensi'] ?? 'file',
                    'ukuran'    => $formattedSize
                ];
            }
        } else {
            $formattedFiles[] = [
                'id'        => 0,
                'nama_asli' => $p->file_nama_asli ?? 'Berkas Utama',
                'ekstensi'  => $p->file_ekstensi ?? 'file',
                'ukuran'    => '-'
            ];
        }

        return response()->json(['files' => $formattedFiles]);
    }

    /** MENGIKUTI LINK DI MODAL: Download berkas tunggal yang dipilih dari dalam pop-up modal */
    public function downloadSingleFile($id, $fileIndex)
    {
        $p = TugasPengumpulan::findOrFail($id);
        $files = json_decode($p->file_path, true);

        if (is_array($files) && isset($files[$fileIndex])) {
            $targetFile = $files[$fileIndex];
            
            if (!Storage::disk('public')->exists($targetFile['path'])) {
                return back()->with('error', 'Berkas fisik tidak ditemukan di server.');
            }

            return response()->download(
                Storage::disk('public')->path($targetFile['path']), 
                $targetFile['nama_asli']
            );
        }

        if (!is_array($files) && Storage::disk('public')->exists($p->file_path)) {
            return response()->download(
                Storage::disk('public')->path($p->file_path), 
                $p->file_nama_asli ?? 'file'
            );
        }

        return back()->with('error', 'Berkas tidak valid atau tidak ditemukan.');
    }

    /** SEAMLESS VIEW/DOWNLOAD ZIP KOLEKTIF: Tombol "Download Zip" di dalam modal */
    public function downloadFile($id)
    {
        $p = TugasPengumpulan::findOrFail($id);
        $files = json_decode($p->file_path, true);

        if (is_array($files)) {
            if (count($files) === 0) {
                return back()->with('error', 'Tidak ada file di dalam sistem.');
            }

            if (count($files) === 1) {
                $singleFile = $files[0]['path'];
                if (!Storage::disk('public')->exists($singleFile)) {
                    return back()->with('error', 'File tidak ditemukan di storage server.');
                }
                return response()->file(Storage::disk('public')->path($singleFile));
            }

            $zipFileName = 'Tugas_' . $p->nim . '_' . $p->tugas_kategori_id . '.zip';
            $zipPath = storage_path('app/public/' . $zipFileName);

            $zip = new ZipArchive;
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                foreach ($files as $file) {
                    if (Storage::disk('public')->exists($file['path'])) {
                        $fullPath = Storage::disk('public')->path($file['path']);
                        $zip->addFile($fullPath, $file['nama_asli']);
                    }
                }
                $zip->close();

                return response()->download($zipPath)->deleteFileAfterSend(true);
            }

            return back()->with('error', 'Gagal membuat file kompresi ZIP.');
        }

        if (!Storage::disk('public')->exists($p->file_path)) {
            return back()->with('error', 'File tidak ditemukan.');
        }

        return response()->file(Storage::disk('public')->path($p->file_path));
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

        $sudahKumpul = TugasPengumpulan::where('user_id', $user->id)
            ->get()
            ->keyBy('tugas_kategori_id');

        return view('peserta.tugas', compact('tugasList', 'sudahKumpul', 'user'));
    }

    /** Upload / replace file tugas peserta */
    public function uploadTugas(Request $request)
    {
        $user       = auth()->user();
        $kategoriId = $request->tugas_kategori_id;
        $tugas      = TugasKategori::where('id', $kategoriId)
            ->where('aktif', true)
            ->firstOrFail();

        $ekstensiOke = implode(',', $tugas->ekstensiDiizinkan());
        $maksKb      = $tugas->maks_ukuran;

        $request->validate([
            'tugas_kategori_id' => 'required|exists:tugas_kategori,id',
            'file_tugas'        => 'required|array',
            'file_tugas.*'      => "file|mimes:{$ekstensiOke}|max:{$maksKb}",
            'catatan'           => 'nullable|string|max:500',
        ], [
            'file_tugas.required' => 'Wajib memilih file untuk dikumpulkan.',
            'file_tugas.*.mimes'  => 'Ada format file tidak diizinkan. File yang boleh: ' . strtoupper($ekstensiOke),
            'file_tugas.*.max'    => 'Ada ukuran file yang melebihi batas (' . round($maksKb / 1024, 1) . ' MB).',
        ]);

        $existing = TugasPengumpulan::where('user_id', $user->id)
            ->where('tugas_kategori_id', $kategoriId)
            ->first();

        if ($existing) {
            $oldFiles = json_decode($existing->file_path, true);
            if (is_array($oldFiles)) {
                foreach ($oldFiles as $oldFile) {
                    if (isset($oldFile['path'])) {
                        Storage::disk('public')->delete($oldFile['path']);
                    }
                }
            } else {
                Storage::disk('public')->delete($existing->file_path);
            }
        }

        $filesData = [];
        $totalSize = 0;
        $listNamaAsli = [];
        $listEkstensi = [];

        foreach ($request->file('file_tugas') as $index => $file) {
            $ekstensi = strtolower($file->getClientOriginalExtension());
            $namaFile = $user->nim . '_' . $tugas->id . '_' . time() . '_' . $index . '.' . $ekstensi;
            $filePath = $file->storeAs('tugas-pengumpulan', $namaFile, 'public');

            $filesData[] = [
                'path'      => $filePath,
                'nama_asli' => $file->getClientOriginalName(),
                'ekstensi'  => $ekstensi,
                'ukuran'    => $file->getSize(),
            ];

            $totalSize += $file->getSize();
            $listNamaAsli[] = $file->getClientOriginalName();
            $listEkstensi[] = $ekstensi;
        }

        $status = $tugas->isTerlambat() ? 'terlambat' : 'tepat_waktu';

        $jsonFilePath = json_encode($filesData);
        $stringNamaAsli = implode(', ', $listNamaAsli);
        $stringEkstensi = implode(', ', array_unique($listEkstensi));

        if ($existing) {
            $existing->update([
                'file_path'       => $jsonFilePath,
                'file_nama_asli'  => $stringNamaAsli,
                'file_ekstensi'   => $stringEkstensi,
                'file_ukuran'     => $totalSize,
                'status'          => $status,
                'catatan'         => $request->catatan,
                'dikumpulkan_at'  => now(),
            ]);

            $msg = 'Tugas berhasil diperbarui dengan ' . count($filesData) . ' file!';
        } else {
            TugasPengumpulan::create([
                'user_id'           => $user->id,
                'tugas_kategori_id' => $kategoriId,
                'nama'              => $user->name,
                'nim'               => $user->nim,
                'kelompok'          => $user->kelompok,
                'file_path'         => $jsonFilePath,
                'file_nama_asli'    => $stringNamaAsli,
                'file_ekstensi'     => $stringEkstensi,
                'file_ukuran'       => $totalSize,
                'status'            => $status,
                'catatan'           => $request->catatan,
                'dikumpulkan_at'    => now(),
            ]);

            $msg = 'Tugas berhasil dikumpulkan! 🎉 (' . count($filesData) . ' file)';
        }

        return back()->with('success', $msg);
    }
}