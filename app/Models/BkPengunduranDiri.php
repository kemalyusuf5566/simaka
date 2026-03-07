<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BkPengunduranDiri extends Model
{
    public const STATUS_DIAJUKAN = 'Diajukan';
    public const STATUS_DIPROSES = 'Diproses';
    public const STATUS_DISETUJUI = 'Disetujui';
    public const STATUS_DITOLAK = 'Ditolak';

    protected $table = 'bk_pengunduran_diri';

    protected $fillable = [
        'data_siswa_id',
        'data_kelas_id',
        'data_tahun_pelajaran_id',
        'tanggal_pengajuan',
        'tanggal_efektif',
        'status',
        'alasan_pengunduran_diri',
        'keterangan',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal_pengajuan' => 'date',
        'tanggal_efektif' => 'date',
    ];

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DIAJUKAN,
            self::STATUS_DIPROSES,
            self::STATUS_DISETUJUI,
            self::STATUS_DITOLAK,
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

