@extends('layouts.adminlte')
@section('title','Absensi Mapel')

@section('content')
<div class="container-fluid">
  <div class="d-flex align-items-center mb-3">
    <h4 class="mb-0">Absensi Mapel</h4>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="card mb-3">
    <div class="card-body">
      <form method="GET" class="row">
        <div class="col-md-4">
          <label>Tanggal</label>
          <input type="date" name="tanggal" class="form-control" value="{{ $tanggal }}">
        </div>
        <div class="col-md-3">
          <label>Hari</label>
          <input type="text" class="form-control" value="{{ $hari }}" readonly>
        </div>
        <div class="col-md-3">
          <label>Tahun Pelajaran Aktif</label>
          <input type="text" class="form-control" value="{{ $tahunAktif?->tahun_pelajaran }} - {{ $tahunAktif?->semester }}" readonly>
        </div>
        <div class="col-md-2 d-flex align-items-end">
          <button class="btn btn-primary btn-block">Tampilkan</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-body table-responsive p-0">
      <table class="table table-bordered table-sm mb-0">
        <thead class="bg-dark text-white">
          <tr>
            <th style="width:70px;">Jam</th>
            <th>Kelas</th>
            <th>Mapel</th>
            <th style="width:160px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($jadwal as $j)
            <tr>
              <td>{{ $j->jam_ke }}</td>
              <td>{{ $j->kelas->nama_kelas ?? '-' }}</td>
              <td>{{ $j->mapel->nama_mapel ?? '-' }}</td>
              <td>
                <a href="{{ route('guru.absensi.input', ['jadwal' => $j->id, 'tanggal' => $tanggal]) }}" class="btn btn-success btn-sm">
                  <i class="fas fa-edit"></i> Input
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="text-center text-muted py-4">Tidak ada jadwal untuk tanggal ini.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

