<?php

namespace App\Http\Controllers;

use App\Models\RiwayatPenyakit;
use App\Models\User;
use Illuminate\Http\Request;

class KesehatanController extends Controller
{
    
    public function indexPanitia(Request $request)
    {
        $kelompokList = User::where('role', 'peserta')
            ->whereNotNull('kelompok')
            ->distinct()
            ->orderBy('kelompok')
            ->pluck('kelompok');

        $query = RiwayatPenyakit::query();

        if ($request->filled('kelompok')) {
            $query->where('kelompok', $request->kelompok);
        }

        // Ambil data dan kelompokkan berdasarkan kolom 'kelompok'
        $semuaRiwayat = $query->orderByRaw("FIELD(kondisi_kesehatan, 'Perlu Perhatian', 'Cukup', 'Baik')")
                            ->orderBy('nama')
                            ->get()
                            ->groupBy('kelompok'); // <--- PENTING: Mengelompokkan koleksi

        return view('panitia.kesehatan.index', compact('semuaRiwayat', 'kelompokList'));
    }
}