<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\QuestLab;
use App\Models\PoinKeaktifan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class QuestLabController extends Controller
{
    // Daftar Lab Valid
    private $listLab = ['Lab PSTE', 'Lab APEL', 'Lab Photovoltaik', 'Lab Green Energy', 'Lab ICONIC - Untirta Robotic Club'];

    /**
     * Tampilan Sisi Peserta
     */
    public function indexPeserta()
    {
        $user = Auth::user();
        // Ambil status quest lab milik user saat ini
        $quests = QuestLab::where('user_id', $user->id)->get()->keyBy('nama_lab');
        $listLab = $this->listLab;

        return view('peserta.quest.index', compact('quests', 'listLab'));
    }

    /**
     * Proses Upload Selfie Peserta + Kompresi Gambar Otomatis
     */
    public function uploadSelfie(Request $request, $labName)
    {
        if (!in_array($labName, $this->listLab)) {
            return back()->with('error', 'Lab tidak valid.');
        }

        $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,jpg|max:10240', // Maksimal file masuk 10MB
        ]);

        $user = Auth::user();

        // Cek jika sudah pernah upload dan statusnya bukan rejected
        $existing = QuestLab::where('user_id', $user->id)->where('nama_lab', $labName)->first();
        if ($existing && $existing->status !== 'rejected') {
            return back()->with('error', 'Anda sudah mengunggah foto untuk lab ini.');
        }

        // Proses Kompresi Gambar Menggunakan GD Library PHP bawaan (Sangat menghemat storage 60GB)
        $file = $request->file('foto');
        $imageInfo = getimagesize($file);
        $mime = $imageInfo['mime'];

        if ($mime == 'image/jpeg' || $mime == 'image/jpg') {
            $image = imagecreatefromjpeg($file);
        } elseif ($mime == 'image/png') {
            $image = imagecreatefrompng($file);
        } else {
            return back()->with('error', 'Format gambar tidak didukung.');
        }

        // Cari tahu ukuran baru (Resize maksimal lebar 800px mempertahankan rasio)
        $width = imagesx($image);
        $height = imagesy($image);
        $newWidth = 800;
        if ($width > $newWidth) {
            $newHeight = floor($height * ($newWidth / $width));
            $tmpImage = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($tmpImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $tmpImage;
        }

        // Simpan gambar yang telah terkompresi ke storage local aplikasi
        $filename = 'quest_' . $user->id . '_' . str_replace(' ', '_', $labName) . '_' . time() . '.jpg';
        $path = storage_path('app/public/quests/' . $filename);

        if (!file_exists(storage_path('app/public/quests'))) {
            mkdir(storage_path('app/public/quests'), 0755, true);
        }

        // Kualitas 60% memotong ukuran file dari 5MB menjadi 150KB - 200KB saja!
        imagejpeg($image, $path, 60);
        imagedestroy($image);

        // Hapus foto lama jika statusnya merupakan perbaikan dari penolakan sebelumnya
        if ($existing && $existing->status === 'rejected') {
            Storage::disk('public')->delete('quests/' . $existing->foto_selfie);
            $existing->update([
                'foto_selfie' => $filename,
                'status' => 'pending',
                'submitted_at' => now()
            ]);
        } else {
            QuestLab::create([
                'user_id' => $user->id,
                'nama_lab' => $labName,
                'foto_selfie' => $filename,
                'status' => 'pending',
                'submitted_at' => now()
            ]);
        }

        return back()->with('success', 'Foto ' . $labName . ' berhasil diunggah! Menunggu konfirmasi panitia.');
    }

    /**
     * Tampilan Halaman Review Panitia (Hanya untuk Divisi ACARA / Admin)
     */
    public function indexPanitia()
    {
        if (!Auth::user()->isAcara()) {
            abort(403, 'Hanya divisi ACARA yang dapat mengakses menu ini.');
        }

        // Mengurutkan antrean berdasarkan waktu submit pertama demi asas keadilan
        $pendingQuests = QuestLab::with('user')
            ->where('status', 'pending')
            ->orderBy('submitted_at', 'asc')
            ->paginate(15);

        return view('panitia.quest.index', compact('pendingQuests'));
    }

    /**
     * Proses ACC Oleh Panitia Acara + Kalkulasi Skor Cepat Dinamis
     */
    public function approveQuest($id)
    {
        if (!Auth::user()->isAcara()) {
            return back()->with('error', 'Akses ditolak.');
        }

        $quest = QuestLab::findOrFail($id);
        $quest->status = 'approved';
        $quest->save();

        $userId = $quest->user_id;

        // Periksa apakah seluruh 4 lab milik user ini sudah disetujui sepenuhnya oleh panitia
        $totalApproved = QuestLab::where('user_id', $userId)->where('status', 'approved')->count();

        if ($totalApproved === 4) {
            // Validasi apakah user sudah pernah mendapatkan poin quest agar tidak terjadi duplikasi nilai
            $sudahDapatPoin = PoinKeaktifan::where('peserta_id', $userId)
                ->where('keterangan', 'LIKE', 'Menyelesaikan Quest 4 Lab Elektro%')
                ->exists();

            if (!$sudahDapatPoin) {
                // KUNCI KEADILAN: Ambil catatan waktu pengiriman berkas lab terakhir milik user ini
                $waktuSelesaiUser = QuestLab::where('user_id', $userId)->max('submitted_at');

                // Hitung berapa banyak peserta lain yang sudah berstatus FULL APPROVED 
                // dengan catatan waktu pengiriman maksimalnya lebih cepat dari user ini
                $pesertaLebihCepat = User::where('role', 'peserta')
                    ->whereHas('absensiPeserta', function () {}, '>=', 0) // pancingan query saja
                    ->get()
                    ->filter(function ($u) use ($waktuSelesaiUser) {
                        $labs = QuestLab::where('user_id', $u->id)->where('status', 'approved')->get();
                        if ($labs->count() !== 4) return false;
                        return $labs->max('submitted_at') < $waktuSelesaiUser;
                    })->count();

                $peringkat = $pesertaLebihCepat + 1;

                // Penentuan perhitungan bobot poin
                if ($peringkat <= 10) {
                    $poin = 200 - (($peringkat - 1) * 10); // Peringkat 1 = 200, Peringkat 2 = 190, ... Peringkat 10 = 110
                } else {
                    $poin = 100; // Poin standar untuk peringkat di atas 10 besar
                }

                // Inject langsung poin ke database tabel poin_keaktifan bawaan aplikasi Anda
                PoinKeaktifan::create([
                    'peserta_id' => $userId,
                    'panitia_id' => Auth::id(),
                    'poin' => $poin,
                    'keterangan' => 'Menyelesaikan Quest 4 Lab Elektro (Peringkat ' . $peringkat . ' Tercepat)'
                ]);
            }
        }

        return back()->with('success', 'Quest Lab berhasil disetujui!');
    }

    /**
     * Proses Tolak Oleh Panitia
     */
    public function rejectQuest($id)
    {
        if (!Auth::user()->isAcara()) {
            return back()->with('error', 'Akses ditolak.');
        }

        $quest = QuestLab::findOrFail($id);
        $quest->status = 'rejected';
        $quest->save();

        return back()->with('success', 'Quest Lab ditolak. Peserta dipersilakan mengunggah ulang foto bukti.');
    }
    public function delete($lab)
    {
        // 1. Cari data quest berdasarkan ID user yang login dan nama labnya
        // ⚠️ Sesuaikan 'nama_lab' atau 'lab_name' dengan nama kolom di tabel Anda
        $quest = QuestLab::where('user_id', auth()->id())
            ->where('nama_lab', $lab)
            ->first();

        if ($quest) {
            // Validasi: Jika status sudah approved, tidak boleh dihapus manual oleh peserta
            if ($quest->status === 'approved') {
                return redirect()->back()->with('error', 'Foto yang telah diverifikasi tidak dapat dihapus.');
            }

            // 2. Hapus fisik file gambar dari folder storage/app/public/quests/
            // ⚠️ Sesuaikan 'foto_selfie' dengan nama kolom penyimpanan berkas Anda
            if ($quest->foto_selfie && Storage::disk('public')->exists('quests/' . $quest->foto_selfie)) {
                Storage::disk('public')->delete('quests/' . $quest->foto_selfie);
            }

            // 3. Hapus baris data dari database
            $quest->delete();

            return redirect()->back()->with('success', 'Foto berhasil dihapus. Silakan ambil foto baru.');
        }

        return redirect()->back()->with('error', 'Data foto tidak ditemukan.');
    }

    /**
     * Proses ACC SEMUA Antrean Quest yang Masih Pending Sekaligus
     */
    public function approveAll()
    {
        // 1. Validasi hak akses divisi acara
        if (!Auth::user()->isAcara()) {
            return back()->with('error', 'Akses ditolak.');
        }

        // 2. Ambil semua quest lab yang statusnya masih 'pending'
        $pendingQuests = QuestLab::where('status', 'pending')->get();

        if ($pendingQuests->isEmpty()) {
            return back()->with('info', 'Tidak ada antrean quest yang perlu disetujui.');
        }

        // 3. Loop satu per satu dan jalankan logika kalkulasi poin keaktifan
        foreach ($pendingQuests as $quest) {
            $quest->status = 'approved';
            $quest->save();

            $userId = $quest->user_id;

            // Periksa apakah seluruh 4 lab milik user ini sudah disetujui sepenuhnya
            $totalApproved = QuestLab::where('user_id', $userId)->where('status', 'approved')->count();

            if ($totalApproved === 4) {
                // Validasi apakah user sudah pernah mendapatkan poin quest agar tidak terjadi duplikasi nilai
                $sudahDapatPoin = PoinKeaktifan::where('peserta_id', $userId)
                    ->where('keterangan', 'LIKE', 'Menyelesaikan Quest 4 Lab Elektro%')
                    ->exists();

                if (!$sudahDapatPoin) {
                    // Ambil catatan waktu pengiriman berkas lab terakhir milik user ini
                    $waktuSelesaiUser = QuestLab::where('user_id', $userId)->max('submitted_at');

                    // Hitung berapa banyak peserta lain yang sudah berstatus FULL APPROVED yang lebih cepat
                    $pesertaLebihCepat = User::where('role', 'peserta')
                        ->get()
                        ->filter(function ($u) use ($waktuSelesaiUser) {
                            $labs = QuestLab::where('user_id', $u->id)->where('status', 'approved')->get();
                            if ($labs->count() !== 4) return false;
                            return $labs->max('submitted_at') < $waktuSelesaiUser;
                        })->count();

                    $peringkat = $pesertaLebihCepat + 1;

                    // Penentuan bobot nilai peringkat terkompresi
                    if ($peringkat <= 10) {
                        $poin = 200 - (($peringkat - 1) * 10);
                    } else {
                        $poin = 100;
                    }

                    // Inject langsung ke database poin_keaktifan
                    PoinKeaktifan::create([
                        'peserta_id' => $userId,
                        'panitia_id' => Auth::id(),
                        'poin' => $poin,
                        'keterangan' => 'Menyelesaikan Quest 4 Lab Elektro (Peringkat ' . $peringkat . ' Tercepat)'
                    ]);
                }
            }
        }

        // 4. Kembalikan response sukses setelah seluruh antrean SELESAI diproses
        return redirect()->back()->with('success', 'Semua antrean quest lab berhasil disetujui seketika dan kalkulasi poin peserta telah diperbarui!');
    }
}
