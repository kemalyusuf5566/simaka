@extends('layouts.adminlte')
@section('title', 'Data BK')
@section('page_title', 'Data BK')

@section('content')
<div class="container-fluid">
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0 pl-3">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="card mb-3">
    <div class="card-header">
      <h3 class="card-title mb-0">Filter Data BK</h3>
    </div>
    <form method="GET" class="card-body">
      <div class="row">
        <div class="col-md-3 mb-2">
          <label class="small text-muted mb-1">Cari</label>
          <input type="text" name="q" class="form-control form-control-sm" value="{{ $q }}" placeholder="Nama siswa / NIS / kasus">
        </div>
        <div class="col-md-2 mb-2">
          <label class="small text-muted mb-1">Kelas</label>
          <select name="kelas_id" class="form-control form-control-sm">
            <option value="">Semua Kelas</option>
            @foreach($kelasOptions as $k)
              <option value="{{ $k->id }}" {{ (string)$kelasId === (string)$k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2 mb-2">
          <label class="small text-muted mb-1">Status</label>
          <select name="status" class="form-control form-control-sm">
            <option value="">Semua Status</option>
            @foreach($statusOptions as $s)
              <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2 mb-2">
          <label class="small text-muted mb-1">Tanggal Dari</label>
          <input type="date" name="tanggal_dari" class="form-control form-control-sm" value="{{ $tanggalDari }}">
        </div>
        <div class="col-md-2 mb-2">
          <label class="small text-muted mb-1">Tanggal Sampai</label>
          <input type="date" name="tanggal_sampai" class="form-control form-control-sm" value="{{ $tanggalSampai }}">
        </div>
        <div class="col-md-1 mb-2">
          <label class="small text-muted mb-1">Limit</label>
          <select name="limit" class="form-control form-control-sm">
            <option value="10" {{ (int)$limit===10 ? 'selected' : '' }}>10</option>
            <option value="25" {{ (int)$limit===25 ? 'selected' : '' }}>25</option>
            <option value="50" {{ (int)$limit===50 ? 'selected' : '' }}>50</option>
            <option value="100" {{ (int)$limit===100 ? 'selected' : '' }}>100</option>
          </select>
        </div>
      </div>
      <div class="d-flex justify-content-between mt-2">
        <div class="text-muted small">
          Tahun aktif: {{ $tahunAktif?->tahun_pelajaran ?? '-' }} {{ $tahunAktif?->semester ? ('- '.$tahunAktif->semester) : '' }}
        </div>
        <div>
          <a href="{{ route($routeBase.'.index') }}" class="btn btn-sm btn-light">Reset</a>
          <button class="btn btn-sm btn-secondary"><i class="fas fa-search"></i> Terapkan</button>
        </div>
      </div>
    </form>
  </div>

  <div class="card mb-3">
    <div class="card-header">
      <h3 class="card-title mb-0">Tambah Catatan BK</h3>
    </div>
    <form method="POST" action="{{ route($routeBase.'.store') }}" class="card-body">
      @csrf
      <div class="row">
        <div class="col-md-4 mb-2">
          <label class="small text-muted mb-1">Siswa</label>
          <select name="data_siswa_id" class="form-control form-control-sm" required>
            <option value="">Pilih Siswa</option>
            @foreach($siswaOptions as $s)
              <option value="{{ $s->id }}">{{ $s->nama_siswa }} - {{ $s->kelas->nama_kelas ?? '-' }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2 mb-2">
          <label class="small text-muted mb-1">Tanggal</label>
          <input type="date" name="tanggal" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
        </div>
        <div class="col-md-3 mb-2">
          <label class="small text-muted mb-1">Jenis Kasus</label>
          <input type="text" name="jenis_kasus" class="form-control form-control-sm" required>
        </div>
        <div class="col-md-3 mb-2">
          <label class="small text-muted mb-1">Status</label>
          <select name="status" class="form-control form-control-sm" required>
            @foreach($statusOptions as $s)
              <option value="{{ $s }}">{{ $s }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-6 mb-2">
          <label class="small text-muted mb-1">Deskripsi Masalah</label>
          <textarea name="deskripsi_masalah" class="form-control form-control-sm" rows="2" required></textarea>
        </div>
        <div class="col-md-6 mb-2">
          <label class="small text-muted mb-1">Tindak Lanjut</label>
          <textarea name="tindak_lanjut" class="form-control form-control-sm" rows="2"></textarea>
        </div>
      </div>
      <button class="btn btn-sm btn-primary"><i class="fas fa-save"></i> Simpan BK</button>
    </form>
  </div>

  <div class="card">
    <div class="card-body table-responsive p-0">
      <table class="table table-bordered table-sm mb-0">
        <thead class="bg-dark text-white">
          <tr>
            <th style="width:50px;">#</th>
            <th>Nama Siswa</th>
            <th>Kelas</th>
            <th style="width:120px;">Tanggal</th>
            <th>Jenis Kasus</th>
            <th style="width:170px;">Status</th>
            <th style="width:260px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @php $start = ($dataBk->currentPage() - 1) * $dataBk->perPage(); @endphp
          @forelse($dataBk as $i => $row)
            <tr>
              <td>{{ $start + $i + 1 }}</td>
              <td>{{ $row->siswa->nama_siswa ?? '-' }}</td>
              <td>{{ $row->kelas->nama_kelas ?? '-' }}</td>
              <td>{{ optional($row->tanggal)->format('d-m-Y') }}</td>
              <td>{{ $row->jenis_kasus }}</td>
              <td>{{ $row->status }}</td>
              <td>
                <a href="{{ route($routeBase.'.riwayat', $row->data_siswa_id) }}" class="btn btn-info btn-xs">
                  <i class="fas fa-history"></i> Riwayat
                </a>

                @if($canEdit)
                  <button type="button" class="btn btn-warning btn-xs" data-toggle="modal" data-target="#editBk{{ $row->id }}">
                    <i class="fas fa-edit"></i> Edit
                  </button>
                @endif

                @if($canDelete)
                  <form method="POST" action="{{ route($routeBase.'.destroy', $row->id) }}" class="d-inline" onsubmit="return confirm('Hapus catatan BK ini?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger btn-xs"><i class="fas fa-trash"></i> Hapus</button>
                  </form>
                @endif
              </td>
            </tr>

            @if($canEdit)
              <div class="modal fade" id="editBk{{ $row->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                  <div class="modal-content">
                    <form method="POST" action="{{ route($routeBase.'.update', $row->id) }}">
                      @csrf
                      @method('PUT')
                      <div class="modal-header">
                        <h5 class="modal-title">Edit BK - {{ $row->siswa->nama_siswa ?? '-' }}</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                      </div>
                      <div class="modal-body">
                        <div class="row">
                          <div class="col-md-4 mb-2">
                            <label class="small text-muted mb-1">Siswa</label>
                            <select name="data_siswa_id" class="form-control form-control-sm" required>
                              @foreach($siswaOptions as $s)
                                <option value="{{ $s->id }}" {{ (int)$row->data_siswa_id === (int)$s->id ? 'selected' : '' }}>
                                  {{ $s->nama_siswa }} - {{ $s->kelas->nama_kelas ?? '-' }}
                                </option>
                              @endforeach
                            </select>
                          </div>
                          <div class="col-md-2 mb-2">
                            <label class="small text-muted mb-1">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control form-control-sm" value="{{ optional($row->tanggal)->format('Y-m-d') }}" required>
                          </div>
                          <div class="col-md-3 mb-2">
                            <label class="small text-muted mb-1">Jenis Kasus</label>
                            <input type="text" name="jenis_kasus" class="form-control form-control-sm" value="{{ $row->jenis_kasus }}" required>
                          </div>
                          <div class="col-md-3 mb-2">
                            <label class="small text-muted mb-1">Status</label>
                            <select name="status" class="form-control form-control-sm" required>
                              @foreach($statusOptions as $s)
                                <option value="{{ $s }}" {{ $row->status === $s ? 'selected' : '' }}>{{ $s }}</option>
                              @endforeach
                            </select>
                          </div>
                          <div class="col-md-6 mb-2">
                            <label class="small text-muted mb-1">Deskripsi Masalah</label>
                            <textarea name="deskripsi_masalah" class="form-control form-control-sm" rows="3" required>{{ $row->deskripsi_masalah }}</textarea>
                          </div>
                          <div class="col-md-6 mb-2">
                            <label class="small text-muted mb-1">Tindak Lanjut</label>
                            <textarea name="tindak_lanjut" class="form-control form-control-sm" rows="3">{{ $row->tindak_lanjut }}</textarea>
                          </div>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Tutup</button>
                        <button class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Simpan</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            @endif
          @empty
            <tr>
              <td colspan="7" class="text-center text-muted py-4">Data BK belum tersedia.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
      <div class="text-muted small">
        Menampilkan {{ $dataBk->firstItem() ?? 0 }} - {{ $dataBk->lastItem() ?? 0 }} dari {{ $dataBk->total() }} data
      </div>
      <div>{{ $dataBk->links('pagination::bootstrap-4') }}</div>
    </div>
  </div>
</div>
@endsection
