<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('capture_moment_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('capture_moment_id')->constrained('capture_moments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('emoji', 20); // emoji bebas dari device peserta, mis: "🔥", "😍"
            $table->timestamps();

            // 1 peserta hanya bisa punya 1 reaction aktif per foto (klik lagi = ganti emoji)
            $table->unique(['capture_moment_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capture_moment_reactions');
    }
};
