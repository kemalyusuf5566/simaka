<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| ADMIN CONTROLLERS
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Admin\DataSiswaController;
use App\Http\Controllers\Admin\DataGuruController;
use App\Http\Controllers\Admin\DataAdminController;
use App\Http\Controllers\Admin\DataSekolahController;
use App\Http\Controllers\Admin\DataTahunPelajaranController;
use App\Http\Controllers\Admin\DataJurusanController;
use App\Http\Controllers\Admin\DataKelasController;
use App\Http\Controllers\Admin\DataMapelController;
use App\Http\Controllers\Admin\DataPembelajaranController;
use App\Http\Controllers\Admin\DataEkstrakurikulerController;
use App\Http\Controllers\Admin\HariLiburController;
use App\Http\Controllers\Admin\AbsensiController as AdminAbsensiController;

// KOKURIKULER
use App\Http\Controllers\Admin\KkDimensiController;
use App\Http\Controllers\Admin\KkKegiatanController;
use App\Http\Controllers\Admin\KkKelompokController;
use App\Http\Controllers\Admin\Kokurikuler\KelompokAnggotaController as AdminKelompokAnggotaController;
use App\Http\Controllers\Admin\Kokurikuler\KelompokKegiatanController as AdminKelompokKegiatanController;

// RAPOR (TANPA subfolder Rapor)
use App\Http\Controllers\Admin\LegerNilaiController;
use App\Http\Controllers\Admin\CetakRaporController;
use App\Http\Controllers\Admin\KelengkapanRaporPdfController;
use App\Http\Controllers\Admin\RaporSemesterPdfController;

/*
|--------------------------------------------------------------------------
| GURU CONTROLLERS
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Guru\PembelajaranController;
use App\Http\Controllers\Guru\NilaiController;
use App\Http\Controllers\Guru\TujuanPembelajaranController;
use App\Http\Controllers\Guru\NilaiAkhirController;
use App\Http\Controllers\Guru\DeskripsiCapaianController;
use App\Http\Controllers\Guru\DashboardController;
use App\Http\Controllers\Guru\AbsensiController as GuruAbsensiController;
use App\Http\Controllers\Guru\Kokurikuler\KelompokController;
use App\Http\Controllers\Guru\Kokurikuler\AnggotaKelompokController;
use App\Http\Controllers\Guru\Kokurikuler\CapaianAkhirController;
use App\Http\Controllers\Guru\Kokurikuler\DeskripsiKokurikulerController;
use App\Http\Controllers\Guru\WaliKelas\DataKelasController as GuruWaliDataKelasController;
use App\Http\Controllers\Guru\WaliKelas\KetidakhadiranController;
use App\Http\Controllers\Guru\WaliKelas\AbsensiController as GuruWaliAbsensiController;
use App\Http\Controllers\Guru\WaliKelas\CatatanWaliKelasController;
use App\Http\Controllers\Guru\Kokurikuler\KegiatanKelompokController;
use App\Http\Controllers\Guru\Kokurikuler\NilaiKokurikulerController;

/*
|--------------------------------------------------------------------------
| ROOT
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| ADMIN AREA
|--------------------------------------------------------------------------
| Wajib: hanya admin yang boleh masuk /admin/*
*/
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        // MASTER DATA

        // ===== SISWA =====
        Route::get('siswa', [DataSiswaController::class, 'index'])->name('siswa.index');
            // ===== IMPORT SISWA (XLSX) =====
            Route::get('siswa/import/format', [DataSiswaController::class, 'downloadFormatImport'])
                ->name('siswa.import.format');

            Route::post('siswa/import', [DataSiswaController::class, 'import'])
                ->name('siswa.import');

            Route::get('siswa/import', [DataSiswaController::class, 'importCreate'])
                ->name('siswa.import.create');

        Route::delete('siswa/destroy-multiple', [DataSiswaController::class, 'destroyMultiple'])
            ->name('siswa.destroyMultiple');
        // CRUD SISWA (taruh setelah import)
        Route::get('siswa/create', [DataSiswaController::class, 'create'])->name('siswa.create');
        Route::post('siswa', [DataSiswaController::class, 'store'])->name('siswa.store');
        Route::get('siswa/{id}', [DataSiswaController::class, 'show'])->name('siswa.show');
        Route::get('siswa/{id}/edit', [DataSiswaController::class, 'edit'])->name('siswa.edit');
        Route::put('siswa/{id}', [DataSiswaController::class, 'update'])->name('siswa.update');
        Route::delete('siswa/{id}', [DataSiswaController::class, 'destroy'])->name('siswa.destroy');

        

        


        // ===== GURU =====
        Route::get('guru', [DataGuruController::class, 'index'])->name('guru.index');
       
        // ===== IMPORT GURU (XLSX) =====
        Route::get('guru/import', [DataGuruController::class, 'importCreate'])->name('guru.import.create');
        Route::get('guru/import/format', [DataGuruController::class, 'downloadFormatImport'])->name('guru.import.format');
        Route::post('guru/import', [DataGuruController::class, 'import'])->name('guru.import');

        // destroy multiple
        Route::delete('guru/destroy-multiple', [DataGuruController::class, 'destroyMultiple'])
            ->name('guru.destroyMultiple');

        // modal detail (AJAX)
        Route::get('guru/{id}/detail', [DataGuruController::class, 'detailModal'])->name('guru.detail.modal');

        // CRUD
        Route::get('guru/create', [DataGuruController::class, 'create'])->name('guru.create');
        Route::post('guru', [DataGuruController::class, 'store'])->name('guru.store');
        Route::get('guru/{id}', [DataGuruController::class, 'show'])->name('guru.show');
        Route::get('guru/{id}/edit', [DataGuruController::class, 'edit'])->name('guru.edit');
        Route::put('guru/{id}', [DataGuruController::class, 'update'])->name('guru.update');
        Route::delete('guru/{id}', [DataGuruController::class, 'destroy'])->name('guru.destroy');

        

       

       
       
       
       
        Route::resource('admin', DataAdminController::class);

        // ADMINISTRASI
        Route::resource('sekolah', DataSekolahController::class)
            ->except(['show', 'destroy']);

        Route::put(
            'sekolah/{id}/logo',
            [DataSekolahController::class, 'updateLogo']
        )->name('sekolah.updateLogo');

        // TAHUN PELAJARAN
        Route::get('tahun-pelajaran', [DataTahunPelajaranController::class, 'index'])
            ->name('tahun.index');
        Route::post('tahun-pelajaran', [DataTahunPelajaranController::class, 'store'])
            ->name('tahun.store');
        Route::put('tahun-pelajaran/{id}', [DataTahunPelajaranController::class, 'update'])
            ->name('tahun.update');
        Route::put('tahun-pelajaran/{id}/aktif', [DataTahunPelajaranController::class, 'setAktif'])
            ->name('tahun.aktif');
        Route::get('pembelajaran/mapel-by-kelas/{kelas}', [DataPembelajaranController::class, 'mapelByKelas'])
            ->name('pembelajaran.mapelByKelas');

        // JURUSAN
        Route::get('jurusan', [DataJurusanController::class, 'index'])->name('jurusan.index');
        Route::post('jurusan', [DataJurusanController::class, 'store'])->name('jurusan.store');
        Route::put('jurusan/{id}', [DataJurusanController::class, 'update'])->name('jurusan.update');
        Route::delete('jurusan/{id}', [DataJurusanController::class, 'destroy'])->name('jurusan.destroy');

        // ADMINISTRASI LANJUTAN
        Route::resource('kelas', DataKelasController::class);

        // MAPEL
        Route::get('mapel/import/format', [DataMapelController::class, 'downloadFormatImport'])
            ->name('mapel.import.format');
        Route::post('mapel/import', [DataMapelController::class, 'import'])
            ->name('mapel.import');

        Route::get('mapel/export', [DataMapelController::class, 'export'])
            ->name('mapel.export');
        Route::resource('mapel', DataMapelController::class);
        

        // PEMBELAJRAN
        Route::resource('pembelajaran', DataPembelajaranController::class);
        Route::get('pembelajaran/{id}/json', [DataPembelajaranController::class, 'json'])
            ->name('pembelajaran.json');

        // HARI LIBUR
        Route::get('hari-libur', [HariLiburController::class, 'index'])->name('hari-libur.index');
        Route::post('hari-libur', [HariLiburController::class, 'store'])->name('hari-libur.store');
        Route::delete('hari-libur/{hariLibur}', [HariLiburController::class, 'destroy'])->name('hari-libur.destroy');

        Route::resource('ekstrakurikuler', DataEkstrakurikulerController::class);
        Route::get('ekstrakurikuler/{id}/json', [DataEkstrakurikulerController::class, 'json'])
            ->name('ekstrakurikuler.json');

        // ABSENSI (REKAP KETIDAKHADIRAN PER SEMESTER)
        Route::get('absensi', [AdminAbsensiController::class, 'index'])->name('absensi.index');
        Route::get('absensi/{kelas}/rekap', [AdminAbsensiController::class, 'rekap'])->name('absensi.rekap');
        Route::get('absensi-jadwal', [AdminAbsensiController::class, 'jadwal'])->name('absensi.jadwal');
        Route::post('absensi-jadwal', [AdminAbsensiController::class, 'jadwalStore'])->name('absensi.jadwal.store');
        Route::delete('absensi-jadwal/{id}', [AdminAbsensiController::class, 'jadwalDestroy'])->name('absensi.jadwal.destroy');
        /*
        |----------------------------------------------------------------------
        | KOKURIKULER
        |----------------------------------------------------------------------
        */
        Route::prefix('kokurikuler')
            ->name('kokurikuler.')
            ->group(function () {
                Route::resource('dimensi', KkDimensiController::class);
                Route::resource('kegiatan', KkKegiatanController::class);
                Route::resource('kelompok', KkKelompokController::class);


                Route::get('kelompok/{kelompok}/anggota', [AdminKelompokAnggotaController::class, 'index'])
                    ->name('kelompok.anggota.index');
                Route::post('kelompok/{kelompok}/anggota', [AdminKelompokAnggotaController::class, 'store'])
                    ->name('kelompok.anggota.store');
                Route::delete('kelompok/{kelompok}/anggota/{anggota}', [AdminKelompokAnggotaController::class, 'destroy'])
                    ->name('kelompok.anggota.destroy');

                // OPTIONAL kalau mau tombol "Tambahkan Semua" beneran jalan
                Route::post('kelompok/{kelompok}/anggota/add-all', [AdminKelompokAnggotaController::class, 'addAll'])
                    ->name('kelompok.anggota.addAll');

                Route::get('kelompok/{kelompok}/kegiatan', [AdminKelompokKegiatanController::class, 'index'])
                    ->name('kelompok.kegiatan.index');
                Route::post('kelompok/{kelompok}/kegiatan', [AdminKelompokKegiatanController::class, 'store'])
                    ->name('kelompok.kegiatan.store');
                Route::delete('kelompok/{kelompok}/kegiatan/{pivot}', [AdminKelompokKegiatanController::class, 'destroy'])
                    ->name('kelompok.kegiatan.destroy');
            
            });

        /*
        |----------------------------------------------------------------------
        | RAPOR
        |----------------------------------------------------------------------
        */
        Route::prefix('rapor')
            ->name('rapor.')
            ->group(function () {

                // LEGER NILAI
                Route::get('leger', [LegerNilaiController::class, 'index'])
                    ->name('leger');
                Route::get('leger/{kelas}', [LegerNilaiController::class, 'detail'])
                    ->name('leger.detail');
                Route::get('leger', [LegerNilaiController::class, 'index'])->name('leger');
                Route::get('leger/{kelas}', [LegerNilaiController::class, 'detail'])->name('leger.detail');
                Route::get('leger/{kelas}/pdf', [LegerNilaiController::class, 'exportPdf'])->name('leger.pdf');
                Route::get('leger/{kelas}/excel', [LegerNilaiController::class, 'exportExcel'])->name('leger.excel');

                // CETAK RAPOR
                Route::get('cetak', [CetakRaporController::class, 'index'])
                    ->name('cetak');
                Route::get('cetak/{kelas}', [CetakRaporController::class, 'detail'])
                    ->name('cetak.detail');

                // PDF
                Route::get('pdf/kelengkapan/{siswa}', [KelengkapanRaporPdfController::class, 'show'])
                    ->name('pdf.kelengkapan');

                Route::get('pdf/semester/{siswa}', [RaporSemesterPdfController::class, 'show'])
                    ->name('pdf.semester');
            });
    });

/*
|--------------------------------------------------------------------------
| GURU AREA
|--------------------------------------------------------------------------
| Minimal: hanya guru_mapel yang boleh masuk /guru/*
| (nanti bisa diperluas untuk multi-role wali_kelas/koordinator_p5/pembina_ekskul)
*/
Route::middleware(['auth', 'role:guru_mapel'])
    ->prefix('guru')
    ->name('guru.')
    ->group(function () {

        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('pembelajaran', [PembelajaranController::class, 'index'])
            ->name('pembelajaran.index');

        Route::get('nilai/{pembelajaran}', [NilaiController::class, 'index'])
            ->name('nilai.index');

        Route::post('nilai', [NilaiController::class, 'store'])
            ->name('nilai.store');

        // ABSENSI PER-JAM (GURU MAPEL)
        Route::get('absensi', [GuruAbsensiController::class, 'index'])->name('absensi.index');
        Route::get('absensi/{jadwal}/input', [GuruAbsensiController::class, 'input'])->name('absensi.input');
        Route::post('absensi/{jadwal}/input', [GuruAbsensiController::class, 'store'])->name('absensi.store');

        Route::get('tujuan-pembelajaran/{pembelajaran}', [TujuanPembelajaranController::class, 'index'])
            ->name('tp.index');

        Route::post('tujuan-pembelajaran/{pembelajaran}', [TujuanPembelajaranController::class, 'store'])
            ->name('tp.store');

        Route::delete('tujuan-pembelajaran/hapus/{id}', [TujuanPembelajaranController::class, 'destroy'])
            ->name('tp.destroy');

        Route::get('nilai-akhir/{pembelajaran}', [NilaiAkhirController::class, 'index'])
            ->name('nilai_akhir.index');

        Route::post('nilai-akhir/{pembelajaran}', [NilaiAkhirController::class, 'update'])
            ->name('nilai_akhir.update');

        Route::post('nilai-akhir/{pembelajaran}/apply-average', [NilaiAkhirController::class, 'applyAverage'])
            ->name('nilai_akhir.applyAverage');

        Route::get('deskripsi-capaian/{pembelajaran}', [DeskripsiCapaianController::class, 'index'])
            ->name('deskripsi.index');

        Route::post('deskripsi-capaian/{pembelajaran}', [DeskripsiCapaianController::class, 'update'])
            ->name('deskripsi.update');

        /*
        |----------------------------------------------------------
        | KOKURIKULER (SATU GROUP SAJA)
        |----------------------------------------------------------
        */
        Route::prefix('kokurikuler')->name('kokurikuler.')->group(function () {

            // (kalau punya route index kelompok, taruh di sini)
            Route::get('/', [KelompokController::class, 'index'])->name('index');

            // ANGGOTA
            Route::get('{kelompok}/anggota', [AnggotaKelompokController::class, 'index'])->name('anggota.index');
            Route::post('{kelompok}/anggota', [AnggotaKelompokController::class, 'store'])->name('anggota.store');
            Route::delete('{kelompok}/anggota/{anggota}', [AnggotaKelompokController::class, 'destroy'])->name('anggota.destroy');
            
            // KEGIATAN
            Route::get('{kelompok}/kegiatan', [KegiatanKelompokController::class, 'index'])->name('kegiatan.index');
            Route::post('{kelompok}/kegiatan', [KegiatanKelompokController::class, 'store'])->name('kegiatan.store');
            Route::delete('{kelompok}/kegiatan/{pivot}', [KegiatanKelompokController::class, 'destroy'])->name('kegiatan.destroy');
          
            // CAPAIAN AKHIR (PINDAH MASUK SINI)
            Route::get('{kelompok}/kegiatan/{pivot}/capaian-akhir', [CapaianAkhirController::class, 'index'])
                ->name('capaian_akhir.index');

            Route::post('{kelompok}/kegiatan/{pivot}/capaian-akhir', [CapaianAkhirController::class, 'store'])
                ->name('capaian_akhir.store');

            Route::put('{kelompok}/kegiatan/{pivot}/capaian-akhir/{id}', [CapaianAkhirController::class, 'update'])
                ->name('capaian_akhir.update');

            Route::delete('{kelompok}/kegiatan/{pivot}/capaian-akhir/{id}', [CapaianAkhirController::class, 'destroy'])
                ->name('capaian_akhir.destroy');

            // NILAI
            Route::get('kelompok/{kelompok}/kegiatan/{kegiatan}/nilai', [NilaiKokurikulerController::class, 'index'])
                ->name('nilai.index');

            Route::post('kelompok/{kelompok}/kegiatan/{kegiatan}/nilai', [NilaiKokurikulerController::class, 'update'])
                ->name('nilai.update');

            // DESKRIPSI KOKURIKULER (JANGAN GROUP KEDUA, TARUH DI SINI)
            Route::get('kelompok/{kelompok}/kegiatan/{kegiatan}/deskripsi', [DeskripsiKokurikulerController::class, 'index'])
                ->name('deskripsi.index');

            Route::post('kelompok/{kelompok}/kegiatan/{kegiatan}/deskripsi', [DeskripsiKokurikulerController::class, 'update'])
                ->name('deskripsi.update');
        });

        /*
        |----------------------------------------------------------
        | EKSKUL
        |----------------------------------------------------------
        */
        Route::prefix('ekskul')->name('ekskul.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Guru\EkskulController::class, 'index'])
                ->name('index');

            Route::get('{ekskul}/anggota', [\App\Http\Controllers\Guru\EkskulAnggotaController::class, 'index'])
                ->name('anggota.index');

            Route::post('{ekskul}/anggota', [\App\Http\Controllers\Guru\EkskulAnggotaController::class, 'store'])
                ->name('anggota.store');

            Route::post('{ekskul}/anggota/update', [\App\Http\Controllers\Guru\EkskulAnggotaController::class, 'update'])
                ->name('anggota.update');

            Route::delete('{ekskul}/anggota/{anggota}', [\App\Http\Controllers\Guru\EkskulAnggotaController::class, 'destroy'])
                ->name('anggota.destroy');
        });

        /*
        |----------------------------------------------------------
        | WALI KELAS
        |----------------------------------------------------------
        */
        Route::prefix('wali-kelas')->name('wali-kelas.')->group(function () {

        // DATA KELAS
        Route::get('data-kelas', [GuruWaliDataKelasController::class, 'index'])->name('data-kelas.index');
        Route::get('data-kelas/{kelas}', [GuruWaliDataKelasController::class, 'detail'])->name('data-kelas.detail');

        // EDIT SISWA (dalam konteks kelas)
        Route::get('data-kelas/siswa/{id}/edit', [GuruWaliDataKelasController::class, 'editSiswa'])->name('data-kelas.siswa.edit');
        Route::put('data-kelas/siswa/{id}', [GuruWaliDataKelasController::class, 'updateSiswa'])->name('data-kelas.siswa.update');

            // KETIDAKHADIRAN
            Route::get('absensi', [GuruWaliAbsensiController::class, 'index'])->name('absensi.index');
            Route::get('absensi/{kelas}', [GuruWaliAbsensiController::class, 'kelola'])->name('absensi.kelola');
            Route::post('absensi/{kelas}', [GuruWaliAbsensiController::class, 'update'])->name('absensi.update');

            // kompatibilitas route lama
            Route::get('ketidakhadiran', [KetidakhadiranController::class, 'index'])->name('ketidakhadiran.index');
            Route::get('ketidakhadiran/{kelas}', [KetidakhadiranController::class, 'kelola'])->name('ketidakhadiran.kelola');
            Route::post('ketidakhadiran/{kelas}', [KetidakhadiranController::class, 'update'])->name('ketidakhadiran.update');

            // CATATAN
            Route::get('catatan', [CatatanWaliKelasController::class, 'index'])->name('catatan.index');
            Route::get('catatan/{kelas}', [CatatanWaliKelasController::class, 'kelola'])->name('catatan.kelola');
            Route::post('catatan/{kelas}', [CatatanWaliKelasController::class, 'update'])->name('catatan.update');


            Route::prefix('rapor')->name('rapor.')->group(function () {

                // LEGER NILAI
                Route::get('leger', [\App\Http\Controllers\Guru\WaliKelas\Rapor\LegerNilaiController::class, 'index'])
                    ->name('leger.index');
                Route::get('leger/{kelas}', [\App\Http\Controllers\Guru\WaliKelas\Rapor\LegerNilaiController::class, 'detail'])
                    ->name('leger.detail');


                // CETAK RAPOR
                Route::get('cetak', [\App\Http\Controllers\Guru\WaliKelas\Rapor\CetakRaporController::class, 'index'])
                    ->name('cetak.index');

                Route::get('cetak/{kelas}', [\App\Http\Controllers\Guru\WaliKelas\Rapor\CetakRaporController::class, 'detail'])
                    ->name('cetak.detail');

                Route::get('pdf/kelengkapan/{siswa}', [\App\Http\Controllers\Guru\WaliKelas\Rapor\RaporKelengkapanPdfController::class, 'show'])
                    ->name('pdf.kelengkapan');

                Route::get('pdf/semester/{siswa}', [\App\Http\Controllers\Guru\WaliKelas\Rapor\RaporSemesterPdfController::class, 'show'])
                    ->name('pdf.semester');
            });
        });
    });


/*
|--------------------------------------------------------------------------
| PROFILE
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])
        ->name('profile.update');
    Route::delete('profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

require __DIR__ . '/auth.php';
