@extends('layouts.adminlte')
@section('title', 'Pengaturan Rekomendasi PKL')
@section('page_title', 'Pengaturan Rekomendasi PKL')

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

<div class="card">
  <div class="card-header">
    <h3 class="card-title">Konfigurasi Perhitungan</h3>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('admin.bk.rekomendasi-pkl.settings.update') }}">
      @csrf
      @method('PUT')

      <h5 class="mb-3">Bobot Komponen (%)</h5>
      <div class="row">
        <div class="col-md-4">
          <label>Kehadiran</label>
          <input type="number" step="0.01" min="0" max="100" name="weight_kehadiran" class="form-control" value="{{ old('weight_kehadiran', $weights['kehadiran']) }}" required>
        </div>
        <div class="col-md-4">
          <label>Sikap</label>
          <input type="number" step="0.01" min="0" max="100" name="weight_sikap" class="form-control" value="{{ old('weight_sikap', $weights['sikap']) }}" required>
        </div>
        <div class="col-md-4">
          <label>Poin BK</label>
          <input type="number" step="0.01" min="0" max="100" name="weight_bk" class="form-control" value="{{ old('weight_bk', $weights['bk']) }}" required>
        </div>
      </div>

      <h5 class="mt-4 mb-3">Batas Grade</h5>
      <div class="row">
        <div class="col-md-3">
          <label>A</label>
          <input type="number" step="0.01" min="0" max="100" name="grade_a" class="form-control" value="{{ old('grade_a', $thresholds['A']) }}" required>
        </div>
        <div class="col-md-3">
          <label>B</label>
          <input type="number" step="0.01" min="0" max="100" name="grade_b" class="form-control" value="{{ old('grade_b', $thresholds['B']) }}" required>
        </div>
        <div class="col-md-3">
          <label>C</label>
          <input type="number" step="0.01" min="0" max="100" name="grade_c" class="form-control" value="{{ old('grade_c', $thresholds['C']) }}" required>
        </div>
        <div class="col-md-3">
          <label>D</label>
          <input type="number" step="0.01" min="0" max="100" name="grade_d" class="form-control" value="{{ old('grade_d', $thresholds['D']) }}" required>
        </div>
      </div>

      <h5 class="mt-4 mb-3">Default Saat Data Kehadiran Kosong</h5>
      <div class="row">
        <div class="col-md-4">
          <label>Skor Default Kehadiran</label>
          <input type="number" step="0.01" min="0" max="100" name="attendance_default" class="form-control" value="{{ old('attendance_default', $attendanceDefault) }}" required>
        </div>
      </div>

      <div class="mt-4 d-flex">
        <button class="btn btn-primary mr-2">
          <i class="fas fa-save"></i> Simpan Pengaturan
        </button>
        <a href="{{ route('admin.bk.rekomendasi-pkl.index') }}" class="btn btn-secondary">Kembali</a>
      </div>
    </form>
  </div>
</div>
@endsection
