<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    /**
     * Tampilkan daftar peserta untuk dipilih admin (via JSON)
     */
    public function getPesertaList()
    {
        $pesertas = User::where('role', 'peserta')
            ->orderBy('kelompok')
            ->orderBy('name')
            ->get(['id', 'name', 'nim', 'kelompok', 'gender']);

        return response()->json($pesertas);
    }

    /**
     * Admin login sebagai peserta tertentu (impersonasi)
     */
    public function loginAsPeserta($id)
    {
        $peserta = User::where('role', 'peserta')->findOrFail($id);

        // Simpan data admin asli ke session
        session()->put('impersonator_id', Auth::id());
        session()->put('impersonator_role', Auth::user()->role);

        // Login sebagai peserta
        Auth::login($peserta);

        return redirect()->route('peserta.index')
            ->with('success', 'Anda sedang melihat portal sebagai ' . $peserta->name);
    }

    /**
     * Keluar dari mode impersonasi, kembali ke admin
     */
    public function leave()
    {
        $adminId = session()->pull('impersonator_id');
        $adminRole = session()->pull('impersonator_role');

        if ($adminId) {
            $admin = User::find($adminId);
            if ($admin) {
                Auth::login($admin);
                return redirect()->route('dashboard')
                    ->with('success', 'Kembali ke dashboard admin.');
            }
        }

        // Fallback: logout saja
        Auth::logout();
        return redirect()->route('login');
    }
}