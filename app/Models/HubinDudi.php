<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HubinDudi extends Model
{
    protected $table = 'hubin_dudi';

    protected $fillable = [
        'nama_instansi',
        'bidang_usaha',
        'alamat',
        'kontak_person',
        'telepon',
        'email',
        'status_aktif',
        'catatan',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
    ];

    public function penempatan()
    {
        return $this->hasMany(HubinPenempatanPkl::class, 'hubin_dudi_id');
    }
}
