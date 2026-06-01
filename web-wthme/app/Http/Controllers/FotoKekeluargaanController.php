<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\FotoKekeluargaan;
use App\Models\PoinKeaktifan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FotoKekeluargaanController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Ambil daftar peserta lain satu angkatan untuk menu dropdown pilihan tag kawan
        $daftarTeman = User::where('role', 'peserta')
            ->where('id', '!=', $user->id)
            ->orderBy('name', 'asc')
            ->get();

        // 2. Foto-foto yang pernah diunggah oleh user ini (Histori Kiriman Saya)
        $kirimanSaya = FotoKekeluargaan::with('teman')->where('pengirim_id', $user->id)->latest()->get();

        // 3. Permintaan konfirmasi masuk dari teman lain yang menandai user ini (Butuh ACC Saya)
        $permintaanMasuk = FotoKekeluargaan::with('pengirim')->where('teman_id', $user->id)->where('status', 'pending')->latest()->get();

        return view('peserta.kekeluargaan.index', compact('daftarTeman', 'kirimanSaya', 'permintaanMasuk'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'teman_id' => 'required|exists:users,id',
            'foto' => 'required|image|mimes:jpeg,png,jpg|max:10240',
        ]);

        $user = Auth::user();

        if ($request->teman_id == $user->id) {
            return back()->with('error', 'Anda tidak bisa menandai diri Anda sendiri.');
        }

        // Cek jika duplikat
        $exists = FotoKekeluargaan::where('pengirim_id', $user->id)->where('teman_id', $request->teman_id)->first();
        if ($exists) {
            return back()->with('error', 'Foto dengan teman ini sudah pernah diunggah sebelumnya.');
        }

        // Kompresi Gambar GD Library
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

        // Resize maksimal lebar 800px
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

        $filename = 'family_' . $user->id . '_to_' . $request->teman_id . '_' . time() . '.jpg';
        $path = storage_path('app/public/kekeluargaan/' . $filename);

        if (!file_exists(storage_path('app/public/kekeluargaan'))) {
            mkdir(storage_path('app/public/kekeluargaan'), 0755, true);
        }

        imagejpeg($image, $path, 60); // Save kualitas 60%
        imagedestroy($image);

        FotoKekeluargaan::create([
            'pengirim_id' => $user->id,
            'teman_id' => $request->teman_id,
            'foto' => $filename,
            'status' => 'pending'
        ]);

        return back()->with('success', 'Foto berhasil diunggah! Menunggu konfirmasi dari teman yang bersangkutan.');
    }

    /**
     * Konfirmasi Silang Oleh Teman (Tanpa Panitia)
     */
    public function approve($id)
    {
        $foto = FotoKekeluargaan::findOrFail($id);
        $user = Auth::user();

        if ($foto->teman_id !== $user->id) {
            return back()->with('error', 'Anda tidak memiliki hak akses valid untuk mengonfirmasi foto ini.');
        }

        if ($foto->status === 'approved') {
            return back()->with('error', 'Foto ini sudah dikonfirmasi sebelumnya.');
        }

        $foto->status = 'approved';
        $foto->save();

        // Cari nama teman untuk dimasukkan ke string deskripsi log
        $namaTeman = User::find($foto->teman_id)?->name ?? 'Teman';

        // Berikan penghargaan +5 poin keaktifan bagi pengirim foto asli (Si A)
        PoinKeaktifan::create([
            'peserta_id' => $foto->pengirim_id,
            'panitia_id' => null, // Sistem Otomatis Verifikasi Silang
            'poin' => 5,
            'keterangan' => 'Foto Kekeluargaan Angkatan dengan ' . $namaTeman
        ]);

        return back()->with('success', 'Terima kasih atas konfirmasi Anda! Teman Anda berhasil mendapatkan 5 poin.');
    }

    /**
     * Tolak Jika Foto Palsu / Salah Tag Orang
     */
    public function reject($id)
    {
        $foto = FotoKekeluargaan::findOrFail($id);
        if ($foto->teman_id !== Auth::id()) {
            return back()->with('error', 'Akses ditolak.');
        }

        $foto->status = 'rejected';
        $foto->save();

        return back()->with('success', 'Foto berhasil ditolak. Pengirim dapat menghapus/memperbaiki kiriman.');
    }
}