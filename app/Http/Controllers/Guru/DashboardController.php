<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\DataEkstrakurikuler;
use App\Models\DataGuru;
use App\Models\DataKelas;
use App\Models\DataPembelajaran;
use App\Models\DataSiswa;
use App\Models\KkKelompok;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // =========================
        // DATA UMUM (GURU MAPEL)
        // =========================
        $kelasIds = DataPembelajaran::where('guru_id', $user->id)->pluck('data_kelas_id');

        $jumlahPembelajaran = DataPembelajaran::where('guru_id', $user->id)->count();

        $jumlahSiswa = $kelasIds->isEmpty()
            ? 0
            : DataSiswa::whereIn('data_kelas_id', $kelasIds)->count();

        // =========================
        // ROLE DINAMIS (berdasarkan data)
        // =========================

        // WALI KELAS: cek dari tabel data_kelas
        $isWali = DataKelas::where('wali_kelas_id', $user->id)->exists();

        // KOORDINATOR KOKURIKULER: cek dari tabel kk_kelompok
        $isKoordinator = KkKelompok::where('koordinator_id', $user->id)->exists();

        // PEMBINA EKSKUL:
        // pembina_id bisa menyimpan:
        // - pengguna.id  (langsung)
        // - ATAU data_guru.id (guru yang punya pengguna_id = user.id)
        $guruId = DataGuru::where('pengguna_id', $user->id)->value('id');

        $isPembinaByUserId = DataEkstrakurikuler::where('pembina_id', $user->id)->exists();
        $isPembinaByGuruId = $guruId
            ? DataEkstrakurikuler::where('pembina_id', $guruId)->exists()
            : false;

        $isPembina = $isPembinaByUserId || $isPembinaByGuruId;

        // =========================
        // JUMLAH DATA PER ROLE
        // =========================
        $jumlahKokurikuler = $isKoordinator
            ? KkKelompok::where('koordinator_id', $user->id)->count()
            : 0;

        $jumlahEkskul = $isPembina
            ? DataEkstrakurikuler::whereIn('pembina_id', array_filter([$user->id, $guruId]))->count()
            : 0;

        // =========================
        // CARD DASHBOARD (tampilkan sesuai role)
        // =========================
        $cards = [];

        // Data Siswa (selalu tampil untuk guru mapel)
        $cards[] = [
            'title' => 'Data Siswa',
            'count' => $jumlahSiswa,
            'route' => route('guru.pembelajaran.index'),
            'color' => 'bg-primary',
            'icon'  => 'fas fa-users',
        ];

        // Data Pembelajaran (selalu tampil)
        $cards[] = [
            'title' => 'Data Pembelajaran',
            'count' => $jumlahPembelajaran,
            'route' => route('guru.pembelajaran.index'),
            'color' => 'bg-success',
            'icon'  => 'fas fa-book',
        ];

        // Data Ekstrakurikuler (hanya kalau pembina)
        if ($isPembina) {
            $cards[] = [
                'title' => 'Data Ekstrakurikuler',
                'count' => $jumlahEkskul,
                'route' => route('guru.ekskul.index'),
                'color' => 'bg-warning',
                'icon'  => 'fas fa-futbol',
            ];
        }

        // Data Kelompok Kokurikuler (hanya kalau koordinator)
        if ($isKoordinator) {
            $cards[] = [
                'title' => 'Data Kelompok Kokurikuler',
                'count' => $jumlahKokurikuler,
                'route' => route('guru.kokurikuler.index'),
                'color' => 'bg-danger',
                'icon'  => 'fas fa-layer-group',
            ];
        }

        // Info wali kelas (opsional, kalau kamu mau card khusus wali kelas)
        // if ($isWali) { ... }

        return view('guru.dashboard', compact(
            'cards',
            'jumlahSiswa',
            'jumlahPembelajaran',
            'jumlahEkskul',
            'jumlahKokurikuler',
            'isWali',
            'isKoordinator',
            'isPembina'
        ));
    }
}
