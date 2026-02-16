<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataKelas extends Model
{
    protected $table = 'data_kelas';

    protected $fillable = [
        'data_sekolah_id',
        'data_tahun_pelajaran_id',
        'nama_kelas',
        'tingkat',
        'wali_kelas_id',
    ];

    public function tahunPelajaran()
    {
        return $this->belongsTo(
            DataTahunPelajaran::class,
            'data_tahun_pelajaran_id'
        );
    }

  
    public function wali()
    {
        return $this->belongsTo(DataGuru::class, 'wali_kelas_id', 'pengguna_id');
    }

    public function siswa()
    {
        return $this->hasMany(
            DataSiswa::class,
            'data_kelas_id'
        );
    }
    public function nilaiMapel()
    {
        return $this->hasMany(NilaiMapelSiswa::class, 'data_kelas_id');
    }
}
