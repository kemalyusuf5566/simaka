@extends('layouts.adminlte')
@section('title', 'Laporan Home Visit')
@section('page_title', 'Laporan Home Visit')

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
    <div class="col-md-4">
      <div class="small-box bg-secondary">
        <div class="inner">
          <h3>{{ $jumlah }}</h3>
          <p>{{ $label }}</p>
        </div>
        <div class="icon"><i class="fas fa-home"></i></div>
      </div>
    </div>
  @endforeach
</div>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">Data Home Visit</h3>
  </div>
  <div class="card-body">
    <div class="d-flex justify-content-between mb-3">
      <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalTambahHomeVisit">
        <i class="fas fa-plus"></i> Tambah Home Visit
      </button>
      <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalFilterHomeVisit">
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
        <input type="text" name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Cari siswa / tujuan / lokasi..." style="width:260px;">
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
            <th style="width:120px">Kelas</th>
            <th style="width:140px">Tanggal</th>
            <th style="width:180px">Lokasi</th>
            <th>Tujuan Kunjungan</th>
            <th style="width:130px">Status</th>
            <th style="width:220px">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($homeVisits as $i => $row)
            <tr>
              <td>{{ $homeVisits->firstItem() + $i }}</td>
              <td>{{ $row->siswa->nama_siswa ?? '-' }}</td>
              <td>{{ $row->kelas->nama_kelas ?? '-' }}</td>
              <td>{{ optional($row->tanggal_kunjungan)->format('d-m-Y') }}</td>
              <td>{{ $row->lokasi_kunjungan ?: '-' }}</td>
              <td>{{ $row->tujuan_kunjungan }}</td>
              <td>{{ $row->status }}</td>
              <td>
                <button class="btn btn-warning btn-xs" data-toggle="modal" data-target="#modalEditHomeVisit{{ $row->id }}">
                  <i class="fas fa-edit"></i> Edit
                </button>
                <form method="POST" action="{{ route('bk.home-visit.destroy', $row->id) }}" class="d-inline" onsubmit="return confirm('Hapus laporan home visit ini?')">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-danger btn-xs">
                    <i class="fas fa-trash"></i> Hapus
                  </button>
                </form>
              </td>
            </tr>

            <div class="modal fade" id="modalEditHomeVisit{{ $row->id }}" tabindex="-1">
              <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                  <form method="POST" action="{{ route('bk.home-visit.update', $row->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                      <h4 class="modal-title">Edit Home Visit</h4>
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
                          <label>Tanggal</label>
                          <input type="date" name="tanggal_kunjungan" class="form-control" value="{{ optional($row->tanggal_kunjungan)->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3">
                          <label>Lokasi</label>
                          <input type="text" name="lokasi_kunjungan" class="form-control" value="{{ $row->lokasi_kunjungan }}">
                        </div>
                        <div class="col-md-3">
                          <label>Status</label>
                          <select name="status" class="form-control" required>
                            @foreach($statusOptions as $st)
                              <option value="{{ $st }}" {{ $row->status === $st ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                          </select>
                        </div>
                      </div>
                      <div class="row mt-3">
                        <div class="col-md-12">
                          <label>Tujuan Kunjungan</label>
                          <input type="text" name="tujuan_kunjungan" class="form-control" value="{{ $row->tujuan_kunjungan }}" required>
                        </div>
                      </div>
                      <div class="row mt-3">
                        <div class="col-md-6">
                          <label>Hasil Observasi</label>
                          <textarea name="hasil_observasi" class="form-control" rows="3">{{ $row->hasil_observasi }}</textarea>
                        </div>
                        <div class="col-md-6">
                          <label>Tindak Lanjut</label>
                          <textarea name="tindak_lanjut" class="form-control" rows="3">{{ $row->tindak_lanjut }}</textarea>
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
              <td colspan="8" class="text-center text-muted">Data home visit belum tersedia.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3">
      <div class="text-muted small">
        Menampilkan {{ $homeVisits->firstItem() ?? 0 }} - {{ $homeVisits->lastItem() ?? 0 }} dari {{ $homeVisits->total() }} data
      </div>
      <div>{{ $homeVisits->onEachSide(1)->links('pagination::bootstrap-4') }}</div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalTambahHomeVisit" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="{{ route('bk.home-visit.store') }}">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title">Tambah Home Visit</h4>
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
              <label>Tanggal</label>
              <input type="date" name="tanggal_kunjungan" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-3">
              <label>Lokasi</label>
              <input type="text" name="lokasi_kunjungan" class="form-control" placeholder="Alamat / wilayah">
            </div>
            <div class="col-md-3">
              <label>Status</label>
              <select name="status" class="form-control" required>
                @foreach($statusOptions as $st)
                  <option value="{{ $st }}">{{ $st }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="row mt-3">
            <div class="col-md-12">
              <label>Tujuan Kunjungan</label>
              <input type="text" name="tujuan_kunjungan" class="form-control" required>
            </div>
          </div>
          <div class="row mt-3">
            <div class="col-md-6">
              <label>Hasil Observasi</label>
              <textarea name="hasil_observasi" class="form-control" rows="3"></textarea>
            </div>
            <div class="col-md-6">
              <label>Tindak Lanjut</label>
              <textarea name="tindak_lanjut" class="form-control" rows="3"></textarea>
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

<div class="modal fade" id="modalFilterHomeVisit" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="GET">
        <div class="modal-header">
          <h4 class="modal-title">Filter Data Home Visit</h4>
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
          <a href="{{ route('bk.home-visit.index') }}" class="btn btn-secondary">Reset</a>
          <button class="btn btn-primary">Terapkan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

