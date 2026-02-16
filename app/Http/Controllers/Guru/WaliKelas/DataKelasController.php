<?php

namespace App\Http\Controllers\Guru\WaliKelas;

use App\Http\Controllers\Controller;
use App\Models\DataKelas;
use App\Models\DataSiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DataKelasController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $kelas = DataKelas::withCount('siswa')
            ->where('wali_kelas_id', $user->id)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->paginate(10);

        $namaWali = $user->nama ?? $user->name ?? '-';

        return view('guru.wali_kelas.data_kelas.index', compact('kelas', 'namaWali'));
    }

    public function detail($id)
    {
        $user = Auth::user();

        $kelas = DataKelas::where('wali_kelas_id', $user->id)->findOrFail($id);

        $siswa = DataSiswa::with('kelas')
            ->where('data_kelas_id', $kelas->id)
            ->orderBy('nama_siswa')
            ->get();

        $namaWali = $user->nama ?? $user->name ?? '-';

        return view('guru.wali_kelas.data_kelas.detail', compact('kelas', 'siswa', 'namaWali'));
    }

    private function assertWaliBolehAksesSiswa(DataSiswa $siswa): void
    {
        $user = Auth::user();

        if (! $siswa->data_kelas_id) abort(404);

        $kelas = DataKelas::where('id', $siswa->data_kelas_id)
            ->where('wali_kelas_id', $user->id)
            ->first();

        if (! $kelas) abort(403, 'Anda bukan wali kelas dari siswa ini.');
    }

    public function editSiswa($id)
    {
        $siswa = DataSiswa::with('kelas')->findOrFail($id);
        $this->assertWaliBolehAksesSiswa($siswa);

        // kalau kamu tidak mau wali kelas bisa pindah kelas siswa, nanti kita kunci di blade
        $kelas = DataKelas::orderBy('tingkat')->orderBy('nama_kelas')->get();

        return view('guru.wali_kelas.data_kelas.form_siswa', [
            'mode'  => 'edit',
            'siswa' => $siswa,
            'kelas' => $kelas,
        ]);
    }

    public function updateSiswa(Request $request, $id)
    {
        $siswa = DataSiswa::findOrFail($id);
        $this->assertWaliBolehAksesSiswa($siswa);

        $data = $request->validate([
            'data_kelas_id'         => 'required|exists:data_kelas,id',
            'nama_siswa'            => 'required|string|max:255',
            'nis'                   => 'nullable|string|max:50',
            'nisn'                  => 'nullable|string|max:50',
            'tempat_lahir'          => 'nullable|string|max:100',
            'tanggal_lahir'         => 'nullable|date',
            'jenis_kelamin'         => 'nullable|in:L,P',
            'agama'                 => 'nullable|string|max:50',
            'alamat'                => 'nullable|string',
            'telepon'               => 'nullable|string|max:20',
            'status_siswa'          => 'nullable|string|max:20',
        ]);

        $siswa->update($data);

        return redirect()
            ->route('guru.wali-kelas.data-kelas.detail', $siswa->data_kelas_id)
            ->with('success', 'Data siswa berhasil diperbarui.');
    }
}
