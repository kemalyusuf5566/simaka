<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ComprehensiveDummySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::transaction(function () use ($now) {
            $roleNames = ['admin', 'bk', 'wali_kelas', 'guru_mapel', 'koordinator_p5', 'pembina_ekskul'];
            foreach ($roleNames as $role) {
                DB::table('peran')->updateOrInsert(
                    ['nama_peran' => $role],
                    ['created_at' => $now, 'updated_at' => $now]
                );
            }
            $roleIds = DB::table('peran')->pluck('id', 'nama_peran');
            $this->resetAcademicSeedData((int) ($roleIds['guru_mapel'] ?? 0));

            $users = [
                ['email' => 'admin@simaka.test', 'nama' => 'Administrator', 'role' => 'admin'],
                ['email' => 'bk@simaka.test', 'nama' => 'Guru BK', 'role' => 'bk'],
                ['email' => 'wali@simaka.test', 'nama' => 'Wali Kelas', 'role' => 'wali_kelas'],
                ['email' => 'guru.mt@simaka.test', 'nama' => 'Guru Matematika', 'role' => 'guru_mapel'],
                ['email' => 'guru.bi@simaka.test', 'nama' => 'Guru Bahasa Indonesia', 'role' => 'guru_mapel'],
                ['email' => 'p5@simaka.test', 'nama' => 'Koordinator P5', 'role' => 'koordinator_p5'],
                ['email' => 'ekskul@simaka.test', 'nama' => 'Pembina Ekskul', 'role' => 'pembina_ekskul'],
            ];
            foreach ($users as $u) {
                DB::table('pengguna')->updateOrInsert(
                    ['email' => $u['email']],
                    [
                        'peran_id' => $roleIds[$u['role']] ?? null,
                        'nama' => $u['nama'],
                        'password' => Hash::make('password'),
                        'status_aktif' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
            DB::table('users')->updateOrInsert(
                ['email' => 'webadmin@simaka.test'],
                [
                    'name' => 'Web Admin',
                    'password' => Hash::make('password'),
                    'email_verified_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
            $pengguna = DB::table('pengguna')->pluck('id', 'email');

            DB::table('pengguna_peran')->updateOrInsert(
                ['pengguna_id' => $pengguna['guru.mt@simaka.test'], 'peran_id' => $roleIds['wali_kelas']],
                ['created_at' => $now, 'updated_at' => $now]
            );

            DB::table('data_sekolah')->updateOrInsert(
                ['nama_sekolah' => 'SMK Simaka Nusantara'],
                [
                    'npsn' => '12345678',
                    'alamat' => 'Jl. Pendidikan No. 1',
                    'kepala_sekolah' => 'Drs. Budi Santoso',
                    'nip_kepala_sekolah' => '197812122005011001',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
            $sekolahId = DB::table('data_sekolah')->where('nama_sekolah', 'SMK Simaka Nusantara')->value('id');

            $tahunRows = [
                ['tahun_pelajaran' => '2025/2026', 'semester' => 'Ganjil', 'status_aktif' => true],
                ['tahun_pelajaran' => '2025/2026', 'semester' => 'Genap', 'status_aktif' => false],
            ];
            foreach ($tahunRows as $t) {
                DB::table('data_tahun_pelajaran')->updateOrInsert(
                    ['tahun_pelajaran' => $t['tahun_pelajaran'], 'semester' => $t['semester']],
                    $t + [
                        'tempat_pembagian_rapor' => 'Aula',
                        'tanggal_pembagian_rapor' => $t['semester'] === 'Ganjil' ? '2025-12-20' : '2026-06-20',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
            $tahunId = DB::table('data_tahun_pelajaran')->where('tahun_pelajaran', '2025/2026')->where('semester', 'Ganjil')->value('id');

            foreach ([['RPL', 'Rekayasa Perangkat Lunak'], ['TKJ', 'Teknik Komputer dan Jaringan']] as $j) {
                DB::table('data_jurusan')->updateOrInsert(
                    ['kode_jurusan' => $j[0]],
                    ['nama_jurusan' => $j[1], 'status' => 'AKTIF', 'status_aktif' => true, 'created_at' => $now, 'updated_at' => $now]
                );
            }
            $jurusan = DB::table('data_jurusan')->pluck('id', 'kode_jurusan');

            $guruProfiles = [
                ['wali@simaka.test', 'P'],
                ['guru.mt@simaka.test', 'L'],
                ['guru.bi@simaka.test', 'P'],
                ['bk@simaka.test', 'L'],
                ['p5@simaka.test', 'P'],
                ['ekskul@simaka.test', 'L'],
            ];
            foreach ($guruProfiles as $idx => $g) {
                DB::table('data_guru')->updateOrInsert(
                    ['pengguna_id' => $pengguna[$g[0]]],
                    [
                        'nip' => '1988' . str_pad((string) ($idx + 1), 14, '0', STR_PAD_LEFT),
                        'nuptk' => 'NUPTK' . str_pad((string) ($idx + 1), 8, '0', STR_PAD_LEFT),
                        'tempat_lahir' => 'Bandung',
                        'tanggal_lahir' => '1988-01-0' . (($idx % 8) + 1),
                        'jenis_kelamin' => $g[1],
                        'alamat' => 'Alamat guru ' . ($idx + 1),
                        'telepon' => '0812300000' . ($idx + 1),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
            $guruRealIds = $this->seedRealTeachers((int) ($roleIds['guru_mapel'] ?? 0), $now);

            $kelasRows = [
                ['nama_kelas' => 'X RPL 1', 'tingkat' => 'X', 'jurusan_id' => $jurusan['RPL'], 'wali_kelas_id' => $pengguna['wali@simaka.test']],
                ['nama_kelas' => 'XI TKJ 1', 'tingkat' => 'XI', 'jurusan_id' => $jurusan['TKJ'], 'wali_kelas_id' => $pengguna['guru.mt@simaka.test']],
            ];
            foreach ($kelasRows as $k) {
                DB::table('data_kelas')->updateOrInsert(
                    ['nama_kelas' => $k['nama_kelas'], 'data_tahun_pelajaran_id' => $tahunId],
                    [
                        'data_sekolah_id' => $sekolahId,
                        'tingkat' => $k['tingkat'],
                        'jurusan_id' => $k['jurusan_id'],
                        'wali_kelas_id' => $k['wali_kelas_id'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
            $kelas = DB::table('data_kelas')->pluck('id', 'nama_kelas');

            $mapelRows = [
                ['Matematika', 'MTK', 'Wajib', 'SEMUA', null, 1],
                ['Bahasa Indonesia', 'BIND', 'Wajib', 'SEMUA', null, 2],
                ['Pemrograman Dasar', 'PROGDAS', 'Produktif', 'X', $jurusan['RPL'], 3],
                ['Jaringan Dasar', 'JARDAS', 'Produktif', 'XI', $jurusan['TKJ'], 4],
            ];
            foreach ($mapelRows as $m) {
                DB::table('data_mapel')->updateOrInsert(
                    ['nama_mapel' => $m[0]],
                    [
                        'singkatan' => $m[1],
                        'kelompok_mapel' => $m[2],
                        'tingkat' => $m[3],
                        'jurusan_id' => $m[4],
                        'urutan_cetak' => $m[5],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
            $mapel = DB::table('data_mapel')->pluck('id', 'nama_mapel');

            for ($i = 1; $i <= 6; $i++) {
                DB::table('data_siswa')->updateOrInsert(
                    ['nis' => '25010' . str_pad((string) $i, 2, '0', STR_PAD_LEFT)],
                    [
                        'data_kelas_id' => $kelas['X RPL 1'],
                        'nisn' => '005010' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                        'nama_siswa' => 'Siswa RPL ' . $i,
                        'jenis_kelamin' => $i % 2 === 0 ? 'P' : 'L',
                        'tempat_lahir' => 'Jakarta',
                        'tanggal_lahir' => '2010-01-' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                        'agama' => 'Islam',
                        'alamat' => 'Jl. Siswa No. ' . $i,
                        'status_siswa' => 'AKTIF',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
            for ($i = 1; $i <= 4; $i++) {
                DB::table('data_siswa')->updateOrInsert(
                    ['nis' => '25020' . str_pad((string) $i, 2, '0', STR_PAD_LEFT)],
                    [
                        'data_kelas_id' => $kelas['XI TKJ 1'],
                        'nisn' => '005020' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                        'nama_siswa' => 'Siswa TKJ ' . $i,
                        'jenis_kelamin' => $i % 2 === 0 ? 'L' : 'P',
                        'tempat_lahir' => 'Bandung',
                        'tanggal_lahir' => '2010-02-' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                        'agama' => 'Islam',
                        'alamat' => 'Jl. TKJ No. ' . $i,
                        'status_siswa' => 'AKTIF',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
            $siswa = DB::table('data_siswa')->select('id', 'data_kelas_id', 'nama_siswa')->orderBy('id')->get();

            $pembelajaranRows = [
                ['X RPL 1', 'Matematika', 'guru.mt@simaka.test'],
                ['X RPL 1', 'Bahasa Indonesia', 'guru.bi@simaka.test'],
                ['X RPL 1', 'Pemrograman Dasar', 'guru.mt@simaka.test'],
                ['XI TKJ 1', 'Matematika', 'guru.mt@simaka.test'],
                ['XI TKJ 1', 'Bahasa Indonesia', 'guru.bi@simaka.test'],
                ['XI TKJ 1', 'Jaringan Dasar', 'guru.bi@simaka.test'],
            ];
            foreach ($pembelajaranRows as $p) {
                DB::table('data_pembelajaran')->updateOrInsert(
                    ['data_kelas_id' => $kelas[$p[0]], 'data_mapel_id' => $mapel[$p[1]], 'guru_id' => $pengguna[$p[2]]],
                    ['created_at' => $now, 'updated_at' => $now]
                );
            }

            foreach (DB::table('data_pembelajaran')->select('id')->get() as $pem) {
                DB::table('tujuan_pembelajaran')->updateOrInsert(
                    ['data_pembelajaran_id' => $pem->id, 'urutan' => 1],
                    ['tujuan' => 'Memahami konsep dasar materi.', 'created_at' => $now, 'updated_at' => $now]
                );
                DB::table('tujuan_pembelajaran')->updateOrInsert(
                    ['data_pembelajaran_id' => $pem->id, 'urutan' => 2],
                    ['tujuan' => 'Menerapkan materi pada studi kasus.', 'created_at' => $now, 'updated_at' => $now]
                );
            }

            $pemByKelas = DB::table('data_pembelajaran')->join('data_mapel', 'data_mapel.id', '=', 'data_pembelajaran.data_mapel_id')
                ->select('data_pembelajaran.*', 'data_mapel.nama_mapel')
                ->get()
                ->groupBy('data_kelas_id');
            $tpByPem = DB::table('tujuan_pembelajaran')->select('id', 'data_pembelajaran_id', 'urutan')->get()->groupBy('data_pembelajaran_id');

            foreach ($siswa as $idx => $ss) {
                foreach (($pemByKelas[$ss->data_kelas_id] ?? collect()) as $pem) {
                    $nilai = 75 + (($idx + $pem->id) % 20);
                    $predikat = $nilai >= 90 ? 'A' : ($nilai >= 80 ? 'B' : 'C');
                    DB::table('nilai_mapel_siswa')->updateOrInsert(
                        ['data_siswa_id' => $ss->id, 'data_mapel_id' => $pem->data_mapel_id, 'semester' => 'Ganjil', 'data_tahun_pelajaran_id' => $tahunId],
                        [
                            'data_kelas_id' => $ss->data_kelas_id,
                            'nilai_angka' => $nilai,
                            'predikat' => $predikat,
                            'deskripsi' => 'Pencapaian baik pada ' . $pem->nama_mapel,
                            'deskripsi_tinggi' => 'Kuat dalam pemahaman konsep.',
                            'deskripsi_rendah' => 'Perlu konsistensi latihan.',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]
                    );
                    $nilaiId = DB::table('nilai_mapel_siswa')->where('data_siswa_id', $ss->id)->where('data_mapel_id', $pem->data_mapel_id)->where('semester', 'Ganjil')->where('data_tahun_pelajaran_id', $tahunId)->value('id');
                    foreach (($tpByPem[$pem->id] ?? collect()) as $tp) {
                        DB::table('nilai_mapel_siswa_tujuan')->updateOrInsert(
                            ['nilai_mapel_siswa_id' => $nilaiId, 'tujuan_pembelajaran_id' => $tp->id],
                            ['kategori' => $tp->urutan === 1 ? 'optimal' : 'perlu', 'created_at' => $now, 'updated_at' => $now]
                        );
                    }
                    DB::table('leger_nilai')->updateOrInsert(
                        ['data_pembelajaran_id' => $pem->id, 'data_siswa_id' => $ss->id],
                        ['nilai_akhir' => $nilai, 'predikat' => $predikat, 'deskripsi' => 'Nilai akhir dari dummy.', 'created_at' => $now, 'updated_at' => $now]
                    );
                }
                DB::table('data_ketidakhadiran')->updateOrInsert(
                    ['data_siswa_id' => $ss->id, 'data_tahun_pelajaran_id' => $tahunId, 'semester' => 'Ganjil'],
                    ['sakit' => $idx % 3, 'izin' => $idx % 2, 'tanpa_keterangan' => $idx % 2, 'created_at' => $now, 'updated_at' => $now]
                );
                DB::table('catatan_wali_kelas')->updateOrInsert(
                    ['data_siswa_id' => $ss->id, 'data_tahun_pelajaran_id' => $tahunId, 'semester' => 'Ganjil'],
                    ['catatan' => 'Perkembangan baik.', 'status_kenaikan_kelas' => 'Naik', 'created_at' => $now, 'updated_at' => $now]
                );
            }

            DB::table('hari_libur')->updateOrInsert(['tanggal' => '2025-08-17'], ['keterangan' => 'Hari Kemerdekaan RI', 'created_at' => $now, 'updated_at' => $now]);
            DB::table('hari_libur')->updateOrInsert(['tanggal' => '2025-12-25'], ['keterangan' => 'Hari Raya Natal', 'created_at' => $now, 'updated_at' => $now]);

            $jadwalRows = [
                ['X RPL 1', 'Matematika', 'guru.mt@simaka.test', 'Senin', 1],
                ['X RPL 1', 'Bahasa Indonesia', 'guru.bi@simaka.test', 'Senin', 2],
                ['XI TKJ 1', 'Jaringan Dasar', 'guru.bi@simaka.test', 'Selasa', 1],
            ];
            foreach ($jadwalRows as $j) {
                DB::table('jadwal_pelajaran')->updateOrInsert(
                    ['data_tahun_pelajaran_id' => $tahunId, 'data_kelas_id' => $kelas[$j[0]], 'hari' => $j[3], 'jam_ke' => $j[4]],
                    ['data_mapel_id' => $mapel[$j[1]], 'guru_id' => $pengguna[$j[2]], 'created_at' => $now, 'updated_at' => $now]
                );
            }
            foreach ($siswa->take(6) as $idx => $ss) {
                DB::table('absensi_jam_siswa')->updateOrInsert(
                    ['tanggal' => '2025-08-04', 'data_siswa_id' => $ss->id, 'jam_ke' => 1],
                    [
                        'data_tahun_pelajaran_id' => $tahunId,
                        'semester' => 'Ganjil',
                        'data_kelas_id' => $ss->data_kelas_id,
                        'data_mapel_id' => $mapel['Matematika'],
                        'guru_id' => $pengguna['guru.mt@simaka.test'],
                        'hari' => 'Senin',
                        'status' => $idx % 4 === 0 ? 'A' : 'H',
                        'catatan' => $idx % 4 === 0 ? 'Tidak hadir tanpa keterangan' : null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            DB::table('data_ekstrakurikuler')->updateOrInsert(['nama_ekskul' => 'Pramuka'], ['pembina_id' => $pengguna['ekskul@simaka.test'], 'status_aktif' => true, 'created_at' => $now, 'updated_at' => $now]);
            DB::table('data_ekstrakurikuler')->updateOrInsert(['nama_ekskul' => 'Futsal'], ['pembina_id' => $pengguna['ekskul@simaka.test'], 'status_aktif' => true, 'created_at' => $now, 'updated_at' => $now]);
            $ekskul = DB::table('data_ekstrakurikuler')->pluck('id', 'nama_ekskul');
            foreach ($siswa->take(6) as $idx => $ss) {
                DB::table('ekskul_anggota')->updateOrInsert(
                    ['data_ekstrakurikuler_id' => $idx % 2 === 0 ? $ekskul['Pramuka'] : $ekskul['Futsal'], 'data_siswa_id' => $ss->id],
                    ['predikat' => 'Baik', 'deskripsi' => 'Aktif mengikuti kegiatan.', 'created_at' => $now, 'updated_at' => $now]
                );
            }

            foreach (['Beriman Bertakwa', 'Gotong Royong'] as $d) {
                DB::table('kk_dimensi')->updateOrInsert(['nama_dimensi' => $d], ['created_at' => $now, 'updated_at' => $now]);
            }
            DB::table('kk_kegiatan')->updateOrInsert(['nama_kegiatan' => 'Proyek Literasi', 'tema' => 'Kebhinekaan'], ['deskripsi' => 'Proyek literasi.', 'created_at' => $now, 'updated_at' => $now]);
            DB::table('kk_kegiatan')->updateOrInsert(['nama_kegiatan' => 'Proyek Kewirausahaan', 'tema' => 'Kewirausahaan'], ['deskripsi' => 'Proyek kewirausahaan.', 'created_at' => $now, 'updated_at' => $now]);
            $dimensi = DB::table('kk_dimensi')->pluck('id', 'nama_dimensi');
            $kegiatan = DB::table('kk_kegiatan')->pluck('id', 'nama_kegiatan');

            foreach (['X RPL 1', 'XI TKJ 1'] as $namaKelas) {
                DB::table('kk_kelompok')->updateOrInsert(
                    ['nama_kelompok' => 'Kelompok P5 ' . $namaKelas, 'data_kelas_id' => $kelas[$namaKelas]],
                    ['koordinator_id' => $pengguna['p5@simaka.test'], 'created_at' => $now, 'updated_at' => $now]
                );
            }
            $kkKelompok = DB::table('kk_kelompok')->select('id', 'data_kelas_id')->get();
            foreach ($kkKelompok as $kgp) {
                $anggota = $siswa->where('data_kelas_id', $kgp->data_kelas_id)->take(3);
                foreach ($anggota as $ss) {
                    DB::table('kk_kelompok_anggota')->updateOrInsert(['kk_kelompok_id' => $kgp->id, 'data_siswa_id' => $ss->id], ['created_at' => $now, 'updated_at' => $now]);
                }
                foreach (['Proyek Literasi', 'Proyek Kewirausahaan'] as $kn) {
                    DB::table('kk_kelompok_kegiatan')->updateOrInsert(['kk_kelompok_id' => $kgp->id, 'kk_kegiatan_id' => $kegiatan[$kn]], ['created_at' => $now, 'updated_at' => $now]);
                }
            }
            $kkKeg = DB::table('kk_kelompok_kegiatan')->get();
            foreach ($kkKeg as $row) {
                foreach ($dimensi as $did) {
                    DB::table('kk_capaian_akhir')->updateOrInsert(
                        ['kk_kelompok_kegiatan_id' => $row->id, 'kk_dimensi_id' => $did],
                        ['capaian' => 'Perkembangan karakter sesuai dimensi.', 'created_at' => $now, 'updated_at' => $now]
                    );
                }
            }
            $capaianByKeg = DB::table('kk_capaian_akhir')->select('id', 'kk_kelompok_kegiatan_id')->get()->groupBy('kk_kelompok_kegiatan_id');
            foreach ($kkKelompok as $kgp) {
                $anggotaIds = DB::table('kk_kelompok_anggota')->where('kk_kelompok_id', $kgp->id)->pluck('data_siswa_id');
                foreach (DB::table('kk_kelompok_kegiatan')->where('kk_kelompok_id', $kgp->id)->get() as $kgk) {
                    $capaianId = $capaianByKeg[$kgk->id][0]->id ?? null;
                    foreach ($anggotaIds as $sid) {
                        DB::table('kk_nilai')->updateOrInsert(
                            ['kk_kelompok_id' => $kgp->id, 'kk_kegiatan_id' => $kgk->kk_kegiatan_id, 'data_siswa_id' => $sid, 'kk_capaian_akhir_id' => $capaianId],
                            ['capaian' => 'Mampu kolaborasi dalam proyek.', 'predikat' => 'Baik', 'deskripsi' => 'Aktif saat proyek berlangsung.', 'created_at' => $now, 'updated_at' => $now]
                        );
                    }
                }
            }

            DB::table('bk_jenis_pelanggaran')->updateOrInsert(['kode' => 'BK-001'], ['nama_pelanggaran' => 'Terlambat', 'poin_default' => 5, 'status_aktif' => true, 'created_at' => $now, 'updated_at' => $now]);
            $pelId = DB::table('bk_jenis_pelanggaran')->where('kode', 'BK-001')->value('id');
            $satuSiswa = $siswa->first();
            if ($satuSiswa) {
                $this->seedBk($satuSiswa, $tahunId, $pengguna['bk@simaka.test'], $pelId, $now);
            }

            DB::table('hubin_rekomendasi_pkl_settings')->updateOrInsert(
                ['id' => 1],
                [
                    'weights' => json_encode(['nilai_rata_rata' => 40, 'kehadiran' => 35, 'sikap' => 25]),
                    'grade_thresholds' => json_encode(['A' => 90, 'B' => 80, 'C' => 70]),
                    'attendance_default_score_without_data' => 70,
                    'updated_by' => $pengguna['bk@simaka.test'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
            DB::table('hubin_dudi')->updateOrInsert(
                ['nama_instansi' => 'PT Teknologi Nusantara'],
                ['bidang_usaha' => 'Software House', 'alamat' => 'Jakarta', 'kontak_person' => 'Andi Pratama', 'telepon' => '0211234567', 'email' => 'hr@teknologi.test', 'status_aktif' => true, 'catatan' => 'Mitra aktif PKL', 'created_by' => $pengguna['bk@simaka.test'], 'updated_by' => $pengguna['bk@simaka.test'], 'created_at' => $now, 'updated_at' => $now]
            );
            $dudiId = DB::table('hubin_dudi')->where('nama_instansi', 'PT Teknologi Nusantara')->value('id');
            foreach ($siswa->take(3) as $idx => $ss) {
                DB::table('hubin_penempatan_pkl')->updateOrInsert(
                    ['data_siswa_id' => $ss->id, 'data_tahun_pelajaran_id' => $tahunId],
                    ['data_kelas_id' => $ss->data_kelas_id, 'hubin_dudi_id' => $dudiId, 'tanggal_mulai' => '2025-10-01', 'tanggal_selesai' => '2026-01-31', 'status_penempatan' => $idx === 0 ? 'Berjalan' : 'Direncanakan', 'catatan' => 'Dummy penempatan PKL.', 'created_by' => $pengguna['bk@simaka.test'], 'updated_by' => $pengguna['bk@simaka.test'], 'created_at' => $now, 'updated_at' => $now]
                );
            }
            foreach (DB::table('hubin_penempatan_pkl')->pluck('id') as $pid) {
                DB::table('hubin_monitoring_pkl_logs')->updateOrInsert(
                    ['hubin_penempatan_pkl_id' => $pid, 'tanggal_monitoring' => '2025-10-15'],
                    ['status_monitoring' => 'Baik', 'topik_monitoring' => 'Disiplin dan teknis', 'catatan' => 'Perkembangan baik.', 'skor_kinerja' => 85, 'tindak_lanjut' => 'Lanjutkan target berikutnya.', 'created_by' => $pengguna['bk@simaka.test'], 'updated_by' => $pengguna['bk@simaka.test'], 'created_at' => $now, 'updated_at' => $now]
                );
            }

            $this->seedRealStudentsFromJson($tahunId, $sekolahId, $guruRealIds, $now);
            $this->seedRealJadwalFromExtract($tahunId, $sekolahId, $guruRealIds, $now);
        });
    }

    private function resetAcademicSeedData(int $guruRoleId): void
    {
        // Hapus data turunan dulu agar aman terhadap foreign key.
        $tables = [
            'absensi_jam_siswa',
            'jadwal_pelajaran',
            'nilai_mapel_siswa_tujuan',
            'nilai_mapel_siswa',
            'leger_nilai',
            'tujuan_pembelajaran',
            'data_pembelajaran',
            'kk_nilai',
            'kk_capaian_akhir',
            'kk_kelompok_kegiatan',
            'kk_kelompok_anggota',
            'kk_kelompok',
            'kk_kegiatan',
            'kk_dimensi',
            'ekskul_anggota',
            'data_ekstrakurikuler',
            'bk_pelanggaran_siswa',
            'bk_pembinaan_siswa',
            'bk_home_visit',
            'bk_pemanggilan_orang_tua',
            'bk_perjanjian_siswa',
            'bk_pengunduran_diri',
            'bk_peminatan_siswa',
            'bk_sikap_siswa',
            'data_bk',
            'hubin_monitoring_pkl_logs',
            'hubin_penempatan_pkl',
            'hubin_dudi',
            'hubin_rekomendasi_pkl_settings',
            'catatan_wali_kelas',
            'data_ketidakhadiran',
            'hari_libur',
            'data_siswa',
            'data_kelas',
        ];

        foreach ($tables as $table) {
            DB::table($table)->delete();
        }

        if ($guruRoleId > 0) {
            $guruIds = DB::table('pengguna')
                ->where('peran_id', $guruRoleId)
                ->pluck('id')
                ->all();

            if ($guruIds !== []) {
                DB::table('data_guru')->whereIn('pengguna_id', $guruIds)->delete();
                DB::table('pengguna_peran')->whereIn('pengguna_id', $guruIds)->delete();
                DB::table('pengguna')->whereIn('id', $guruIds)->delete();
            }
        }
    }

    private function seedRealTeachers(int $guruRoleId, $now): array
    {
        if ($guruRoleId <= 0) {
            return [];
        }

        $teacherNames = [
            'Acep Adit, S.Hum', 'Aditya Rachman Mulana, S.Tr.T', 'Agus Prasetio, S.Pd', 'Agus Sobari',
            'Ahmad Rafif Fauzi, S.Pd', 'Ai Cahyaningsih, S.Pd', 'Aldi Ridwan, S.E', 'Amelia Sugiharti',
            'Anggita Eka Sowandini, S.Li', 'Annisa Luthfiastuti, S.Pd', 'Aris Makmudin', 'Arya Wijaya Kusuma',
            'Astina, SE', 'Bisri Mustofa, S.Pd', 'Baskoro Ahnaf Nugroho, S.Kom', 'Diah Lutfi Khasani, S.Pd',
            'Dian Resti Kurniawati, S.S', 'Dimas Riki Adam', 'Dra. Mulyati', 'Dwiayu Hadilawati, S.Pi',
            'Eva Farhati, S.H.I, S.Pd.I', 'Finny Robbyatul Adawaiyah, S.Pd', 'Ida Zubaedah, S.Psi',
            'Imam Al Muharramain, S.Pd', 'Isna Ahwati, S.Pd', 'Kemal Yusuf Noviandi, S.Kom',
            'M. Estty Mei Indrayani, S.Pd', 'Muhammad Mahmudin, ST', 'Muhammad Febriansyah. M, S.Kom',
            'Muhammad Robi Sani', 'Murjiyanto', 'Nisa Farra Ulya, S.Pd', 'Nur Auliya Rahmawati, S.Pd',
            'Nurul Fauziah, S. I.Kom', 'Purwanti Hersriasih, SE', 'Rezza Denis Setiawan', 'Ria Mariana, S.Pd',
            'Rifda Zulfiana, S.Pd', 'Rizal Eko Mustofa, S.Pd', 'Saepul Hiar', 'Sapto Adi Putro, ST',
            'Siti Musyifah, S.Pd', 'Siti Zubaedah', 'Ujang Saepul Bahri, ST', 'Umin, ST',
            'Wachyudin, S.Pd.I', 'Wahyu Adi Luhur Prinanto, A.Md', 'Yogi Prasetyo',
        ];

        $ids = [];
        foreach ($teacherNames as $i => $name) {
            $slug = Str::slug(Str::limit($name, 40, ''), '.');
            $email = ($slug !== '' ? $slug : ('guru.' . ($i + 1))) . '@simaka.test';

            DB::table('pengguna')->updateOrInsert(
                ['email' => $email],
                [
                    'peran_id' => $guruRoleId,
                    'nama' => $name,
                    'password' => Hash::make('password'),
                    'status_aktif' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $penggunaId = (int) DB::table('pengguna')->where('email', $email)->value('id');
            if ($penggunaId > 0) {
                DB::table('data_guru')->updateOrInsert(
                    ['pengguna_id' => $penggunaId],
                    [
                        'nip' => null,
                        'nuptk' => null,
                        'tempat_lahir' => null,
                        'tanggal_lahir' => null,
                        'jenis_kelamin' => 'L',
                        'alamat' => null,
                        'telepon' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
                $ids[] = $penggunaId;
            }
        }

        return array_values(array_unique($ids));
    }

    private function seedRealStudentsFromJson(int $tahunId, int $sekolahId, array $guruIds, $now): void
    {
        $files = [
            base_path('database/data/siswa_x.json'),
            base_path('database/data/siswa_xi.json'),
            base_path('database/data/siswa_xii.json'),
        ];

        $students = [];
        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }
            $decoded = json_decode((string) file_get_contents($file), true);
            if (is_array($decoded)) {
                $students = array_merge($students, $decoded);
            }
        }

        if ($students === [] || $guruIds === []) {
            return;
        }

        $jurusanCodes = [];
        foreach ($students as $row) {
            $code = strtoupper(trim((string) ($row['jurusan'] ?? '')));
            if ($code !== '') {
                $jurusanCodes[$code] = true;
            }
        }
        foreach (array_keys($jurusanCodes) as $code) {
            DB::table('data_jurusan')->updateOrInsert(
                ['kode_jurusan' => $code],
                ['nama_jurusan' => $code, 'status' => 'AKTIF', 'status_aktif' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }
        $jurusanMap = DB::table('data_jurusan')->pluck('id', 'kode_jurusan')->all();

        $kelasMap = [];
        $waliIdx = 0;
        foreach ($students as $row) {
            $kelas = trim((string) ($row['kelas'] ?? ''));
            $tingkat = trim((string) ($row['tingkat'] ?? ''));
            $jurusanCode = strtoupper(trim((string) ($row['jurusan'] ?? '')));
            if ($kelas === '' || $tingkat === '') {
                continue;
            }
            if (isset($kelasMap[$kelas])) {
                continue;
            }
            $waliId = $guruIds[$waliIdx % count($guruIds)];
            $waliIdx++;

            DB::table('data_kelas')->updateOrInsert(
                ['nama_kelas' => $kelas, 'data_tahun_pelajaran_id' => $tahunId],
                [
                    'data_sekolah_id' => $sekolahId,
                    'tingkat' => $tingkat,
                    'jurusan_id' => $jurusanMap[$jurusanCode] ?? null,
                    'wali_kelas_id' => $waliId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
            $kelasMap[$kelas] = (int) DB::table('data_kelas')
                ->where('nama_kelas', $kelas)
                ->where('data_tahun_pelajaran_id', $tahunId)
                ->value('id');
        }

        foreach ($students as $row) {
            $nis = trim((string) ($row['nis'] ?? ''));
            $kelas = trim((string) ($row['kelas'] ?? ''));
            $nama = trim((string) ($row['nama_siswa'] ?? ''));
            if ($nis === '' || $kelas === '' || !isset($kelasMap[$kelas])) {
                continue;
            }

            if ($nama === '' || str_contains($nama, 'System.Xml.XmlElement')) {
                $nama = 'Siswa ' . $nis;
            }

            $jkRaw = strtoupper(trim((string) ($row['jk'] ?? '')));
            $jk = str_contains($jkRaw, 'PEREMPUAN') ? 'P' : 'L';
            $agama = trim((string) ($row['agama'] ?? 'Islam'));
            if ($agama === '') {
                $agama = 'Islam';
            }

            DB::table('data_siswa')->updateOrInsert(
                ['nis' => $nis],
                [
                    'data_kelas_id' => $kelasMap[$kelas],
                    'nisn' => null,
                    'nama_siswa' => $nama,
                    'jenis_kelamin' => $jk,
                    'tempat_lahir' => null,
                    'tanggal_lahir' => null,
                    'agama' => $agama,
                    'alamat' => null,
                    'status_siswa' => 'AKTIF',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    private function seedRealJadwalFromExtract(int $tahunId, int $sekolahId, array $guruIds, $now): int
    {
        $path = base_path('storage/app/jadwal_xlsx_out.txt');
        if (!is_file($path)) {
            return 0;
        }

        $raw = (string) file_get_contents($path);
        if (trim($raw) === '') {
            return 0;
        }

        $lines = preg_split('/\R/u', $raw) ?: [];
        $rowTexts = [];
        $current = null;
        foreach ($lines as $line) {
            $line = rtrim($line);
            if ($line === '' || str_starts_with($line, '===')) {
                continue;
            }
            if (preg_match('/^\d+\|/', $line) === 1) {
                if ($current !== null) {
                    $rowTexts[] = $current;
                }
                $current = $line;
            } elseif ($current !== null) {
                $current .= "\n" . $line;
            }
        }
        if ($current !== null) {
            $rowTexts[] = $current;
        }

        $kelasRows = DB::table('data_kelas')
            ->where('data_tahun_pelajaran_id', $tahunId)
            ->select('id', 'nama_kelas', 'tingkat', 'jurusan_id')
            ->get();
        $kelasByNorm = [];
        foreach ($kelasRows as $k) {
            $kelasByNorm[$this->normalizeText((string) $k->nama_kelas)] = [
                'id' => (int) $k->id,
                'tingkat' => (string) ($k->tingkat ?? ''),
                'jurusan_id' => $k->jurusan_id ? (int) $k->jurusan_id : null,
            ];
        }

        $jurusanMap = DB::table('data_jurusan')->pluck('id', 'kode_jurusan')->all();
        $guruIndex = $this->buildGuruNameIndex();
        $waliIdx = 0;
        $inserted = 0;

        foreach ($rowTexts as $rowText) {
            $parts = array_map('trim', explode('|', $rowText));
            if (count($parts) < 26) {
                continue;
            }

            $classRaw = trim((string) ($parts[1] ?? ''));
            if ($classRaw === '') {
                continue;
            }
            $classUpper = strtoupper($classRaw);
            if (in_array($classUpper, ['HARI', 'KELAS', 'PIKET'], true) || is_numeric($classRaw) || $classRaw === '\\') {
                continue;
            }

            $cells = array_slice($parts, 2, 24);
            if (count($cells) < 24) {
                continue;
            }

            $classNorm = $this->normalizeText($classRaw);
            $kelasMeta = $kelasByNorm[$classNorm] ?? null;
            if ($kelasMeta === null) {
                $meta = $this->inferClassMeta($classRaw);
                $jurusanCode = $meta['jurusan'];
                if ($jurusanCode !== null && !isset($jurusanMap[$jurusanCode])) {
                    DB::table('data_jurusan')->updateOrInsert(
                        ['kode_jurusan' => $jurusanCode],
                        ['nama_jurusan' => $jurusanCode, 'status' => 'AKTIF', 'status_aktif' => true, 'created_at' => $now, 'updated_at' => $now]
                    );
                    $jurusanMap = DB::table('data_jurusan')->pluck('id', 'kode_jurusan')->all();
                }
                $waliId = $guruIds !== [] ? $guruIds[$waliIdx % count($guruIds)] : (int) DB::table('pengguna')->value('id');
                $waliIdx++;

                DB::table('data_kelas')->updateOrInsert(
                    ['nama_kelas' => $classRaw, 'data_tahun_pelajaran_id' => $tahunId],
                    [
                        'data_sekolah_id' => $sekolahId,
                        'tingkat' => $meta['tingkat'],
                        'jurusan_id' => $jurusanCode !== null ? ($jurusanMap[$jurusanCode] ?? null) : null,
                        'wali_kelas_id' => $waliId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
                $kelasId = (int) DB::table('data_kelas')
                    ->where('data_tahun_pelajaran_id', $tahunId)
                    ->where('nama_kelas', $classRaw)
                    ->value('id');
                $kelasMeta = [
                    'id' => $kelasId,
                    'tingkat' => $meta['tingkat'],
                    'jurusan_id' => $jurusanCode !== null ? ($jurusanMap[$jurusanCode] ?? null) : null,
                ];
                $kelasByNorm[$classNorm] = $kelasMeta;
            }

            if (($kelasMeta['id'] ?? 0) <= 0) {
                continue;
            }

            for ($slot = 0; $slot < 24; $slot++) {
                $cell = trim((string) ($cells[$slot] ?? ''));
                if ($cell === '' || strtoupper($cell) === 'PIKET') {
                    continue;
                }

                $slotMeta = $this->jadwalSlotMeta($slot);
                if ($slotMeta === null) {
                    continue;
                }

                $parsed = $this->parseJadwalCell($cell, $guruIndex);
                if ($parsed['guru_id'] <= 0 || $parsed['mapel'] === '') {
                    continue;
                }

                $mapelId = $this->resolveMapelId(
                    $parsed['mapel'],
                    (string) ($kelasMeta['tingkat'] ?? 'SEMUA'),
                    $kelasMeta['jurusan_id'] ?? null,
                    $now
                );
                if ($mapelId <= 0) {
                    continue;
                }

                DB::table('jadwal_pelajaran')->updateOrInsert(
                    [
                        'data_tahun_pelajaran_id' => $tahunId,
                        'data_kelas_id' => $kelasMeta['id'],
                        'hari' => $slotMeta['hari'],
                        'jam_ke' => $slotMeta['jam_ke'],
                    ],
                    [
                        'data_mapel_id' => $mapelId,
                        'guru_id' => $parsed['guru_id'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
                $inserted++;
            }
        }

        return $inserted;
    }

    private function buildGuruNameIndex(): array
    {
        $rows = DB::table('pengguna')->select('id', 'nama')->get();
        $index = [];

        $manualAliases = [
            'rizal eko m' => 'Rizal Eko Mustofa, S.Pd',
            'm estty mei indriyani' => 'M. Estty Mei Indrayani, S.Pd',
            'imam al muharamain' => 'Imam Al Muharramain, S.Pd',
            'baskoro anhaf nugroho' => 'Baskoro Ahnaf Nugroho, S.Kom',
            'mohammad robi sani' => 'Muhammad Robi Sani',
            'anisa luthfiastuti' => 'Annisa Luthfiastuti, S.Pd',
            'siti zubaidah' => 'Siti Zubaedah',
            'murjianto' => 'Murjiyanto',
            'dwi ayu hadilawati' => 'Dwiayu Hadilawati, S.Pi',
            'nurul fauziah, s.ikom' => 'Nurul Fauziah, S. I.Kom',
            'eva farhati, s.hi, s.pd.i' => 'Eva Farhati, S.H.I, S.Pd.I',
            'wachyudin, s.pdi' => 'Wachyudin, S.Pd.I',
            'astina,s.t' => 'Astina, SE',
            'bisri mustofa,s.t' => 'Bisri Mustofa, S.Pd',
        ];

        $nameToId = [];
        foreach ($rows as $r) {
            $name = trim((string) ($r->nama ?? ''));
            if ($name === '') {
                continue;
            }
            $nameToId[$name] = (int) $r->id;
        }

        foreach ($rows as $r) {
            $name = trim((string) ($r->nama ?? ''));
            if ($name === '') {
                continue;
            }
            $base = trim((string) strtok($name, ','));
            $aliases = [$name, $base];
            foreach ($aliases as $alias) {
                $norm = $this->normalizeText($alias);
                if ($norm !== '') {
                    $index[] = ['guru_id' => (int) $r->id, 'alias' => $alias, 'norm' => $norm];
                }
            }
        }

        foreach ($manualAliases as $alias => $fullName) {
            $guruId = $nameToId[$fullName] ?? 0;
            if ($guruId <= 0) {
                continue;
            }
            $norm = $this->normalizeText($alias);
            if ($norm === '') {
                continue;
            }
            $index[] = ['guru_id' => $guruId, 'alias' => $alias, 'norm' => $norm];
        }

        usort($index, static fn ($a, $b) => strlen($b['norm']) <=> strlen($a['norm']));
        return $index;
    }

    private function parseJadwalCell(string $cell, array $guruIndex): array
    {
        $flat = trim((string) preg_replace('/\s+/u', ' ', $cell));
        $normCell = $this->normalizeText($flat);

        $guruId = 0;
        $matchedAlias = '';
        foreach ($guruIndex as $entry) {
            if ($entry['norm'] === '') {
                continue;
            }
            if (str_contains($normCell, $entry['norm'])) {
                $guruId = (int) $entry['guru_id'];
                $matchedAlias = (string) $entry['alias'];
                break;
            }
        }

        $mapel = $flat;
        if ($matchedAlias !== '') {
            $escaped = preg_quote($matchedAlias, '/');
            $mapel = trim((string) preg_replace('/' . $escaped . '/iu', ' ', $mapel));
        }

        $mapel = trim((string) preg_replace('/\s+/u', ' ', $mapel));
        $mapel = trim($mapel, "-,.;: \t\n\r\0\x0B");
        if ($mapel === '') {
            $mapel = 'Mapel Umum';
        }

        return [
            'guru_id' => $guruId,
            'mapel' => Str::limit($mapel, 190, ''),
        ];
    }

    private function jadwalSlotMeta(int $slot): ?array
    {
        if ($slot < 0 || $slot > 23) {
            return null;
        }

        if ($slot <= 4) {
            $idx = $slot;
            $hari = 'Senin';
        } elseif ($slot <= 9) {
            $idx = $slot - 5;
            $hari = 'Selasa';
        } elseif ($slot <= 14) {
            $idx = $slot - 10;
            $hari = 'Rabu';
        } elseif ($slot <= 19) {
            $idx = $slot - 15;
            $hari = 'Kamis';
        } else {
            $idx = $slot - 20;
            $hari = 'Jumat';
        }

        $jamKe = 1 + ($idx * 2);
        return ['hari' => $hari, 'jam_ke' => $jamKe];
    }

    private function resolveMapelId(string $namaMapel, string $tingkat, ?int $jurusanId, $now): int
    {
        $namaMapel = trim($namaMapel);
        if ($namaMapel === '') {
            return 0;
        }

        $mapelId = (int) DB::table('data_mapel')->where('nama_mapel', $namaMapel)->value('id');
        if ($mapelId > 0) {
            return $mapelId;
        }

        $singkatan = strtoupper(substr(preg_replace('/[^A-Za-z0-9]+/', '', $namaMapel) ?? '', 0, 20));
        if ($singkatan === '') {
            $singkatan = 'MAPEL';
        }

        DB::table('data_mapel')->insert([
            'nama_mapel' => $namaMapel,
            'singkatan' => $singkatan,
            'kelompok_mapel' => $jurusanId ? 'Produktif' : 'Wajib',
            'tingkat' => $tingkat !== '' ? $tingkat : 'SEMUA',
            'jurusan_id' => $jurusanId,
            'urutan_cetak' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) DB::table('data_mapel')->where('nama_mapel', $namaMapel)->value('id');
    }

    private function inferClassMeta(string $className): array
    {
        $up = strtoupper($className);
        $tingkat = str_starts_with($up, 'XII') ? 'XII' : (str_starts_with($up, 'XI') ? 'XI' : 'X');
        $jurusan = null;
        foreach (['TP', 'TKR', 'TITL', 'TKJ', 'OTKP', 'RPL'] as $kode) {
            if (str_contains($up, $kode)) {
                $jurusan = $kode;
                break;
            }
        }
        return ['tingkat' => $tingkat, 'jurusan' => $jurusan];
    }

    private function normalizeText(string $text): string
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/u', '', $text) ?? '';
        return $text;
    }

    private function seedBk(object $siswa, int $tahunId, int $bkUserId, int $jenisPelanggaranId, $now): void
    {
        DB::table('data_bk')->updateOrInsert(
            ['data_siswa_id' => $siswa->id, 'tanggal' => '2025-08-10', 'jenis_kasus' => 'Konseling belajar'],
            ['data_kelas_id' => $siswa->data_kelas_id, 'data_tahun_pelajaran_id' => $tahunId, 'deskripsi_masalah' => 'Kesulitan manajemen waktu belajar.', 'tindak_lanjut' => 'Pendampingan mingguan.', 'status' => 'Ditindaklanjuti', 'created_by' => $bkUserId, 'updated_by' => $bkUserId, 'created_at' => $now, 'updated_at' => $now]
        );
        DB::table('bk_pelanggaran_siswa')->updateOrInsert(
            ['data_siswa_id' => $siswa->id, 'tanggal' => '2025-08-12', 'bk_jenis_pelanggaran_id' => $jenisPelanggaranId],
            ['data_kelas_id' => $siswa->data_kelas_id, 'data_tahun_pelajaran_id' => $tahunId, 'poin' => 5, 'status' => 'Proses', 'kronologi' => 'Siswa terlambat datang ke sekolah.', 'tindakan' => 'Peringatan lisan.', 'created_by' => $bkUserId, 'updated_by' => $bkUserId, 'created_at' => $now, 'updated_at' => $now]
        );
        DB::table('bk_pembinaan_siswa')->updateOrInsert(
            ['data_siswa_id' => $siswa->id, 'tanggal_mulai' => '2025-08-13', 'bentuk_pembinaan' => 'Konseling Individu'],
            ['data_kelas_id' => $siswa->data_kelas_id, 'data_tahun_pelajaran_id' => $tahunId, 'tanggal_selesai' => '2025-09-13', 'tujuan' => 'Meningkatkan disiplin kehadiran.', 'status' => 'Berjalan', 'catatan' => 'Progress baik.', 'rekomendasi' => 'Pantau selama 1 bulan.', 'created_by' => $bkUserId, 'updated_by' => $bkUserId, 'created_at' => $now, 'updated_at' => $now]
        );
        DB::table('bk_home_visit')->updateOrInsert(
            ['data_siswa_id' => $siswa->id, 'tanggal_kunjungan' => '2025-08-20'],
            ['data_kelas_id' => $siswa->data_kelas_id, 'data_tahun_pelajaran_id' => $tahunId, 'lokasi_kunjungan' => 'Rumah siswa', 'tujuan_kunjungan' => 'Koordinasi dukungan belajar di rumah.', 'status' => 'Selesai', 'hasil_observasi' => 'Lingkungan belajar cukup baik.', 'tindak_lanjut' => 'Jadwal belajar terstruktur.', 'created_by' => $bkUserId, 'updated_by' => $bkUserId, 'created_at' => $now, 'updated_at' => $now]
        );
        DB::table('bk_pemanggilan_orang_tua')->updateOrInsert(
            ['data_siswa_id' => $siswa->id, 'tanggal_panggilan' => '2025-08-25'],
            ['data_kelas_id' => $siswa->data_kelas_id, 'data_tahun_pelajaran_id' => $tahunId, 'nomor_surat' => 'BK/SP/08/2025/001', 'nama_wali_hadir' => 'Orang Tua ' . $siswa->nama_siswa, 'hubungan_wali' => 'Ayah', 'status' => 'Selesai', 'alasan_pemanggilan' => 'Diskusi perkembangan akademik.', 'hasil_pertemuan' => 'Orang tua mendukung pembinaan.', 'tindak_lanjut' => 'Monitoring 2 minggu.', 'created_by' => $bkUserId, 'updated_by' => $bkUserId, 'created_at' => $now, 'updated_at' => $now]
        );
        DB::table('bk_perjanjian_siswa')->updateOrInsert(
            ['data_siswa_id' => $siswa->id, 'tanggal_perjanjian' => '2025-08-26'],
            ['data_kelas_id' => $siswa->data_kelas_id, 'data_tahun_pelajaran_id' => $tahunId, 'nomor_dokumen' => 'BK/PJ/08/2025/001', 'pihak_orang_tua' => 'Orang Tua ' . $siswa->nama_siswa, 'hubungan_orang_tua' => 'Ibu', 'status' => 'Aktif', 'isi_perjanjian' => 'Siswa hadir tepat waktu dan menyelesaikan tugas.', 'target_perbaikan' => 'Disiplin meningkat dalam 1 bulan.', 'tanggal_evaluasi' => '2025-09-26', 'created_by' => $bkUserId, 'updated_by' => $bkUserId, 'created_at' => $now, 'updated_at' => $now]
        );
        DB::table('bk_pengunduran_diri')->updateOrInsert(
            ['data_siswa_id' => $siswa->id, 'tanggal_pengajuan' => '2025-09-01'],
            ['data_kelas_id' => $siswa->data_kelas_id, 'data_tahun_pelajaran_id' => $tahunId, 'status' => 'Diajukan', 'alasan_pengunduran_diri' => 'Pertimbangan keluarga.', 'keterangan' => 'Masih dalam proses konseling.', 'created_by' => $bkUserId, 'updated_by' => $bkUserId, 'created_at' => $now, 'updated_at' => $now]
        );
        DB::table('bk_peminatan_siswa')->updateOrInsert(
            ['data_siswa_id' => $siswa->id, 'tanggal_peminatan' => '2025-08-28'],
            ['data_kelas_id' => $siswa->data_kelas_id, 'data_tahun_pelajaran_id' => $tahunId, 'minat_utama' => 'Software Engineering', 'minat_alternatif' => 'UI/UX Design', 'rencana_lanjutan' => 'Kuliah Informatika', 'status' => 'Direkomendasikan', 'rekomendasi_bk' => 'Arahkan ke kegiatan coding intensif.', 'catatan_orang_tua' => 'Mendukung minat anak.', 'catatan_tindak_lanjut' => 'Ikut kelas tambahan pemrograman.', 'created_by' => $bkUserId, 'updated_by' => $bkUserId, 'created_at' => $now, 'updated_at' => $now]
        );
        DB::table('bk_sikap_siswa')->updateOrInsert(
            ['data_siswa_id' => $siswa->id, 'tanggal_penilaian' => '2025-08-29', 'aspek_sikap' => 'Disiplin'],
            ['data_kelas_id' => $siswa->data_kelas_id, 'data_tahun_pelajaran_id' => $tahunId, 'predikat' => 'Baik', 'skor' => 85, 'status' => 'Perlu Monitoring', 'catatan' => 'Perbaikan terlihat namun perlu konsistensi.', 'tindak_lanjut' => 'Evaluasi bulan depan.', 'created_by' => $bkUserId, 'updated_by' => $bkUserId, 'created_at' => $now, 'updated_at' => $now]
        );
    }
}
