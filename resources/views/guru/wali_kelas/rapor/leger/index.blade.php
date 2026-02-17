@extends('layouts.adminlte')
@section('title','Leger')

@section('content')
<div class="container-fluid">

  <div class="d-flex align-items-center mb-3">
    <h4 class="mb-0">Leger</h4>
  </div>

  <div class="card">

    {{-- Toolbar atas (UI mirip gambar) --}}
    <div class="card-body pb-2">
      <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center" style="gap:10px;">
          <span class="text-muted">Tampilkan</span>
          <select class="form-control form-control-sm" style="width:85px;" disabled>
            <option selected>10</option>
          </select>
          <span class="text-muted">data</span>
        </div>

        <div style="width:220px;">
          <input type="text" class="form-control form-control-sm" placeholder="Cari..." disabled>
        </div>
      </div>
    </div>

    <div class="card-body pt-0 table-responsive">
      <table class="table table-bordered table-sm mb-0">
        <thead class="bg-dark text-white">
          <tr>
            <th style="width:60px;">No.</th>
            <th>Nama Kelas</th>
            <th>Wali Kelas</th>
            <th style="width:90px;">Tingkat</th>
            <th style="width:130px;">Jumlah Siswa</th>
            <th style="width:120px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @php
            $startNo = method_exists($kelas,'firstItem') ? (($kelas->firstItem() ?? 1) - 1) : 0;
          @endphp

          @forelse($kelas as $i => $k)
            @php
              // String aman (hindari Optional object)
              $waliNama =
                (optional(optional($k->wali)->pengguna)->nama)
                ?? (optional(optional($k->wali)->pengguna)->name)
                ?? (optional($k->wali)->nama)
                ?? '-';

              $jumlahSiswa = $k->siswa_count ?? '-';
            @endphp
            <tr>
              <td class="text-center align-middle">{{ $startNo + $i + 1 }}</td>
              <td class="align-middle">{{ $k->nama_kelas }}</td>
              <td class="align-middle">{{ $waliNama }}</td>
              <td class="align-middle">{{ $k->tingkat }}</td>
              <td class="align-middle">{{ $jumlahSiswa }}</td>
              <td class="align-middle">
                <a class="btn btn-success btn-sm"
                   href="{{ route('guru.wali-kelas.rapor.leger.detail', $k->id) }}">
                  <i class="fas fa-eye"></i> Detail
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center text-muted py-4">
                Data kelas belum tersedia.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Footer pagination (asli) --}}
    <div class="card-footer d-flex justify-content-between align-items-center">
      <div class="text-muted small">
        Menampilkan {{ $kelas->firstItem() ?? 0 }} - {{ $kelas->lastItem() ?? 0 }} dari {{ $kelas->total() }} data
      </div>
      <div class="mb-0">
        {{ $kelas->links() }}
      </div>
    </div>

  </div>
</div>
@endsection
