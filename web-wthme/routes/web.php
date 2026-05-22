<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PanitiaController;
use App\Http\Controllers\PesertaController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\QrController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\KasController;
use App\Http\Controllers\TugasController;
use App\Http\Controllers\KesehatanController;
use App\Http\Controllers\NotulensiController;
use App\Http\Controllers\MentoringController;
use App\Http\Controllers\GanttController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\FaceController;
use App\Http\Controllers\FaceAbsensiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile',   [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/ganti-password', [ChangePasswordController::class, 'show'])->name('password.change');
    Route::put('/ganti-password', [ChangePasswordController::class, 'update'])->name('password.change.update');

    // --- ADMIN ---
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/',             [AdminController::class, 'index'])->name('index');
        Route::get('/panitia',      [AdminController::class, 'index'])->name('panitia');
        Route::get('/import',       [AdminController::class, 'importForm'])->name('import');
        Route::post('/import',      [AdminController::class, 'importStore'])->name('import.store');
        Route::delete('/panitia/{id}',              [AdminController::class, 'deletePanitia'])->name('panitia.delete');
        Route::post('/panitia/{id}/reset-password', [AdminController::class, 'resetPassword'])->name('panitia.reset');
        Route::get('/panitia/{id}/edit',            [AdminController::class, 'editPanitia'])->name('panitia.edit');
        Route::put('/panitia/{id}',                 [AdminController::class, 'updatePanitia'])->name('panitia.update');
        Route::get('/template',     [AdminController::class, 'downloadTemplate'])->name('template');
        // Route Baru untuk Peserta
        Route::get('/import-peserta', [AdminController::class, 'importPesertaForm'])->name('import.peserta');
        Route::post('/import-peserta', [AdminController::class, 'importPesertaStore'])->name('import.peserta.store');
        Route::get('/template-peserta', [AdminController::class, 'downloadTemplatePeserta'])->name('template.peserta');
        Route::post('/peserta/reset/{id}', [AdminController::class, 'resetPasswordPeserta'])->name('peserta.reset');
    });

    // --- PANITIA ---
    Route::prefix('panitia')->name('panitia.')->middleware('panitia')->group(function () {
        Route::get('/', [PanitiaController::class, 'index'])->name('index');
        // Di dalam Route::prefix('panitia')->name('panitia.')...
        Route::post('/links', [PanitiaController::class, 'storeLink'])->name('links.store');
        Route::delete('/links/{id}', [PanitiaController::class, 'destroyLink'])->name('links.destroy');
        Route::post('/broadcast-peserta', [PanitiaController::class, 'storeInfoPeserta'])->name('info.peserta.store');
        Route::delete('/broadcast-peserta/{id}', [PanitiaController::class, 'destroyInfoPeserta'])->name('info.peserta.destroy');
        // Di dalam Route::prefix('panitia')...
        Route::get('/informasi-peserta', [PanitiaController::class, 'indexInfoPeserta'])->name('info.peserta.index');
        Route::post('/informasi-peserta', [PanitiaController::class, 'storeInfoPeserta'])->name('info.peserta.store');
        Route::delete('/informasi-peserta/{id}', [PanitiaController::class, 'destroyInfoPeserta'])->name('info.peserta.destroy');
        // Di dalam Route::prefix('panitia')->name('panitia.')->middleware('panitia')
        Route::get('/absensi/face-gate',     [FaceAbsensiController::class, 'gate'])->name('absen.face.gate');
        Route::post('/absensi/face-gate',    [FaceAbsensiController::class, 'gateProcess'])->name('absen.face.process');

        // QR & ABSENSI
        Route::get('/qr/buat',                 [QrController::class, 'create'])->name('qr.create');
        Route::post('/qr/buat',                [QrController::class, 'store'])->name('qr.store');
        Route::get('/qr/tampilkan/{code}',     [QrController::class, 'show'])->name('qr.show');
        Route::patch('/qr/{id}/toggle',        [QrController::class, 'toggle'])->name('qr.toggle');
        Route::get('/qr/{code}/refresh-token', [QrController::class, 'refreshToken'])->name('qr.refresh');
        Route::get('/absensi/peserta', [AbsensiController::class, 'dataPeserta'])->name('absensi.peserta');
        Route::get('/absensi/panitia', [AbsensiController::class, 'dataPanitia'])->name('absensi.panitia');
        Route::get('/absen',  [AbsensiController::class, 'formPanitia'])->name('absen');
        Route::post('/absen', [AbsensiController::class, 'storePanitia'])->name('absen.store');

        // MENTORING (LOGBOOK) - Tambahkan di sini
        Route::prefix('mentoring')->name('mentoring.')->group(function () {
            Route::get('/', [MentoringController::class, 'index'])->name('index');
            Route::post('/sesi', [MentoringController::class, 'storeSesi'])->name('storeSesi');
            Route::get('/catatan/{sesiId}', [MentoringController::class, 'formCatatan'])->name('catatan');
            Route::post('/catatan/{sesiId}', [MentoringController::class, 'simpanCatatan'])->name('catatan.simpan');
            Route::post('/assign-mentor', [MentoringController::class, 'assignMentor'])->name('assignMentor');
        });

        // EXPORT
        Route::get('/export/peserta', [ExportController::class, 'exportPeserta'])->name('export.peserta');
        Route::get('/export/panitia', [ExportController::class, 'exportPanitia'])->name('export.panitia');
        Route::get('/export/kesehatan', [ExportController::class, 'exportKesehatan'])->name('export.kesehatan');

        // KESEHATAN
        Route::prefix('kesehatan')->name('kesehatan.')->group(function () {
            Route::get('/', [KesehatanController::class, 'indexPanitia'])->name('index');
        });

        // KAS
        // --- KAS (Akses Terbuka untuk semua Panitia) ---
        Route::prefix('kas')->name('kas.')->group(function () {
            // 🔓 PINDAHKAN KE SINI (Di luar middleware bendahara)
            // Sekarang Panitia biasa bisa melihat halamannya
            Route::get('/', [KasController::class, 'index'])->name('index');

            // 🔒 HANYA Bendahara yang bisa eksekusi (Input, Hapus, Export)
            Route::middleware('bendahara')->group(function () {
                Route::post('/',       [KasController::class, 'store'])->name('store');
                Route::delete('/{id}', [KasController::class, 'destroy'])->name('destroy');
                Route::get('/export',  [KasController::class, 'export'])->name('export');
            });
        });

        // TUGAS
        Route::prefix('tugas')->name('tugas.')->group(function () {
            Route::get('/',              [TugasController::class, 'indexPanitia'])->name('index');
            Route::post('/',             [TugasController::class, 'storeTugas'])->name('store');
            Route::patch('/{id}/toggle', [TugasController::class, 'toggleTugas'])->name('toggle');
            Route::delete('/{id}',       [TugasController::class, 'destroyTugas'])->name('destroy');
            Route::get('/rekap',         [TugasController::class, 'rekap'])->name('rekap');
            Route::get('/download/{id}', [TugasController::class, 'downloadFile'])->name('download');
            Route::get('/export',        [TugasController::class, 'exportRekap'])->name('export');
        });

        // NOTULENSI
        Route::prefix('notulensi')->name('notulensi.')->group(function () {
            Route::get('/', [NotulensiController::class, 'index'])->name('index');
            Route::post('/', [NotulensiController::class, 'store'])->name('store');
            Route::get('/download/{id}', [NotulensiController::class, 'downloadDoc'])->name('download');
            Route::get('/export/{id}', [ExportController::class, 'exportNotulensi'])->name('export');
        });
        // --- MENTORING ---
        // --- MENTORING ---
        Route::prefix('mentoring')->name('mentoring.')->group(function () {
            // SEMUA PANITIA bisa akses ini (Read-Only)
            Route::get('/', [MentoringController::class, 'index'])->name('index');
            Route::get('/rekap-global', [MentoringController::class, 'rekapGlobal'])->name('rekap');
            Route::get('/kelompok/{kelompok}', [MentoringController::class, 'kelompok'])->name('kelompok');

            // HANYA ROLE TERTENTU (Misal: Admin/Sekretaris/Bendahara/Mentor) yang bisa Edit/Input
            // Jika ingin semua panitia bisa lihat tapi hanya 'mentor' yang bisa isi, ganti middlewarenya
            Route::middleware('mentor')->group(function () {
                Route::post('/kelompok/{kelompok}', [MentoringController::class, 'store'])->name('store');
                Route::put('/detail/{id}', [MentoringController::class, 'updateDetail'])->name('updateDetail');
                Route::delete('/{id}', [MentoringController::class, 'destroy'])->name('destroy');
                Route::get('/kelompok/{kelompok}/export', [MentoringController::class, 'export'])->name('export');
                Route::get('/export-seluruh-kelompok', [MentoringController::class, 'exportSeluruh'])->name('export_seluruh');
            });
        });

        Route::prefix('gantt')->name('gantt.')->group(function () {
            Route::get('/', [GanttController::class, 'index'])->name('index');
            Route::post('/', [GanttController::class, 'store'])->name('store');
            Route::put('/{id}', [GanttController::class, 'update'])->name('update');
            Route::delete('/{id}', [GanttController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('barang')->name('barang.')->group(function () {
            // List kelompok (semua panitia bisa lihat)
            Route::get('/', [\App\Http\Controllers\BarangController::class, 'panitiaIndex'])->name('index');
            Route::get('/kelompok/{kelompok}', [\App\Http\Controllers\BarangController::class, 'panitiaKelompok'])->name('kelompok');
            Route::get('/rekap', [\App\Http\Controllers\BarangController::class, 'panitiaRekap'])->name('rekap');
            Route::get('/export', [\App\Http\Controllers\BarangController::class, 'exportRekap'])->name('export');

            // Manage barang (hanya divisi Logistik, dicek di controller)
            Route::get('/manage', [\App\Http\Controllers\BarangController::class, 'manageIndex'])->name('manage');
            Route::post('/manage', [\App\Http\Controllers\BarangController::class, 'manageStore'])->name('manage.store');
            Route::put('/manage/{id}', [\App\Http\Controllers\BarangController::class, 'manageUpdate'])->name('manage.update');
            Route::delete('/manage/{id}', [\App\Http\Controllers\BarangController::class, 'manageDestroy'])->name('manage.destroy');
        });
    }); // <--- PENUTUP PANITIA (Tadi kamu lupa ini)

    // --- PESERTA ---
    Route::prefix('peserta')->name('peserta.')->middleware('peserta')->group(function () {
        Route::get('/',                  [PesertaController::class, 'index'])->name('index');
        Route::get('/absen',             [AbsensiController::class, 'formPeserta'])->name('absen');
        Route::post('/absen',            [AbsensiController::class, 'storePeserta'])->name('absen.store');
        Route::get('/riwayat-penyakit',  [PesertaController::class, 'riwayatPenyakit'])->name('riwayat');
        Route::post('/riwayat-penyakit', [PesertaController::class, 'simpanRiwayat'])->name('riwayat.store');
        Route::get('/tugas',             [TugasController::class, 'indexPeserta'])->name('tugas');
        Route::post('/tugas/upload',     [TugasController::class, 'uploadTugas'])->name('tugas.upload');
        Route::get('/barang', [\App\Http\Controllers\BarangController::class, 'pesertaIndex'])->name('barang');
        Route::patch('/barang/{barangId}', [\App\Http\Controllers\BarangController::class, 'pesertaUpdate'])->name('barang.update');
        Route::delete('/barang/{barangId}/foto', [\App\Http\Controllers\BarangController::class, 'pesertaHapusFoto'])->name('barang.hapus-foto');
        Route::delete('/barang/{barangId}', [\App\Http\Controllers\BarangController::class, 'pesertaReset'])->name('barang.reset');
        // Pastikan pakai Route::delete, bukan Route::get
        Route::delete('/peserta/barang/{id}/foto', [BarangController::class, 'deleteFoto'])->name('barang.foto.destroy');
        Route::get('/daftar-wajah',  [FaceController::class, 'registerForm'])->name('face.register');
        Route::post('/daftar-wajah', [FaceController::class, 'registerStore'])->name('face.register.store');
    });
}); // <--- PENUTUP AUTH

require __DIR__ . '/auth.php';
