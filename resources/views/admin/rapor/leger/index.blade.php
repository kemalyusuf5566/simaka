@extends('layouts.adminlte')
@section('title','Leger')


@section('content')
<div class="container-fluid">

  <div class="d-flex align-items-center mb-3">
    <h4 class="mb-0">Leger</h4>
  </div>

  <div class="card">

    {{-- Toolbar: filter + perpage + search --}}
    <div class="card-body pb-2">
      <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:10px;">

        <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalFilter">
          <i class="fas fa-filter"></i> Filter Data
        </button>

        <form method="GET" action="{{ route('admin.rapor.leger') }}" class="d-flex align-items-center" style="gap:10px;">
          {{-- keep filter --}}
          <input type="hidden" name="tingkat" value="{{ $tingkat ?? '' }}">

          <div class="d-flex align-items-center" style="gap:8px;">
            <span class="text-muted">Tampilkan</span>
            <select name="per_page" class="form-control form-control-sm" style="width:85px" onchange="this.form.submit()">
              @foreach([10,25,50,100] as $pp)
                <option value="{{ $pp }}" @selected(($perPage ?? 10) == $pp)>{{ $pp }}</option>
              @endforeach
            </select>
            <span class="text-muted">data</span>
          </div>

          <div style="width:220px;">
            <input type="text"
                   name="q"
                   value="{{ $q ?? '' }}"
                   class="form-control form-control-sm"
                   placeholder="Cari..."
                   onkeydown="if(event.key==='Enter'){ this.form.submit(); }">
          </div>
        </form>

      </div>
    </div>

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
            $startNo = ($kelas->firstItem() ?? 1) - 1;
          @endphp

          @forelse($kelas as $i => $k)
            @php
              $waliNama =
                (optional(optional($k->wali)->pengguna)->nama)
                ?? (optional(optional($k->wali)->pengguna)->name)
                ?? '-';
            @endphp
            <tr>
              <td class="text-center align-middle">{{ $startNo + $i + 1 }}</td>
              <td class="align-middle">{{ $k->nama_kelas }}</td>
              <td class="align-middle">{{ $waliNama }}</td>
              <td class="align-middle">{{ $k->tingkat }}</td>
              <td class="align-middle">{{ $k->siswa_count ?? '-' }}</td>
              <td class="align-middle">
                <a class="btn btn-success btn-sm"
                   href="{{ route('admin.rapor.leger.detail', $k->id) }}">
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

    {{-- Footer pagination --}}
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

{{-- MODAL FILTER --}}
<div class="modal fade" id="modalFilter" tabindex="-1">
  <div class="modal-dialog">
    <form method="GET" action="{{ route('admin.rapor.leger') }}">
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