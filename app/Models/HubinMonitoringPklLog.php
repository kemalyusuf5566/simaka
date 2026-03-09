<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HubinMonitoringPklLog extends Model
{
    public const STATUS_BAIK = 'Baik';
    public const STATUS_PERLU_PERHATIAN = 'Perlu Perhatian';
    public const STATUS_KRITIS = 'Kritis';

    protected $table = 'hubin_monitoring_pkl_logs';

    protected $fillable = [
        'hubin_penempatan_pkl_id',
        'tanggal_monitoring',
        'status_monitoring',
        'topik_monitoring',
        'catatan',
        'skor_kinerja',
        'tindak_lanjut',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal_monitoring' => 'date',
    ];

    public static function statusOptions(): array
    {
        return [
            self::STATUS_BAIK,
            self::STATUS_PERLU_PERHATIAN,
            self::STATUS_KRITIS,
        ];
    }

    public function penempatan()
    {
        return $this->belongsTo(HubinPenempatanPkl::class, 'hubin_penempatan_pkl_id');
    }
}
