@extends('layouts.adminlte')
@section('title','Leger '.$kelas->nama_kelas)

@section('content')
<div class="container-fluid">

  {{-- HEADER --}}
  <div class="d-flex align-items-center mb-3">
    <a href="{{ route('admin.rapor.leger') }}" class="btn btn-link p-0 mr-2" title="Kembali">
      <i class="fas fa-arrow-left"></i>
    </a>
    <h4 class="mb-0">Leger {{ $kelas->nama_kelas }}</h4>
  </div>

  @php
    $waliNama =
      (optional(optional($kelas->wali)->pengguna)->nama)
      ?? (optional(optional($kelas->wali)->pengguna)->name)
      ?? '-';
  @endphp

  {{-- INFO --}}
  <div class="card mb-3">
    <div class="card-body">
      <div class="row">
        <div class="col-md-3 font-weight-bold">Nama Kelas</div>
        <div class="col-md-9">: {{ $kelas->nama_kelas }}</div>

        <div class="col-md-3 font-weight-bold mt-2">Wali Kelas</div>
        <div class="col-md-9 mt-2">: {{ $waliNama }}</div>
      </div>
    </div>
  </div>

  {{-- TOOLBAR --}}
  <div class="card">
    <div class="card-body pb-2">
      <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:10px;">
        <div class="d-flex" style="gap:8px;">
          <a class="btn btn-light btn-sm"
             href="{{ route('admin.rapor.leger.excel', $kelas->id) }}">
            Excel
          </a>
          <a class="btn btn-light btn-sm"
             href="{{ route('admin.rapor.leger.pdf', $kelas->id) }}">
            PDF
          </a>
        </div>

        <div class="d-flex align-items-center" style="gap:8px;">
          <span class="text-muted small">Search:</span>
          <input type="text" class="form-control form-control-sm" id="searchLeger" style="width:220px;">
        </div>
      </div>
    </div>

    <div class="card-body pt-0 table-responsive p-0">
      <table class="table table-bordered table-sm mb-0" id="tableLeger">
        <thead>
          <tr class="bg-dark text-white">
            <th rowspan="2" style="width:60px;">No.</th>
            <th rowspan="2" style="width:140px;">NIS</th>
            <th rowspan="2" style="min-width:200px;">NAMA</th>
            <th rowspan="2" style="width:70px;">L/P</th>

            <th colspan="{{ $mapel->count() }}" class="text-center" style="background:#f1c40f; color:#000;">
              NILAI
            </th>

            <th rowspan="2" style="width:110px;">TOTAL NILAI</th>
            <th rowspan="2" style="width:110px;">RATA-RATA</th>
            <th rowspan="2" style="width:90px;">RANKING</th>
          </tr>

          <tr class="bg-dark text-white">
            @foreach($mapel as $m)
              <th class="text-center" style="min-width:70px;">
                {{ $m->singkatan ?? $m->kode_mapel ?? '-' }}
              </th>
            @endforeach
          </tr>
        </thead>

        <tbody>
          @forelse($rows as $i => $r)
            @php
              $s = $r['siswa'];
              $nama = strtolower($s->nama_siswa ?? '');
              $nis  = strtolower($s->nis ?? '');
              $jk   = strtoupper($s->jenis_kelamin ?? '-');
              $jk   = in_array($jk, ['L','P'], true) ? $jk : '-';
            @endphp
            <tr data-nama="{{ $nama }}" data-nis="{{ $nis }}">
              <td class="text-center">{{ $i+1 }}</td>
              <td>{{ $s->nis ?? '-' }}</td>
              <td>{{ $s->nama_siswa ?? '-' }}</td>
              <td class="text-center">{{ $jk }}</td>

              @foreach($mapel as $m)
                @php
                  $v = $nilaiMap[(int)$s->id][(int)$m->id] ?? null;
                @endphp
                <td class="text-center">{{ is_numeric($v) ? $v : '-' }}</td>
              @endforeach

              <td class="text-center font-weight-bold">{{ $r['total'] }}</td>
              <td class="text-center">{{ is_numeric($r['rata']) ? number_format($r['rata'], 1) : '-' }}</td>
              <td class="text-center">{{ $r['rank'] ?? '-' }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="{{ 7 + $mapel->count() }}" class="text-center text-muted py-4">
                Data siswa / nilai belum tersedia.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>

@push('scripts')
<script>
(function(){
  const input = document.getElementById('searchLeger');
  const table = document.getElementById('tableLeger');
  if (!input || !table) return;

  input.addEventListener('input', function(){
    const q = (this.value || '').toLowerCase().trim();
    const rows = table.querySelectorAll('tbody tr');

    rows.forEach(tr => {
      const nama = tr.getAttribute('data-nama') || '';
      const nis  = tr.getAttribute('data-nis') || '';
      const ok = !q || nama.includes(q) || nis.includes(q);
      tr.style.display = ok ? '' : 'none';
    });
  });
})();
</script>
@endpush

@endsection