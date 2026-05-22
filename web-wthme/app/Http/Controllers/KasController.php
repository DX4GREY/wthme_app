<?php

namespace App\Http\Controllers;

use App\Exports\KasExport;
use App\Models\KasTransaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class KasController extends Controller
{
    private array $divisiList = [
        'PDD', 'Konsumsi', 'Logistik', 'Acara',
        'P3K', 'Mentor', 'Transportasi', 'Humas', 'Komdis',
    ];

    public function index(Request $request)
    {
        // Query dengan filter
        $query = KasTransaksi::with('pencatat')
            ->orderBy('tanggal', 'asc')
            ->orderBy('id', 'asc');

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }
        if ($request->filled('divisi')) {
            $query->where('divisi', $request->divisi);
        }

        $transaksi = $query->get();

        // Hitung running balance (saldo berjalan)
        $saldo = 0;
        foreach ($transaksi as $t) {
            $saldo += $t->jenis === 'masuk' ? $t->nominal : -$t->nominal;
            $t->saldo_berjalan = $saldo;
        }

        // Statistik global (tanpa filter)
        $totalMasuk  = KasTransaksi::where('jenis', 'masuk')->sum('nominal');
        $totalKeluar = KasTransaksi::where('jenis', 'keluar')->sum('nominal');
        $saldoAkhir  = $totalMasuk - $totalKeluar;

        // Ringkasan per divisi
        $ringkasanDivisi = KasTransaksi::where('jenis', 'keluar')
            ->selectRaw('divisi, SUM(nominal) as total')
            ->groupBy('divisi')
            ->orderBy('total', 'desc')
            ->get();

        return view('panitia.kas', compact(
            'transaksi', 'totalMasuk', 'totalKeluar',
            'saldoAkhir', 'ringkasanDivisi'
        ))->with('divisiList', $this->divisiList);
    }

    public function store(Request $request)
    {
        $rules = [
            'tanggal'    => 'required|date',
            'jenis'      => 'required|in:masuk,keluar',
            'nominal'    => 'required|integer|min:1',
            'keterangan' => 'required|string|max:500',
            'pic'        => 'required|string|max:255',
            'bukti_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];

        if ($request->jenis === 'keluar') {
            $rules['divisi'] = 'required|string|in:' . implode(',', $this->divisiList);
        }

        $request->validate($rules, [
            'nominal.min'     => 'Nominal harus lebih dari 0.',
            'divisi.required' => 'Divisi wajib dipilih untuk transaksi keluar.',
        ]);

        $data = [
            'tanggal'    => $request->tanggal,
            'jenis'      => $request->jenis,
            'nominal'    => $request->nominal,
            'divisi'     => $request->jenis === 'keluar' ? $request->divisi : null,
            'keterangan' => $request->keterangan,
            'pic'        => $request->pic,
            'created_by' => auth()->id(),
        ];

        if ($request->hasFile('bukti_file')) {
            $data['bukti_file'] = $request->file('bukti_file')->store('kas-bukti', 'public');
        }

        KasTransaksi::create($data);

        return back()->with('success', 'Transaksi berhasil dicatat!');
    }

    public function destroy($id)
    {
        $kas = KasTransaksi::findOrFail($id);

        if ($kas->bukti_file) {
            Storage::disk('public')->delete($kas->bukti_file);
        }

        $kas->delete();

        return back()->with('success', 'Transaksi berhasil dihapus.');
    }

    public function export(Request $request)
    {
        return Excel::download(
            new KasExport($request->only('jenis', 'divisi')),
            'kas-event-' . date('Ymd') . '.xlsx'
        );
    }
}
