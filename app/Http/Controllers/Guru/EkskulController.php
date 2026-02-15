<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\DataEkstrakurikuler;
use App\Models\DataGuru;
use Illuminate\Support\Facades\Auth;

class EkskulController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Ambil kemungkinan ID guru (kalau pembina_id ternyata menyimpan data_guru.id)
        $guruRow = DataGuru::where('pengguna_id', $user->id)->first();
        $guruId  = $guruRow?->id;

        // Ambil data ekskul yang dibina guru ini + hitung jumlah anggota
        $ekskul = DataEkstrakurikuler::query()
            ->withCount('anggota') // => menghasilkan kolom anggota_count
            ->where(function ($q) use ($user, $guruId) {
                $q->where('pembina_id', $user->id);

                if ($guruId) {
                    $q->orWhere('pembina_id', $guruId);
                }
            })
            ->orderByDesc('id')
            ->paginate(10);

        return view('guru.ekskul.index', compact('ekskul'));
    }
}
