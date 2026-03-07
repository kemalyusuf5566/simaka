@extends('layouts.adminlte')
@section('title', $modul['title'])
@section('page_title', $modul['title'])

@section('content')
<div class="card">
  <div class="card-header">
    <h3 class="card-title">{{ $modul['title'] }}</h3>
  </div>
  <div class="card-body">
    <p class="text-muted mb-3">{{ $modul['desc'] }}</p>

    <div class="alert alert-info mb-3">
      Modul ini sudah disiapkan di menu. Langkah berikutnya adalah implementasi form, tabel, dan alur validasi.
    </div>

    <h6 class="mb-2">Ruang Lingkup Modul</h6>
    <ul class="mb-0 pl-3">
      @foreach($modul['scope'] as $scope)
        <li>{{ $scope }}</li>
      @endforeach
    </ul>
  </div>
</div>
@endsection

