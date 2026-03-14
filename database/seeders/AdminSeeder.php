<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRoleId = DB::table('peran')->where('nama_peran', 'admin')->value('id');
        if (!$adminRoleId) {
            DB::table('peran')->updateOrInsert(
                ['nama_peran' => 'admin'],
                ['created_at' => now(), 'updated_at' => now()]
            );
            $adminRoleId = DB::table('peran')->where('nama_peran', 'admin')->value('id');
        }

        DB::table('pengguna')->updateOrInsert(
            // kondisi pencarian
            ['email' => 'admin@simaka.test'],

            // data yang diisi / diupdate
            [
                'peran_id' => $adminRoleId,
                'nama' => 'Administrator',
                'password' => Hash::make('password'),
                'status_aktif' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
