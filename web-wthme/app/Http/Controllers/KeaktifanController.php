<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PoinKeaktifan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KeaktifanController extends Controller
{
    public function index()
    {
        // 1. Ambil data peserta
        $pesertas = User::where('role', 'peserta')->orderBy('name')->get();
        
        // 2. Ambil riwayat log menggunakan Eager Loading (Jauh lebih bersih dibanding Join SQL manual)
        $riwayatLog = PoinKeaktifan::with(['peserta', 'panitia'])
            ->latest()
            ->paginate(10);

        return view('panitia.keaktifan.index', compact('pesertas', 'riwayatLog'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'peserta_id' => 'required|exists:users,id',
            'poin'       => 'required|integer',
            // 'keterangan' => 'string|max:255',
        ]);

        // CUKUP GUNAKAN INI SAJA (Hapus DB::table agar tidak double input)
        PoinKeaktifan::create([
            'peserta_id' => $request->peserta_id,
            'panitia_id' => Auth::id(), // Mengunci ID panitia yang sedang login
            'poin'       => $request->poin,
            'keterangan' => $request->keterangan,
        ]);

        return back()->with('success', 'Poin berhasil ditambahkan!');
    }

    public function destroy($id)
    {
        // Menghapus data menggunakan Eloquent
        $log = PoinKeaktifan::findOrFail($id);
        $log->delete();

        return back()->with('success', 'Poin berhasil dihapus.');
    }
}