<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataMapel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DataMapelController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()->peran->nama_peran === 'admin', 403);

        // pagination biar sama seperti gambar
        $mapel = DataMapel::orderByRaw('COALESCE(urutan_cetak, 999999) ASC')
            ->orderBy('nama_mapel')
            ->paginate(10);

        return view('admin.mapel.index', compact('mapel'));
    }

    public function create()
    {
        abort_unless(Auth::user()->peran->nama_peran === 'admin', 403);

        return view('admin.mapel.form', [
            'mapel' => null
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->peran->nama_peran === 'admin', 403);

        $data = $request->validate([
            'nama_mapel'     => 'required|string|max:255',
            'singkatan'      => 'required|string|max:30',
            'urutan_cetak'   => 'required|integer|min:1|max:9999',
            'kelompok_mapel' => 'required|in:Mata Pelajaran Umum,Mata Pelajaran Pilihan,Muatan Lokal',
        ]);

        DataMapel::create($data);

        return redirect()
            ->route('admin.mapel.index')
            ->with('success', 'Data mata pelajaran berhasil ditambahkan');
    }

    public function edit($id)
    {
        abort_unless(Auth::user()->peran->nama_peran === 'admin', 403);

        $mapel = DataMapel::findOrFail($id);

        return view('admin.mapel.form', compact('mapel'));
    }

    public function update(Request $request, $id)
    {
        abort_unless(Auth::user()->peran->nama_peran === 'admin', 403);

        $mapel = DataMapel::findOrFail($id);

        $data = $request->validate([
            'nama_mapel'     => 'required|string|max:255',
            'singkatan'      => 'required|string|max:30',
            'urutan_cetak'   => 'required|integer|min:1|max:9999',
            'kelompok_mapel' => 'required|in:Mata Pelajaran Umum,Mata Pelajaran Pilihan,Muatan Lokal',
        ]);

        $mapel->update($data);

        return redirect()
            ->route('admin.mapel.index')
            ->with('success', 'Data mata pelajaran berhasil diperbarui');
    }

    public function destroy($id)
    {
        abort_unless(Auth::user()->peran->nama_peran === 'admin', 403);

        $mapel = DataMapel::findOrFail($id);
        $mapel->delete();

        return redirect()
            ->route('admin.mapel.index')
            ->with('success', 'Data mata pelajaran berhasil dihapus');
    }
}
