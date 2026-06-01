<?php

namespace App\Http\Controllers;

use App\Models\RiwayatPenyakit;
use App\Models\User;
use Illuminate\Http\Request;

class KesehatanController extends Controller
{

    public function indexPanitia(Request $request)
    {
        // 1. Ambil list kelompok unik, lalu urutkan secara natural (untuk dropdown filter)
        $kelompokArray = User::where('role', 'peserta')
            ->whereNotNull('kelompok')
            ->distinct()
            ->pluck('kelompok')
            ->toArray();

        sort($kelompokArray, SORT_NATURAL | SORT_FLAG_CASE);
        $kelompokList = collect($kelompokArray);

        // 2. Query data riwayat penyakit
        $query = RiwayatPenyakit::query();

        if ($request->filled('kelompok')) {
            $query->where('kelompok', $request->kelompok);
        }

        // 3. Ambil data, kelompokkan, lalu urutkan key kelompoknya secara natural
        $groupedRiwayat = $query->orderBy('nama')
            ->get()
            ->groupBy('kelompok');

        // Paksa urutan key kelompok: Kelompok 1, Kelompok 2, ... Kelompok 10
        $semuaRiwayat = $groupedRiwayat->sortKeysUsing(function ($a, $b) {
            return strnatcasecmp($a, $b);
        });

        return view('panitia.kesehatan.index', compact('semuaRiwayat', 'kelompokList'));
    }
}
