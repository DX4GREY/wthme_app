<?php

namespace App\Http\Controllers;

use App\Models\GanttChart;
use Illuminate\Http\Request;
use Carbon\Carbon;

class GanttController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $startMonth = $request->get('start', date('n')); 
        
        $months = [];
        for ($i = 0; $i < 3; $i++) {
            $m = $startMonth + $i;
            $months[] = ($m > 12) ? $m - 12 : $m;
        }

        $kegiatans = GanttChart::orderBy('tanggal_mulai', 'asc')->get();

        // Ambil Hari H (status merah) terdekat yang belum lewat
        $nextEvent = GanttChart::where('status', 'merah')
            ->where('tanggal_mulai', '>=', now()->format('Y-m-d'))
            ->orderBy('tanggal_mulai', 'asc')
            ->first();

        $countdownTarget = $nextEvent ? $nextEvent->tanggal_mulai : null;
        $namaEventH = $nextEvent ? $nextEvent->nama_kegiatan : null;

        return view('panitia.gantt.index', compact('kegiatans', 'months', 'year', 'countdownTarget', 'namaEventH'));
    }

    public function store(Request $request)
    {
        if (!in_array(auth()->user()->role, ['korlap', 'admin'])) abort(403);
        $validated = $request->validate([
            'nama_kegiatan' => 'required|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date',
            'status'        => 'required|in:merah,kuning,hijau',
        ]);
        GanttChart::create($validated);
        return back()->with('success', 'Agenda ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        if (!in_array(auth()->user()->role, ['korlap', 'admin'])) abort(403);
        $kegiatan = GanttChart::findOrFail($id);
        $kegiatan->update([
            'status' => $request->status,
            'nama_kegiatan' => $request->nama_kegiatan ?? $kegiatan->nama_kegiatan
        ]);
        return back()->with('success', 'Status diperbarui!');
    }

    public function destroy($id)
    {
        if (!in_array(auth()->user()->role, ['korlap', 'admin'])) abort(403);
        GanttChart::findOrFail($id)->delete();
        return back()->with('success', 'Agenda dihapus!');
    }
}