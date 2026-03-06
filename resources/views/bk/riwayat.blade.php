@extends('layouts.adminlte')
@section('title', 'Riwayat BK Siswa')
@section('page_title', 'Riwayat BK Siswa')

@section('content')
<div class="container-fluid">
  <div class="d-flex align-items-center mb-3">
    <a href="{{ route($routeBase.'.index') }}" class="btn btn-link p-0 mr-2">
      <i class="fas fa-arrow-left"></i>
    </a>
    <h4 class="mb-0">Riwayat BK: {{ $siswa->nama_siswa }}</h4>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <div class="row">
        <div class="col-md-2 font-weight-bold">Nama</div>
        <div class="col-md-10">: {{ $siswa->nama_siswa }}</div>
        <div class="col-md-2 font-weight-bold mt-2">NIS</div>
        <div class="col-md-10 mt-2">: {{ $siswa->nis ?? '-' }}</div>
        <div class="col-md-2 font-weight-bold mt-2">Kelas</div>
        <div class="col-md-10 mt-2">: {{ $siswa->kelas->nama_kelas ?? '-' }}</div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body table-responsive p-0">
      <table class="table table-bordered table-sm mb-0">
        <thead class="bg-dark text-white">
          <tr>
            <th style="width:50px;">#</th>
            <th style="width:120px;">Tanggal</th>
            <th>Jenis Kasus</th>
            <th>Deskripsi Masalah</th>
            <th>Tindak Lanjut</th>
            <th style="width:170px;">Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse($riwayat as $i => $r)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td>{{ optional($r->tanggal)->format('d-m-Y') }}</td>
              <td>{{ $r->jenis_kasus }}</td>
              <td>{{ $r->deskripsi_masalah }}</td>
              <td>{{ $r->tindak_lanjut ?: '-' }}</td>
              <td>{{ $r->status }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center text-muted py-4">Belum ada riwayat BK.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
