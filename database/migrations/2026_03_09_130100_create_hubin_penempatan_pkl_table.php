<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hubin_penempatan_pkl', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_siswa_id')->constrained('data_siswa')->cascadeOnDelete();
            $table->foreignId('data_kelas_id')->constrained('data_kelas')->cascadeOnDelete();
            $table->foreignId('data_tahun_pelajaran_id')->constrained('data_tahun_pelajaran')->cascadeOnDelete();
            $table->foreignId('hubin_dudi_id')->constrained('hubin_dudi')->restrictOnDelete();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->string('status_penempatan', 30)->default('Direncanakan');
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->timestamps();

            $table->index('data_siswa_id');
            $table->index('data_kelas_id');
            $table->index('data_tahun_pelajaran_id');
            $table->index('hubin_dudi_id');
            $table->index('status_penempatan');
            $table->unique(['data_siswa_id', 'data_tahun_pelajaran_id'], 'hubin_unique_siswa_tahun');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hubin_penempatan_pkl');
    }
};
