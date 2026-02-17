<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('data_mapel', function (Blueprint $table) {
            if (!Schema::hasColumn('data_mapel', 'singkatan')) {
                $table->string('singkatan', 30)->nullable()->after('nama_mapel');
            }

            if (!Schema::hasColumn('data_mapel', 'urutan_cetak')) {
                $table->unsignedInteger('urutan_cetak')->nullable()->after('singkatan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('data_mapel', function (Blueprint $table) {
            if (Schema::hasColumn('data_mapel', 'singkatan')) {
                $table->dropColumn('singkatan');
            }
            if (Schema::hasColumn('data_mapel', 'urutan_cetak')) {
                $table->dropColumn('urutan_cetak');
            }
        });
    }
};
