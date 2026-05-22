<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::table('qr_sessions', function (Blueprint $table) {
            $table->boolean('rotating')->default(false);
            $table->integer('rotate_interval')->default(30); // detik
            $table->string('current_token', 64)->nullable(); // token aktif saat ini
            $table->timestamp('token_expires_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('qr_sessions', function (Blueprint $table) {
            //
        });
    }
};
