<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BkPeminatanSiswa extends Model
{
    public const STATUS_DIREKOMENDASIKAN = 'Direkomendasikan';
    public const STATUS_DITETAPKAN = 'Ditetapkan';
    public const STATUS_MONITORING = 'Monitoring';

    protected $table = 'bk_peminatan_siswa';

    protected $fillable = [
        'data_siswa_id',
        'data_kelas_id',
        'data_tahun_pelajaran_id',
        'tanggal_peminatan',
        'minat_utama',
        'minat_alternatif',
        'rencana_lanjutan',
        'status',
        'rekomendasi_bk',
        'catatan_orang_tua',
        'catatan_tindak_lanjut',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal_peminatan' => 'date',
    ];

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DIREKOMENDASIKAN,
            self::STATUS_DITETAPKAN,
            self::STATUS_MONITORING,
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

