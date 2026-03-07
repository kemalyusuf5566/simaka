<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bk_peminatan_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_siswa_id')->constrained('data_siswa')->cascadeOnDelete();
            $table->foreignId('data_kelas_id')->constrained('data_kelas')->cascadeOnDelete();
            $table->foreignId('data_tahun_pelajaran_id')->constrained('data_tahun_pelajaran')->cascadeOnDelete();
            $table->date('tanggal_peminatan');
            $table->string('minat_utama', 150);
            $table->string('minat_alternatif', 150)->nullable();
            $table->string('rencana_lanjutan', 180)->nullable();
            $table->string('status', 40)->default('Direkomendasikan');
            $table->text('rekomendasi_bk')->nullable();
            $table->text('catatan_orang_tua')->nullable();
            $table->text('catatan_tindak_lanjut')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->timestamps();

            $table->index('data_siswa_id');
            $table->index('data_kelas_id');
            $table->index('data_tahun_pelajaran_id');
            $table->index('tanggal_peminatan');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bk_peminatan_siswa');
    }
};

