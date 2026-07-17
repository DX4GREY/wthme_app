<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('capture_moment_comment_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('capture_moment_comment_id')->constrained('capture_moment_comments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            // 1 peserta hanya bisa like 1 komentar 1x (tidak bisa like 2x)
            $table->unique(['capture_moment_comment_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capture_moment_comment_likes');
    }
};