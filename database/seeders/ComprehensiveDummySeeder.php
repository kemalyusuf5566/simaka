<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
        });
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
