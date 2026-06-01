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

        // Urutan langsung dirapikan berdasarkan nama alfabetis
        $semuaRiwayat = $query->orderBy('nama')
                            ->get()
                            ->groupBy('kelompok'); // Mengelompokkan koleksi berdasarkan kelompok masing-masing

        return view('panitia.kesehatan.index', compact('semuaRiwayat', 'kelompokList'));
    }
}