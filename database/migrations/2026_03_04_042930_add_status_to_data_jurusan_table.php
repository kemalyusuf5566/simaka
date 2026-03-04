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
        Schema::table('data_jurusan', function (Blueprint $table) {
            $table->enum('status', ['AKTIF', 'TIDAK AKTIF'])
                ->default('AKTIF')
                ->after('nama_jurusan');
        });
    }

    public function down(): void
    {
        Schema::table('data_jurusan', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
