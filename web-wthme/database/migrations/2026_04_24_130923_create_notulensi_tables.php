<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    // Jalankan: php artisan make:migration create_notulensi_tables
    public function up(): void
    {
        Schema::create('notulensi', function (Blueprint $table) {
            $table->id();
            $table->string('topik');
            $table->date('tanggal');
            $table->string('tempat')->nullable();
            $table->string('pemimpin_rapat')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notulensi_tables');
    }
};
