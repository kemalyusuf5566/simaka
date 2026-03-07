<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BkPemanggilanOrangTua extends Model
{
    public const STATUS_DIJADWALKAN = 'Dijadwalkan';
    public const STATUS_HADIR = 'Hadir';
    public const STATUS_TIDAK_HADIR = 'Tidak Hadir';
    public const STATUS_SELESAI = 'Selesai';

    protected $table = 'bk_pemanggilan_orang_tua';

    protected $fillable = [
        'data_siswa_id',
        'data_kelas_id',
        'data_tahun_pelajaran_id',
        'tanggal_panggilan',
        'nomor_surat',
        'nama_wali_hadir',
        'hubungan_wali',
        'status',
        'alasan_pemanggilan',
        'hasil_pertemuan',
        'tindak_lanjut',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal_panggilan' => 'date',
    ];

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DIJADWALKAN,
            self::STATUS_HADIR,
            self::STATUS_TIDAK_HADIR,
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

