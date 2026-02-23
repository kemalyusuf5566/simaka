@extends('layouts.adminlte')
@section('title','Cetak Rapor')

@section('content')
<div class="container-fluid">

  <div class="d-flex align-items-center mb-3">
    <h4 class="mb-0">Cetak Rapor</h4>
  </div>

  <div class="card">

    {{-- Toolbar atas: tombol filter di kanan (sesuai screenshot) --}}
    <div class="card-body pb-2">
      <div class="d-flex justify-content-end">
        <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalFilter">
          <i class="fas fa-filter"></i> Filter Data
        </button>
      </div>
    </div>

    {{-- Bar: Tampilkan + Search (persis gaya screenshot) --}}
    <div class="card-body pt-0 pb-2">
      <form method="GET" action="{{ route('admin.rapor.cetak') }}">
        <input type="hidden" name="tingkat" value="{{ $tingkat ?? '' }}">

        <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:10px;">
          <div class="d-flex align-items-center" style="gap:8px;">
            <span class="text-muted">Tampilkan</span>
            <select name="per_page" class="form-control form-control-sm" style="width:70px" onchange="this.form.submit()">
              @foreach([10,25,50,100] as $pp)
                <option value="{{ $pp }}" @selected(($perPage ?? 10) == $pp)>{{ $pp }}</option>
              @endforeach
            </select>
            <span class="text-muted">data</span>
          </div>

          <div style="width:180px;">
            <input type="text"
                   name="q"
                   value="{{ $q ?? '' }}"
                   class="form-control form-control-sm"
                   placeholder="Cari..."
                   onkeydown="if(event.key==='Enter'){ this.form.submit(); }">
          </div>
        </div>
      </form>
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
          @php $startNo = ($kelas->firstItem() ?? 1) - 1; @endphp

          @forelse($kelas as $i => $k)
            @php
              $waliNama = optional(optional($k->wali)->pengguna)->nama ?? '-';
            @endphp
            <tr>
              <td class="text-center align-middle">{{ $startNo + $i + 1 }}</td>
              <td class="align-middle">{{ $k->nama_kelas }}</td>
              <td class="align-middle">{{ $waliNama }}</td>
              <td class="text-center align-middle">{{ $k->tingkat }}</td>
              <td class="text-center align-middle">{{ $k->siswa_count ?? 0 }}</td>
              <td class="text-center align-middle">
                <a href="{{ route('admin.rapor.cetak.detail', $k->id) }}" class="btn btn-success btn-sm">
                  <i class="fas fa-eye"></i> Detail
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center text-muted py-4">Data kelas belum tersedia.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Footer: info kiri + pagination kanan (mirip screenshot) --}}
    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap" style="gap:10px;">
      <div class="text-muted small">
        Menampilkan {{ $kelas->firstItem() ?? 0 }} - {{ $kelas->lastItem() ?? 0 }} dari {{ $kelas->total() }} data
      </div>
      <div class="mb-0">
        {{ $kelas->links() }}
      </div>
    </div>

  </div>
</div>

{{-- MODAL FILTER (tingkat) --}}
<div class="modal fade" id="modalFilter" tabindex="-1">
  <div class="modal-dialog">
    <form method="GET" action="{{ route('admin.rapor.cetak') }}">
      <input type="hidden" name="per_page" value="{{ $perPage ?? 10 }}">
      <input type="hidden" name="q" value="{{ $q ?? '' }}">

      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Filter Data</h5>
          <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>

        <div class="modal-body">
          <div class="form-group mb-0">
            <label class="mb-1">Tingkat Kelas</label>
            <select name="tingkat" class="form-control">
              <option value="">-- Semua Tingkat --</option>
              @foreach(($tingkatList ?? []) as $t)
                <option value="{{ $t }}" @selected((string)($tingkat ?? '') === (string)$t)>{{ $t }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
          <button class="btn btn-primary" type="submit">Terapkan</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection