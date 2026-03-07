@extends('layouts.adminlte')
@section('title', 'Laporan Pengunduran Diri')
@section('page_title', 'Laporan Pengunduran Diri')

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

<div class="row mb-3">
  @foreach($statusCounts as $label => $jumlah)
    <div class="col-md-3">
      <div class="small-box bg-secondary">
        <div class="inner">
          <h3>{{ $jumlah }}</h3>
          <p>{{ $label }}</p>
        </div>
        <div class="icon"><i class="fas fa-user-times"></i></div>
      </div>
    </div>
  @endforeach
</div>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">Data Pengunduran Diri</h3>
  </div>
  <div class="card-body">
    <div class="d-flex justify-content-between mb-3">
      <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalTambahPengunduran">
        <i class="fas fa-plus"></i> Tambah Pengajuan
      </button>
      <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalFilterPengunduran">
        <i class="fas fa-filter"></i> Filter Data
      </button>
    </div>

    <form method="GET" class="d-flex justify-content-between align-items-center mb-2">
      <div>
        <label class="mb-0">
          Tampilkan
          <select name="limit" class="custom-select custom-select-sm w-auto" onchange="this.form.submit()">
            <option value="10" {{ (int)$limit===10 ? 'selected' : '' }}>10</option>
            <option value="25" {{ (int)$limit===25 ? 'selected' : '' }}>25</option>
            <option value="50" {{ (int)$limit===50 ? 'selected' : '' }}>50</option>
            <option value="100" {{ (int)$limit===100 ? 'selected' : '' }}>100</option>
          </select>
          data
        </label>
      </div>
      <div class="d-flex">
        <input type="text" name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Cari siswa / alasan..." style="width:240px;">
        <button class="btn btn-sm btn-secondary ml-2"><i class="fas fa-search"></i></button>
      </div>
      <input type="hidden" name="kelas_id" value="{{ $kelasId }}">
      <input type="hidden" name="status" value="{{ $status }}">
      <input type="hidden" name="tanggal_dari" value="{{ $tanggalDari }}">
      <input type="hidden" name="tanggal_sampai" value="{{ $tanggalSampai }}">
    </form>

    <div class="table-responsive">
      <table class="table table-bordered table-hover mb-0">
        <thead>
          <tr>
            <th style="width:60px">No</th>
            <th>Siswa</th>
            <th style="width:110px">Kelas</th>
            <th style="width:130px">Tgl Pengajuan</th>
            <th style="width:130px">Tgl Efektif</th>
            <th style="width:120px">Status</th>
            <th style="width:220px">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($pengunduran as $i => $row)
            <tr>
              <td>{{ $pengunduran->firstItem() + $i }}</td>
              <td>{{ $row->siswa->nama_siswa ?? '-' }}</td>
              <td>{{ $row->kelas->nama_kelas ?? '-' }}</td>
              <td>{{ optional($row->tanggal_pengajuan)->format('d-m-Y') }}</td>
              <td>{{ optional($row->tanggal_efektif)->format('d-m-Y') ?: '-' }}</td>
              <td>{{ $row->status }}</td>
              <td>
                <button class="btn btn-warning btn-xs" data-toggle="modal" data-target="#modalEditPengunduran{{ $row->id }}">
                  <i class="fas fa-edit"></i> Edit
                </button>
                <form method="POST" action="{{ route('bk.pengunduran-diri.destroy', $row->id) }}" class="d-inline" onsubmit="return confirm('Hapus data pengunduran diri ini?')">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-danger btn-xs">
                    <i class="fas fa-trash"></i> Hapus
                  </button>
                </form>
              </td>
            </tr>

            <div class="modal fade" id="modalEditPengunduran{{ $row->id }}" tabindex="-1">
              <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                  <form method="POST" action="{{ route('bk.pengunduran-diri.update', $row->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                      <h4 class="modal-title">Edit Pengunduran Diri</h4>
                      <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                      <div class="row">
                        <div class="col-md-4">
                          <label>Siswa</label>
                          <select name="data_siswa_id" class="form-control" required>
                            @foreach($siswaOptions as $s)
                              <option value="{{ $s->id }}" {{ (int)$row->data_siswa_id === (int)$s->id ? 'selected' : '' }}>
                                {{ $s->nama_siswa }} - {{ $s->kelas->nama_kelas ?? '-' }}
                              </option>
                            @endforeach
                          </select>
                        </div>
                        <div class="col-md-2">
                          <label>Tgl Pengajuan</label>
                          <input type="date" name="tanggal_pengajuan" class="form-control" value="{{ optional($row->tanggal_pengajuan)->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-2">
                          <label>Tgl Efektif</label>
                          <input type="date" name="tanggal_efektif" class="form-control" value="{{ optional($row->tanggal_efektif)->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                          <label>Status</label>
                          <select name="status" class="form-control" required>
                            @foreach($statusOptions as $st)
                              <option value="{{ $st }}" {{ $row->status === $st ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                          </select>
                        </div>
                      </div>
                      <div class="row mt-3">
                        <div class="col-md-6">
                          <label>Alasan Pengunduran Diri</label>
                          <textarea name="alasan_pengunduran_diri" class="form-control" rows="3" required>{{ $row->alasan_pengunduran_diri }}</textarea>
                        </div>
                        <div class="col-md-6">
                          <label>Keterangan</label>
                          <textarea name="keterangan" class="form-control" rows="3">{{ $row->keterangan }}</textarea>
                        </div>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                      <button class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          @empty
            <tr>
              <td colspan="7" class="text-center text-muted">Data pengunduran diri belum tersedia.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3">
      <div class="text-muted small">
        Menampilkan {{ $pengunduran->firstItem() ?? 0 }} - {{ $pengunduran->lastItem() ?? 0 }} dari {{ $pengunduran->total() }} data
      </div>
      <div>{{ $pengunduran->onEachSide(1)->links('pagination::bootstrap-4') }}</div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalTambahPengunduran" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="{{ route('bk.pengunduran-diri.store') }}">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title">Tambah Pengunduran Diri</h4>
          <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body">
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
            <div class="col-md-2">
              <label>Tgl Pengajuan</label>
              <input type="date" name="tanggal_pengajuan" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-2">
              <label>Tgl Efektif</label>
              <input type="date" name="tanggal_efektif" class="form-control">
            </div>
            <div class="col-md-4">
              <label>Status</label>
              <select name="status" class="form-control" required>
                @foreach($statusOptions as $st)
                  <option value="{{ $st }}">{{ $st }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="row mt-3">
            <div class="col-md-6">
              <label>Alasan Pengunduran Diri</label>
              <textarea name="alasan_pengunduran_diri" class="form-control" rows="3" required></textarea>
            </div>
            <div class="col-md-6">
              <label>Keterangan</label>
              <textarea name="keterangan" class="form-control" rows="3"></textarea>
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

<div class="modal fade" id="modalFilterPengunduran" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="GET">
        <div class="modal-header">
          <h4 class="modal-title">Filter Pengunduran Diri</h4>
          <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <label>Kelas</label>
              <select name="kelas_id" class="form-control">
                <option value="">Semua</option>
                @foreach($kelasOptions as $k)
                  <option value="{{ $k->id }}" {{ (string)$kelasId === (string)$k->id ? 'selected' : '' }}>
                    {{ $k->nama_kelas }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label>Status</label>
              <select name="status" class="form-control">
                <option value="">Semua</option>
                @foreach($statusOptions as $st)
                  <option value="{{ $st }}" {{ $status === $st ? 'selected' : '' }}>{{ $st }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="row mt-3">
            <div class="col-md-6">
              <label>Tanggal Dari</label>
              <input type="date" name="tanggal_dari" class="form-control" value="{{ $tanggalDari }}">
            </div>
            <div class="col-md-6">
              <label>Tanggal Sampai</label>
              <input type="date" name="tanggal_sampai" class="form-control" value="{{ $tanggalSampai }}">
            </div>
          </div>
          <input type="hidden" name="q" value="{{ $q }}">
          <input type="hidden" name="limit" value="{{ $limit }}">
        </div>
        <div class="modal-footer">
          <a href="{{ route('bk.pengunduran-diri.index') }}" class="btn btn-secondary">Reset</a>
          <button class="btn btn-primary">Terapkan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

