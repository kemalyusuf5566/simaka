@extends('layouts.adminlte')

@section('page_title', $mode === 'edit' ? 'Edit Jurusan' : 'Tambah Jurusan')

@section('content')
<div class="card card-dark">
  <div class="card-header">
    <h3 class="card-title">{{ $mode === 'edit' ? 'Edit Jurusan' : 'Tambah Jurusan' }}</h3>
  </div>

  <form method="POST"
        action="{{ $mode === 'edit' ? route('admin.jurusan.update',$jurusan->id) : route('admin.jurusan.store') }}">
    @csrf
    @if($mode === 'edit') @method('PUT') @endif

    <div class="card-body">

      <div class="form-group">
        <label>Kode Jurusan</label>
        <input type="text" name="kode_jurusan" class="form-control"
               value="{{ old('kode_jurusan', $jurusan->kode_jurusan ?? '') }}"
               placeholder="Contoh: TKJ" required>
      </div>

      <div class="form-group">
        <label>Nama Jurusan</label>
        <input type="text" name="nama_jurusan" class="form-control"
               value="{{ old('nama_jurusan', $jurusan->nama_jurusan ?? '') }}"
               placeholder="Contoh: Teknik Komputer dan Jaringan" required>
      </div>

      <div class="form-group">
        <label>Status</label>
        <select name="status_aktif" class="form-control" required>
          @php $st = old('status_aktif', isset($jurusan) ? (int)$jurusan->status_aktif : 1); @endphp
          <option value="1" {{ (string)$st==='1' ? 'selected' : '' }}>AKTIF</option>
          <option value="0" {{ (string)$st==='0' ? 'selected' : '' }}>TIDAK AKTIF</option>
        </select>
      </div>

    </div>

    <div class="card-footer d-flex justify-content-between">
      <a href="{{ route('admin.jurusan.index') }}" class="btn btn-secondary">Kembali</a>
      <button class="btn btn-primary">Simpan</button>
    </div>
  </form>
</div>
@endsection