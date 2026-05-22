<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mentoring;
use App\Models\MentoringDetail;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MentoringExport;
use App\Exports\MentoringSeluruhExport; // <--- Tambahkan ini

class MentoringController extends Controller
{
    public function index()
    {
        $listKelompok = User::where('role', 'peserta')
            ->whereNotNull('kelompok')
            ->distinct()
            ->orderBy('kelompok', 'asc')
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
            'nama_kegiatan' => 'required|string|max:255',
            'tanggal'       => 'required|date',
            'kehadiran'     => 'required|array',
        ]);

        $mentoring = Mentoring::create([
            'mentor_id'     => Auth::id(),
            'nama_kegiatan' => $request->nama_kegiatan,
            'kelompok'      => $kelompok,
            'tanggal'       => $request->tanggal,
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
            'kehadiran' => 'required|in:Hadir,Izin,Alpha',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $detail = MentoringDetail::findOrFail($id);
        $detail->update([
            'kehadiran' => $request->kehadiran,
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

    // --- FITUR EXPORT (SOLUSI ERROR TADI) ---
    public function export($kelompok)
    {
        // Pastikan kamu sudah membuat file App\Exports\MentoringExport
        return Excel::download(new MentoringExport($kelompok), 'mentoring_kelompok_' . $kelompok . '.xlsx');
    }

    public function rekapGlobal()
    {
        $rekapDetail = MentoringDetail::with(['mentoring', 'peserta'])
            ->get()
            ->groupBy([
                function ($item) {
                    return $item->peserta->kelompok;
                }, // Kelompokkan berdasarkan Kelompok
                function ($item) {
                    return $item->mentoring->nama_kegiatan;
                } // Lalu kelompokkan berdasarkan Kegiatan
            ])
            ->sortKeys(); // Urutkan Kelompok 1, 2, 3...

        return view('panitia.mentoring.rekap', compact('rekapDetail'));
    }

    public function exportSeluruh()
    {
        // Ini akan memanggil class Export yang mendownload SEMUA kelompok
        return Excel::download(new MentoringSeluruhExport, 'Master_Rekap_Mentoring.xlsx');
    }
}
