<?php

namespace App\Http\Controllers\Guru\WaliKelas;

use App\Http\Controllers\Controller;
use App\Models\DataKelas;
use App\Models\DataSiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DataSiswaController extends Controller
{
    /**
     * Wali kelas hanya boleh edit siswa yang ada di kelas yang dia walikan.
     */
    private function assertWaliBolehAksesSiswa(DataSiswa $siswa): void
    {
        $user = Auth::user();

        // Pastikan siswa punya data_kelas_id
        if (! $siswa->data_kelas_id) {
            abort(404);
        }

        // Pastikan kelas siswa ini adalah kelas wali tersebut
        $kelas = DataKelas::where('id', $siswa->data_kelas_id)
            ->where('wali_kelas_id', $user->id)
            ->first();

        if (! $kelas) {
            abort(403, 'Anda bukan wali kelas dari siswa ini.');
        }
    }

    public function edit($id)
    {
        $siswa = DataSiswa::with('kelas')->findOrFail($id);
        $this->assertWaliBolehAksesSiswa($siswa);

        // untuk dropdown kelas (kalau kamu mau tetap bisa pindah kelas, biarkan)
        // kalau kamu TIDAK mau wali memindah kelas, nanti saya kunci disabled saja di blade.
        $kelas = DataKelas::orderBy('tingkat')->orderBy('nama_kelas')->get();

        return view('guru.wali_kelas.data_siswa.form', [
            'mode'  => 'edit',
            'siswa' => $siswa,
            'kelas' => $kelas,
        ]);
    }

    public function update(Request $request, $id)
    {
        $siswa = DataSiswa::findOrFail($id);
        $this->assertWaliBolehAksesSiswa($siswa);

        $data = $this->validatedData($request);

        $siswa->update($data);

        // balik ke halaman kelola siswa kelasnya (bukan ke admin)
        return redirect()
            ->route('guru.wali-kelas.data-kelas.detail', $siswa->data_kelas_id)
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'data_kelas_id'         => 'required|exists:data_kelas,id',
            'nama_siswa'            => 'required|string|max:255',
            'nis'                   => 'nullable|string|max:50',
            'nisn'                  => 'nullable|string|max:50',
            'tempat_lahir'          => 'nullable|string|max:100',
            'tanggal_lahir'         => 'nullable|date',
            'jenis_kelamin'         => 'nullable|in:L,P',
            'agama'                 => 'nullable|string|max:50',
            'status_dalam_keluarga' => 'nullable|string|max:50',
            'anak_ke'               => 'nullable|integer',
            'alamat'                => 'nullable|string',
            'telepon'               => 'nullable|string|max:20',

            'sekolah_asal'          => 'nullable|string|max:255',
            'diterima_di_kelas'     => 'nullable|string|max:50',
            'tanggal_diterima'      => 'nullable|date',

            'nama_ayah'             => 'nullable|string|max:255',
            'pekerjaan_ayah'        => 'nullable|string|max:255',
            'nama_ibu'              => 'nullable|string|max:255',
            'pekerjaan_ibu'         => 'nullable|string|max:255',
            'alamat_orang_tua'      => 'nullable|string',
            'telepon_orang_tua'     => 'nullable|string|max:20',

            'nama_wali'             => 'nullable|string|max:255',
            'pekerjaan_wali'        => 'nullable|string|max:255',
            'alamat_wali'           => 'nullable|string',
            'telepon_wali'          => 'nullable|string|max:20',

            // status (fallback)
            'status_siswa'          => 'nullable|string|max:20',
        ]);
    }
}
