@extends('layouts.adminlte')
@section('title', 'Penempatan PKL')
@section('page_title', 'Penempatan PKL')

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
        <input type="text" class="form-control" name="q" value="{{ $q }}" placeholder="Siswa / DU-DI">
      </div>
      <div class="col-md-2">
        <label>Kelas</label>
        <select name="kelas_id" class="form-control">
          <option value="">Semua</option>
          @foreach($kelasOptions as $k)
            <option value="{{ $k->id }}" {{ (string)$kelasId === (string)$k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <label>DU/DI</label>
        <select name="dudi_id" class="form-control">
          <option value="">Semua</option>
          @foreach($dudiOptions as $d)
            <option value="{{ $d->id }}" {{ (string)$dudiId === (string)$d->id ? 'selected' : '' }}>{{ $d->nama_instansi }}</option>
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
      <div class="col-md-2 d-flex align-items-end">
        <button class="btn btn-primary mr-2">Filter</button>
        <a href="{{ route($routeBase . '.hubin.penempatan-pkl.index') }}" class="btn btn-secondary">Reset</a>
      </div>
    </form>
  </div>
</div>

<div class="card mb-3">
  <div class="card-header">
    <h3 class="card-title">Tambah / Update Penempatan</h3>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route($routeBase . '.hubin.penempatan-pkl.store') }}">
      @csrf
      <div class="row">
        <div class="col-md-4">
          <label>Siswa</label>
          <select name="data_siswa_id" class="form-control" required>
            <option value="">Pilih Siswa</option>
            @foreach($siswaOptions as $s)
              <option value="{{ $s->id }}">{{ $s->nama_siswa }} - {{ $s->kelas->nama_kelas ?? '-' }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4">
          <label>DU/DI</label>
          <select name="hubin_dudi_id" class="form-control" required>
            <option value="">Pilih DU/DI</option>
            @foreach($dudiOptions as $d)
              <option value="{{ $d->id }}">{{ $d->nama_instansi }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label>Tanggal Mulai</label>
          <input type="date" name="tanggal_mulai" class="form-control">
        </div>
        <div class="col-md-2">
          <label>Tanggal Selesai</label>
          <input type="date" name="tanggal_selesai" class="form-control">
        </div>
      </div>
      <div class="row mt-3">
        <div class="col-md-3">
          <label>Status</label>
          <select name="status_penempatan" class="form-control" required>
            @foreach($statusOptions as $st)
              <option value="{{ $st }}">{{ $st }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-9">
          <label>Catatan</label>
          <input type="text" name="catatan" class="form-control">
        </div>
      </div>
      <div class="mt-3">
        <button class="btn btn-success"><i class="fas fa-save"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">Daftar Penempatan PKL</h3>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <thead>
          <tr>
            <th style="width:60px;">No</th>
            <th>Siswa</th>
            <th>Kelas</th>
            <th>DU/DI</th>
            <th style="width:110px;">Mulai</th>
            <th style="width:110px;">Selesai</th>
            <th style="width:120px;">Status</th>
            <th style="width:240px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($penempatan as $i => $row)
            <tr>
              <td>{{ $penempatan->firstItem() + $i }}</td>
              <td>{{ $row->siswa->nama_siswa ?? '-' }}</td>
              <td>{{ $row->kelas->nama_kelas ?? '-' }}</td>
              <td>{{ $row->dudi->nama_instansi ?? '-' }}</td>
              <td>{{ optional($row->tanggal_mulai)->format('d-m-Y') ?: '-' }}</td>
              <td>{{ optional($row->tanggal_selesai)->format('d-m-Y') ?: '-' }}</td>
              <td>{{ $row->status_penempatan }}</td>
              <td>
                <button class="btn btn-warning btn-xs" data-toggle="modal" data-target="#editPenempatan{{ $row->id }}">
                  <i class="fas fa-edit"></i> Edit
                </button>
                @if($routeBase !== 'guru')
                <form method="POST" action="{{ route($routeBase . '.hubin.penempatan-pkl.destroy', $row->id) }}" class="d-inline" onsubmit="return confirm('Hapus data penempatan ini?')">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-danger btn-xs"><i class="fas fa-trash"></i> Hapus</button>
                </form>
                @endif
              </td>
            </tr>

            <div class="modal fade" id="editPenempatan{{ $row->id }}" tabindex="-1">
              <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                  <form method="POST" action="{{ route($routeBase . '.hubin.penempatan-pkl.update', $row->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                      <h4 class="modal-title">Edit Penempatan PKL</h4>
                      <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                      <div class="row">
                        <div class="col-md-6">
                          <label>DU/DI</label>
                          <select name="hubin_dudi_id" class="form-control" required>
                            @foreach($dudiOptions as $d)
                              <option value="{{ $d->id }}" {{ (int)$row->hubin_dudi_id === (int)$d->id ? 'selected' : '' }}>{{ $d->nama_instansi }}</option>
                            @endforeach
                          </select>
                        </div>
                        <div class="col-md-3">
                          <label>Tanggal Mulai</label>
                          <input type="date" name="tanggal_mulai" class="form-control" value="{{ optional($row->tanggal_mulai)->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                          <label>Tanggal Selesai</label>
                          <input type="date" name="tanggal_selesai" class="form-control" value="{{ optional($row->tanggal_selesai)->format('Y-m-d') }}">
                        </div>
                      </div>
                      <div class="row mt-2">
                        <div class="col-md-4">
                          <label>Status</label>
                          <select name="status_penempatan" class="form-control" required>
                            @foreach($statusOptions as $st)
                              <option value="{{ $st }}" {{ $row->status_penempatan === $st ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                          </select>
                        </div>
                        <div class="col-md-8">
                          <label>Catatan</label>
                          <input type="text" name="catatan" class="form-control" value="{{ $row->catatan }}">
                        </div>
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
              <td colspan="8" class="text-center text-muted">Data penempatan PKL belum tersedia.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3">
      <div class="text-muted small">Menampilkan {{ $penempatan->firstItem() ?? 0 }} - {{ $penempatan->lastItem() ?? 0 }} dari {{ $penempatan->total() }} data</div>
      <div>{{ $penempatan->onEachSide(1)->links('pagination::bootstrap-4') }}</div>
    </div>
  </div>
</div>
@endsection
