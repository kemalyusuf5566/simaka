@extends('layouts.adminlte')

@section('title', 'Dashboard')
@section('page_title', 'Sistem Informasi Manajemen SMK (SIMAKA)')

@section('content')
<div class="row">

  {{-- DATA SISWA --}}
  <div class="col-lg-3 col-6">
    <div class="small-box bg-primary">
      <div class="inner">
        <h3>{{ \App\Models\DataSiswa::count() }}</h3>
        <p>Data Siswa</p>
      </div>
      <div class="icon">
        <i class="fas fa-user-graduate"></i>
      </div>
      <a href="{{ route('admin.siswa.index') }}" class="small-box-footer">
        Lihat detail <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>

  {{-- DATA GURU --}}
  <div class="col-lg-3 col-6">
    <div class="small-box bg-danger">
      <div class="inner">
        <h3>{{ \App\Models\DataGuru::count() }}</h3>
        <p>Data Guru</p>
      </div>
      <div class="icon">
        <i class="fas fa-chalkboard-teacher"></i>
      </div>
      <a href="{{ route('admin.guru.index') }}" class="small-box-footer">
        Lihat detail <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>

  {{-- DATA ADMIN --}}
  <div class="col-lg-3 col-6">
    <div class="small-box bg-warning">
      <div class="inner">
        <h3>{{ \App\Models\User::whereHas('peran', fn($q) => $q->where('nama_peran','admin'))->count() }}</h3>
        <p>Data Admin</p>
      </div>
      <div class="icon">
        <i class="fas fa-user-shield"></i>
      </div>
      <a href="{{ route('admin.admin.index') }}" class="small-box-footer">
        Lihat detail <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>

  {{-- DATA KELAS --}}
  <div class="col-lg-3 col-6">
    <div class="small-box bg-success">
      <div class="inner">
        <h3>{{ \App\Models\DataKelas::count() }}</h3>
        <p>Data Kelas</p>
      </div>
      <div class="icon">
        <i class="fas fa-door-open"></i>
      </div>
      <a href="{{ route('admin.kelas.index') }}" class="small-box-footer">
        Lihat detail <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>

  {{-- DATA MAPEL --}}
  <div class="col-lg-3 col-6">
    <div class="small-box bg-danger">
      <div class="inner">
        <h3>{{ \App\Models\DataMapel::count() }}</h3>
        <p>Data Mapel</p>
      </div>
      <div class="icon">
        <i class="fas fa-book"></i>
      </div>
      <a href="{{ route('admin.mapel.index') }}" class="small-box-footer">
        Lihat detail <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>

  {{-- DATA PEMBELAJARAN --}}
  <div class="col-lg-3 col-6">
    <div class="small-box bg-success">
      <div class="inner">
        <h3>{{ \App\Models\DataPembelajaran::count() }}</h3>
        <p>Data Pembelajaran</p>
      </div>
      <div class="icon">
        <i class="fas fa-tasks"></i>
      </div>
      <a href="{{ route('admin.pembelajaran.index') }}" class="small-box-footer">
        Lihat detail <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>

  {{-- DATA EKSTRAKURIKULER --}}
  <div class="col-lg-3 col-6">
    <div class="small-box bg-primary">
      <div class="inner">
        <h3>{{ \App\Models\DataEkstrakurikuler::count() }}</h3>
        <p>Data Ekstrakurikuler</p>
      </div>
      <div class="icon">
        <i class="fas fa-futbol"></i>
      </div>
      <a href="{{ route('admin.ekstrakurikuler.index') }}" class="small-box-footer">
        Lihat detail <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>

  {{-- DATA KEGIATAN KOKURIKULER --}}
  <div class="col-lg-3 col-6">
    <div class="small-box bg-warning">
      <div class="inner">
        <h3>{{ \App\Models\KkKegiatan::count() }}</h3>
        <p>Data Kegiatan</p>
      </div>
      <div class="icon">
        <i class="fas fa-clipboard-list"></i>
      </div>
      <a href="{{ route('admin.kokurikuler.kegiatan.index') }}" class="small-box-footer">
        Lihat detail <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>

</div>
@endsection