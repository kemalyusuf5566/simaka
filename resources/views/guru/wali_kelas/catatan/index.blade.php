@extends('layouts.adminlte')
@section('title','Wali Kelas - Catatan')

@section('content')
<div class="container-fluid">

  <div class="d-flex align-items-center mb-3">
    <h4 class="mb-0">Data Catatan Wali Kelas</h4>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="card">

    {{-- Toolbar (UI saja) --}}
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

    {{-- Table --}}
    <div class="card-body pt-0 table-responsive p-0">
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
            // nomor jalan kalau paginator, kalau bukan ya mulai dari 1
            $startNo = method_exists($kelas,'firstItem') ? ($kelas->firstItem() ?? 1) : 1;
          @endphp

          @forelse($kelas as $i => $k)
            @php
              // Jangan ubah model/logic: coba ambil dari relasi yang SUDAH ADA, kalau kosong fallback ke '-' / $namaWali jika memang dikirim dari controller
              $waliNama = '-';

              if (isset($k->wali) && isset($k->wali->pengguna) && !empty($k->wali->pengguna->name)) {
                $waliNama = $k->wali->pengguna->name;
              } elseif (isset($k->wali) && !empty($k->wali->pengguna->nama)) {
                // kalau field nama di pengguna pakai 'nama'
                $waliNama = $k->wali->pengguna->nama;
              } elseif (!empty($namaWali ?? null)) {
                $waliNama = $namaWali;
              }
            @endphp

            <tr>
              <td class="text-center align-middle">{{ $startNo + $i }}</td>
              <td class="align-middle">{{ $k->nama_kelas }}</td>
              <td class="align-middle">{{ $waliNama }}</td>
              <td class="align-middle">{{ $k->tingkat }}</td>
              <td class="align-middle">{{ $k->siswa_count ?? 0 }}</td>
              <td class="align-middle">
                <a class="btn btn-success btn-sm"
                   href="{{ route('guru.wali-kelas.catatan.kelola', $k->id) }}">
                  <i class="fas fa-cog"></i> Kelola
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center text-muted py-4">
                Anda belum menjadi wali kelas.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Footer pagination (kalau paginator) --}}
    @if(method_exists($kelas,'links'))
      <div class="card-footer d-flex justify-content-between align-items-center">
        <div class="text-muted small">
          Menampilkan {{ $kelas->firstItem() ?? 0 }} - {{ $kelas->lastItem() ?? 0 }} dari {{ $kelas->total() }} data
        </div>
        <div class="mb-0">
          {{ $kelas->links() }}
        </div>
      </div>
    @endif

  </div>
</div>
@endsection
