<?php

namespace App\Http\Controllers\Admin\Kokurikuler;

use App\Http\Controllers\Controller;
use App\Models\KkKelompok;
use App\Models\KkKelompokKegiatan;
use App\Models\KkKegiatan;
use Illuminate\Http\Request;

class KelompokKegiatanController extends Controller
{
    public function index(KkKelompok $kelompok)
    {
        $kelompok->load(['kelas', 'koordinator']);

        $items = KkKelompokKegiatan::with('kegiatan')
            ->where('kk_kelompok_id', $kelompok->id)
            ->orderByDesc('id')
            ->get();

        $kegiatanList = KkKegiatan::orderByDesc('id')->get();

        return view('admin.kokurikuler.kelompok.kegiatan.index', compact('kelompok', 'items', 'kegiatanList'));
    }

    public function store(Request $request, KkKelompok $kelompok)
    {
        $request->validate([
            'kk_kegiatan_id' => 'required|exists:kk_kegiatan,id',
        ]);

        $sudahAda = KkKelompokKegiatan::where('kk_kelompok_id', $kelompok->id)
            ->where('kk_kegiatan_id', $request->kk_kegiatan_id)
            ->exists();

        if ($sudahAda) {
            return back()->with('success', 'Kegiatan sudah ada di kelompok ini.');
        }

        KkKelompokKegiatan::create([
            'kk_kelompok_id' => $kelompok->id,
            'kk_kegiatan_id' => $request->kk_kegiatan_id,
        ]);

        return back()->with('success', 'Kegiatan berhasil ditambahkan.');
    }

    public function destroy(KkKelompok $kelompok, $pivotId)
    {
        $pivot = KkKelompokKegiatan::where('kk_kelompok_id', $kelompok->id)
            ->where('id', $pivotId)
            ->firstOrFail();

        $pivot->delete();

        return back()->with('success', 'Kegiatan berhasil dihapus dari kelompok.');
    }
}
