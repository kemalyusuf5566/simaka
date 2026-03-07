<?php

namespace App\Http\Controllers\BK;

use App\Http\Controllers\Controller;
use App\Models\AbsensiJamSiswa;
use App\Models\DataKelas;
use App\Models\DataSiswa;
use App\Models\DataTahunPelajaran;
use Illuminate\Http\Request;

class AbsensiBulananController extends Controller
{
    public function index(Request $request)
    {
        $tahunAktif = DataTahunPelajaran::where('status_aktif', 1)->first();

        $bulanInput = (int) $request->get('bulan', (int) date('n'));
        $tahunInput = (int) $request->get('tahun', (int) date('Y'));
        if ($bulanInput < 1 || $bulanInput > 12) {
            $bulanInput = (int) date('n');
        }

        $q = trim((string) $request->get('q', ''));
        $kelasId = $request->get('kelas_id');

        $startDate = sprintf('%04d-%02d-01', $tahunInput, $bulanInput);
        $endDate = date('Y-m-t', strtotime($startDate));
        $periodeLabel = date('F Y', strtotime($startDate));

        $siswaQuery = DataSiswa::query()
            ->with('kelas')
            ->when($kelasId, fn($builder) => $builder->where('data_kelas_id', $kelasId))
            ->when($q !== '', function ($builder) use ($q) {
                $builder->where(function ($w) use ($q) {
                    $w->where('nama_siswa', 'like', "%{$q}%")
                        ->orWhere('nis', 'like', "%{$q}%")
                        ->orWhere('nisn', 'like', "%{$q}%");
                });
            })
            ->orderBy('nama_siswa');

        $siswa = $siswaQuery->get();

        $records = AbsensiJamSiswa::query()
            ->when($tahunAktif, fn($builder) => $builder->where('data_tahun_pelajaran_id', $tahunAktif->id))
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->when($kelasId, fn($builder) => $builder->where('data_kelas_id', $kelasId))
            ->whereIn('data_siswa_id', $siswa->pluck('id'))
            ->get()
            ->groupBy('data_siswa_id');

        $rows = [];
        foreach ($siswa as $s) {
            $daily = ($records->get($s->id) ?? collect())->groupBy('tanggal');
            $h = $si = $iz = $a = 0;

            foreach ($daily as $items) {
                $statuses = $items->pluck('status');
                if ($statuses->contains('H')) {
                    $h++;
                    continue;
                }

                $countS = $statuses->filter(fn($x) => $x === 'S')->count();
                $countI = $statuses->filter(fn($x) => $x === 'I')->count();
                $countA = $statuses->filter(fn($x) => $x === 'A')->count();
                $max = max($countA, $countI, $countS);

                if ($max === 0) {
                    continue;
                }
                if ($countA === $max) {
                    $a++;
                } elseif ($countI === $max) {
                    $iz++;
                } else {
                    $si++;
                }
            }

            $rows[] = [
                'siswa' => $s,
                'hadir' => $h,
                'sakit' => $si,
                'izin' => $iz,
                'alpa' => $a,
            ];
        }

        usort($rows, fn($x, $y) => ($y['alpa'] <=> $x['alpa']) ?: ($y['izin'] <=> $x['izin']));

        $ringkasan = [
            'total_siswa' => count($rows),
            'total_hadir' => array_sum(array_column($rows, 'hadir')),
            'total_sakit' => array_sum(array_column($rows, 'sakit')),
            'total_izin' => array_sum(array_column($rows, 'izin')),
            'total_alpa' => array_sum(array_column($rows, 'alpa')),
        ];

        $kelasOptions = DataKelas::orderBy('nama_kelas')->get();

        return view('bk.absensi_bulanan.index', [
            'rows' => $rows,
            'ringkasan' => $ringkasan,
            'kelasOptions' => $kelasOptions,
            'tahunAktif' => $tahunAktif,
            'bulan' => $bulanInput,
            'tahun' => $tahunInput,
            'kelasId' => $kelasId,
            'q' => $q,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'periodeLabel' => $periodeLabel,
        ]);
    }
}

