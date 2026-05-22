<?php

namespace App\Http\Controllers;

use App\Models\Notulensi;
use App\Models\NotulensiPoin;
use App\Models\User;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;
use Carbon\Carbon;

class NotulensiController extends Controller
{
    public function index()
    {
        $notulensi = Notulensi::with('poin')->orderBy('tanggal', 'desc')->get();

        // Daftar semua divisi sesuai struktur panitia kamu
        $divisiList = [
            'BPH', 
            'Acara', 
            'Konsumsi', 
            'Logistik', 
            'PDD', 
            'Humas', 
            'Komdis', 
            'Mentor'
        ];

        return view('panitia.notulensi.index', compact('notulensi', 'divisiList'));
    }

    public function store(Request $request)
    {
        // ... kode store yang sebelumnya (sudah mendukung array) ...
        $request->validate([
            'topik' => 'required',
            'tanggal' => 'required|date',
        ]);

        $notulensi = Notulensi::create([
            'topik' => $request->topik,
            'tanggal' => $request->tanggal,
            'created_by' => auth()->id(),
        ]);

        if($request->has('divisi_input')) {
            foreach($request->divisi_input as $key => $divisi) {
                NotulensiPoin::create([
                    'notulensi_id' => $notulensi->id,
                    'divisi' => $divisi,
                    'isi_poin' => $request->isi_poin_input[$key],
                ]);
            }
        }

        return redirect()->back()->with('success', 'Notulensi berhasil disimpan!');
    }
    public function downloadDoc($id)
    {
        $notulensi = Notulensi::with(['poin' => function($query) {
            $query->orderBy('id', 'asc');
        }])->findOrFail($id);

        $phpWord = new PhpWord();
        
        // Setting Font Default
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(12);

        // Tambah Section (Halaman)
        $section = $phpWord->addSection([
            'marginTop' => 1200,
            'marginRight' => 1200,
            'marginBottom' => 1200,
            'marginLeft' => 1200,
        ]);

        // Judul: NOTULENSI (Center, Bold, Underline)
        $section->addText("NOTULENSI", ['bold' => true, 'underline' => 'single', 'size' => 14], ['alignment' => Jc::CENTER]);
        $section->addTextBreak(1);

        // Tabel Informasi (Tanpa Border)
        $tableStyle = ['borderSize' => 0, 'cellMargin' => 0];
        $table = $section->addTable($tableStyle);

        $this->addTableRow($table, "Nama Rapat", $notulensi->topik);
        $this->addTableRow($table, "Hari, Tanggal", Carbon::parse($notulensi->tanggal)->translatedFormat('l, d F Y'));
        $this->addTableRow($table, "Tempat", $notulensi->tempat ?? 'Auditorium Lt.3');
        $this->addTableRow($table, "Pimpinan Rapat", $notulensi->pemimpin_rapat ?? '-');
        $this->addTableRow($table, "Notulis", auth()->user()->name);
        $this->addTableRow($table, "Pembahasan", $notulensi->topik);
        $this->addTableRow($table, "Hasil", "");

        $section->addTextBreak(1);

        // Isi Notulensi Per Divisi
        foreach ($notulensi->poin as $item) {
            // Header Divisi (Bold, Underline, Uppercase)
            $section->addText("DIVISI " . strtoupper($item->divisi), ['bold' => true, 'underline' => 'single']);
            
            // Isi Poin (Mendukung baris baru)
            $textLines = explode("\n", $item->isi_poin);
            foreach ($textLines as $line) {
                $section->addText(trim($line), [], ['alignment' => Jc::BOTH]);
            }
            $section->addTextBreak(1);
        }

        // Proses Download
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $fileName = 'Notulensi_' . str_replace(' ', '_', $notulensi->topik) . '.docx';
        $path = storage_path($fileName);
        $objWriter->save($path);

        return response()->download($path)->deleteFileAfterSend(true);
    }

    // Helper untuk membuat baris tabel informasi agar rapi
    private function addTableRow($table, $label, $value)
    {
        $row = $table->addRow();
        $row->addCell(2500)->addText($label);
        $row->addCell(500)->addText(":");
        $row->addCell(6000)->addText($value);
    }

}