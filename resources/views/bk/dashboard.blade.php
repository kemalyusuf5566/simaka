@extends('layouts.adminlte')
@section('title', 'Dashboard BK')
@section('page_title', 'Dashboard BK')

@section('content')
<div class="row">
  <div class="col-md-4">
    <div class="small-box bg-info">
      <div class="inner">
        <h3>{{ $totalKasus }}</h3>
        <p>Total Catatan BK</p>
      </div>
      <div class="icon"><i class="fas fa-notes-medical"></i></div>
      <a href="{{ route('bk.data-bk.index') }}" class="small-box-footer">Buka Catatan BK <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>

  @foreach($statusCounts as $statusLabel => $count)
    <div class="col-md-4">
      <div class="small-box bg-secondary">
        <div class="inner">
          <h3>{{ $count }}</h3>
          <p>{{ $statusLabel }}</p>
        </div>
        <div class="icon"><i class="fas fa-chart-bar"></i></div>
      </div>
    </div>
  @endforeach
</div>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">Peta Modul BK</h3>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="col-lg-4 col-md-6 mb-3">
        <a href="{{ route('bk.data-bk.index') }}" class="btn btn-outline-primary btn-block text-left">
          <i class="fas fa-book mr-2"></i> Catatan BK
        </a>
      </div>
      <div class="col-lg-4 col-md-6 mb-3">
        <a href="{{ route('bk.sikap.index') }}" class="btn btn-outline-primary btn-block text-left">
          <i class="fas fa-user-check mr-2"></i> Sikap Siswa
        </a>
      </div>
      <div class="col-lg-4 col-md-6 mb-3">
        <a href="{{ route('bk.pelanggaran.index') }}" class="btn btn-outline-primary btn-block text-left">
          <i class="fas fa-exclamation-triangle mr-2"></i> Daftar Pelanggaran
        </a>
      </div>
      <div class="col-lg-4 col-md-6 mb-3">
        <a href="{{ route('bk.pembinaan.index') }}" class="btn btn-outline-primary btn-block text-left">
          <i class="fas fa-hands-helping mr-2"></i> Laporan Pembinaan
        </a>
      </div>
      <div class="col-lg-4 col-md-6 mb-3">
        <a href="{{ route('bk.home-visit.index') }}" class="btn btn-outline-primary btn-block text-left">
          <i class="fas fa-home mr-2"></i> Laporan Home Visit
        </a>
      </div>
      <div class="col-lg-4 col-md-6 mb-3">
        <a href="{{ route('bk.pemanggilan-ortu.index') }}" class="btn btn-outline-primary btn-block text-left">
          <i class="fas fa-users mr-2"></i> Pemanggilan Orang Tua
        </a>
      </div>
      <div class="col-lg-4 col-md-6 mb-3">
        <a href="{{ route('bk.perjanjian-siswa.index') }}" class="btn btn-outline-primary btn-block text-left">
          <i class="fas fa-file-signature mr-2"></i> Perjanjian Siswa
        </a>
      </div>
      <div class="col-lg-4 col-md-6 mb-3">
        <a href="{{ route('bk.peminatan.index') }}" class="btn btn-outline-primary btn-block text-left">
          <i class="fas fa-compass mr-2"></i> Peminatan Siswa
        </a>
      </div>
      <div class="col-lg-4 col-md-6 mb-3">
        <a href="{{ route('bk.absensi-bulanan.index') }}" class="btn btn-outline-primary btn-block text-left">
          <i class="fas fa-calendar-check mr-2"></i> Absensi Bulanan
        </a>
      </div>
      <div class="col-lg-4 col-md-6 mb-3">
        <a href="{{ route('bk.pengunduran-diri.index') }}" class="btn btn-outline-primary btn-block text-left">
          <i class="fas fa-user-times mr-2"></i> Pengunduran Diri
        </a>
      </div>
    </div>
  </div>
</div>
@endsection

