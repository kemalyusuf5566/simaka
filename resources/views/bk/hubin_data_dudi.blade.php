@extends('layouts.adminlte')
@section('title', 'Data DU/DI')
@section('page_title', 'Data DU/DI')

@section('content')
@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
  <div class="alert alert-danger">{{ session('error') }}</div>
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
      <div class="col-md-4">
        <label>Cari</label>
        <input type="text" class="form-control" name="q" value="{{ $q }}" placeholder="Nama instansi / bidang usaha / kontak">
      </div>
      <div class="col-md-3">
        <label>Status</label>
        <select name="status" class="form-control">
          <option value="">Semua</option>
          <option value="aktif" {{ $status === 'aktif' ? 'selected' : '' }}>Aktif</option>
          <option value="nonaktif" {{ $status === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
        </select>
      </div>
      <div class="col-md-2">
        <label>Limit</label>
        <select name="limit" class="form-control">
          <option value="10" {{ (int) $limit === 10 ? 'selected' : '' }}>10</option>
          <option value="20" {{ (int) $limit === 20 ? 'selected' : '' }}>20</option>
          <option value="50" {{ (int) $limit === 50 ? 'selected' : '' }}>50</option>
          <option value="100" {{ (int) $limit === 100 ? 'selected' : '' }}>100</option>
        </select>
      </div>
      <div class="col-md-3 d-flex align-items-end">
        <button class="btn btn-primary mr-2"><i class="fas fa-search"></i> Filter</button>
        <a href="{{ route($routeBase . '.hubin.data-dudi.index') }}" class="btn btn-secondary">Reset</a>
      </div>
    </form>
  </div>
</div>

@if($canManageMaster)
<div class="card mb-3">
  <div class="card-header">
    <h3 class="card-title">Tambah DU/DI</h3>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route($routeBase . '.hubin.data-dudi.store') }}">
      @csrf
      <div class="row">
        <div class="col-md-4">
          <label>Nama Instansi</label>
          <input type="text" name="nama_instansi" class="form-control" required>
        </div>
        <div class="col-md-3">
          <label>Bidang Usaha</label>
          <input type="text" name="bidang_usaha" class="form-control">
        </div>
        <div class="col-md-3">
          <label>Kontak Person</label>
          <input type="text" name="kontak_person" class="form-control">
        </div>
        <div class="col-md-2">
          <label>Telepon</label>
          <input type="text" name="telepon" class="form-control">
        </div>
      </div>
      <div class="row mt-3">
        <div class="col-md-4">
          <label>Email</label>
          <input type="email" name="email" class="form-control">
        </div>
        <div class="col-md-6">
          <label>Alamat</label>
          <input type="text" name="alamat" class="form-control">
        </div>
        <div class="col-md-2 d-flex align-items-center">
          <div class="form-check mt-4">
            <input class="form-check-input" type="checkbox" name="status_aktif" value="1" checked id="statusAktifBaru">
            <label class="form-check-label" for="statusAktifBaru">Aktif</label>
          </div>
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
@endif

<div class="card">
  <div class="card-header">
    <h3 class="card-title">Daftar DU/DI</h3>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <thead>
          <tr>
            <th style="width:60px;">No</th>
            <th>Instansi</th>
            <th>Bidang</th>
            <th>Kontak</th>
            <th>Alamat</th>
            <th style="width:100px;">Status</th>
            @if($canManageMaster)<th style="width:230px;">Aksi</th>@endif
          </tr>
        </thead>
        <tbody>
          @forelse($dudi as $i => $row)
            <tr>
              <td>{{ $dudi->firstItem() + $i }}</td>
              <td>{{ $row->nama_instansi }}</td>
              <td>{{ $row->bidang_usaha ?: '-' }}</td>
              <td>
                {{ $row->kontak_person ?: '-' }}<br>
                <small class="text-muted">{{ $row->telepon ?: '-' }}</small>
              </td>
              <td>{{ $row->alamat ?: '-' }}</td>
              <td>
                <span class="badge {{ $row->status_aktif ? 'badge-success' : 'badge-secondary' }}">
                  {{ $row->status_aktif ? 'Aktif' : 'Nonaktif' }}
                </span>
              </td>
              @if($canManageMaster)
              <td>
                <button class="btn btn-warning btn-xs" data-toggle="modal" data-target="#editDudi{{ $row->id }}">
                  <i class="fas fa-edit"></i> Edit
                </button>
                <form method="POST" action="{{ route($routeBase . '.hubin.data-dudi.destroy', $row->id) }}" class="d-inline" onsubmit="return confirm('Hapus data DU/DI ini?')">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-danger btn-xs"><i class="fas fa-trash"></i> Hapus</button>
                </form>
              </td>
              @endif
            </tr>

            @if($canManageMaster)
            <div class="modal fade" id="editDudi{{ $row->id }}" tabindex="-1">
              <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                  <form method="POST" action="{{ route($routeBase . '.hubin.data-dudi.update', $row->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                      <h4 class="modal-title">Edit DU/DI</h4>
                      <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                      <div class="row">
                        <div class="col-md-6">
                          <label>Nama Instansi</label>
                          <input type="text" name="nama_instansi" class="form-control" value="{{ $row->nama_instansi }}" required>
                        </div>
                        <div class="col-md-6">
                          <label>Bidang Usaha</label>
                          <input type="text" name="bidang_usaha" class="form-control" value="{{ $row->bidang_usaha }}">
                        </div>
                      </div>
                      <div class="row mt-2">
                        <div class="col-md-6">
                          <label>Kontak Person</label>
                          <input type="text" name="kontak_person" class="form-control" value="{{ $row->kontak_person }}">
                        </div>
                        <div class="col-md-6">
                          <label>Telepon</label>
                          <input type="text" name="telepon" class="form-control" value="{{ $row->telepon }}">
                        </div>
                      </div>
                      <div class="row mt-2">
                        <div class="col-md-6">
                          <label>Email</label>
                          <input type="email" name="email" class="form-control" value="{{ $row->email }}">
                        </div>
                        <div class="col-md-6">
                          <label>Alamat</label>
                          <input type="text" name="alamat" class="form-control" value="{{ $row->alamat }}">
                        </div>
                      </div>
                      <div class="form-group mt-2">
                        <label>Catatan</label>
                        <textarea name="catatan" class="form-control" rows="2">{{ $row->catatan }}</textarea>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="status_aktif" value="1" id="statusAktif{{ $row->id }}" {{ $row->status_aktif ? 'checked' : '' }}>
                        <label class="form-check-label" for="statusAktif{{ $row->id }}">Status Aktif</label>
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
            @endif
          @empty
            <tr>
              <td colspan="{{ $canManageMaster ? 7 : 6 }}" class="text-center text-muted">Data DU/DI belum tersedia.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="d-flex justify-content-between align-items-center mt-3">
      <div class="text-muted small">Menampilkan {{ $dudi->firstItem() ?? 0 }} - {{ $dudi->lastItem() ?? 0 }} dari {{ $dudi->total() }} data</div>
      <div>{{ $dudi->onEachSide(1)->links('pagination::bootstrap-4') }}</div>
    </div>
  </div>
</div>
@endsection
