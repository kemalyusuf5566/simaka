@extends('layouts.adminlte')
@section('title','Cetak Rapor '.$kelas->nama_kelas)

@section('content')
<div class="container-fluid">

  {{-- Header: panah kembali + judul --}}
  <div class="d-flex align-items-center mb-3">
    <a href="{{ route('admin.rapor.cetak') }}" class="btn btn-link p-0 mr-2" title="Kembali">
      <i class="fas fa-arrow-left"></i>
    </a>
    <h4 class="mb-0">Cetak Rapor {{ $kelas->nama_kelas }}</h4>
  </div>

  {{-- Box info (ada garis kuning kiri seperti screenshot) --}}
  <div class="card mb-3" style="border-left:4px solid #f1c40f;">
    <div class="card-body">
      <div class="row">
        <div class="col-md-3 font-weight-bold">Nama Kelas</div>
        <div class="col-md-9">: {{ $kelas->nama_kelas }}</div>

        <div class="col-md-3 font-weight-bold mt-2">Wali Kelas</div>
        <div class="col-md-9 mt-2">: {{ optional(optional($kelas->wali)->pengguna)->nama ?? '-' }}</div>

        <div class="col-md-3 font-weight-bold mt-2">Tahun Pelajaran</div>
        <div class="col-md-9 mt-2">: {{ $tahun->tahun_pelajaran ?? '-' }} - {{ $semester ?? '-' }}</div>

        <div class="col-md-3 font-weight-bold mt-2">Jenis Kertas</div>
        <div class="col-md-9 mt-2">
          :
          <form method="GET" action="{{ route('admin.rapor.cetak.detail', $kelas->id) }}" class="d-inline">
            <input type="hidden" name="per_page" value="{{ $perPage ?? 10 }}">
            <input type="hidden" name="q" value="{{ $q ?? '' }}">
            <select name="paper" class="form-control form-control-sm d-inline-block" style="width:120px" onchange="this.form.submit()">
              <option value="A4" @selected(($paper ?? 'F4') === 'A4')>A4</option>
              <option value="F4" @selected(($paper ?? 'F4') === 'F4')>F4</option>
            </select>
          </form>
        </div>
      </div>
    </div>
  </div>

  {{-- Card tabel siswa --}}
  <div class="card">

    {{-- Bar tampilkan + search (mirip screenshot) --}}
    <div class="card-body pb-2">
      <form method="GET" action="{{ route('admin.rapor.cetak.detail', $kelas->id) }}">
        <input type="hidden" name="paper" value="{{ $paper ?? 'F4' }}">

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

    <div class="card-body pt-0 table-responsive p-0">
      <table class="table table-bordered table-sm mb-0">
        <thead class="bg-dark text-white">
          <tr>
            <th style="width:60px;">No.</th>
            <th style="width:220px;">NIS/NISN</th>
            <th>Nama</th>
            <th style="width:70px;">L/P</th>
            <th style="width:280px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @php $startNo = ($siswa->firstItem() ?? 1) - 1; @endphp

          @forelse($siswa as $i => $s)
            <tr>
              <td class="text-center align-middle">{{ $startNo + $i + 1 }}</td>
              <td class="align-middle">{{ $s->nis ?? '-' }}/{{ $s->nisn ?? '-' }}</td>
              <td class="align-middle">{{ $s->nama_siswa ?? '-' }}</td>
              <td class="text-center align-middle">{{ $s->jenis_kelamin ?? '-' }}</td>
              <td class="text-center align-middle">
                {{-- tombol aksi jangan diubah --}}
                <a href="{{ route('admin.rapor.pdf.kelengkapan', $s->id) }}"
                   class="btn btn-warning btn-xs" target="_blank">
                  <i class="fas fa-file-pdf"></i> Kelengkapan Rapor
                </a>

                <a href="{{ route('admin.rapor.pdf.semester', $s->id) }}"
                   class="btn btn-info btn-xs" target="_blank">
                  <i class="fas fa-print"></i> Rapor Semester
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center text-muted py-4">Data siswa belum tersedia.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Footer info + pagination --}}
    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap" style="gap:10px;">
      <div class="text-muted small">
        Menampilkan {{ $siswa->firstItem() ?? 0 }} - {{ $siswa->lastItem() ?? 0 }} dari {{ $siswa->total() }} data
      </div>
      <div class="mb-0">
        {{ $siswa->links() }}
      </div>
    </div>

  </div>

</div>
@endsection