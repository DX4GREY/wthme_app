<?php

namespace App\Http\Controllers;

use App\Exports\AbsensiPesertaExport;
use App\Exports\AbsensiPanitiaExport;
use App\Exports\KesehatanExport;
use App\Exports\NotulensiExport;
use App\Models\QrSession;
use Illuminate\Http\Request; // Tambahkan ini
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function exportPeserta(Request $request)
    {
        $sessionId = $request->query('session_id');
        
        if ($sessionId) {
            // Jika ekspor per sesi tertentu
            $sesi = QrSession::findOrFail($sessionId);
            $namaFile = 'Absensi_' . str_replace(' ', '_', $sesi->nama_sesi) . '_' . date('Ymd_His') . '.xlsx';
        } else {
            // Jika ekspor semua data (global)
            $namaFile = 'Rekap_Total_Absensi_Peserta_' . date('Ymd') . '.xlsx';
        }

        return Excel::download(
            new AbsensiPesertaExport($sessionId),
            $namaFile
        );
    }

    public function exportPanitia()
    {
        return Excel::download(
            new AbsensiPanitiaExport(),
            'absensi-panitia-pkkmb-' . date('Ymd') . '.xlsx'
        );
    }

    public function exportKesehatan()
    {
        return Excel::download(new KesehatanExport, 'rekap-kesehatan-peserta.xlsx');
    }

    public function exportNotulensi($id)
    {
        $notulensi = \App\Models\Notulensi::findOrFail($id);
        $namaFile = 'Notulensi_' . str_replace(' ', '_', $notulensi->topik) . '_' . $notulensi->tanggal . '.xlsx';
        
        return Excel::download(new NotulensiExport($id), $namaFile);
    }
}