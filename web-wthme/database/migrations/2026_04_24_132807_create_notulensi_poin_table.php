<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notulensi_poin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notulensi_id')->constrained('notulensi')->onDelete('cascade');
            $table->string('divisi'); // BPH, Acara, Konsumsi, dll
            $table->text('isi_poin');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notulensi_poin');
    }
};
