<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BkPembinaanSiswa extends Model
{
    public const STATUS_DIRENCANAKAN = 'Direncanakan';
    public const STATUS_PROSES = 'Proses';
    public const STATUS_SELESAI = 'Selesai';

    protected $table = 'bk_pembinaan_siswa';

    protected $fillable = [
        'data_siswa_id',
        'data_kelas_id',
        'data_tahun_pelajaran_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'bentuk_pembinaan',
        'tujuan',
        'status',
        'catatan',
        'hasil',
        'rekomendasi',
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
            self::STATUS_PROSES,
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

