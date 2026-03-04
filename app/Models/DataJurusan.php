<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataJurusan extends Model
{
    protected $table = 'data_jurusan';

    protected $fillable = [
        'kode_jurusan',
        'nama_jurusan',
        'status_aktif',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
    ];

    public function kelas()
    {
        return $this->hasMany(DataKelas::class, 'jurusan_id');
    }
}
