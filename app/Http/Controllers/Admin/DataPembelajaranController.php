<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataPembelajaran;
use App\Models\DataKelas;
use App\Models\DataMapel;
use App\Models\DataJurusan;
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

        $exists = DataPembelajaran::where('data_kelas_id', $data['data_kelas_id'])
            ->where('data_mapel_id', $data['data_mapel_id'])
            ->exists();

        if ($exists) {
            return redirect()
                ->route('admin.pembelajaran.index')
                ->withInput()
                ->with('error', 'Pembelajaran untuk kelas dan mata pelajaran tersebut sudah ada.');
        }

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

        $exists = DataPembelajaran::where('id', '!=', $pembelajaran->id)
            ->where('data_kelas_id', $data['data_kelas_id'])
            ->where('data_mapel_id', $data['data_mapel_id'])
            ->exists();

        if ($exists) {
            return redirect()
                ->route('admin.pembelajaran.index')
                ->withInput()
                ->with('error', 'Pembelajaran untuk kelas dan mata pelajaran tersebut sudah ada.');
        }

        $pembelajaran->update($data);

        return redirect()->route('admin.pembelajaran.index')
            ->with('success', 'Data pembelajaran berhasil diperbarui');
    }

    public function mapelByKelas($kelasId)
    {
        abort_unless(Auth::user()->peran->nama_peran === 'admin', 403);

        $kelas = DataKelas::findOrFail($kelasId);

        $rawTingkat = strtoupper(trim((string)$kelas->tingkat));
        $mapTingkat = ['10' => 'X', '11' => 'XI', '12' => 'XII', 'X' => 'X', 'XI' => 'XI', 'XII' => 'XII'];
        $tingkatKelas = $mapTingkat[$rawTingkat] ?? $rawTingkat;

        $jurusanId  = $kelas->jurusan_id;
        $hasJurusan = !empty($jurusanId);

        $rows = DataMapel::query()
            ->whereIn('tingkat', [$tingkatKelas, 'SEMUA'])
            ->where(function ($q) use ($hasJurusan, $jurusanId) {
                if ($hasJurusan) {
                    $q->whereNull('jurusan_id')
                        ->orWhere('jurusan_id', (int)$jurusanId);
                } else {
                    $q->whereNull('jurusan_id');
                }
            })
            ->get();

        $rows = $rows->map(function ($m) use ($tingkatKelas, $jurusanId) {
            $tingkatPrior = ($m->tingkat === 'SEMUA') ? 0 : (($m->tingkat === $tingkatKelas) ? 1 : 9);
            $jurusanPrior = is_null($m->jurusan_id) ? 0 : (((int)$m->jurusan_id === (int)$jurusanId) ? 1 : 9);
            $urutan = is_null($m->urutan_cetak) ? 999999 : (int)$m->urutan_cetak;

            $m->_sortKey = [$tingkatPrior, $jurusanPrior, $urutan, mb_strtolower($m->nama_mapel)];
            return $m;
        });

        $unique = $rows
            ->groupBy(fn($m) => mb_strtolower(trim((string)$m->nama_mapel)))
            ->map(function ($grp) {
                return $grp->sortBy(fn($m) => $m->_sortKey)->first();
            })
            ->values()
            ->sortBy(fn($m) => $m->_sortKey)
            ->values()
            ->map(fn($m) => [
                'id'   => $m->id,
                'nama' => $m->nama_mapel,
            ]);

        return response()->json($unique);
    }
}
