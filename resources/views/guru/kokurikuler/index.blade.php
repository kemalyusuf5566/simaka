@extends('layouts.adminlte')

@section('title', 'Kokurikuler')

@section('content')
<div class="container-fluid">

  {{-- Judul Halaman --}}
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Data Kelompok Kegiatan</h4>

    {{-- Tombol filter (UI saja) --}}
    <button type="button" class="btn btn-info btn-sm">
      <i class="fas fa-filter mr-1"></i> Filter Data
    </button>
  </div>

  <div class="card">
    <div class="card-body">

      {{-- Toolbar: tampilkan + cari (UI saja, biar mirip gambar) --}}
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="d-flex align-items-center">
          <span class="mr-2">Tampilkan</span>
          <select class="form-control form-control-sm" style="width:80px;">
            <option selected>10</option>
            <option>25</option>
            <option>50</option>
            <option>100</option>
          </select>
          <span class="ml-2">data</span>
        </div>

        <div style="width:220px;">
          <input type="text" class="form-control form-control-sm" placeholder="Cari...">
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-bordered table-hover table-sm mb-0">
          <thead class="bg-dark text-white">
            <tr>
              <th style="width:60px;" class="text-center">No.</th>
              <th style="width:200px;">Nama Kelompok Kegiatan</th>
              <th style="width:120px;">Kelas</th>
              <th style="width:150px;">Koordinator</th>
              <th style="width:260px;">Aksi</th>
            </tr>
          </thead>

          <tbody>
            @forelse ($kelompok as $i => $row)
              <tr>
                <td class="text-center align-middle">{{ $i+1 }}</td>
                <td class="align-middle">{{ $row->nama_kelompok }}</td>
                <td class="align-middle">{{ $row->kelas->nama_kelas ?? '-' }}</td>
                <td class="align-middle">{{ $row->koordinator->nama ?? '-' }}</td>

                <td class="align-middle">
                  <div class="d-flex flex-wrap" style="gap:8px;">
                    <a href="{{ route('guru.kokurikuler.anggota.index', $row->id) }}"
                       class="btn btn-info btn-sm">
                      <i class="fas fa-cog mr-1"></i> Anggota Kelompok
                    </a>

                    <a href="{{ route('guru.kokurikuler.kegiatan.index', $row->id) }}"
                       class="btn btn-success btn-sm">
                      <i class="fas fa-cog mr-1"></i> Kelola Kegiatan &amp; Input Nilai
                    </a>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center text-muted py-3">
                  Tidak ada kelompok yang Anda koordinatori.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Footer tabel (UI saja) --}}
      <div class="d-flex justify-content-between align-items-center mt-2">
        <div class="text-muted">
          Menampilkan {{ $kelompok->count() > 0 ? '1 - '.$kelompok->count() : '0' }} dari {{ $kelompok->count() }} data
        </div>

        <div class="d-flex align-items-center" style="gap:6px;">
          <button class="btn btn-light btn-sm" type="button">&laquo;</button>
          <button class="btn btn-primary btn-sm" type="button">1</button>
          <button class="btn btn-light btn-sm" type="button">&raquo;</button>
        </div>
      </div>

    </div>
  </div>

</div>
@endsection
