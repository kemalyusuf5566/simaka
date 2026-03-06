<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataSiswa extends Model
{
     protected $table = 'data_siswa';
        protected $fillable = [
            'data_kelas_id',
            'nama_siswa',
            'nis',
            'nisn',
            'tempat_lahir',
            'tanggal_lahir',
            'jenis_kelamin',
            'agama',
            'status_dalam_keluarga',
            'anak_ke',
            'alamat',
            'telepon',
            'sekolah_asal',
            'diterima_di_kelas',
            'tanggal_diterima',
            'nama_ayah',
            'pekerjaan_ayah',
            'nama_ibu',
            'pekerjaan_ibu',
            'alamat_orang_tua',
            'telepon_orang_tua',
            'nama_wali',
            'pekerjaan_wali',
            'alamat_wali',
            'telepon_wali',
            'status_siswa',
        ];
    public function kelas()
    {
        return $this->belongsTo(DataKelas::class, 'data_kelas_id');
    }

    public function nilai()
    {
        return $this->hasMany(LegerNilai::class, 'data_siswa_id');
    }

    // 🔗 relasi ke kehadiran
    public function kehadiran()
    {
        return $this->hasOne(DataKetidakhadiran::class, 'data_siswa_id');
    }

    // 🔗 relasi ke catatan wali kelas
    public function catatanWali()
    {
        return $this->hasOne(CatatanWaliKelas::class, 'data_siswa_id');
    }

    public function nilaiMapel()
    {
        return $this->hasMany(NilaiMapelSiswa::class, 'data_siswa_id');
    }

    public function absensiJam()
    {
        return $this->hasMany(AbsensiJamSiswa::class, 'data_siswa_id');
    }

    public function dataBk()
    {
        return $this->hasMany(DataBk::class, 'data_siswa_id');
    }
}
