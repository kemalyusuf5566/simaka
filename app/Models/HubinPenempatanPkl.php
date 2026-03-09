<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HubinPenempatanPkl extends Model
{
    public const STATUS_DIRENCANAKAN = 'Direncanakan';
    public const STATUS_BERJALAN = 'Berjalan';
    public const STATUS_SELESAI = 'Selesai';
    public const STATUS_BATAL = 'Batal';

    protected $table = 'hubin_penempatan_pkl';

    protected $fillable = [
        'data_siswa_id',
        'data_kelas_id',
        'data_tahun_pelajaran_id',
        'hubin_dudi_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'status_penempatan',
        'catatan',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DIRENCANAKAN,
            self::STATUS_BERJALAN,
            self::STATUS_SELESAI,
            self::STATUS_BATAL,
        ];
    }

    public function siswa()
    {
        return $this->belongsTo(DataSiswa::class, 'data_siswa_id');
    }

    public function kelas()
    {
        return $this->belongsTo(DataKelas::class, 'data_kelas_id');
    }

    public function tahunPelajaran()
    {
        return $this->belongsTo(DataTahunPelajaran::class, 'data_tahun_pelajaran_id');
    }

    public function dudi()
    {
        return $this->belongsTo(HubinDudi::class, 'hubin_dudi_id');
    }

    public function monitoringLogs()
    {
        return $this->hasMany(HubinMonitoringPklLog::class, 'hubin_penempatan_pkl_id');
    }
}
