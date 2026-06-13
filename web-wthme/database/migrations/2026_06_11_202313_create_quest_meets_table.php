<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quest_meets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // ID Peserta
            $table->string('kategori_angkatan'); // '2021', '2022', '2023', '2024', 'alumni'
            $table->enum('tipe_meet', ['individu', 'group']);
            $table->json('selected_abang'); // Menyimpan array nama/ID abang-abang yang dipilih/diketik
            $table->string('foto_bukti');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete('set null'); // Panitia Acara/Admin
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quest_meets');
    }
};