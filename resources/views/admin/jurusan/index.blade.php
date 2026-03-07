@extends('layouts.adminlte')

@section('page_title','Data Jurusan')

@section('content')

@php
  $status = request('status', '');
  $limitVal = (int)($limit ?? 10);
@endphp

@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
  <div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
@endif

<div class="card card-dark">
  <div class="card-header">
    <h3 class="card-title">Data Jurusan</h3>
  </div>

  <div class="card-body">

    {{-- TOOLBAR --}}
    <div class="d-flex justify-content-between mb-3">
      <div>
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalTambahJurusan">
          <i class="fas fa-plus"></i> Tambah Jurusan
        </button>
      </div>
      <div>
        <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalFilter">
          <i class="fas fa-filter"></i> Filter Data
        </button>
      </div>
    </div>

    {{-- FILTER BAR --}}
    <form method="GET" action="{{ route('admin.jurusan.index') }}"
          class="d-flex justify-content-between align-items-center mb-2">

      <div>
        <label class="mb-0">
          Tampilkan
          <select name="limit" class="custom-select custom-select-sm w-auto" onchange="this.form.submit()">
            <option value="10"  {{ $limitVal===10 ? 'selected' : '' }}>10</option>
            <option value="25"  {{ $limitVal===25 ? 'selected' : '' }}>25</option>
            <option value="50"  {{ $limitVal===50 ? 'selected' : '' }}>50</option>
            <option value="100" {{ $limitVal===100 ? 'selected' : '' }}>100</option>
          </select>
          data
        </label>

        <input type="hidden" name="status" value="{{ $status }}">
      </div>

      <div class="d-flex">
        <input type="text" name="q" value="{{ $q ?? '' }}"
               class="form-control form-control-sm"
               placeholder="Cari kode / nama jurusan..."
               style="width:260px">
        <button class="btn btn-sm btn-secondary ml-2" type="submit">
          <i class="fas fa-search"></i>
        </button>
      </div>
    </form>

    {{-- TABLE --}}
    <div class="table-responsive">
      <table class="table table-bordered table-hover mb-0">
        <thead>
          <tr>
            <th width="60">No</th>
            <th width="160">Kode</th>
            <th>Nama Jurusan</th>
            <th width="130" class="text-center">Status</th>
            <th width="220">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($jurusan as $i => $j)
            <tr>
              <td>{{ $jurusan->firstItem() + $i }}</td>
              <td>{{ $j->kode_jurusan }}</td>
              <td>{{ $j->nama_jurusan }}</td>
              <td class="text-center">
                <span class="badge {{ $j->status_aktif ? 'badge-success' : 'badge-danger' }}">
                  {{ $j->status_aktif ? 'AKTIF' : 'TIDAK AKTIF' }}
                </span>
              </td>
              <td>
                <button type="button"
                        class="btn btn-warning btn-xs btn-edit"
                        data-toggle="modal"
                        data-target="#modalEditJurusan"
                        data-id="{{ $j->id }}"
                        data-kode="{{ e($j->kode_jurusan) }}"
                        data-nama="{{ e($j->nama_jurusan) }}"
                        data-status="{{ $j->status_aktif ? 1 : 0 }}">
                  <i class="fas fa-edit"></i> Edit
                </button>

                <form action="{{ route('admin.jurusan.destroy',$j->id) }}"
                      method="POST" class="d-inline"
                      onsubmit="return confirm('Hapus jurusan ini?')">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-danger btn-xs">
                    <i class="fas fa-trash"></i> Hapus
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center text-muted">Data jurusan belum tersedia</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- PAGINATION --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mt-3">
      <div class="text-muted small mb-2 mb-md-0">
        Menampilkan {{ $jurusan->count() ? $jurusan->firstItem() : 0 }} - {{ $jurusan->count() ? $jurusan->lastItem() : 0 }}
        dari {{ $jurusan->total() }} data
      </div>
      <div class="d-flex justify-content-end">
        {{ $jurusan->onEachSide(1)->links('pagination::bootstrap-4') }}
      </div>
    </div>

  </div>
</div>

{{-- ================= MODAL FILTER ================= --}}
<div class="modal fade" id="modalFilter" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Filter Data Jurusan</h4>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>

      <form method="GET" action="{{ route('admin.jurusan.index') }}">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <label>Status</label>
              <select name="status" class="form-control">
                <option value=""  {{ $status==='' ? 'selected' : '' }}>Semua</option>
                <option value="1" {{ $status==='1' ? 'selected' : '' }}>AKTIF</option>
                <option value="0" {{ $status==='0' ? 'selected' : '' }}>TIDAK AKTIF</option>
              </select>
            </div>
          </div>

          <hr>
          <input type="hidden" name="limit" value="{{ $limitVal }}">
          <input type="hidden" name="q" value="{{ $q ?? '' }}">
        </div>

        <div class="modal-footer d-flex justify-content-between">
          <a href="{{ route('admin.jurusan.index') }}" class="btn btn-secondary">Reset</a>
          <div>
            <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
            <button type="submit" class="btn btn-primary">Terapkan</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ================= MODAL TAMBAH ================= --}}
<div class="modal fade" id="modalTambahJurusan" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form method="POST" action="{{ route('admin.jurusan.store') }}" class="modal-content">
      @csrf
      <div class="modal-header">
        <h4 class="modal-title">Tambah Jurusan</h4>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>

      <div class="modal-body">

            <div class="form-group">
                <label>Kode Jurusan</label>
                <input type="text"
                    name="kode_jurusan"
                    class="form-control"
                    placeholder="Contoh: TKJ"
                    required>
            </div>

            <div class="form-group">
                <label>Nama Jurusan</label>
                <input type="text"
                    name="nama_jurusan"
                    class="form-control"
                    placeholder="Contoh: Teknik Komputer dan Jaringan"
                    required>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status_aktif" class="form-control" required>
                    <option value="1">AKTIF</option>
                    <option value="0">TIDAK AKTIF</option>
                </select>
            </div>

        </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
        <button class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

{{-- ================= MODAL EDIT ================= --}}
<div class="modal fade" id="modalEditJurusan" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form method="POST" action="#" class="modal-content" id="formEditJurusan">
      @csrf
      @method('PUT')

      <div class="modal-header">
        <h4 class="modal-title">Edit Jurusan</h4>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-md-4">
            <label>Kode Jurusan</label>
            <input type="text" name="kode_jurusan" class="form-control" id="eKode" required>
          </div>
          <div class="col-md-8">
            <label>Nama Jurusan</label>
            <input type="text" name="nama_jurusan" class="form-control" id="eNama" required>
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-md-6">
            <label>Status</label>
            <select name="status_aktif" class="form-control" id="eStatus" required>
              <option value="1">AKTIF</option>
              <option value="0">TIDAK AKTIF</option>
            </select>
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

@endsection

@push('scripts')
<script>
$(function(){
  $('.btn-edit').on('click', function(){
    const id = $(this).data('id');
    const kode = $(this).data('kode');
    const nama = $(this).data('nama');
    const status = $(this).data('status');

    $('#eKode').val(kode);
    $('#eNama').val(nama);
    $('#eStatus').val(status);

    $('#formEditJurusan').attr('action', `{{ url('admin/jurusan') }}/${id}`);
  });
});
</script>
@endpush
