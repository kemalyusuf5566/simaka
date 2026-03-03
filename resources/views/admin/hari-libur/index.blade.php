@extends('layouts.adminlte')
@section('title','Data Hari Libur')
@section('page_title','Data Hari Libur')

@section('content')

@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
    </ul>
  </div>
@endif

<div class="card">
  <div class="card-header p-2">
    <ul class="nav nav-pills">
      {{-- <li class="nav-item">
        <a class="nav-link active" href="javascript:void(0)">
          <i class="fas fa-plus"></i> Hari Libur
        </a>
      </li> --}}
      <li class="nav-item">
        <a class="nav-link bg-primary text-white ml-2" href="javascript:void(0)" data-toggle="modal" data-target="#modalTambah">
            <i class="fas fa-plus"></i>Tambah Hari Libur
        </a>
      </li>
    </ul>
  </div>

  <div class="card-body">
    <table id="tblHariLibur" class="table table-bordered table-striped">
      <thead class="bg-dark text-white">
        <tr>
          <th style="width:60px">#</th>
          <th>Tanggal</th>
          <th>Keterangan</th>
          <th style="width:90px" class="text-center">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($data as $i => $row)
          <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ \Carbon\Carbon::parse($row->tanggal)->translatedFormat('l, j F Y') }}</td>
            <td>{{ $row->keterangan }}</td>
            <td class="text-center">
              <form action="{{ route('admin.hari-libur.destroy', $row->id) }}" method="POST"
                    onsubmit="return confirm('Hapus hari libur ini?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger btn-sm" title="Hapus">
                  <i class="fas fa-trash"></i>
                </button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

{{-- MODAL TAMBAH --}}
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Hari Libur</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form action="{{ route('admin.hari-libur.store') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="form-group row">
            <label class="col-md-3 col-form-label">Tanggal</label>
            <div class="col-md-9">
              <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal') }}" required>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-md-3 col-form-label">Keterangan</label>
            <div class="col-md-9">
              <input type="text" name="keterangan" class="form-control"
                     placeholder="Masukkan keterangan" value="{{ old('keterangan') }}">
            </div>
          </div>

          <div class="form-group">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="konfirmasi" name="konfirmasi" value="1" {{ old('konfirmasi') ? 'checked' : '' }}>
              <label class="custom-control-label" for="konfirmasi">
                Saya yakin sudah mengisi dengan benar
              </label>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
  $(function () {
    $('#tblHariLibur').DataTable({
      responsive: true,
      autoWidth: false,
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100],
      ordering: false
    });
  });
</script>
@endpush