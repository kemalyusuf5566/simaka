<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hubin_dudi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_instansi', 160);
            $table->string('bidang_usaha', 120)->nullable();
            $table->string('alamat', 220)->nullable();
            $table->string('kontak_person', 120)->nullable();
            $table->string('telepon', 40)->nullable();
            $table->string('email', 120)->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->timestamps();

            $table->index('nama_instansi');
            $table->index('status_aktif');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hubin_dudi');
    }
};
