@extends('layouts.adminlte')

@section('page_title','Import Data Guru')

@section('content')
<div class="card card-dark">
  <div class="card-header">
    <h3 class="card-title">Import Data Guru</h3>
  </div>

  <form method="POST" action="{{ route('admin.siswa.import') }}" enctype="multipart/form-data">
    @csrf
    <div class="card-body">
      <div class="alert alert-warning">
        <b>Penting!</b> File yang diunggah harus berupa dokumen Microsoft Excel dengan ekstensi <b>.xlsx</b><br>
        <a href="{{ route('admin.siswa.import.format') }}">Download Format Import</a>
      </div>

      <div class="form-group">
        <input type="file" name="file" class="form-control" accept=".xlsx" required>
      </div>

      <div class="form-group mt-3">
        <label class="mb-0">
          <input type="checkbox" name="yakin" value="1" required>
          Saya yakin sudah mengisi dengan benar
        </label>
      </div>
    </div>

    <div class="card-footer d-flex justify-content-between">
      <a href="{{ route('admin.siswa.index') }}" class="btn btn-secondary">Kembali</a>
      <button class="btn btn-primary">Simpan</button>
    </div>
  </form>
</div>
@endsection
