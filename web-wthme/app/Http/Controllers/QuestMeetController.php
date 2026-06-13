<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\MasterAbangKbm;
use App\Models\QuestMeet;
use App\Models\PoinKeaktifan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Imports\MasterAbangImport;
use App\Exports\TemplateAbangExport;
use Maatwebsite\Excel\Facades\Excel;

class QuestMeetController extends Controller
{
    // ==========================================
    //               PORTAL PESERTA
    // ==========================================

    public function indexPeserta()
    {
        $myQuests = QuestMeet::where('user_id', Auth::id())->latest()->get();
        return view('peserta.quest_meet.index', compact('myQuests'));
    }

    public function create()
    {
        return view('peserta.quest_meet.create');
    }

    public function getAbangByAngkatan($angkatan)
    {
        // AJAX endpoint untuk mengambil data abang berdasarkan angkatan yang dipilih
        $abangs = MasterAbangKbm::where('angkatan', $angkatan)->orderBy('name')->get();
        return response()->json($abangs);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kategori_angkatan' => 'required|in:2021,2022,2023,2024,alumni',
            'tipe_meet'         => 'required|in:individu,group',
            'foto_bukti'        => 'required|image|max:10240', // Max 5MB sebelum kompresi
        ]);

        // 1. Ekstraksi nama abang/alumni berdasarkan kategori angkatan yang dipilih
        if ($request->kategori_angkatan === 'alumni') {
            $request->validate(['alumni_names' => 'required|array|min:1']);
            $selectedAbang = $request->alumni_names;
        } else {
            $request->validate(['abang_ids' => 'required|array|min:1']);
            // Ambil string nama asli berdasarkan ID master yang dipilih peserta
            $selectedAbang = MasterAbangKbm::whereIn('id', $request->abang_ids)
                ->pluck('name')
                ->toArray();
        }

        // ===================================================================
        // 2. 🛡️ ANTI-FARMING BUG (Validasi Duplikasi Master Data & Alumni Teks Bebas)
        // ===================================================================
        
        // Ambil semua histori nama abang/alumni yang sudah diajukan peserta ini (approved & pending)
        $existingMeets = QuestMeet::where('user_id', Auth::id())
                            ->whereIn('status', ['approved', 'pending'])
                            ->pluck('selected_abang')
                            ->toArray();

        // Satukan semua nama dari database menjadi satu array datar
        $flattenedExistingAbang = [];
        foreach ($existingMeets as $meetArray) {
            if (is_array($meetArray)) {
                $flattenedExistingAbang = array_merge($flattenedExistingAbang, $meetArray);
            }
        }

        // Fungsi pembantu untuk membersihkan string total (Hapus spasi hantu, lowercase, hapus tanda baca)
        // Contoh: "Bang Budi, S.T." atau "  budi " keduanya akan dipadatkan menjadi "bangbudi"
        $cleanString = function($string) {
            $lower = strtolower(trim($string));
            return preg_replace('/[^a-z0-9]/', '', $lower); // Hanya menyisakan huruf dan angka alfanumerik
        };

        // Bersihkan seluruh list nama historis yang tersimpan di database
        $cleanedExistingNames = array_map($cleanString, $flattenedExistingAbang);

        // Lakukan pengecekan ketat pada nama-nama yang baru diinput/dipilih oleh peserta
        foreach ($selectedAbang as $namaInputBaru) {
            $cleanedInput = $cleanString($namaInputBaru);

            // Cegah input iseng yang hanya berisi spasi atau simbol tanda baca
            if (empty($cleanedInput)) {
                return back()->withInput()->with('error', "Format penulisan nama tidak valid atau kosong.");
            }

            // Jika hasil normalisasi teks cocok dengan database, kunci pengiriman (Block farming)
            if (in_array($cleanedInput, $cleanedExistingNames)) {
                return back()->withInput()->with('error', "Gagal! Anda sudah pernah mengajukan atau memvalidasi pertemuan dengan: '{$namaInputBaru}'.");
            }
        }
        // ===================================================================

        // 3. Proses Kompresi Gambar
        if ($request->hasFile('foto_bukti')) {
            $file = $request->file('foto_bukti');
            $filename = 'meet_' . Auth::id() . '_' . $request->kategori_angkatan . '_' . time() . '.jpg';
            $destinationPath = public_path('storage/quests_meet');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $imageInfo = getimagesize($file->getRealPath());
            $mime = $imageInfo['mime'];

            switch ($mime) {
                case 'image/jpeg':
                    $image = imagecreatefromjpeg($file->getRealPath());
                    break;
                case 'image/png':
                    $image = imagecreatefrompng($file->getRealPath());
                    break;
                case 'image/gif':
                    $image = imagecreatefromgif($file->getRealPath());
                    break;
                default:
                    return back()->with('error', 'Format gambar tidak didukung!');
            }

            $savePath = $destinationPath . '/' . $filename;
            imagejpeg($image, $savePath, 60);
            imagedestroy($image);

            $pathDb = 'storage/quests_meet/' . $filename;
        }

        // 4. Simpan ke Database jika lolos seluruh verifikasi keaslian nama
        QuestMeet::create([
            'user_id'           => Auth::id(),
            'kategori_angkatan' => $request->kategori_angkatan,
            'tipe_meet'         => $request->tipe_meet,
            'selected_abang'    => $selectedAbang,
            'foto_bukti'        => $pathDb,
            'status'            => 'pending'
        ]);

        return redirect()->route('peserta.meet.index')->with('success', 'Bukti Meet KBM berhasil dikirim! Menunggu validasi Acara.');
    }

    // ==========================================
    //               PORTAL PANITIA
    // ==========================================

    public function indexPanitia(Request $request)
    {
        // Proteksi mutlak: Hanya divisi ACARA dan role admin yang bisa masuk
        if (!Auth::user()->isAcara() && !Auth::user()->isAdmin()) {
            abort(403, 'Akses ditolak. Hanya Divisi Acara atau Admin yang diizinkan.');
        }

        // Data submisi tugas meet untuk antrean ACC/Tolak Panitia Acara
        $submissions = QuestMeet::with('peserta')->orderBy('status', 'asc')->latest()->paginate(20);

        return view('panitia.quest_meet.index', compact('submissions'));
    }

    public function approve($id)
    {
        if (!Auth::user()->isAcara() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        $meet = QuestMeet::findOrFail($id);
        if ($meet->status !== 'pending') {
            return back()->with('error', 'Submisi ini sudah divalidasi sebelumnya.');
        }

        // Hitung total poin berdasarkan jumlah abang-abang yang ada di dalam array data
        $jumlahNama = count($meet->selected_abang);
        $totalPoinInject = $jumlahNama * 50; // Aturan: 50 poin per nama terdaftar

        // 1. Update status transaksi quest meet
        $meet->update([
            'status' => 'approved',
            'validated_by' => Auth::id()
        ]);

        // 2. Inject otomatis ke tabel poin_keaktifan agar masuk ke sistem Leaderboard
        PoinKeaktifan::create([
            'peserta_id' => $meet->user_id,
            'panitia_id' => Auth::id(),
            'poin'       => $totalPoinInject,
            'keterangan' => 'Quest Meet KBM - Angkatan ' . strtoupper($meet->kategori_angkatan) . ' (' . ucfirst($meet->tipe_meet) . ': ' . $jumlahNama . ' Orang)'
        ]);

        return back()->with('success', 'Submisi disetujui! ' . $totalPoinInject . ' poin telah ditambahkan ke peserta.');
    }

    public function reject($id)
    {
        if (!Auth::user()->isAcara() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        $meet = QuestMeet::findOrFail($id);
        $meet->update([
            'status' => 'rejected',
            'validated_by' => Auth::id()
        ]);

        return back()->with('success', 'Submisi berhasil ditolak.');
    }

    // ==========================================
    //        🟢 PORTAL MASTER DATA ABANG (ADMIN)
    // ==========================================

    /**
     * Tampilkan Halaman Index Manajemen Data Master Abang untuk Admin
     */
    public function indexAbang(Request $request)
    {
        if (!Auth::user()->isAcara() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        $search = $request->query('search');
        $abangQuery = MasterAbangKbm::query();

        if ($search) {
            $abangQuery->where('name', 'LIKE', "%{$search}%")
                ->orWhere('angkatan', 'LIKE', "%{$search}%");
        }

        // Ambil list master data abang (Gunakan pagination agar tidak crash jika datanya banyak)
        $abangList = $abangQuery->orderBy('angkatan', 'desc')->orderBy('name')->paginate(25);
        $totalAbang = MasterAbangKbm::count();

        // Diarahkan khusus ke folder view admin/abang/index
        return view('admin.abang.index', compact('abangList', 'totalAbang'));
    }

    // ==========================================
    //          IMPORT & EXPORT EXCEL
    // ==========================================

    /**
     * Tampilkan Form Import Data Abang KBMS
     */
    public function importForm()
    {
        if (!Auth::user()->isAcara() && !Auth::user()->isAdmin()) {
            abort(403);
        }
        return view('admin.abang.import');
    }

    /**
     * Proses Upload & Import Data Abang KBMS (Menggunakan Maatwebsite Excel)
     */
    public function importStore(Request $request)
    {
        if (!Auth::user()->isAcara() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        set_time_limit(0);
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ], [
            'file.required' => 'File Excel data abang wajib dipilih.',
            'file.mimes'    => 'File harus berformat .xlsx atau .xls.',
            'file.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        $import = new MasterAbangImport();
        Excel::import($import, $request->file('file'));

        $message = $import->getImportedCount() . ' data abang KBMS berhasil diimport.';
        if ($import->getSkippedCount() > 0) {
            $message .= ' ' . $import->getSkippedCount() . ' data dilewati (sudah ada).';
        }

        return redirect()->route('admin.abang.index')->with('success', $message);
    }

    /**
     * Download Template Excel On-The-Fly (Anti-Error FileNotFound)
     */
    public function downloadTemplateAbang()
    {
        if (!Auth::user()->isAcara() && !Auth::user()->isAdmin()) {
            abort(403);
        }
        return Excel::download(new TemplateAbangExport, 'template-import-abang-kbms.xlsx');
    }
}