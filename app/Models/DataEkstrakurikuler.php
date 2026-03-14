<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataEkstrakurikuler extends Model
{
    protected $table = 'data_ekstrakurikuler';
    protected $primaryKey = 'id';
    protected $fillable = [
        'nama_ekskul',
        'pembina_id',
        'status_aktif',
    ];

    public function pembina()
    {
        return $this->belongsTo(User::class, 'pembina_id');
    }

    public function anggota()
    {
        return $this->hasMany(EkskulAnggota::class, 'data_ekstrakurikuler_id');
    }

    public function getPembinaNamaAttribute(): string
    {
        if ($this->pembina?->nama) {
            return $this->pembina->nama;
        }

        // Fallback untuk data lama jika pembina_id pernah terisi data_guru.id
        $guru = DataGuru::with('pengguna')->find($this->pembina_id);
        return $guru?->pengguna?->nama ?? '-';
    }
}
