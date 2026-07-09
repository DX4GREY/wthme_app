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
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\KeaktifanController;
// 🟢 IMPORT CONTROLLER BARU
use App\Http\Controllers\P3kBarangController;
use App\Http\Controllers\QuestMeetController;
use App\Http\Controllers\QuestLabController;
use App\Http\Controllers\FotoKekeluargaanController;
use App\Http\Controllers\CaptureMomentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'secure.uploads'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile',   [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/ganti-password', [ChangePasswordController::class, 'show'])->name('password.change');
    Route::put('/ganti-password', [ChangePasswordController::class, 'update'])->name('password.change.update');
    Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard.index');

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

        // 🟢 PINDAH KE SINI: Route Import Abang-Abang KBMS khusus Admin
        // --- EDIT PADA BAGIAN KELOMPOK ROUTE INI SAJA ---
        Route::prefix('abang')->name('abang.')->group(function () {
            // 🟢 Diubah ke method 'indexAbang' agar mengarah ke admin.abang.index yang asli
            Route::get('/', [QuestMeetController::class, 'indexAbang'])->name('index');

            Route::get('/import', [QuestMeetController::class, 'importForm'])->name('import');
            Route::post('/import', [QuestMeetController::class, 'importStore'])->name('import.store');
            Route::get('/template', [QuestMeetController::class, 'downloadTemplateAbang'])->name('template');
        });
    });

    // --- PANITIA ---
    Route::prefix('panitia')->name('panitia.')->middleware('panitia')->group(function () {
        Route::get('/', [PanitiaController::class, 'index'])->name('index');
        Route::post('/links', [PanitiaController::class, 'storeLink'])->name('links.store');
        Route::delete('/links/{id}', [PanitiaController::class, 'destroyLink'])->name('links.destroy');
        Route::post('/broadcast-peserta', [PanitiaController::class, 'storeInfoPeserta'])->name('info.peserta.store');
        Route::delete('/broadcast-peserta/{id}', [PanitiaController::class, 'destroyInfoPeserta'])->name('info.peserta.destroy');
        Route::get('/informasi-peserta', [PanitiaController::class, 'indexInfoPeserta'])->name('info.peserta.index');
        Route::post('/informasi-peserta', [PanitiaController::class, 'storeInfoPeserta'])->name('info.peserta.store');
        Route::delete('/informasi-peserta/{id}', [PanitiaController::class, 'destroyInfoPeserta'])->name('info.peserta.destroy');
        Route::get('/absensi/face-gate',     [FaceAbsensiController::class, 'gate'])->name('absen.face.gate');
        Route::post('/absensi/face-gate',    [FaceAbsensiController::class, 'gateProcess'])->name('absen.face.process');

        // KEAKTIFAN
        Route::prefix('keaktifan')->name('keaktifan.')->group(function () {
            Route::get('/', [KeaktifanController::class, 'index'])->name('index');
            Route::post('/store', [KeaktifanController::class, 'store'])->name('store');
            Route::delete('/{id}', [KeaktifanController::class, 'destroy'])->name('destroy');
        });

        // CAPTURE MOMENT (Quest Foto Kelompok)
        Route::prefix('capture-moment')->name('capture.')->group(function () {
            Route::get('/', [CaptureMomentController::class, 'panitiaIndex'])->name('index');
            Route::post('/{id}/nilai', [CaptureMomentController::class, 'nilai'])->name('nilai');
            Route::post('/settings', [CaptureMomentController::class, 'settingsUpdate'])->name('settings');
        });

        // Fitur Kontrol Divisi Acara & Admin untuk Quest Meet KBM (Tanpa Route Import)
        Route::prefix('quest-meet')->name('meet.')->group(function () {
            Route::get('/', [QuestMeetController::class, 'indexPanitia'])->name('index');
            Route::post('/approve/{id}', [QuestMeetController::class, 'approve'])->name('approve');
            Route::post('/reject/{id}', [QuestMeetController::class, 'reject'])->name('reject');
        });

        // 🔴 FITUR QUEST LAB (Pembersihan Duplikat)
        Route::prefix('quest-lab')->name('quest.')->group(function () {
            Route::get('/', [QuestLabController::class, 'indexPanitia'])->name('index');
            Route::post('/approve-all', [QuestLabController::class, 'approveAll'])->name('approveAll');
            Route::post('/approve/{id}', [QuestLabController::class, 'approveQuest'])->name('approve');
            Route::post('/reject/{id}', [QuestLabController::class, 'rejectQuest'])->name('reject');
        });
         // P3K BARANG (PANITIA)
        Route::prefix('p3k')->name('p3k.')->group(function () {
            Route::get('/', [P3kBarangController::class, 'panitiaIndex'])->name('index');
            Route::get('/kelompok/{kelompok}', [P3kBarangController::class, 'panitiaKelompok'])->name('kelompok');
            Route::get('/rekap', [P3kBarangController::class, 'panitiaRekap'])->name('rekap');
            Route::get('/export', [P3kBarangController::class, 'exportRekap'])->name('export');

            // Validasi & terpakai - BARANG KELOMPOK (agregat per kelompok)
            Route::post('/validasi/{barangId}/{kelompok}', [P3kBarangController::class, 'toggleValidasi'])->name('validasi');
            Route::post('/terpakai/{barangId}/{kelompok}', [P3kBarangController::class, 'updateTerpakai'])->name('terpakai');

            // Validasi - PENGUMPULAN KOLEKTIF barang INDIVIDU (per perwakilan kelompok)
            Route::post('/kolektif/{pengumpulanId}/validasi', [P3kBarangController::class, 'toggleValidasiKolektif'])->name('kolektif.validasi');

            // Stok terpakai barang INDIVIDU — per kelompok (dikontrol dari halaman kelompok)
            Route::post('/stok/{barangId}/{kelompok}/terpakai', [P3kBarangController::class, 'updateStokTerpakai'])->name('stok.terpakai');
            Route::post('/stok/{barangId}/{kelompok}/adjust', [P3kBarangController::class, 'adjustStokTerpakai'])->name('stok.adjust');
            // Global stok (tidak per-kelompok) — untuk kontrol di halaman index
            Route::post('/stok-global/{barangId}/adjust', [P3kBarangController::class, 'globalAdjustStok'])->name('stok.global.adjust');
            Route::post('/stok-global/{barangId}/set', [P3kBarangController::class, 'globalSetStok'])->name('stok.global.set');

            Route::post('/obat/{id}/toggle', [P3kBarangController::class, 'obatToggleDiserahkan'])->name('obat.toggle');

            // Manage daftar barang & mapping PJ (divisi P3K saja)
            Route::get('/manage', [P3kBarangController::class, 'manageIndex'])->name('manage');
            Route::post('/manage', [P3kBarangController::class, 'manageStore'])->name('manage.store');
            Route::put('/manage/{id}', [P3kBarangController::class, 'manageUpdate'])->name('manage.update');
            Route::delete('/manage/{id}', [P3kBarangController::class, 'manageDestroy'])->name('manage.destroy');

            Route::post('/manage/pj', [P3kBarangController::class, 'pjStore'])->name('manage.pj.store');
        });

        // LEADERBOARD INPUT
        Route::prefix('leaderboard')->name('leaderboard.')->group(function () {
            Route::get('/input', [LeaderboardController::class, 'inputPoint'])->name('input');
        });

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
        Route::post('/absensi/peserta/update-status', [AbsensiController::class, 'updateStatusPeserta'])->name('absensi.updateStatus');

        // MENTORING (LOGBOOK)
        Route::prefix('mentoring')->name('mentoring.')->group(function () {
            Route::get('/', [MentoringController::class, 'index'])->name('index');
            Route::post('/sesi', [MentoringController::class, 'storeSesi'])->name('storeSesi');
            Route::get('/catatan/{sesiId}', [MentoringController::class, 'formCatatan'])->name('catatan');
            Route::post('/catatan/{sesiId}', [MentoringController::class, 'simpanCatatan'])->name('catatan.simpan');
            Route::post('/assign-mentor', [MentoringController::class, 'assignMentor'])->name('assignMentor');

            Route::get('/rekap-global', [MentoringController::class, 'rekapGlobal'])->name('rekap');
            Route::get('/kelompok/{kelompok}', [MentoringController::class, 'kelompok'])->name('kelompok');

            Route::middleware('mentor')->group(function () {
                Route::post('/kelompok/{kelompok}', [MentoringController::class, 'store'])->name('store');
                Route::put('/detail/{id}', [MentoringController::class, 'updateDetail'])->name('updateDetail');
                Route::delete('/{id}', [MentoringController::class, 'destroy'])->name('destroy');
                Route::get('/kelompok/{kelompok}/export', [MentoringController::class, 'export'])->name('export');
                Route::get('/export-seluruh-kelompok', [MentoringController::class, 'exportSeluruh'])->name('export_seluruh');
            });
        });

        // EXPORT
        Route::get('/export/peserta', [ExportController::class, 'exportPeserta'])->name('export.peserta');
        Route::get('/export/panitia', [ExportController::class, 'exportPanitia'])->name('export.panitia');
        Route::get('/export/kesehatan', [ExportController::class, 'exportKesehatan'])->name('export.kesehatan');

        // KESEHATAN
        Route::prefix('kesehatan')->name('kesehatan.')->group(function () {
            Route::get('/', [KesehatanController::class, 'indexPanitia'])->name('index');
            Route::post('/{id}/update-pita', [KesehatanController::class, 'updateWarnaPita'])->name('updatePita');
        });

        // KAS
        Route::prefix('kas')->name('kas.')->group(function () {
            Route::get('/', [KasController::class, 'index'])->name('index');

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
            Route::get('/files-json/{id}', [TugasController::class, 'getFilesJson'])->name('files-json');
            Route::get('/file/download/{id}/{fileIndex}', [TugasController::class, 'downloadSingleFile'])->name('download-single');
            Route::delete('/tolak/{id}', [TugasController::class, 'tolakTugas'])->name('tugas.tolak');
            Route::put('/{id}', [TugasController::class, 'updateTugas'])->name('update');
        });

        // NOTULENSI
        Route::prefix('notulensi')->name('notulensi.')->group(function () {
            Route::get('/', [NotulensiController::class, 'index'])->name('index');
            Route::post('/', [NotulensiController::class, 'store'])->name('store');
            Route::get('/download/{id}', [NotulensiController::class, 'downloadDoc'])->name('download');
            Route::get('/export/{id}', [ExportController::class, 'exportNotulensi'])->name('export');
        });

        // GANTT CHART
        Route::prefix('gantt')->name('gantt.')->group(function () {
            Route::get('/', [GanttController::class, 'index'])->name('index');
            Route::post('/', [GanttController::class, 'store'])->name('store');
            Route::put('/{id}', [GanttController::class, 'update'])->name('update');
            Route::delete('/{id}', [GanttController::class, 'destroy'])->name('destroy');
        });

        // LOGISTIK BARANG (PANITIA)
        Route::prefix('barang')->name('barang.')->group(function () {
            Route::get('/', [BarangController::class, 'panitiaIndex'])->name('index');
            Route::get('/kelompok/{kelompok}', [BarangController::class, 'panitiaKelompok'])->name('kelompok');
            Route::get('/rekap', [BarangController::class, 'panitiaRekap'])->name('rekap');
            Route::get('/export', [BarangController::class, 'exportRekap'])->name('export');
            Route::post('/validasi/{barangId}/{kelompok}', [BarangController::class, 'toggleValidasi'])->name('validasi');

            Route::get('/manage', [BarangController::class, 'manageIndex'])->name('manage');
            Route::post('/manage', [BarangController::class, 'manageStore'])->name('manage.store');
            Route::put('/manage/{id}', [BarangController::class, 'manageUpdate'])->name('manage.update');
            Route::delete('/manage/{id}', [BarangController::class, 'manageDestroy'])->name('manage.destroy');
        });

        
    });

    // --- PESERTA ---
    Route::prefix('peserta')->name('peserta.')->middleware('peserta')->group(function () {
        Route::get('/',                   [PesertaController::class, 'index'])->name('index');
        Route::get('/absen',              [AbsensiController::class, 'formPeserta'])->name('absen');
        Route::post('/absen',             [AbsensiController::class, 'storePeserta'])->name('absen.store');
        Route::get('/riwayat-penyakit',  [PesertaController::class, 'riwayatPenyakit'])->name('riwayat');
        Route::post('/riwayat-penyakit', [PesertaController::class, 'simpanRiwayat'])->name('riwayat.store');
        Route::get('/tugas',             [TugasController::class, 'indexPeserta'])->name('tugas');
        Route::post('/tugas/upload',     [TugasController::class, 'uploadTugas'])->name('tugas.upload');


        // Logistik Barang (Peserta)
        Route::get('/barang', [BarangController::class, 'pesertaIndex'])->name('barang');
        Route::patch('/barang/{barangId}', [BarangController::class, 'pesertaUpdate'])->name('barang.update');
        Route::delete('/barang/{barangId}/foto', [BarangController::class, 'pesertaHapusFoto'])->name('barang.hapus-foto');
        Route::delete('/barang/{barangId}', [BarangController::class, 'pesertaReset'])->name('barang.reset');
        Route::delete('/peserta/barang/{id}/foto', [BarangController::class, 'deleteFoto'])->name('barang.foto.destroy');

        // P3K Barang (Peserta)
        Route::get('/p3k', [P3kBarangController::class, 'pesertaIndex'])->name('p3k');

        // Barang KELOMPOK (agregat, sama seperti Logistik)
        Route::patch('/p3k/kelompok/{barangId}', [P3kBarangController::class, 'pesertaUpdateKelompok'])->name('p3k.kelompok.update');
        Route::delete('/p3k/kelompok/{barangId}/foto', [P3kBarangController::class, 'pesertaHapusFotoKelompok'])->name('p3k.kelompok.hapus-foto');
        Route::delete('/p3k/kelompok/{barangId}', [P3kBarangController::class, 'pesertaResetKelompok'])->name('p3k.kelompok.reset');

        // Barang INDIVIDU — Pengumpulan Kolektif per menu (logistik/konsumsi/p3k)
        Route::get('/p3k/individu/{menu}', [P3kBarangController::class, 'pesertaIndividuForm'])->name('p3k.individu');
        Route::post('/p3k/individu/{menu}', [P3kBarangController::class, 'pesertaIndividuStore'])->name('p3k.individu.store');
        Route::delete('/p3k/individu/{menu}/foto', [P3kBarangController::class, 'pesertaIndividuHapusFoto'])->name('p3k.individu.hapus-foto');
        Route::post('/p3k/individu/{menu}/keluar', [P3kBarangController::class, 'pesertaIndividuKeluar'])->name('p3k.individu.keluar');
        Route::delete('/p3k/individu/{menu}', [P3kBarangController::class, 'pesertaIndividuBubarkan'])->name('p3k.individu.bubarkan');

        // Obat Pribadi (Peserta)
        Route::post('/p3k/obat', [P3kBarangController::class, 'obatStore'])->name('p3k.obat.store');
        Route::delete('/p3k/obat/{id}', [P3kBarangController::class, 'obatDestroy'])->name('p3k.obat.destroy');

        // Face Recognition
        Route::get('/daftar-wajah',  [FaceController::class, 'registerForm'])->name('face.register');
        Route::post('/daftar-wajah', [FaceController::class, 'registerStore'])->name('face.register.store');

        // Quest 4 Lab Elektro
        Route::prefix('quest-lab')->name('quest.')->group(function () {
            Route::get('/', [QuestLabController::class, 'indexPeserta'])->name('index');
            Route::post('/upload/{labName}', [QuestLabController::class, 'uploadSelfie'])->name('upload');
            Route::delete('/delete/{lab}', [QuestLabController::class, 'delete'])->name('delete');
        });

        // Quest Meet
        Route::prefix('quest-meet')->name('meet.')->group(function () {
            Route::get('/', [QuestMeetController::class, 'indexPeserta'])->name('index');
            Route::get('/create', [QuestMeetController::class, 'create'])->name('create');
            Route::get('/get-abang/{angkatan}', [QuestMeetController::class, 'getAbangByAngkatan'])->name('get-abang');
            Route::post('/store', [QuestMeetController::class, 'store'])->name('store');
        });

        // Misi Kekeluargaan Angkatan
        Route::prefix('kekeluargaan')->name('kekeluargaan.')->group(function () {
            Route::get('/', [FotoKekeluargaanController::class, 'index'])->name('index');
            Route::post('/store', [FotoKekeluargaanController::class, 'store'])->name('store');
            Route::post('/approve/{id}', [FotoKekeluargaanController::class, 'approve'])->name('approve');
            Route::post('/reject/{id}', [FotoKekeluargaanController::class, 'reject'])->name('reject');
        });

        // Capture Moment (Quest Foto Kelompok)
        Route::prefix('capture-moment')->name('capture.')->group(function () {
            Route::get('/', [CaptureMomentController::class, 'pesertaIndex'])->name('index');
            Route::post('/upload', [CaptureMomentController::class, 'pesertaStore'])->name('upload');
            Route::post('/{id}/react', [CaptureMomentController::class, 'react'])->name('react');
        });
    });
});

require __DIR__ . '/auth.php';
