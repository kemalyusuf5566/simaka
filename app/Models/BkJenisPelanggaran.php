<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BkJenisPelanggaran extends Model
{
    protected $table = 'bk_jenis_pelanggaran';

    protected $fillable = [
        'kode',
        'nama_pelanggaran',
        'poin_default',
        'status_aktif',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
    ];

    public function pelanggaranSiswa()
    {
        return $this->hasMany(BkPelanggaranSiswa::class, 'bk_jenis_pelanggaran_id');
    }
}

