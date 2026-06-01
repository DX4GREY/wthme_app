<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mentoring;
use App\Models\MentoringDetail;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MentoringExport;
use App\Exports\MentoringSeluruhExport;

class MentoringController extends Controller
{
    public function index()
    {
        $listKelompok = User::where('role', 'peserta')
            ->whereNotNull('kelompok')
            ->distinct()
            // FIX: Ubah string kelompok jadi Integer sebelum di-order agar urutannya pas (1, 2, 3... 10)
            ->orderByRaw('CAST(kelompok AS UNSIGNED) ASC')
            ->pluck('kelompok')
            ->toArray();

        return view('panitia.mentoring.from', compact('listKelompok'));
    }

    public function kelompok($kelompok)
    {
        $peserta = User::where('role', 'peserta')
            ->where('kelompok', $kelompok)
            ->orderBy('name')
            ->get();

        $mentorings = Mentoring::where('kelompok', $kelompok)
            ->with('details.peserta') // Eager loading supaya tidak berat
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('panitia.mentoring.from', compact('peserta', 'kelompok', 'mentorings'));
    }

    public function store(Request $request, $kelompok)
    {
        $request->validate([
            'nama_kegiatan'     => 'required|string|max:255',
            'tanggal'           => 'required|date',
            'kehadiran'         => 'required|array',
            'catatan_pertemuan' => 'nullable|string',
        ]);

        // MENYIMPAN 'catatan_pertemuan' dari inputan mentor
        $mentoring = Mentoring::create([
            'mentor_id'         => Auth::id(),
            'nama_kegiatan'     => $request->nama_kegiatan,
            'kelompok'          => $kelompok,
            'tanggal'           => $request->tanggal,
            'catatan_pertemuan' => $request->catatan_pertemuan,
        ]);

        foreach ($request->kehadiran as $peserta_id => $status) {
            MentoringDetail::create([
                'mentoring_id' => $mentoring->id,
                'peserta_id'   => $peserta_id,
                'kehadiran'    => $status,
                'keterangan'   => $request->keterangan[$peserta_id] ?? '',
            ]);
        }

        return back()->with('success', 'Catatan mentoring kelompok ' . $kelompok . ' berhasil disimpan!');
    }

    // --- FITUR UPDATE (EDIT) ---
    public function updateDetail(Request $request, $id)
    {
        $request->validate([
            'kehadiran'  => 'required|in:Hadir,Izin,Alpha',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $detail = MentoringDetail::findOrFail($id);
        $detail->update([
            'kehadiran'  => $request->kehadiran,
            'keterangan' => $request->keterangan,
        ]);

        return back()->with('success', 'Data kehadiran berhasil diperbarui!');
    }

    // --- FITUR HAPUS ---
    public function destroy($id)
    {
        $mentoring = Mentoring::findOrFail($id);

        // Hapus detail secara manual jika di database tidak pakai 'onDelete cascade'
        $mentoring->details()->delete();
        $mentoring->delete();

        return back()->with('success', 'Riwayat kegiatan berhasil dihapus!');
    }

    // --- FITUR EXPORT ---
    public function export($kelompok)
    {
        return Excel::download(new MentoringExport($kelompok), 'mentoring_kelompok_' . $kelompok . '.xlsx');
    }

    // FIX: Menggunakan nama rekapGlobal() agar singkron dengan route, 
    // serta diproteksi agar data kosong/null tidak bikin sistem crash.
    public function rekapGlobal()
    {
        $rekapDetail = MentoringDetail::with(['mentoring', 'peserta'])
            ->whereHas('peserta', function ($query) {
                $query->whereNotNull('kelompok');
            })
            ->whereHas('mentoring', function ($query) {
                $query->whereNotNull('nama_kegiatan');
            })
            ->get()
            ->groupBy([
                function ($item) {
                    return $item->peserta->kelompok ?? 'Tanpa Kelompok';
                },
                function ($item) {
                    return $item->mentoring->nama_kegiatan ?? 'Kegiatan Tanpa Nama';
                }
            ])
            ->sortKeys();

        return view('panitia.mentoring.rekap', compact('rekapDetail'));
    }

    public function exportSeluruh()
    {
        return Excel::download(new MentoringSeluruhExport, 'Master_Rekap_Mentoring.xlsx');
    }
}
