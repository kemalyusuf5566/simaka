@extends('layouts.adminlte')
@section('title', 'Perjanjian Siswa')
@section('page_title', 'Perjanjian Siswa')

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
        <div class="icon"><i class="fas fa-file-signature"></i></div>
      </div>
    </div>
  @endforeach
</div>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">Data Perjanjian Siswa</h3>
  </div>
  <div class="card-body">
    <div class="d-flex justify-content-between mb-3">
      <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalTambahPerjanjian">
        <i class="fas fa-plus"></i> Tambah Perjanjian
      </button>
      <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalFilterPerjanjian">
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
        <input type="text" name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Cari siswa / dokumen..." style="width:240px;">
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
            <th style="width:130px">Tanggal</th>
            <th style="width:130px">No Dokumen</th>
            <th style="width:130px">Pihak Ortu</th>
            <th style="width:120px">Status</th>
            <th style="width:220px">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($perjanjian as $i => $row)
            <tr>
              <td>{{ $perjanjian->firstItem() + $i }}</td>
              <td>{{ $row->siswa->nama_siswa ?? '-' }}</td>
              <td>{{ $row->kelas->nama_kelas ?? '-' }}</td>
              <td>{{ optional($row->tanggal_perjanjian)->format('d-m-Y') }}</td>
              <td>{{ $row->nomor_dokumen ?: '-' }}</td>
              <td>{{ $row->pihak_orang_tua ?: '-' }}</td>
              <td>{{ $row->status }}</td>
              <td>
                <button class="btn btn-warning btn-xs" data-toggle="modal" data-target="#modalEditPerjanjian{{ $row->id }}">
                  <i class="fas fa-edit"></i> Edit
                </button>
                <form method="POST" action="{{ route('bk.perjanjian-siswa.destroy', $row->id) }}" class="d-inline" onsubmit="return confirm('Hapus perjanjian ini?')">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-danger btn-xs">
                    <i class="fas fa-trash"></i> Hapus
                  </button>
                </form>
              </td>
            </tr>

            <div class="modal fade" id="modalEditPerjanjian{{ $row->id }}" tabindex="-1">
              <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                  <form method="POST" action="{{ route('bk.perjanjian-siswa.update', $row->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                      <h4 class="modal-title">Edit Perjanjian Siswa</h4>
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
                          <input type="date" name="tanggal_perjanjian" class="form-control" value="{{ optional($row->tanggal_perjanjian)->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-2">
                          <label>No Dokumen</label>
                          <input type="text" name="nomor_dokumen" class="form-control" value="{{ $row->nomor_dokumen }}">
                        </div>
                        <div class="col-md-2">
                          <label>Pihak Ortu</label>
                          <input type="text" name="pihak_orang_tua" class="form-control" value="{{ $row->pihak_orang_tua }}">
                        </div>
                        <div class="col-md-2">
                          <label>Hubungan</label>
                          <input type="text" name="hubungan_orang_tua" class="form-control" value="{{ $row->hubungan_orang_tua }}">
                        </div>
                      </div>
                      <div class="row mt-3">
                        <div class="col-md-3">
                          <label>Status</label>
                          <select name="status" class="form-control" required>
                            @foreach($statusOptions as $st)
                              <option value="{{ $st }}" {{ $row->status === $st ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                          </select>
                        </div>
                        <div class="col-md-3">
                          <label>Tanggal Evaluasi</label>
                          <input type="date" name="tanggal_evaluasi" class="form-control" value="{{ optional($row->tanggal_evaluasi)->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                          <label>Target Perbaikan</label>
                          <textarea name="target_perbaikan" class="form-control" rows="2">{{ $row->target_perbaikan }}</textarea>
                        </div>
                      </div>
                      <div class="row mt-3">
                        <div class="col-md-6">
                          <label>Isi Perjanjian</label>
                          <textarea name="isi_perjanjian" class="form-control" rows="3" required>{{ $row->isi_perjanjian }}</textarea>
                        </div>
                        <div class="col-md-6">
                          <label>Hasil Evaluasi</label>
                          <textarea name="hasil_evaluasi" class="form-control" rows="3">{{ $row->hasil_evaluasi }}</textarea>
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
              <td colspan="8" class="text-center text-muted">Data perjanjian siswa belum tersedia.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3">
      <div class="text-muted small">
        Menampilkan {{ $perjanjian->firstItem() ?? 0 }} - {{ $perjanjian->lastItem() ?? 0 }} dari {{ $perjanjian->total() }} data
      </div>
      <div>{{ $perjanjian->onEachSide(1)->links('pagination::bootstrap-4') }}</div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalTambahPerjanjian" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="{{ route('bk.perjanjian-siswa.store') }}">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title">Tambah Perjanjian Siswa</h4>
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
              <input type="date" name="tanggal_perjanjian" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-2">
              <label>No Dokumen</label>
              <input type="text" name="nomor_dokumen" class="form-control">
            </div>
            <div class="col-md-2">
              <label>Pihak Ortu</label>
              <input type="text" name="pihak_orang_tua" class="form-control">
            </div>
            <div class="col-md-2">
              <label>Hubungan</label>
              <input type="text" name="hubungan_orang_tua" class="form-control">
            </div>
          </div>
          <div class="row mt-3">
            <div class="col-md-3">
              <label>Status</label>
              <select name="status" class="form-control" required>
                @foreach($statusOptions as $st)
                  <option value="{{ $st }}">{{ $st }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <label>Tanggal Evaluasi</label>
              <input type="date" name="tanggal_evaluasi" class="form-control">
            </div>
            <div class="col-md-6">
              <label>Target Perbaikan</label>
              <textarea name="target_perbaikan" class="form-control" rows="2"></textarea>
            </div>
          </div>
          <div class="row mt-3">
            <div class="col-md-6">
              <label>Isi Perjanjian</label>
              <textarea name="isi_perjanjian" class="form-control" rows="3" required></textarea>
            </div>
            <div class="col-md-6">
              <label>Hasil Evaluasi</label>
              <textarea name="hasil_evaluasi" class="form-control" rows="3"></textarea>
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

<div class="modal fade" id="modalFilterPerjanjian" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="GET">
        <div class="modal-header">
          <h4 class="modal-title">Filter Data Perjanjian</h4>
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
          <a href="{{ route('bk.perjanjian-siswa.index') }}" class="btn btn-secondary">Reset</a>
          <button class="btn btn-primary">Terapkan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

