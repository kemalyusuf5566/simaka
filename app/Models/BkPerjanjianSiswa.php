<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BkPerjanjianSiswa extends Model
{
    public const STATUS_AKTIF = 'Aktif';
    public const STATUS_SELESAI = 'Selesai';
    public const STATUS_DIBATALKAN = 'Dibatalkan';

    protected $table = 'bk_perjanjian_siswa';

    protected $fillable = [
        'data_siswa_id',
        'data_kelas_id',
        'data_tahun_pelajaran_id',
        'tanggal_perjanjian',
        'nomor_dokumen',
        'pihak_orang_tua',
        'hubungan_orang_tua',
        'status',
        'isi_perjanjian',
        'target_perbaikan',
        'tanggal_evaluasi',
        'hasil_evaluasi',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal_perjanjian' => 'date',
        'tanggal_evaluasi' => 'date',
    ];

    public static function statusOptions(): array
    {
        return [
            self::STATUS_AKTIF,
            self::STATUS_SELESAI,
            self::STATUS_DIBATALKAN,
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

