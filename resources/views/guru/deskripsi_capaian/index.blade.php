@extends('layouts.adminlte')

@section('title', 'Kelola Deskripsi Capaian')

@section('content')
<div class="container-fluid">

  {{-- HEADER: tombol kembali + judul --}}
  <div class="d-flex align-items-center mb-3">
    <a href="{{ route('guru.nilai_akhir.index', $pembelajaran->id) }}" class="btn btn-link p-0 mr-2" title="Kembali">
      <i class="fas fa-arrow-left"></i>
    </a>
    <h4 class="mb-0">Kelola Deskripsi Capaian</h4>
  </div>

  {{-- INFO PEMBELAJARAN --}}
  <div class="card mb-3">
    <div class="card-body">
      <div class="row">
        <div class="col-md-3 font-weight-bold">Mata Pelajaran</div>
        <div class="col-md-9">: {{ $pembelajaran->mapel->nama_mapel ?? '-' }}</div>

        <div class="col-md-3 font-weight-bold mt-2">Kelas</div>
        <div class="col-md-9 mt-2">: {{ $pembelajaran->kelas->nama_kelas ?? '-' }}</div>

        <div class="col-md-3 font-weight-bold mt-2">Guru Pengampu</div>
        <div class="col-md-9 mt-2">: {{ $pembelajaran->guru->nama ?? '-' }}</div>

        {{-- @isset($tahunAktif)
          <div class="col-md-3 font-weight-bold mt-2">Tahun Pelajaran</div>
          <div class="col-md-9 mt-2">: {{ $tahunAktif->tahun_pelajaran ?? '-' }} ({{ $semester ?? '-' }})</div>
        @endisset --}}
      </div>
    </div>
  </div>

  {{-- ALERT --}}
  @if (session('success'))
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

  {{-- TOOLBAR: Edit Capaian TP (kembali ke Nilai Akhir) + Search (UI saja) --}}
  <div class="d-flex justify-content-between align-items-center mb-2">
    <a href="{{ route('guru.nilai_akhir.index', $pembelajaran->id) }}" class="btn btn-warning btn-sm">
      <i class="fas fa-pen"></i> Edit Capaian TP
    </a>

    <form action="{{ route('guru.nilai_akhir.applyAverage', $pembelajaran->id) }}"
            method="POST"
            onsubmit="return confirmApplyAverage(this)"
            class="m-0">
        @csrf
        <input type="hidden" name="nilai_rata" id="nilai_rata_input">
        <button type="submit" class="btn btn-info btn-sm">
            <i class="fas fa-calculator"></i> Terapkan Nilai Rata
        </button>
    </form>
  </div>
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div style="width:220px;" class="ml-auto">
        <input type="text" class="form-control form-control-sm" placeholder="Cari...">
        </div>
    </div>
  <form action="{{ route('guru.deskripsi.update', $pembelajaran->id) }}" method="POST">
    @csrf

    <div class="card">
      <div class="card-body table-responsive p-0">

        <table class="table table-bordered table-sm mb-0">
          <thead class="bg-dark text-white">
            <tr>
              <th style="width:60px;" class="text-center">No.</th>
              <th style="width:160px;">NIS</th>
              <th style="width:240px;">Nama Siswa</th>
              <th style="width:110px;" class="text-center">Nilai</th>
              <th>Deskripsi Capaian Tinggi</th>
              <th>Deskripsi Capaian Rendah</th>
            </tr>
          </thead>

          <tbody>
            @forelse ($siswa as $i => $s)
              @php
                $nilai = $nilaiRows[$s->id] ?? null;
              @endphp
              <tr>
                <td class="text-center align-top">{{ $i + 1 }}</td>
                <td class="align-top">{{ $s->nis ?? '-' }}</td>
                <td class="align-top">{{ $s->nama_siswa ?? '-' }}</td>

                <td class="align-top">
                  <input type="number"
                         class="form-control form-control-sm"
                         name="nilai[{{ $s->id }}]"
                         value="{{ old('nilai.'.$s->id, $nilai->nilai_angka ?? '') }}"
                         min="0" max="100">
                </td>

                <td class="align-top">
                  <textarea class="form-control form-control-sm"
                            name="deskripsi_tinggi[{{ $s->id }}]"
                            rows="3"
                            style="resize:vertical;"
                            placeholder="Isi deskripsi capaian tinggi...">{{ old('deskripsi_tinggi.'.$s->id, $nilai->deskripsi_tinggi ?? '') }}</textarea>
                </td>

                <td class="align-top">
                  <textarea class="form-control form-control-sm"
                            name="deskripsi_rendah[{{ $s->id }}]"
                            rows="3"
                            style="resize:vertical;"
                            placeholder="Isi deskripsi capaian rendah...">{{ old('deskripsi_rendah.'.$s->id, $nilai->deskripsi_rendah ?? '') }}</textarea>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center text-muted py-4">Tidak ada siswa pada kelas ini.</td>
              </tr>
            @endforelse
          </tbody>
        </table>

      </div>

      <div class="card-footer d-flex justify-content-end">
        <button type="submit" class="btn btn-primary">
          Simpan Perubahan
        </button>
      </div>
    </div>

  </form>
</div>
@endsection
