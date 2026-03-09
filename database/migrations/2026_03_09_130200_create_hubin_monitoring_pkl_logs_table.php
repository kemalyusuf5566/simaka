<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hubin_monitoring_pkl_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hubin_penempatan_pkl_id')->constrained('hubin_penempatan_pkl')->cascadeOnDelete();
            $table->date('tanggal_monitoring');
            $table->string('status_monitoring', 30)->default('Baik');
            $table->string('topik_monitoring', 150)->nullable();
            $table->text('catatan')->nullable();
            $table->unsignedTinyInteger('skor_kinerja')->nullable();
            $table->text('tindak_lanjut')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->timestamps();

            $table->index('hubin_penempatan_pkl_id');
            $table->index('tanggal_monitoring');
            $table->index('status_monitoring');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hubin_monitoring_pkl_logs');
    }
};
