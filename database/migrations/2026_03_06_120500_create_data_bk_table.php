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
        Schema::create('data_bk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_siswa_id')->constrained('data_siswa')->cascadeOnDelete();
            $table->foreignId('data_kelas_id')->constrained('data_kelas')->cascadeOnDelete();
            $table->foreignId('data_tahun_pelajaran_id')->constrained('data_tahun_pelajaran')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('jenis_kasus', 150);
            $table->text('deskripsi_masalah');
            $table->text('tindak_lanjut')->nullable();
            $table->string('status', 40)->default('Belum Ditindaklanjuti');
            $table->foreignId('created_by')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->timestamps();

            $table->index('data_siswa_id');
            $table->index('data_kelas_id');
            $table->index('data_tahun_pelajaran_id');
            $table->index('tanggal');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_bk');
    }
};
