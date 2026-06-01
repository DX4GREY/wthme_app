<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Imports\PanitiaImport;
use App\Imports\PesertaImport;
use App\Exports\TemplatePesertaExport; // Pastikan kelas export ini sudah dibuat
use Maatwebsite\Excel\Facades\Excel;

class AdminController extends Controller
{
    /**
     * Halaman Utama Panel Admin
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        // 1. Query Panitia dengan Filter
        $panitiaQuery = User::where('role', 'panitia');
        if ($search) {
            $panitiaQuery->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('nim', 'LIKE', "%{$search}%");
            });
        }
        $panitiaList = $panitiaQuery->orderBy('divisi')->orderBy('name')->get();

        // 2. Query Peserta dengan Filter
        $pesertaQuery = User::where('role', 'peserta');
        if ($search) {
            $pesertaQuery->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('nim', 'LIKE', "%{$search}%");
            });
        }
        $pesertaList = $pesertaQuery->orderBy('kelompok')->orderBy('name')->get();

        // 3. Statistik (Tetap hitung total asli tanpa filter search)
        $totalPeserta = User::where('role', 'peserta')->count();
        $totalPanitia = User::where('role', 'panitia')->count();

        return view('admin.index', compact('totalPeserta', 'totalPanitia', 'panitiaList', 'pesertaList'));
    }

    /**
     * Tampilkan Form Import Panitia
     */
    public function importForm()
    {
        return view('admin.import-panitia');
    }

    /**
     * Proses Upload & Import Panitia
     */
    public function importStore(Request $request)
    {
        set_time_limit(0);
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ], [
            'file.required' => 'File Excel wajib dipilih.',
            'file.mimes'    => 'File harus berformat .xlsx atau .xls.',
            'file.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        $import = new PanitiaImport();
        Excel::import($import, $request->file('file'));

        $imported = $import->getImported();
        $skipped  = $import->getSkipped();

        $message  = count($imported) . ' akun panitia berhasil dibuat.';
        if (count($skipped) > 0) {
            $message .= ' ' . count($skipped) . ' data dilewati (sudah ada).';
        }

        return redirect()->route('admin.panitia')
            ->with('success', $message)
            ->with('imported', $imported)
            ->with('skipped', $skipped);
    }

    /**
     * Hapus Akun Panitia
     */
    public function deletePanitia($id)
    {
        $user = User::where('id', $id)->where('role', 'panitia')->firstOrFail();
        $user->delete();

        return back()->with('success', 'Akun panitia ' . $user->name . ' berhasil dihapus.');
    }

    /**
     * Reset Password Panitia ke NIM
     */
    public function resetPassword($id)
    {
        $user = User::where('id', $id)->where('role', 'panitia')->firstOrFail();
        $user->update([
            'password'             => bcrypt($user->nim),
            'must_change_password' => true,
        ]);

        return back()->with('success', 'Password ' . $user->name . ' berhasil direset ke NIM.');
    }

    /**
     * Edit Data Panitia
     */
    public function editPanitia($id)
    {
        $panitia = User::where('id', $id)->where('role', 'panitia')->firstOrFail();
        return view('admin.edit-panitia', compact('panitia'));
    }

    public function updatePanitia(Request $request, $id)
    {
        $panitia = User::where('id', $id)->where('role', 'panitia')->firstOrFail();

        $request->validate([
            'name'     => 'required|string|max:255',
            'nim'      => 'required|string|unique:users,nim,' . $id,
            'angkatan' => 'required|string',
            'divisi'   => 'required|string',
            'email'    => 'required|email|unique:users,email,' . $id,
        ]);

        $panitia->update($request->only('name', 'nim', 'angkatan', 'divisi', 'email'));

        return redirect()->route('admin.panitia')
            ->with('success', 'Data panitia ' . $panitia->name . ' berhasil diperbarui.');
    }

    /**
     * Download Template Excel Panitia (Statis via Folder Public)
     */
    public function downloadTemplate()
    {
        $path = public_path('templates/template-import-panitia.xlsx');
        
        if (!file_exists($path)) {
            return back()->with('error', 'File template panitia belum tersedia di server.');
        }

        return response()->download($path, 'template-import-panitia.xlsx');
    }

    /**
     * Tampilkan Form Import Peserta
     */
    public function importPesertaForm()
    {
        return view('admin.import-peserta');
    }

    /**
     * Proses Upload & Import Peserta
     */
    public function importPesertaStore(Request $request)
    {
        set_time_limit(0);
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ], [
            'file.required' => 'File Excel peserta wajib dipilih.',
            'file.mimes'    => 'File harus berformat .xlsx atau .xls.',
            'file.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        $import = new PesertaImport();
        Excel::import($import, $request->file('file'));

        $message = count($import->getImported()) . ' data peserta berhasil diimport.';
        if (count($import->getSkipped()) > 0) {
            $message .= ' ' . count($import->getSkipped()) . ' data dilewati.';
        }

        return redirect()->route('admin.index')
            ->with('success', $message)
            ->with('imported', $import->getImported())
            ->with('skipped', $import->getSkipped());
    }

    /**
     * Download Template Excel Peserta (On-The-Fly Tanpa File Fisik / Anti-Error)
     */
    public function downloadTemplatePeserta()
    {
        // Mengunduh instan lewat class Export agar terhindar dari FileNotFoundException
        return Excel::download(new TemplatePesertaExport, 'template-import-peserta.xlsx');
    }

    /**
     * Reset Password Peserta ke NIM
     */
    public function resetPasswordPeserta($id)
    {
        $user = User::where('id', $id)->where('role', 'peserta')->firstOrFail();

        $user->update([
            'password'             => bcrypt($user->nim),
            'must_change_password' => true,
        ]);

        return back()->with('success', 'Password peserta ' . $user->name . ' berhasil direset ke NIM.');
    }
}