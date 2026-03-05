<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataPembelajaran;
use App\Models\DataKelas;
use App\Models\DataMapel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DataPembelajaranController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()->peran->nama_peran === 'admin', 403);

        $pembelajaran = DataPembelajaran::with(['kelas', 'mapel', 'guru'])
            ->orderBy('data_kelas_id')
            ->get();

        return view('admin.pembelajaran.index', [
            'pembelajaran' => $pembelajaran,
            'kelas' => DataKelas::orderBy('nama_kelas')->get(),
            // mapel tetap dikirim (dipakai filter modal kamu)
            'mapel' => DataMapel::orderBy('nama_mapel')->get(),
            'guru'  => User::whereHas('peran', fn($q) => $q->where('nama_peran', 'guru_mapel'))
                ->orderBy('nama')->get(),
        ]);
    }

    public function create()
    {
        abort_unless(Auth::user()->peran->nama_peran === 'admin', 403);

        return view('admin.pembelajaran.form', [
            'pembelajaran' => null,
            'kelas' => DataKelas::orderBy('nama_kelas')->get(),
            'mapel' => DataMapel::orderBy('nama_mapel')->get(),
            'guru'  => User::whereHas('peran', fn($q) => $q->where('nama_peran', 'guru_mapel'))
                ->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->peran->nama_peran === 'admin', 403);

        $data = $request->validate([
            'data_kelas_id' => 'required|exists:data_kelas,id',
            'data_mapel_id' => 'required|exists:data_mapel,id',
            'guru_id'       => 'required|exists:pengguna,id',
        ]);

        DataPembelajaran::where('data_kelas_id', $data['data_kelas_id'])
            ->where('data_mapel_id', $data['data_mapel_id'])
            ->exists() && abort(422, 'Pembelajaran sudah ada');

        DataPembelajaran::create($data);

        return redirect()->route('admin.pembelajaran.index')
            ->with('success', 'Data pembelajaran berhasil ditambahkan');
    }

    public function edit($id)
    {
        abort_unless(Auth::user()->peran->nama_peran === 'admin', 403);

        return view('admin.pembelajaran.form', [
            'pembelajaran' => DataPembelajaran::findOrFail($id),
            'kelas' => DataKelas::orderBy('nama_kelas')->get(),
            'mapel' => DataMapel::orderBy('nama_mapel')->get(),
            'guru'  => User::whereHas('peran', fn($q) => $q->where('nama_peran', 'guru_mapel'))
                ->orderBy('nama')->get(),
        ]);
    }

    public function json($id)
    {
        abort_unless(Auth::user()->peran->nama_peran === 'admin', 403);

        $p = DataPembelajaran::findOrFail($id);

        return response()->json([
            'id' => $p->id,
            'data_kelas_id' => $p->data_kelas_id,
            'data_mapel_id' => $p->data_mapel_id,
            'guru_id' => $p->guru_id,
        ]);
    }

    public function update(Request $request, $id)
    {
        abort_unless(Auth::user()->peran->nama_peran === 'admin', 403);

        $pembelajaran = DataPembelajaran::findOrFail($id);

        $data = $request->validate([
            'data_kelas_id' => 'required|exists:data_kelas,id',
            'data_mapel_id' => 'required|exists:data_mapel,id',
            'guru_id'       => 'required|exists:pengguna,id',
        ]);

        DataPembelajaran::where('id', '!=', $pembelajaran->id)
            ->where('data_kelas_id', $data['data_kelas_id'])
            ->where('data_mapel_id', $data['data_mapel_id'])
            ->exists() && abort(422, 'Pembelajaran sudah ada');

        $pembelajaran->update($data);

        return redirect()->route('admin.pembelajaran.index')
            ->with('success', 'Data pembelajaran berhasil diperbarui');
    }

    public function mapelByKelas($kelasId)
    {
        abort_unless(Auth::user()->peran->nama_peran === 'admin', 403);

        $kelas = DataKelas::findOrFail($kelasId);

        // tingkat di data_kelas: 10/11/12 -> X/XI/XII
        $rawTingkat = trim((string) $kelas->tingkat);

        $mapTingkat = [
            '10' => 'X',
            '11' => 'XI',
            '12' => 'XII',
            'X'  => 'X',
            'XI' => 'XI',
            'XII' => 'XII',
        ];

        $tingkatKelas = $mapTingkat[strtoupper($rawTingkat)] ?? strtoupper($rawTingkat);

        $jurusanId  = $kelas->jurusan_id;
        $hasJurusan = !empty($jurusanId);

        $rows = DataMapel::query()
            // filter tingkat: tingkat kelas + SEMUA
            ->whereIn('tingkat', [$tingkatKelas, 'SEMUA'])
            // filter jurusan: UMUM (NULL) + jurusan kelas (kalau ada)
            ->where(function ($q) use ($hasJurusan, $jurusanId) {
                if ($hasJurusan) {
                    $q->whereNull('jurusan_id')
                        ->orWhere('jurusan_id', (int)$jurusanId);
                } else {
                    $q->whereNull('jurusan_id');
                }
            })
            // ✅ URUTAN TINGKAT: SEMUA dulu, baru tingkat kelas
            ->orderByRaw("CASE WHEN tingkat = 'SEMUA' THEN 0 WHEN tingkat = ? THEN 1 ELSE 2 END", [$tingkatKelas])
            // ✅ URUTAN JURUSAN: UMUM (NULL) dulu, baru jurusan kelas
            ->when($hasJurusan, function ($q) use ($jurusanId) {
                $q->orderByRaw("CASE WHEN jurusan_id IS NULL THEN 0 WHEN jurusan_id = ? THEN 1 ELSE 2 END", [(int)$jurusanId]);
            }, function ($q) {
                $q->orderByRaw("CASE WHEN jurusan_id IS NULL THEN 0 ELSE 1 END");
            })
            // urutan cetak lalu nama
            ->orderByRaw('COALESCE(urutan_cetak, 999999) ASC')
            ->orderBy('nama_mapel')
            ->get();

        // unik berdasarkan nama_mapel (hindari double di dropdown)
        $unique = $rows
            ->groupBy(fn($m) => mb_strtolower(trim((string)$m->nama_mapel)))
            ->map(fn($grp) => $grp->first())
            ->values()
            ->map(fn($m) => [
                'id'   => $m->id,
                'nama' => $m->nama_mapel,
            ]);

        return response()->json($unique);
    }
}
