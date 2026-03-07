@extends('layouts.adminlte')

@section('page_title','Data Tahun Pelajaran')

@section('content')

{{-- ALERT --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible">
  {{ session('success') }}
</div>
@endif

<div class="card card-dark">
  <div class="card-header">
    <h3 class="card-title">Data Tahun Pelajaran</h3>
  </div>

  <div class="card-body">

    {{-- TOOLBAR ATAS --}}
    <div class="d-flex justify-content-between mb-3">
      <div>
        <button class="btn btn-primary btn-sm"
                data-toggle="modal"
                data-target="#modalCreate">
          <i class="fas fa-plus"></i> Tambah Tahun Pelajaran
        </button>
      </div>
      <div>
        <button class="btn btn-info btn-sm" disabled>
          <i class="fas fa-filter"></i> Filter Data
        </button>
      </div>
    </div>

    {{-- FILTER BAR --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
      <div class="d-flex align-items-center">
        <span class="mr-2 text-muted">Tampilkan</span>
        <select class="form-control form-control-sm" style="width:90px">
          <option>10</option>
          <option>25</option>
          <option>50</option>
          <option>100</option>
        </select>
        <span class="ml-2 text-muted">data</span>
      </div>

      <div>
        <input type="text"
               class="form-control form-control-sm"
               style="width:220px"
               placeholder="Cari...">
      </div>
    </div>

    {{-- TABEL --}}
    <div class="table-responsive">
      <table class="table table-bordered table-striped table-hover mb-0">
        <thead>
          <tr>
            <th style="width:50px">No</th>
            <th>Tahun Pelajaran</th>
            <th style="width:100px">Semester</th>
            <th>Tempat Pembagian Rapor</th>
            <th style="width:160px">Tanggal Pembagian</th>
            <th style="width:160px">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($tahun as $i => $t)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td>
                {{ $t->tahun_pelajaran }}
                @if($t->status_aktif)
                  <span class="badge badge-success ml-2">AKTIF</span>
                @endif
              </td>
              <td>{{ $t->semester }}</td>
              <td>{{ $t->tempat_pembagian_rapor ?? '-' }}</td>
              <td>
                {{ $t->tanggal_pembagian_rapor
                  ? $t->tanggal_pembagian_rapor->translatedFormat('d F Y')
                  : '-' }}
              </td>
              <td>
                <button class="btn btn-warning btn-xs"
                        data-toggle="modal"
                        data-target="#modalEdit{{ $t->id }}">
                  <i class="fas fa-edit"></i> Edit
                </button>

                @if(!$t->status_aktif)
                <form action="{{ route('admin.tahun.aktif',$t->id) }}"
                      method="POST"
                      class="d-inline">
                  @csrf
                  @method('PUT')
                  <button class="btn btn-success btn-xs">
                    Aktifkan
                  </button>
                </form>
                @endif
              </td>
            </tr>

          {{-- MODAL EDIT --}}
          <div class="modal fade" id="modalEdit{{ $t->id }}">
            <div class="modal-dialog modal-lg">
              <div class="modal-content">
                <form method="POST"
                      action="{{ route('admin.tahun.update',$t->id) }}">
                  @csrf
                  @method('PUT')

                  <div class="modal-header">
                    <h5 class="modal-title">Edit Tahun Pelajaran</h5>
                    <button type="button" class="close" data-dismiss="modal">
                      &times;
                    </button>
                  </div>

                  <div class="modal-body">
                    @include('admin.tahun.form', ['data' => $t])
                  </div>

                  <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">
                      Batal
                    </button>
                    <button class="btn btn-primary">
                      Simpan
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          @empty
            <tr>
              <td colspan="6" class="text-center text-muted">
                Belum ada data tahun pelajaran
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

  </div>
</div>

{{-- MODAL CREATE --}}
<div class="modal fade" id="modalCreate">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="{{ route('admin.tahun.store') }}">
        @csrf

        <div class="modal-header">
          <h5 class="modal-title">Tambah Tahun Pelajaran</h5>
          <button type="button" class="close" data-dismiss="modal">
            &times;
          </button>
        </div>

        <div class="modal-body">
          @include('admin.tahun.form', ['data' => null])
        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" data-dismiss="modal">
            Batal
          </button>
          <button class="btn btn-primary">
            Simpan
          </button>
        </div>

      </form>
    </div>
  </div>
</div>

@endsection
