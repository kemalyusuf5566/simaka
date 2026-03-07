<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bk_pemanggilan_orang_tua', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_siswa_id')->constrained('data_siswa')->cascadeOnDelete();
            $table->foreignId('data_kelas_id')->constrained('data_kelas')->cascadeOnDelete();
            $table->foreignId('data_tahun_pelajaran_id')->constrained('data_tahun_pelajaran')->cascadeOnDelete();
            $table->date('tanggal_panggilan');
            $table->string('nomor_surat', 80)->nullable();
            $table->string('nama_wali_hadir', 150)->nullable();
            $table->string('hubungan_wali', 80)->nullable();
            $table->string('status', 40)->default('Dijadwalkan');
            $table->text('alasan_pemanggilan');
            $table->text('hasil_pertemuan')->nullable();
            $table->text('tindak_lanjut')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->timestamps();

            $table->index('data_siswa_id');
            $table->index('data_kelas_id');
            $table->index('data_tahun_pelajaran_id');
            $table->index('tanggal_panggilan');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bk_pemanggilan_orang_tua');
    }
};

