<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // dalam migration baru
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('face_registered')->default(false)->after('gender');
            $table->timestamp('face_registered_at')->nullable()->after('face_registered');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
