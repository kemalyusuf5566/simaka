<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataKelas;
use App\Models\DataMapel;
use App\Models\DataSiswa;
use App\Models\DataTahunPelajaran;
use App\Models\NilaiMapelSiswa;
use Illuminate\Http\Request;

class LegerNilaiController extends Controller
{
    public function index(Request $request)
    {
        // per page
        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100], true)) $perPage = 10;

        $q = trim((string) $request->get('q', ''));
        $tingkat = (string) $request->get('tingkat', '');

        // list tingkat untuk dropdown filter
        $tingkatList = DataKelas::query()
            ->select('tingkat')
            ->whereNotNull('tingkat')
            ->groupBy('tingkat')
            ->orderBy('tingkat')
            ->pluck('tingkat')
            ->toArray();

        $kelasQuery = DataKelas::query()
            ->withCount('siswa')
            ->with(['wali.pengguna']);

        if ($tingkat !== '') {
            $kelasQuery->where('tingkat', $tingkat);
        }

        if ($q !== '') {
            $kelasQuery->where(function ($qq) use ($q) {
                $qq->where('nama_kelas', 'like', "%{$q}%")
                    ->orWhereHas('wali.pengguna', function ($w) use ($q) {
                        $w->where('nama', 'like', "%{$q}%")
                            ->orWhere('name', 'like', "%{$q}%");
                    });
            });
        }

        $kelas = $kelasQuery
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.rapor.leger.index', compact(
            'kelas',
            'perPage',
            'q',
            'tingkat',
            'tingkatList'
        ));
    }

    public function detail($kelasId, Request $request)
    {
        $kelas = DataKelas::with(['wali.pengguna'])->findOrFail($kelasId);

        // tahun+semester aktif (kalau belum diset, tetap tampilkan tabel)
        $tahunAktif = DataTahunPelajaran::where('status_aktif', 1)->first();
        $tahunId = $tahunAktif->id ?? null;
        $semester = $tahunAktif->semester ?? null;

        // siswa kelas
        $siswa = DataSiswa::where('data_kelas_id', $kelasId)
            ->orderBy('nama_siswa')
            ->get();

        // tampilkan SEMUA mapel (bukan hanya yang ada nilai)
        $mapel = DataMapel::orderBy('nama_mapel')->get();

        // ambil nilai untuk kelas + (tahun aktif) + (semester aktif)
        $nilaiQuery = NilaiMapelSiswa::query()
            ->where('data_kelas_id', $kelasId);

        if ($tahunId) $nilaiQuery->where('data_tahun_pelajaran_id', $tahunId);
        if ($semester) $nilaiQuery->where('semester', $semester);

        // map nilai: [siswa_id][mapel_id] = nilai_angka
        $nilaiMap = [];
        foreach ($nilaiQuery->get() as $n) {
            $nilaiMap[(int)$n->data_siswa_id][(int)$n->data_mapel_id] = $n->nilai_angka;
        }

        // hitung total/rata
        $rowsTemp = [];
        foreach ($siswa as $s) {
            $total = 0;
            $count = 0;

            foreach ($mapel as $m) {
                $v = $nilaiMap[(int)$s->id][(int)$m->id] ?? null;
                if (is_numeric($v)) {
                    $total += (int)$v;
                    $count++;
                }
            }

            $rowsTemp[] = [
                'siswa' => $s,
                'total' => $total,
                'rata'  => $count > 0 ? round($total / $count, 1) : null,
            ];
        }

        // ranking (dense rank) berdasarkan total desc
        usort($rowsTemp, fn($a, $b) => ($b['total'] <=> $a['total']));

        $rankBySiswa = [];
        $rank = 0;
        $prevTotal = null;

        foreach ($rowsTemp as $r) {
            if ($prevTotal === null || $r['total'] !== $prevTotal) {
                $rank++;
                $prevTotal = $r['total'];
            }

            // jika semua nilai kosong, total=0 -> ranking '-'
            $rankBySiswa[(int)$r['siswa']->id] = ($r['total'] === 0) ? '-' : $rank;
        }

        // susun final rows sesuai urutan nama siswa (rapih)
        $rows = [];
        foreach ($siswa as $s) {
            $sid = (int)$s->id;

            $total = 0;
            $count = 0;
            foreach ($mapel as $m) {
                $v = $nilaiMap[$sid][(int)$m->id] ?? null;
                if (is_numeric($v)) {
                    $total += (int)$v;
                    $count++;
                }
            }

            $rows[] = [
                'siswa' => $s,
                'total' => $total,
                'rata'  => $count > 0 ? round($total / $count, 1) : null,
                'rank'  => $rankBySiswa[$sid] ?? '-',
            ];
        }

        return view('admin.rapor.leger.detail', compact(
            'kelas',
            'siswa',
            'mapel',
            'nilaiMap',
            'rows',
            'tahunAktif',
            'semester'
        ));
    }

    public function exportPdf($kelasId)
    {
        $kelas = DataKelas::with(['wali.pengguna'])->findOrFail($kelasId);

        $tahunAktif = DataTahunPelajaran::where('status_aktif', 1)->first();
        $tahunId = $tahunAktif->id ?? null;
        $semester = $tahunAktif->semester ?? null;

        $siswa = DataSiswa::where('data_kelas_id', $kelasId)->orderBy('nama_siswa')->get();
        $mapel = DataMapel::orderBy('nama_mapel')->get();

        $nilaiQuery = NilaiMapelSiswa::query()->where('data_kelas_id', $kelasId);
        if ($tahunId) $nilaiQuery->where('data_tahun_pelajaran_id', $tahunId);
        if ($semester) $nilaiQuery->where('semester', $semester);

        $nilaiMap = [];
        foreach ($nilaiQuery->get() as $n) {
            $nilaiMap[(int)$n->data_siswa_id][(int)$n->data_mapel_id] = $n->nilai_angka;
        }

        // rows + rank
        $tmp = [];
        foreach ($siswa as $s) {
            $total = 0;
            $count = 0;
            foreach ($mapel as $m) {
                $v = $nilaiMap[(int)$s->id][(int)$m->id] ?? null;
                if (is_numeric($v)) {
                    $total += (int)$v;
                    $count++;
                }
            }
            $tmp[] = ['siswa' => $s, 'total' => $total, 'rata' => $count > 0 ? round($total / $count, 1) : null];
        }
        usort($tmp, fn($a, $b) => ($b['total'] <=> $a['total']));
        $rankBy = [];
        $rank = 0;
        $prev = null;
        foreach ($tmp as $r) {
            if ($prev === null || $r['total'] !== $prev) {
                $rank++;
                $prev = $r['total'];
            }
            $rankBy[(int)$r['siswa']->id] = ($r['total'] === 0) ? '-' : $rank;
        }

        $rows = [];
        foreach ($siswa as $s) {
            $sid = (int)$s->id;
            $total = 0;
            $count = 0;
            foreach ($mapel as $m) {
                $v = $nilaiMap[$sid][(int)$m->id] ?? null;
                if (is_numeric($v)) {
                    $total += (int)$v;
                    $count++;
                }
            }
            $rows[] = [
                'siswa' => $s,
                'total' => $total,
                'rata' => $count > 0 ? round($total / $count, 1) : null,
                'rank' => $rankBy[$sid] ?? '-',
            ];
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.rapor.leger.pdf', compact(
            'kelas',
            'mapel',
            'nilaiMap',
            'rows',
            'tahunAktif',
            'semester'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('leger-' . $kelas->nama_kelas . '.pdf');
    }

    /**
     * Excel fallback -> CSV (bisa dibuka Excel)
     * Kalau kamu pakai maatwebsite/excel, bilang nanti aku ubah jadi XLSX.
     */
    public function exportExcel($kelasId)
    {
        $kelas = DataKelas::with(['wali.pengguna'])->findOrFail($kelasId);

        $tahunAktif = DataTahunPelajaran::where('status_aktif', 1)->first();
        $tahunId = $tahunAktif->id ?? null;
        $semester = $tahunAktif->semester ?? null;

        $siswa = DataSiswa::where('data_kelas_id', $kelasId)->orderBy('nama_siswa')->get();
        $mapel = DataMapel::orderBy('nama_mapel')->get();

        $nilaiQuery = NilaiMapelSiswa::query()->where('data_kelas_id', $kelasId);
        if ($tahunId) $nilaiQuery->where('data_tahun_pelajaran_id', $tahunId);
        if ($semester) $nilaiQuery->where('semester', $semester);

        $nilaiMap = [];
        foreach ($nilaiQuery->get() as $n) {
            $nilaiMap[(int)$n->data_siswa_id][(int)$n->data_mapel_id] = $n->nilai_angka;
        }

        $filename = 'leger-' . $kelas->nama_kelas . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($siswa, $mapel, $nilaiMap) {
            $out = fopen('php://output', 'w');

            // BOM UTF-8 untuk Excel Windows
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            $mapelHeaders = [];
            foreach ($mapel as $m) {
                $mapelHeaders[] = $m->singkatan ?? $m->kode_mapel ?? $m->nama_mapel;
            }

            fputcsv($out, array_merge(['No', 'NIS', 'Nama', 'L/P'], $mapelHeaders, ['Total', 'Rata-rata', 'Ranking']));

            // totals untuk rank
            $totals = [];
            foreach ($siswa as $s) {
                $total = 0;
                foreach ($mapel as $m) {
                    $v = $nilaiMap[(int)$s->id][(int)$m->id] ?? null;
                    if (is_numeric($v)) $total += (int)$v;
                }
                $totals[(int)$s->id] = $total;
            }

            arsort($totals);
            $rankBy = [];
            $rank = 0;
            $prev = null;

            foreach ($totals as $sid => $t) {
                if ($prev === null || $t !== $prev) {
                    $rank++;
                    $prev = $t;
                }
                $rankBy[$sid] = ($t === 0) ? '-' : $rank;
            }

            $no = 1;
            foreach ($siswa as $s) {
                $sid = (int)$s->id;

                $rowNilai = [];
                $total = 0;
                $count = 0;

                foreach ($mapel as $m) {
                    $v = $nilaiMap[$sid][(int)$m->id] ?? null;
                    $rowNilai[] = is_numeric($v) ? (int)$v : '-';
                    if (is_numeric($v)) {
                        $total += (int)$v;
                        $count++;
                    }
                }

                $rata = $count > 0 ? round($total / $count, 1) : '-';

                fputcsv($out, array_merge([
                    $no++,
                    $s->nis ?? '-',
                    $s->nama_siswa ?? '-',
                    strtoupper($s->jenis_kelamin ?? '-') ?: '-',
                ], $rowNilai, [
                    $total,
                    $rata,
                    $rankBy[$sid] ?? '-'
                ]));
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
