<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bk_perjanjian_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_siswa_id')->constrained('data_siswa')->cascadeOnDelete();
            $table->foreignId('data_kelas_id')->constrained('data_kelas')->cascadeOnDelete();
            $table->foreignId('data_tahun_pelajaran_id')->constrained('data_tahun_pelajaran')->cascadeOnDelete();
            $table->date('tanggal_perjanjian');
            $table->string('nomor_dokumen', 80)->nullable();
            $table->string('pihak_orang_tua', 150)->nullable();
            $table->string('hubungan_orang_tua', 80)->nullable();
            $table->string('status', 40)->default('Aktif');
            $table->text('isi_perjanjian');
            $table->text('target_perbaikan')->nullable();
            $table->date('tanggal_evaluasi')->nullable();
            $table->text('hasil_evaluasi')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->timestamps();

            $table->index('data_siswa_id');
            $table->index('data_kelas_id');
            $table->index('data_tahun_pelajaran_id');
            $table->index('tanggal_perjanjian');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bk_perjanjian_siswa');
    }
};

