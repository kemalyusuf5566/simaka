@extends('layouts.adminlte')
@section('title', 'Absensi Bulanan')
@section('page_title', 'Absensi Bulanan')

@section('content')
<div class="card mb-3">
  <div class="card-header">
    <h3 class="card-title">Filter Rekap Absensi Bulanan</h3>
  </div>
  <form method="GET" class="card-body">
    <div class="row">
      <div class="col-md-2">
        <label>Bulan</label>
        <select name="bulan" class="form-control" required>
          @for($m = 1; $m <= 12; $m++)
            <option value="{{ $m }}" {{ (int)$bulan === $m ? 'selected' : '' }}>
              {{ date('F', mktime(0, 0, 0, $m, 1)) }}
            </option>
          @endfor
        </select>
      </div>
      <div class="col-md-2">
        <label>Tahun</label>
        <input type="number" name="tahun" class="form-control" value="{{ $tahun }}" min="2020" max="2100" required>
      </div>
      <div class="col-md-3">
        <label>Kelas</label>
        <select name="kelas_id" class="form-control">
          <option value="">Semua Kelas</option>
          @foreach($kelasOptions as $k)
            <option value="{{ $k->id }}" {{ (string)$kelasId === (string)$k->id ? 'selected' : '' }}>
              {{ $k->nama_kelas }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <label>Cari Siswa</label>
        <input type="text" name="q" class="form-control" value="{{ $q }}" placeholder="Nama / NIS / NISN">
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button class="btn btn-primary btn-block">Terapkan</button>
      </div>
    </div>
    <div class="mt-3 text-muted small">
      Periode: {{ $periodeLabel }} ({{ $startDate }} s/d {{ $endDate }}) |
      Tahun Aktif: {{ $tahunAktif?->tahun_pelajaran ?? '-' }} {{ $tahunAktif?->semester ? ('- '.$tahunAktif->semester) : '' }}
    </div>
  </form>
</div>

<div class="row mb-3">
  <div class="col-md-2">
    <div class="small-box bg-secondary">
      <div class="inner"><h3>{{ $ringkasan['total_siswa'] }}</h3><p>Total Siswa</p></div>
    </div>
  </div>
  <div class="col-md-2">
    <div class="small-box bg-success">
      <div class="inner"><h3>{{ $ringkasan['total_hadir'] }}</h3><p>Hadir</p></div>
    </div>
  </div>
  <div class="col-md-2">
    <div class="small-box bg-warning">
      <div class="inner"><h3>{{ $ringkasan['total_sakit'] }}</h3><p>Sakit</p></div>
    </div>
  </div>
  <div class="col-md-2">
    <div class="small-box bg-info">
      <div class="inner"><h3>{{ $ringkasan['total_izin'] }}</h3><p>Izin</p></div>
    </div>
  </div>
  <div class="col-md-2">
    <div class="small-box bg-danger">
      <div class="inner"><h3>{{ $ringkasan['total_alpa'] }}</h3><p>Alpa</p></div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">Rekap Per Siswa (Sumber: Absensi Guru Mapel)</h3>
  </div>
  <div class="card-body table-responsive">
    <table class="table table-bordered table-hover mb-0">
      <thead>
        <tr>
          <th style="width:60px">No</th>
          <th>Nama Siswa</th>
          <th style="width:120px">Kelas</th>
          <th style="width:90px">Hadir</th>
          <th style="width:90px">Sakit</th>
          <th style="width:90px">Izin</th>
          <th style="width:90px">Alpa</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rows as $i => $row)
          <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $row['siswa']->nama_siswa ?? '-' }}</td>
            <td>{{ $row['siswa']->kelas->nama_kelas ?? '-' }}</td>
            <td><span class="badge badge-success">{{ $row['hadir'] }}</span></td>
            <td><span class="badge badge-warning">{{ $row['sakit'] }}</span></td>
            <td><span class="badge badge-info">{{ $row['izin'] }}</span></td>
            <td><span class="badge badge-danger">{{ $row['alpa'] }}</span></td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-center text-muted">Belum ada data absensi pada periode ini.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection

