<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataEkstrakurikuler;
use App\Models\DataGuru;
use Illuminate\Http\Request;

class DataEkstrakurikulerController extends Controller
{
    public function index()
    {
        $ekskul = DataEkstrakurikuler::with('pembina.pengguna')
            ->withCount('anggota')
            ->get();

        $pembina = DataGuru::with('pengguna')->get();

        return view('admin.ekstrakurikuler.index', compact('ekskul', 'pembina'));
    }

    public function create()
    {
        $pembina = DataGuru::with('pengguna')->get();

        return view('admin.ekstrakurikuler.form', [
            'ekskul' => null,
            'pembina' => $pembina
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_ekskul'  => 'required',
            'pembina_id'   => 'nullable|exists:data_guru,id',
            'status_aktif' => 'required|boolean',
        ]);

        DataEkstrakurikuler::create($data);

        return redirect()
            ->route('admin.ekstrakurikuler.index')
            ->with('success', 'Ekstrakurikuler berhasil disimpan');
    }

    public function edit($id)
    {
        $ekskul = DataEkstrakurikuler::findOrFail($id);
        $pembina = DataGuru::with('pengguna')->get();

        return view('admin.ekstrakurikuler.form', compact('ekskul', 'pembina'));
    }

    public function json($id)
    {
        $e = DataEkstrakurikuler::findOrFail($id);

        return response()->json([
            'id' => $e->id,
            'nama_ekskul' => $e->nama_ekskul,
            'pembina_id' => $e->pembina_id,
            'status_aktif' => (int) $e->status_aktif,
        ]);
    }

    public function update(Request $request, $id)
    {
        $ekskul = DataEkstrakurikuler::findOrFail($id);

        $data = $request->validate([
            'nama_ekskul'  => 'required',
            'pembina_id'   => 'nullable|exists:data_guru,id',
            'status_aktif' => 'required|boolean',
        ]);

        $ekskul->update($data);

        return redirect()
            ->route('admin.ekstrakurikuler.index')
            ->with('success', 'Ekstrakurikuler diperbarui');
    }

    public function destroy($id)
    {
        $ekskul = DataEkstrakurikuler::findOrFail($id);
        $ekskul->delete();

        return redirect()
            ->route('admin.ekstrakurikuler.index')
            ->with('success', 'Ekstrakurikuler berhasil dihapus');
    }
}
