<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataJurusan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DataJurusanController extends Controller
{
    private function assertAdmin()
    {
        abort_unless(Auth::user()?->peran?->nama_peran === 'admin', 403);
    }

    public function index(Request $request)
    {
        $this->assertAdmin();

        $limit = (int) $request->get('limit', 10);
        if (!in_array($limit, [10, 25, 50, 100])) $limit = 10;

        $q = trim((string) $request->get('q', ''));

        // status: '' | '1' | '0'
        $status = $request->get('status', '');
        if (!in_array((string)$status, ['', '1', '0'], true)) $status = '';

        $jurusan = DataJurusan::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('kode_jurusan', 'like', "%{$q}%")
                    ->orWhere('nama_jurusan', 'like', "%{$q}%");
            })
            ->when($status !== '', function ($query) use ($status) {
                $query->where('status_aktif', (int)$status);
            })
            ->orderBy('nama_jurusan')
            ->paginate($limit)
            ->appends([
                'limit' => $limit,
                'q' => $q,
                'status' => $status,
            ]);

        return view('admin.jurusan.index', compact('jurusan', 'limit', 'q', 'status'));
    }

    public function store(Request $request)
    {
        $this->assertAdmin();

        $data = $request->validate([
            'kode_jurusan' => 'required|string|max:30|unique:data_jurusan,kode_jurusan',
            'nama_jurusan' => 'required|string|max:255',
            'status_aktif' => 'required|in:1,0',
        ]);

        DataJurusan::create([
            'kode_jurusan' => strtoupper(trim($data['kode_jurusan'])),
            'nama_jurusan' => trim($data['nama_jurusan']),
            'status_aktif' => (bool)$data['status_aktif'],
        ]);

        return back()->with('success', 'Jurusan berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $this->assertAdmin();

        $jurusan = DataJurusan::findOrFail($id);

        $data = $request->validate([
            'kode_jurusan' => 'required|string|max:30|unique:data_jurusan,kode_jurusan,' . $jurusan->id,
            'nama_jurusan' => 'required|string|max:255',
            'status_aktif' => 'required|in:1,0',
        ]);

        $jurusan->update([
            'kode_jurusan' => strtoupper(trim($data['kode_jurusan'])),
            'nama_jurusan' => trim($data['nama_jurusan']),
            'status_aktif' => (bool)$data['status_aktif'],
        ]);

        return back()->with('success', 'Jurusan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $this->assertAdmin();

        DataJurusan::findOrFail($id)->delete();

        return back()->with('success', 'Jurusan berhasil dihapus.');
    }
}
