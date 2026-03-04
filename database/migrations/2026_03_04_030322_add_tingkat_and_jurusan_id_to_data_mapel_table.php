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
        Schema::table('data_mapel', function (Blueprint $table) {
            // tingkat: X | XI | XII | SEMUA
            $table->string('tingkat', 10)->default('SEMUA')->after('kelompok_mapel');

            // jurusan_id nullable (mapel umum = null)
            $table->unsignedBigInteger('jurusan_id')->nullable()->after('tingkat');

            // kalau kamu sudah punya tabel jurusan, aktifkan FK ini
            // $table->foreign('jurusan_id')->references('id')->on('jurusan')->nullOnDelete();

            $table->index(['tingkat', 'jurusan_id']);
        });
    }

    public function down(): void
    {
        Schema::table('data_mapel', function (Blueprint $table) {
            // kalau FK dipakai, drop dulu
            // $table->dropForeign(['jurusan_id']);

            $table->dropIndex(['tingkat', 'jurusan_id']);
            $table->dropColumn(['tingkat', 'jurusan_id']);
        });
    }
};
