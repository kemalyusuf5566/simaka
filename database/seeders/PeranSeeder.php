<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PeranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
        'admin',
        'bk',
        'wali_kelas',
        'guru_mapel',
        'koordinator_p5',
        'pembina_ekskul',
    ];

    foreach ($roles as $role) {
        DB::table('peran')->updateOrInsert(
            ['nama_peran' => $role],
            [
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
    }
}
