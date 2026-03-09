@extends('layouts.adminlte')
@section('title', 'Monitoring PKL')
@section('page_title', 'Monitoring PKL')

@section('content')
@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0 pl-3">
      @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<div class="card mb-3">
  <div class="card-body">
    <form method="GET" class="row">
      <div class="col-md-3">
        <label>Cari</label>
        <input type="text" class="form-control" name="q" value="{{ $q }}" placeholder="Topik / catatan / siswa">
      </div>
      <div class="col-md-4">
        <label>Penempatan</label>
        <select name="penempatan_id" class="form-control">
          <option value="">Semua</option>
          @foreach($penempatanOptions as $p)
            <option value="{{ $p->id }}" {{ (string)$penempatanId === (string)$p->id ? 'selected' : '' }}>
              {{ $p->siswa->nama_siswa ?? '-' }} - {{ $p->dudi->nama_instansi ?? '-' }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <label>Status</label>
        <select name="status" class="form-control">
          <option value="">Semua</option>
          @foreach($statusOptions as $st)
            <option value="{{ $st }}" {{ $status === $st ? 'selected' : '' }}>{{ $st }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3 d-flex align-items-end">
        <button class="btn btn-primary mr-2">Filter</button>
        <a href="{{ route($routeBase . '.hubin.monitoring-pkl.index') }}" class="btn btn-secondary">Reset</a>
      </div>
    </form>
  </div>
</div>

<div class="card mb-3">
  <div class="card-header">
    <h3 class="card-title">Tambah Log Monitoring</h3>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route($routeBase . '.hubin.monitoring-pkl.store') }}">
      @csrf
      <div class="row">
        <div class="col-md-5">
          <label>Penempatan</label>
          <select name="hubin_penempatan_pkl_id" class="form-control" required>
            <option value="">Pilih Penempatan</option>
            @foreach($penempatanOptions as $p)
              <option value="{{ $p->id }}">{{ $p->siswa->nama_siswa ?? '-' }} - {{ $p->dudi->nama_instansi ?? '-' }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label>Tanggal</label>
          <input type="date" name="tanggal_monitoring" class="form-control" value="{{ date('Y-m-d') }}" required>
        </div>
        <div class="col-md-3">
          <label>Status</label>
          <select name="status_monitoring" class="form-control" required>
            @foreach($statusOptions as $st)
              <option value="{{ $st }}">{{ $st }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label>Skor</label>
          <input type="number" name="skor_kinerja" min="0" max="100" class="form-control">
        </div>
      </div>
      <div class="row mt-3">
        <div class="col-md-6">
          <label>Topik Monitoring</label>
          <input type="text" name="topik_monitoring" class="form-control">
        </div>
        <div class="col-md-6">
          <label>Tindak Lanjut</label>
          <input type="text" name="tindak_lanjut" class="form-control">
        </div>
      </div>
      <div class="form-group mt-3 mb-0">
        <label>Catatan</label>
        <textarea name="catatan" class="form-control" rows="2"></textarea>
      </div>
      <div class="mt-3">
        <button class="btn btn-success"><i class="fas fa-plus"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">Riwayat Monitoring PKL</h3>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <thead>
          <tr>
            <th style="width:60px;">No</th>
            <th>Tanggal</th>
            <th>Siswa</th>
            <th>DU/DI</th>
            <th>Topik</th>
            <th>Status</th>
            <th>Skor</th>
            <th style="width:230px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($logs as $i => $row)
            <tr>
              <td>{{ $logs->firstItem() + $i }}</td>
              <td>{{ optional($row->tanggal_monitoring)->format('d-m-Y') }}</td>
              <td>{{ $row->penempatan->siswa->nama_siswa ?? '-' }}</td>
              <td>{{ $row->penempatan->dudi->nama_instansi ?? '-' }}</td>
              <td>{{ $row->topik_monitoring ?: '-' }}</td>
              <td>{{ $row->status_monitoring }}</td>
              <td>{{ is_null($row->skor_kinerja) ? '-' : $row->skor_kinerja }}</td>
              <td>
                <button class="btn btn-warning btn-xs" data-toggle="modal" data-target="#editLog{{ $row->id }}">
                  <i class="fas fa-edit"></i> Edit
                </button>
                @if($canDelete)
                <form method="POST" action="{{ route($routeBase . '.hubin.monitoring-pkl.destroy', $row->id) }}" class="d-inline" onsubmit="return confirm('Hapus log monitoring ini?')">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-danger btn-xs"><i class="fas fa-trash"></i> Hapus</button>
                </form>
                @endif
              </td>
            </tr>

            <div class="modal fade" id="editLog{{ $row->id }}" tabindex="-1">
              <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                  <form method="POST" action="{{ route($routeBase . '.hubin.monitoring-pkl.update', $row->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                      <h4 class="modal-title">Edit Log Monitoring</h4>
                      <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                      <div class="row">
                        <div class="col-md-4">
                          <label>Tanggal</label>
                          <input type="date" name="tanggal_monitoring" class="form-control" value="{{ optional($row->tanggal_monitoring)->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4">
                          <label>Status</label>
                          <select name="status_monitoring" class="form-control" required>
                            @foreach($statusOptions as $st)
                              <option value="{{ $st }}" {{ $row->status_monitoring === $st ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                          </select>
                        </div>
                        <div class="col-md-4">
                          <label>Skor</label>
                          <input type="number" name="skor_kinerja" min="0" max="100" class="form-control" value="{{ $row->skor_kinerja }}">
                        </div>
                      </div>
                      <div class="row mt-2">
                        <div class="col-md-6">
                          <label>Topik</label>
                          <input type="text" name="topik_monitoring" class="form-control" value="{{ $row->topik_monitoring }}">
                        </div>
                        <div class="col-md-6">
                          <label>Tindak Lanjut</label>
                          <input type="text" name="tindak_lanjut" class="form-control" value="{{ $row->tindak_lanjut }}">
                        </div>
                      </div>
                      <div class="form-group mt-2 mb-0">
                        <label>Catatan</label>
                        <textarea name="catatan" class="form-control" rows="2">{{ $row->catatan }}</textarea>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                      <button class="btn btn-primary">Simpan</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          @empty
            <tr>
              <td colspan="8" class="text-center text-muted">Log monitoring PKL belum tersedia.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3">
      <div class="text-muted small">Menampilkan {{ $logs->firstItem() ?? 0 }} - {{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} data</div>
      <div>{{ $logs->onEachSide(1)->links('pagination::bootstrap-4') }}</div>
    </div>
  </div>
</div>
@endsection
