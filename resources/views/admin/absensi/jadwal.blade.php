@extends('layouts.adminlte')
@section('title','Jadwal Absensi')

@section('content')
<div class="container-fluid">
  <div class="d-flex align-items-center mb-3">
    <a href="{{ route('admin.absensi.index') }}" class="btn btn-link p-0 mr-2">
      <i class="fas fa-arrow-left"></i>
    </a>
    <h4 class="mb-0">Jadwal Pelajaran Untuk Absensi</h4>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">
      {{ $errors->first() }}
    </div>
  @endif

  <div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <b>Tambah Jadwal</b>
      <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modalImportJadwal">
        <i class="fas fa-file-import"></i> Import Jadwal
      </button>
    </div>
    <form method="POST" action="{{ route('admin.absensi.jadwal.store') }}">
      @csrf
      <div class="card-body">
        <div class="alert alert-info py-2">
          Format jadwal absensi: <b>Senin - Kamis 10 jam pelajaran</b>, <b>Jumat 8 jam pelajaran</b>.
        </div>
        <div class="row">
          <div class="col-md-3">
            <label>Kelas</label>
            <select name="data_kelas_id" class="form-control" required>
              <option value="">-- pilih --</option>
              @foreach($kelas as $k)
                <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label>Mapel</label>
            <select name="data_mapel_id" id="data_mapel_id" class="form-control" required disabled>
              <option value="">Pilih kelas terlebih dahulu</option>
            </select>
          </div>
          <div class="col-md-3">
            <label>Guru</label>
            <select name="guru_id" class="form-control" required>
              <option value="">-- pilih --</option>
              @foreach($guru as $g)
                <option value="{{ $g->id }}">{{ $g->nama }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-1">
            <label>Hari</label>
            <select name="hari" class="form-control" required>
              <option>Senin</option>
              <option>Selasa</option>
              <option>Rabu</option>
              <option>Kamis</option>
              <option>Jumat</option>
            </select>
          </div>
          <div class="col-md-1">
            <label>Jam</label>
            <input type="number" name="jam_ke" class="form-control" min="1" max="10" required>
          </div>
          <div class="col-md-1 d-flex align-items-end">
            <button class="btn btn-primary btn-block">Simpan</button>
          </div>
        </div>
      </div>
    </form>
  </div>

  <div class="card mb-3">
    <div class="card-header">
      <b>Filter & Cek Jadwal</b>
    </div>
    <div class="card-body">
      <form method="GET" action="{{ route('admin.absensi.jadwal') }}">
        <div class="row">
          <div class="col-md-5">
            <label>Cari Jadwal</label>
            <input
              type="text"
              name="q"
              class="form-control"
              value="{{ $q ?? '' }}"
              placeholder="Cari kelas, mapel, guru, hari, atau jam...">
          </div>
          <div class="col-md-7 d-flex align-items-end">
            <button class="btn btn-primary mr-2">
              <i class="fas fa-search"></i> Cari
            </button>
            <button type="submit" name="check" value="1" class="btn btn-success mr-2">
              <i class="fas fa-clipboard-check"></i> Cek Jadwal Terpenuhi
            </button>
            <a href="{{ route('admin.absensi.jadwal') }}" class="btn btn-secondary">
              Reset
            </a>
          </div>
        </div>
      </form>
    </div>
  </div>

  @if($check)
    <div class="card mb-3">
      <div class="card-header">
        <b>Hasil Cek Kelengkapan Jadwal</b>
      </div>
      <div class="card-body">
        @php
          $kelasLengkap = collect($kelengkapanJadwal)->where('lengkap', true)->count();
          $kelasBelumLengkap = collect($kelengkapanJadwal)->where('lengkap', false)->count();
        @endphp

        <div class="mb-3">
          <span class="badge badge-success mr-2">Lengkap: {{ $kelasLengkap }} kelas</span>
          <span class="badge badge-warning">Belum lengkap: {{ $kelasBelumLengkap }} kelas</span>
        </div>

        @if($kelasBelumLengkap === 0)
          <div class="alert alert-success mb-0">
            Semua kelas sudah memenuhi format jadwal absensi.
          </div>
        @else
          <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
              <thead class="bg-light">
                <tr>
                  <th style="width: 60px;">#</th>
                  <th>Kelas</th>
                  <th>Status</th>
                  <th>Terisi</th>
                  <th>Jadwal Yang Belum Terisi</th>
                </tr>
              </thead>
              <tbody>
                @foreach($kelengkapanJadwal as $index => $item)
                  <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item['kelas']->nama_kelas }}</td>
                    <td>
                      @if($item['lengkap'])
                        <span class="badge badge-success">Lengkap</span>
                      @else
                        <span class="badge badge-warning">Belum lengkap</span>
                      @endif
                    </td>
                    <td>{{ $item['total_terisi'] }}/{{ $item['total_wajib'] }}</td>
                    <td>
                      @if($item['lengkap'])
                        <span class="text-success">Semua slot sudah terisi.</span>
                      @else
                        @foreach($item['detail_kosong'] as $detail)
                          <div>
                            <b>{{ $detail['hari'] }}</b>: jam {{ implode(', ', $detail['jam']) }}
                          </div>
                        @endforeach
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </div>
  @endif

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div>
        <b>Daftar Jadwal</b>
        <span class="text-muted">({{ $tahunAktif->tahun_pelajaran }} - {{ $tahunAktif->semester }})</span>
      </div>
      @if(($q ?? '') !== '')
        <small class="text-muted">Hasil pencarian: "{{ $q }}"</small>
      @endif
    </div>
    <div class="card-body table-responsive p-0">
      <table class="table table-bordered table-sm mb-0">
        <thead class="bg-dark text-white">
          <tr>
            <th style="width:50px;">#</th>
            <th>Hari</th>
            <th>Jam</th>
            <th>Kelas</th>
            <th>Mapel</th>
            <th>Guru</th>
            <th style="width:100px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @php $start = ($jadwal->currentPage() - 1) * $jadwal->perPage(); @endphp
          @forelse($jadwal as $i => $j)
            <tr>
              <td>{{ $start + $i + 1 }}</td>
              <td>{{ $j->hari }}</td>
              <td>{{ $j->jam_ke }}</td>
              <td>{{ $j->kelas->nama_kelas ?? '-' }}</td>
              <td>{{ $j->mapel->nama_mapel ?? '-' }}</td>
              <td>{{ $j->guru->nama ?? '-' }}</td>
              <td>
                <form method="POST" action="{{ route('admin.absensi.jadwal.destroy', $j->id) }}" onsubmit="return confirm('Hapus jadwal ini?')">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center text-muted py-4">Jadwal belum ada.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer">{{ $jadwal->links('pagination::bootstrap-4') }}</div>
  </div>
</div>

<div class="modal fade" id="modalImportJadwal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Import Jadwal Pembelajaran Absensi</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form method="POST" action="{{ route('admin.absensi.jadwal.import') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <div class="alert alert-warning">
            File harus <b>.xlsx</b>. Gunakan format ini:
            <a href="{{ route('admin.absensi.jadwal.import.format') }}">Download Format Import</a>
          </div>

          <div class="form-group">
            <label>File XLSX</label>
            <input type="file" name="file" class="form-control" accept=".xlsx" required>
          </div>

          <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="check-import-jadwal" name="yakin" value="1" required>
            <label class="custom-control-label" for="check-import-jadwal">
              Saya yakin data import sudah benar
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
          <button type="submit" class="btn btn-primary">Import</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
  const kelasSelect = document.querySelector('select[name="data_kelas_id"]');
  const mapelSelect = document.getElementById('data_mapel_id');
  const hariSelect = document.querySelector('select[name="hari"]');
  const jamInput = document.querySelector('input[name="jam_ke"]');
  if (!kelasSelect || !mapelSelect) return;

  const oldMapelId = "{{ old('data_mapel_id', '') }}";
  const oldKelasId = "{{ old('data_kelas_id', '') }}";

  function resetMapel(message) {
    mapelSelect.innerHTML = '';
    const opt = document.createElement('option');
    opt.value = '';
    opt.textContent = message;
    mapelSelect.appendChild(opt);
    mapelSelect.disabled = true;
  }

  async function loadMapelByKelas(kelasId) {
    if (!kelasId) {
      resetMapel('Pilih kelas terlebih dahulu');
      return;
    }

    resetMapel('Memuat mapel...');

    try {
      const res = await fetch(`{{ url('admin/pembelajaran/mapel-by-kelas') }}/${kelasId}`, {
        headers: { 'Accept': 'application/json' }
      });

      if (!res.ok) throw new Error('Gagal memuat mapel');
      const data = await res.json();

      mapelSelect.innerHTML = '';
      const first = document.createElement('option');
      first.value = '';
      first.textContent = '-- pilih --';
      mapelSelect.appendChild(first);

      (data || []).forEach(item => {
        const opt = document.createElement('option');
        opt.value = item.id;
        opt.textContent = item.nama;
        if (oldMapelId && String(oldMapelId) === String(item.id)) {
          opt.selected = true;
        }
        mapelSelect.appendChild(opt);
      });

      mapelSelect.disabled = false;

      if ((data || []).length === 0) {
        resetMapel('Mapel untuk kelas ini belum tersedia');
      }
    } catch (e) {
      resetMapel('Gagal memuat mapel');
    }
  }

  kelasSelect.addEventListener('change', function () {
    loadMapelByKelas(this.value);
  });

  if (oldKelasId) {
    kelasSelect.value = oldKelasId;
    loadMapelByKelas(oldKelasId);
  } else {
    resetMapel('Pilih kelas terlebih dahulu');
  }

  function syncJamLimit() {
    if (!hariSelect || !jamInput) return;
    const isJumat = hariSelect.value === 'Jumat';
    jamInput.max = isJumat ? '8' : '10';
    if (isJumat && Number(jamInput.value || 0) > 8) {
      jamInput.value = '8';
    }
  }

  if (hariSelect && jamInput) {
    hariSelect.addEventListener('change', syncJamLimit);
    syncJamLimit();
  }
})();
</script>
@endpush
