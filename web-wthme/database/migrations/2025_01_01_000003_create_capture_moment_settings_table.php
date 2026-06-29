<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('capture_moment_settings', function (Blueprint $table) {
            $table->id();
            $table->timestamp('mulai_at')->nullable();
            $table->timestamp('selesai_at')->nullable();
            $table->timestamps();
        });

        // Seed 1 baris default supaya tidak perlu cek null di controller
        \Illuminate\Support\Facades\DB::table('capture_moment_settings')->insert([
            'mulai_at'   => null,
            'selesai_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('capture_moment_settings');
    }
};
