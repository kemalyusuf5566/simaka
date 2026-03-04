<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataKelas;
use App\Models\DataGuru;
use App\Models\DataTahunPelajaran;
use Illuminate\Http\Request;
use App\Models\DataJurusan;

class DataKelasController extends Controller
{
    public function index(Request $request)
    {
        $limit = (int) $request->get('limit', 10);
        if (!in_array($limit, [10, 25, 50, 100])) $limit = 10;

        $q = trim((string) $request->get('q', ''));
        $tingkat = (string) $request->get('tingkat', '');

        $query = DataKelas::withCount('siswa')
            ->with(['wali.pengguna', 'jurusan'])
            ->orderBy('tingkat')
            ->orderBy('nama_kelas');

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('nama_kelas', 'like', "%{$q}%")
                    ->orWhereHas('wali.pengguna', function ($p) use ($q) {
                        $p->where('nama', 'like', "%{$q}%");
                    });
            });
        }

        if ($tingkat !== '' && $tingkat !== 'all') {
            $query->where('tingkat', $tingkat);
        }

        $kelas = $query->paginate($limit)->appends([
            'limit' => $limit,
            'q' => $q,
            'tingkat' => $tingkat,
        ]);

        return view('admin.kelas.index', compact('kelas', 'limit', 'q', 'tingkat'));
    }

    public function create(Request $request)
    {
        $tahunAktif = DataTahunPelajaran::where('status_aktif', 1)->first();

        // kalau dipanggil dari modal (AJAX)
        if ($request->ajax()) {
            if (!$tahunAktif) {
                return response()->view('admin.kelas.form-modal-error', [
                    'message' => 'Tahun pelajaran aktif belum ditentukan. Silakan set Tahun Pelajaran aktif dulu.'
                ], 200);
            }

            return view('admin.kelas.form-modal', [
                'mode'       => 'create',
                'kelas'      => null,
                'tahunAktif' => $tahunAktif,
                'wali'       => DataGuru::with('pengguna')->get(),
                'jurusan' => DataJurusan::where('status', 'AKTIF')
                    ->orderBy('kode_jurusan')
                    ->get(),
            ]);
        }

        // akses normal (non-ajax)
        if (!$tahunAktif) {
            return redirect()
                ->route('admin.tahun.index')
                ->with('error', 'Tahun pelajaran aktif belum ditentukan');
        }

        return view('admin.kelas.create', [
            'tahunAktif' => $tahunAktif,
            'wali' => DataGuru::with('pengguna')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'data_tahun_pelajaran_id' => 'required|exists:data_tahun_pelajaran,id',
            'nama_kelas'              => 'required',
            'tingkat'                 => 'required|numeric',
            'wali_kelas_id'           => 'nullable|exists:pengguna,id',
            'jurusan_id' => 'nullable|exists:data_jurusan,id',
            'yakin'                   => 'required|in:1',
        ]);

        DataKelas::create([
            'data_sekolah_id'         => 1,
            'data_tahun_pelajaran_id' => $request->data_tahun_pelajaran_id,
            'nama_kelas'              => $request->nama_kelas,
            'tingkat'                 => $request->tingkat,
            'wali_kelas_id'           => $request->wali_kelas_id,
            'jurusan_id'              => $request->jurusan_id,
        ]);

        return redirect()
            ->route('admin.kelas.index')
            ->with('success', 'Data kelas berhasil disimpan');
    }

    public function edit(Request $request, $id)
    {
        $kelas = DataKelas::findOrFail($id);

        $data = [
            'mode'       => 'edit',
            'kelas'      => $kelas,
            'tahunAktif' => $kelas->tahunPelajaran ?? DataTahunPelajaran::where('status_aktif', 1)->first(),
            'wali'       => DataGuru::with('pengguna')->get(),
            'jurusan' => \App\Models\DataJurusan::where('status', 'AKTIF')->orderBy('kode_jurusan')->get(),
        ];

        if ($request->ajax()) {
            return view('admin.kelas.form-modal', $data);
        }

        return view('admin.kelas.edit', $data);
    }

    public function update(Request $request, $id)
    {
        $kelas = DataKelas::findOrFail($id);

        $request->validate([
            'data_tahun_pelajaran_id' => 'required|exists:data_tahun_pelajaran,id',
            'nama_kelas'              => 'required',
            'tingkat'                 => 'required|numeric',
            'wali_kelas_id'           => 'nullable|exists:pengguna,id',
            'yakin'                   => 'required|in:1',
        ]);

        $kelas->update($request->only([
            'data_tahun_pelajaran_id',
            'nama_kelas',
            'tingkat',
            'wali_kelas_id',
        ]));

        return redirect()
            ->route('admin.kelas.index')
            ->with('success', 'Data kelas berhasil diperbarui');
    }

    public function destroy($id)
    {
        $kelas = \App\Models\DataKelas::findOrFail($id);
        $kelas->delete();

        return back()->with('success', 'Data kelas berhasil dihapus.');
    }
}
