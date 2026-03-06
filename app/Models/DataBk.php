<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataBk extends Model
{
    protected $table = 'data_bk';

    public const STATUS_BELUM = 'Belum Ditindaklanjuti';
    public const STATUS_PROSES = 'Proses Pembinaan';
    public const STATUS_SELESAI = 'Selesai';

    protected $fillable = [
        'data_siswa_id',
        'data_kelas_id',
        'data_tahun_pelajaran_id',
        'tanggal',
        'jenis_kasus',
        'deskripsi_masalah',
        'tindak_lanjut',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public static function statusOptions(): array
    {
        return [
            self::STATUS_BELUM,
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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
