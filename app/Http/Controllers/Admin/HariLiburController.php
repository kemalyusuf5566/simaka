<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HariLibur;
use Illuminate\Http\Request;

class HariLiburController extends Controller
{
    public function index()
    {
        $data = HariLibur::orderBy('tanggal', 'asc')->get();
        return view('admin.hari-libur.index', compact('data'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => ['required', 'date', 'unique:hari_libur,tanggal'],
            'keterangan' => ['nullable', 'string', 'max:255'],
            'konfirmasi' => ['accepted'], // checkbox “Saya yakin...”
        ], [
            'tanggal.unique' => 'Tanggal ini sudah terdaftar sebagai hari libur.',
            'konfirmasi.accepted' => 'Centang konfirmasi terlebih dahulu.',
        ]);

        HariLibur::create([
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('admin.hari-libur.index')->with('success', 'Hari libur berhasil ditambahkan.');
    }

    public function destroy(HariLibur $hariLibur)
    {
        $hariLibur->delete();
        return redirect()->route('admin.hari-libur.index')->with('success', 'Hari libur berhasil dihapus.');
    }
}