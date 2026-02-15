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
        Schema::table('kk_nilai', function (Blueprint $table) {

            // 1) BUAT unique baru dulu (4 kolom) -> ini sudah cukup jadi index pengganti untuk FK
            $table->unique(
                ['kk_kelompok_id', 'kk_kegiatan_id', 'data_siswa_id', 'kk_capaian_akhir_id'],
                'kk_nilai_unique_per_capaian'
            );

            // 2) BARU drop unique lama (3 kolom)
            $table->dropUnique('kk_nilai_kk_kelompok_id_kk_kegiatan_id_data_siswa_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('kk_nilai', function (Blueprint $table) {

            // 1) bikin unique lama dulu
            $table->unique(
                ['kk_kelompok_id', 'kk_kegiatan_id', 'data_siswa_id'],
                'kk_nilai_kk_kelompok_id_kk_kegiatan_id_data_siswa_id_unique'
            );

            // 2) baru drop unique baru
            $table->dropUnique('kk_nilai_unique_per_capaian');
        });
    }
};
