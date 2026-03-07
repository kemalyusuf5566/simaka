<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BkHomeVisit extends Model
{
    public const STATUS_DIRENCANAKAN = 'Direncanakan';
    public const STATUS_TERLAKSANA = 'Terlaksana';
    public const STATUS_SELESAI = 'Selesai';

    protected $table = 'bk_home_visit';

    protected $fillable = [
        'data_siswa_id',
        'data_kelas_id',
        'data_tahun_pelajaran_id',
        'tanggal_kunjungan',
        'lokasi_kunjungan',
        'tujuan_kunjungan',
        'status',
        'hasil_observasi',
        'tindak_lanjut',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal_kunjungan' => 'date',
    ];

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DIRENCANAKAN,
            self::STATUS_TERLAKSANA,
            self::STATUS_SELESAI,
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
}

