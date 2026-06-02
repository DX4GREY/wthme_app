<?php

namespace App\Http\Controllers;

use App\Models\RiwayatPenyakit;
use App\Models\User;
use Illuminate\Http\Request;

class KesehatanController extends Controller
{

    public function indexPanitia(Request $request)
    {
        // 1. Ambil list kelompok unik (untuk dropdown filter)
        $kelompokArray = User::where('role', 'peserta')
            ->whereNotNull('kelompok')
            ->distinct()
            ->pluck('kelompok')
            ->toArray();

        sort($kelompokArray, SORT_NATURAL | SORT_FLAG_CASE);
        $kelompokList = collect($kelompokArray);

        // 2. Query data riwayat penyakit
        $query = RiwayatPenyakit::query();

        // Filter berdasarkan Kelompok
        if ($request->filled('kelompok')) {
            $query->where('kelompok', $request->kelompok);
        }

        // --- TAMBAHAN: Filter berdasarkan Warna Pita ---
        if ($request->filled('pita')) {
            if ($request->pita === 'Tanpa Pita') {
                $query->whereNull('warna_pita');
            } else {
                $query->where('warna_pita', $request->pita);
            }
        }

        // 3. Ambil data, kelompokkan, lalu urutkan key kelompoknya secara natural
        $groupedRiwayat = $query->orderBy('nama')
            ->get()
            ->groupBy('kelompok');

        $semuaRiwayat = $groupedRiwayat->sortKeysUsing(function ($a, $b) {
            return strnatcasecmp($a, $b);
        });

        return view('panitia.kesehatan.index', compact('semuaRiwayat', 'kelompokList'));
    }

    public function updateWarnaPita(Request $request, $id)
    {
        try {
            // 1. Cari data berdasarkan ID yang dikirim dari view
            // (Sesuaikan RiwayatKesehatan dengan nama model data medis Anda)
            $riwayat = RiwayatPenyakit::findOrFail($id);

            // 2. Update warna pitanya
            $riwayat->warna_pita = $request->warna_pita;
            $riwayat->save();

            // 3. WAJIB: Kembalikan response JSON sukses
            return response()->json([
                'success' => true,
                'message' => 'Status pita berhasil diperbarui.'
            ], 200);
        } catch (\Exception $e) {
            // Jika ada error (misal ID tidak ditemukan atau database error)
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
