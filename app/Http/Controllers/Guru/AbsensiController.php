<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AbsensiJamSiswa;
use App\Models\DataSiswa;
use App\Models\DataTahunPelajaran;
use App\Models\JadwalPelajaran;
use App\Services\AbsensiSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class AbsensiController extends Controller
{
    public function __construct(private readonly AbsensiSyncService $syncService)
    {
    }

    public function index(Request $request)
    {
        if (!$this->absensiTablesReady()) {
            return back()->with('error', 'Modul Absensi belum siap. Jalankan migrasi database terlebih dahulu.');
        }

        $tanggal = $request->get('tanggal', date('Y-m-d'));
        $hari = $this->hariIndonesiaFromDate($tanggal);

        $tahunAktif = DataTahunPelajaran::where('status_aktif', 1)->first();
        $jadwal = JadwalPelajaran::query()
            ->with(['kelas', 'mapel'])
            ->where('guru_id', Auth::id())
            ->when($tahunAktif, fn($q) => $q->where('data_tahun_pelajaran_id', $tahunAktif->id))
            ->where('hari', $hari)
            ->orderBy('jam_ke')
            ->get();

        return view('guru.absensi.index', compact('jadwal', 'tanggal', 'hari', 'tahunAktif'));
    }

    public function input($jadwalId, Request $request)
    {
        if (!$this->absensiTablesReady()) {
            return back()->with('error', 'Modul Absensi belum siap. Jalankan migrasi database terlebih dahulu.');
        }

        $jadwal = JadwalPelajaran::with(['kelas', 'mapel'])->findOrFail($jadwalId);
        abort_unless((int) $jadwal->guru_id === (int) Auth::id(), 403);

        $tanggal = $request->get('tanggal', date('Y-m-d'));
        $hari = $this->hariIndonesiaFromDate($tanggal);
        abort_unless($hari === $jadwal->hari, 422, 'Tanggal tidak sesuai hari jadwal.');

        $siswa = DataSiswa::where('data_kelas_id', $jadwal->data_kelas_id)
            ->orderBy('nama_siswa')
            ->get();

        $existing = AbsensiJamSiswa::query()
            ->where('tanggal', $tanggal)
            ->where('data_kelas_id', $jadwal->data_kelas_id)
            ->where('jam_ke', $jadwal->jam_ke)
            ->whereIn('data_siswa_id', $siswa->pluck('id'))
            ->get()
            ->keyBy('data_siswa_id');

        return view('guru.absensi.input', compact('jadwal', 'tanggal', 'hari', 'siswa', 'existing'));
    }

    public function store($jadwalId, Request $request)
    {
        if (!$this->absensiTablesReady()) {
            return back()->with('error', 'Modul Absensi belum siap. Jalankan migrasi database terlebih dahulu.');
        }

        $jadwal = JadwalPelajaran::findOrFail($jadwalId);
        abort_unless((int) $jadwal->guru_id === (int) Auth::id(), 403);

        $data = $request->validate([
            'tanggal' => 'required|date',
            'status' => 'required|array',
            'status.*' => 'required|in:H,S,I,A',
            'catatan' => 'nullable|array',
            'catatan.*' => 'nullable|string|max:255',
        ]);

        $hari = $this->hariIndonesiaFromDate($data['tanggal']);
        abort_unless($hari === $jadwal->hari, 422, 'Tanggal tidak sesuai hari jadwal.');

        $tahunAktif = DataTahunPelajaran::where('status_aktif', 1)->firstOrFail();
        $validSiswa = DataSiswa::where('data_kelas_id', $jadwal->data_kelas_id)->pluck('id')->map(fn($id) => (int) $id)->all();
        $lookup = array_flip($validSiswa);

        foreach ($data['status'] as $siswaId => $status) {
            $sid = (int) $siswaId;
            if (!isset($lookup[$sid])) {
                continue;
            }

            AbsensiJamSiswa::updateOrCreate(
                [
                    'tanggal' => $data['tanggal'],
                    'data_siswa_id' => $sid,
                    'jam_ke' => (int) $jadwal->jam_ke,
                ],
                [
                    'data_tahun_pelajaran_id' => $tahunAktif->id,
                    'semester' => $tahunAktif->semester,
                    'data_kelas_id' => (int) $jadwal->data_kelas_id,
                    'data_mapel_id' => (int) $jadwal->data_mapel_id,
                    'guru_id' => (int) Auth::id(),
                    'hari' => $jadwal->hari,
                    'status' => $status,
                    'catatan' => $data['catatan'][$siswaId] ?? null,
                ]
            );
        }

        $this->syncService->syncKelasSemester(
            (int) $jadwal->data_kelas_id,
            (int) $tahunAktif->id,
            (string) $tahunAktif->semester
        );

        return redirect()
            ->route('guru.absensi.input', ['jadwal' => $jadwal->id, 'tanggal' => $data['tanggal']])
            ->with('success', 'Absensi jam pelajaran berhasil disimpan.');
    }

    private function hariIndonesiaFromDate(string $tanggal): string
    {
        $en = date('l', strtotime($tanggal));
        return match ($en) {
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            default => 'Senin',
        };
    }

    private function absensiTablesReady(): bool
    {
        return Schema::hasTable('jam_pelajaran')
            && Schema::hasTable('jadwal_pelajaran')
            && Schema::hasTable('absensi_jam_siswa');
    }
}
