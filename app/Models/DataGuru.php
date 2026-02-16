<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataGuru extends Model
{
    protected $table = 'data_guru';

    protected $fillable = [
        'pengguna_id',
        'nip',
        'nuptk',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'telepon',
    ];

    /**
     * FIX: foreign key kamu "pengguna_id"
     * Default Laravel akan cari user_id -> itu bikin nama jadi "-" dan error kolom.
     */
    public function pengguna()
    {
        return $this->belongsTo(\App\Models\User::class, 'pengguna_id');
    }
}
