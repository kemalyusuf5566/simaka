<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatatanWaliKelas extends Model
{
    protected $table = 'catatan_wali_kelas';
        protected $fillable = [
            'data_siswa_id',
            'data_tahun_pelajaran_id',
            'semester',
            'catatan',
            'status_kenaikan_kelas',
        ];
    public function siswa()
    {
        return $this->belongsTo(DataSiswa::class, 'data_siswa_id');
    }
}
