@extends('layouts.adminlte')

@section('page_title','Cetak Rapor')

@section('content')

<div class="card card-dark">
  <div class="card-header">
    <h3 class="card-title">Cetak Rapor {{ $kelas->nama_kelas ?? '' }}</h3>
    <div class="card-tools">
      <a href="{{ route('guru.wali-kelas.rapor.cetak.index') ?? '/guru/wali-kelas/rapor/cetak' }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Kembali
      </a>
    </div>
  </div>

  <div class="card-body">

    {{-- INFO KELAS --}}
    <table class="table table-bordered mb-4">
      <tr>
        <td width="200"><strong>Nama Kelas</strong></td>
        <td>: {{ $kelas->nama_kelas ?? '-' }}</td>
      </tr>
      <tr>
        <td><strong>Wali Kelas</strong></td>
        <td>: {{ $kelas->waliKelas->nama ?? ($kelas->wali?->pengguna?->nama ?? '-') }}</td>
      </tr>
      <tr>
        <td><strong>Tahun Pelajaran</strong></td>
        <td>: {{ $tahun->tahun_pelajaran ?? '-' }}</td>
      </tr>
      <tr>
        <td><strong>Semester</strong></td>
        <td>: {{ $semester ?? '-' }}</td>
      </tr>
    </table>

    {{-- TABEL SISWA --}}
    <table class="table table-bordered table-striped">
      <thead class="bg-secondary">
        <tr>
          <th width="50">No</th>
          <th>NIS / NISN</th>
          <th>Nama Siswa</th>
          <th class="text-center" width="80">L/P</th>
          <th class="text-center" width="260">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($siswa as $i => $s)
          <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $s->nis ?? '-' }} / {{ $s->nisn ?? '-' }}</td>
            <td>{{ $s->nama_siswa }}</td>
            <td class="text-center">{{ $s->jenis_kelamin ?? '-' }}</td>
            <td class="text-center">

              {{-- KELENGKAPAN RAPOR --}}
              <a href="{{ route('guru.wali-kelas.rapor.pdf.kelengkapan', $s->id) }}"
                 class="btn btn-info btn-xs" target="_blank">
                <i class="fas fa-file-pdf"></i> Kelengkapan
              </a>

              {{-- RAPOR SEMESTER --}}
              <a href="{{ route('guru.wali-kelas.rapor.pdf.semester', $s->id) }}"
                 class="btn btn-success btn-xs" target="_blank">
                <i class="fas fa-print"></i> Rapor
              </a>

            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="text-center text-muted">Data siswa belum tersedia</td>
          </tr>
        @endforelse
      </tbody>
    </table>

  </div>
</div>

@endsection
