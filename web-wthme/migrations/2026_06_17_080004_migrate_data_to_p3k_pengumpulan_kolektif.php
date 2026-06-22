<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sistem input barang individu berubah dari "1 peserta = 1 baris per barang"
     * menjadi "1 perwakilan = 1 pengumpulan kolektif untuk beberapa peserta".
     *
     * Migrasi ini mengonversi data lama secara best-effort: setiap peserta yang
     * sebelumnya sudah pernah mengisi data individu sendiri akan dijadikan
     * "perwakilan untuk dirinya sendiri" (jumlah anggota = 1, tanpa rekan yang
     * dititipkan) di struktur baru, supaya data yang sudah diinput tidak hilang.
     * Setelah dikonversi, tabel lama dihapus.
     *
     * Catatan: validasi/ACC pada struktur lama bersifat per-baris (per barang),
     * sedangkan pada struktur baru ACC bersifat per-pengumpulan (mencakup semua
     * barang sekaligus). Karena tidak ada pemetaan 1:1 yang aman, hasil migrasi
     * SELALU dimulai dalam status belum di-ACC (is_validated = false) agar
     * panitia P3K meninjau ulang setiap pengumpulan hasil migrasi.
     */
    public function up(): void
    {
        if (!Schema::hasTable('p3k_pengumpulan_individu')) {
            return;
        }

        $rowsLama = DB::table('p3k_pengumpulan_individu')->orderBy('user_id')->get();
        $now = now();

        foreach ($rowsLama->groupBy('user_id') as $userId => $rows) {
            $user = DB::table('users')->where('id', $userId)->first();

            // Lewati jika user sudah tidak ada / tidak punya kelompok (data yatim)
            if (!$user || empty($user->kelompok)) {
                continue;
            }

            $fotoPertama = $rows->first(fn ($r) => !empty($r->foto_bukti))?->foto_bukti;

            $pengumpulanId = DB::table('p3k_pengumpulan_kolektif')->insertGetId([
                'perwakilan_user_id' => $userId,
                'kelompok'           => $user->kelompok,
                'foto_bukti'         => $fotoPertama,
                'is_validated'       => false,
                'updated_by'         => null,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);

            DB::table('p3k_pengumpulan_kolektif_anggota')->insert([
                'pengumpulan_kolektif_id' => $pengumpulanId,
                'user_id'                 => $userId,
                'created_at'              => $now,
                'updated_at'              => $now,
            ]);

            foreach ($rows as $r) {
                DB::table('p3k_pengumpulan_kolektif_item')->insert([
                    'pengumpulan_kolektif_id'  => $pengumpulanId,
                    'p3k_barang_kebutuhan_id'  => $r->p3k_barang_kebutuhan_id,
                    'jumlah_dibawa'            => $r->jumlah_dibawa,
                    'created_at'               => $now,
                    'updated_at'               => $now,
                ]);
            }
        }

        Schema::dropIfExists('p3k_pengumpulan_individu');
    }

    /**
     * Best-effort: kembalikan struktur tabel lama (kosong). Data yang sudah
     * dipecah ke struktur kolektif tidak direkonstruksi otomatis karena
     * pemetaan baliknya ambigu (satu pengumpulan bisa berasal dari beberapa
     * peserta sekaligus pada penggunaan normal fitur baru).
     */
    public function down(): void
    {
        Schema::create('p3k_pengumpulan_individu', function ($table) {
            $table->id();
            $table->foreignId('p3k_barang_kebutuhan_id')->constrained('p3k_barang_kebutuhan')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('jumlah_dibawa')->default(0);
            $table->string('foto_bukti')->nullable();
            $table->boolean('is_validated')->default(false);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['p3k_barang_kebutuhan_id', 'user_id']);
        });
    }
};
