@extends('layouts.adminlte')

@section('title', 'Deskripsi Capaian Kokurikuler')

@section('content')
<div class="container-fluid">

  {{-- HEADER: back + judul --}}
  <div class="d-flex align-items-center mb-3">
    <a href="{{ route('guru.kokurikuler.kegiatan.index', $kelompok->id) }}" class="btn btn-link p-0 mr-2" title="Kembali">
      <i class="fas fa-arrow-left"></i>
    </a>
    <h4 class="mb-0">Deskripsi Capaian Kokurikuler</h4>
  </div>

  {{-- NOTIF INFO (dismissible) --}}
  <div class="alert alert-info alert-dismissible fade show" role="alert">
    Deskripsi capaian akan terisi otomatis berdasarkan input predikat nilai kokurikuler.
    <button type="button" class="close" data-dismiss="alert" aria-label="Tutup">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>

  {{-- INFO KELOMPOK --}}
  <div class="card mb-3">
    <div class="card-body">
      <div class="row">
        <div class="col-md-3 font-weight-bold">Nama Kelompok</div>
        <div class="col-md-9">: {{ $kelompok->nama_kelompok ?? '-' }}</div>

        <div class="col-md-3 font-weight-bold mt-2">Kelas</div>
        <div class="col-md-9 mt-2">: {{ $kelompok->kelas->nama_kelas ?? '-' }}</div>

        <div class="col-md-3 font-weight-bold mt-2">Guru/Koordinator</div>
        <div class="col-md-9 mt-2">: {{ $kelompok->koordinator->nama ?? '-' }}</div>

        <div class="col-md-3 font-weight-bold mt-2">Nama Kegiatan</div>
        <div class="col-md-9 mt-2">: {{ $kegiatan->nama_kegiatan ?? '-' }}</div>
      </div>
    </div>
  </div>

  {{-- BUTTON BAR --}}
  <div class="d-flex justify-content-between align-items-center mb-2">
    <a href="{{ route('guru.kokurikuler.nilai.index', [$kelompok->id, $kegiatan->id]) }}" class="btn btn-warning btn-sm">
      <i class="fas fa-pen"></i> Input Nilai Kokurikuler
    </a>

    {{-- kanan kosong biar mirip layout --}}
    <div></div>
  </div>

  {{-- ALERT --}}
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- FILTER BAR (UI SAJA) --}}
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

  {{-- FORM SIMPAN --}}
  <form method="POST" action="{{ route('guru.kokurikuler.deskripsi.update', [$kelompok->id, $kegiatan->id]) }}">
    @csrf

    <div class="card">
      <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-sm mb-0">
          <thead class="bg-dark text-white">
            <tr>
              <th style="width:60px;">No.</th>
              <th style="width:160px;">NIS</th>
              <th>Nama</th>
              <th style="width:80px;" class="text-center">L/P</th>
              <th style="min-width:360px;">Deskripsi</th>
            </tr>
          </thead>

          <tbody>
            @forelse($anggota as $i => $a)
              @php
                $siswa = $a->siswa;

                // nilaiRows kadang model (keyBy), kadang bisa collection (kalau nanti kamu groupBy)
                $nr = $nilaiRows[$siswa->id] ?? null;
                $nilaiModel = $nr instanceof \Illuminate\Support\Collection ? $nr->first() : $nr;

                $jk = $siswa->jenis_kelamin ?? $siswa->jk ?? $siswa->lp ?? null;
                if ($jk) {
                  $jk = strtoupper(substr($jk, 0, 1)); // L/P
                } else {
                  $jk = '-';
                }
              @endphp

              <tr>
                <td class="text-center align-top">{{ $i+1 }}</td>
                <td class="align-top">{{ $siswa->nis ?? '-' }}</td>
                <td class="align-top">{{ $siswa->nama_siswa ?? '-' }}</td>
                <td class="text-center align-top">{{ $jk }}</td>

                <td class="align-top">
                  <textarea
                    name="deskripsi[{{ $siswa->id }}]"
                    class="form-control form-control-sm"
                    rows="3"
                    style="resize:vertical;"
                    placeholder="Tulis deskripsi..."
                  >{{ old("deskripsi.$siswa->id", $nilaiModel->deskripsi ?? '') }}</textarea>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center text-muted">Belum ada anggota di kelompok ini.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="card-footer clearfix">
          <span class="text-muted small float-left">
            Menampilkan {{ $anggota->count() ? '1 - '.$anggota->count().' dari '.$anggota->count().' data' : '0 data' }}
          </span>

          <button type="submit" class="btn btn-primary float-right px-4">
            Simpan Perubahan
          </button>
      </div>

    </div>
  </form>

</div>
@endsection
