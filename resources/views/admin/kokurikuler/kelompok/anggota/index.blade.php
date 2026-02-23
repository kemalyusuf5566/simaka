@extends('layouts.adminlte')
@section('title','Anggota Kelompok')
@section('page_title','Anggota Kelompok')

@section('content')
<div class="mb-3">
  <a href="{{ route('admin.kokurikuler.kelompok.index') }}" class="btn btn-link p-0">
    <i class="fas fa-arrow-left"></i> Back
  </a>
</div>

{{-- HEADER INFO --}}
<div class="card mb-3">
  <div class="card-body">
    <div class="row">
      <div class="col-md-3 font-weight-bold">Nama Kelompok</div>
      <div class="col-md-9">: {{ $kelompok->nama_kelompok }}</div>

      <div class="col-md-3 font-weight-bold">Kelas</div>
      <div class="col-md-9">: {{ $kelompok->kelas->nama_kelas ?? '-' }}</div>

      <div class="col-md-3 font-weight-bold">Guru/Koordinator</div>
      <div class="col-md-9">: {{ $kelompok->koordinator->nama ?? '-' }}</div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-body">

    <div class="d-flex justify-content-between mb-3">
      <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalTambahAnggota">
        <i class="fas fa-plus"></i> Tambah Anggota Kelompok
      </button>

      <a href="{{ route('admin.kokurikuler.kelompok.kegiatan.index', $kelompok->id) }}" class="btn btn-warning btn-sm">
        <i class="fas fa-cogs"></i> Kegiatan Pilihan
      </a>
    </div>

    {{-- FILTER BAR (REAL via querystring) --}}
    <form method="GET" class="d-flex justify-content-between align-items-center mb-2">
      <div class="d-flex align-items-center">
        <span class="mr-2 text-muted">Tampilkan</span>
        <select name="per_page" class="form-control form-control-sm" style="width:80px" onchange="this.form.submit()">
          @foreach([10,25,50,100] as $n)
            <option value="{{ $n }}" @selected($perPage==$n)>{{ $n }}</option>
          @endforeach
        </select>
        <span class="ml-2 text-muted">data</span>
      </div>

      <div class="d-flex">
        <input type="text" name="q" value="{{ $q }}" class="form-control form-control-sm" style="width:160px" placeholder="Cari...">
      </div>
    </form>

    <table class="table table-bordered table-striped table-hover">
      <thead class="bg-secondary">
        <tr>
          <th style="width:60px">No.</th>
          <th style="width:160px">NIS</th>
          <th>Nama Siswa</th>
          <th style="width:80px" class="text-center">L/P</th>
          <th style="width:120px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($anggota as $i => $a)
          <tr>
            <td>{{ $anggota->firstItem() + $i }}</td>
            <td>{{ $a->siswa->nis ?? '-' }}</td>
            <td>{{ $a->siswa->nama_siswa ?? '-' }}</td>
            <td class="text-center">{{ $a->siswa->jenis_kelamin ?? '-' }}</td>
            <td>
              <form method="POST" action="{{ route('admin.kokurikuler.kelompok.anggota.destroy', [$kelompok->id, $a->id]) }}"
                    onsubmit="return confirm('Hapus anggota ini?')" class="d-inline">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger btn-xs">
                  <i class="fas fa-trash"></i> Hapus
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="text-center text-muted">Belum ada anggota.</td>
          </tr>
        @endforelse
      </tbody>
    </table>

    <div class="d-flex justify-content-between align-items-center">
      <div class="text-muted">
        Menampilkan {{ $anggota->firstItem() ?? 0 }} - {{ $anggota->lastItem() ?? 0 }} dari {{ $anggota->total() }} data
      </div>
      <div>
        {{ $anggota->onEachSide(1)->links() }}
      </div>
    </div>

  </div>
</div>

{{-- MODAL TAMBAH ANGGOTA (seperti screenshot: ada tampilkan/cari/paging di dalam modal) --}}
<div class="modal fade" id="modalTambahAnggota" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Tambah Anggota Kelompok</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>

      <div class="modal-body">

        <form method="GET" id="formKandidatSearch" class="d-flex justify-content-between align-items-center mb-2">
          {{-- pertahankan filter anggota --}}
          <input type="hidden" name="per_page" value="{{ $perPage }}">
          <input type="hidden" name="q" value="{{ $q }}">

          <div class="d-flex align-items-center">
            <span class="mr-2 text-muted">Tampilkan</span>
            <select name="per_page" class="form-control form-control-sm" style="width:80px" onchange="this.form.submit()">
              @foreach([10,25,50,100] as $n)
                <option value="{{ $n }}" @selected($perPage==$n)>{{ $n }}</option>
              @endforeach
            </select>
            <span class="ml-2 text-muted">data</span>
          </div>

          <input type="text" name="kq" value="{{ $kq }}" class="form-control form-control-sm" style="width:160px" placeholder="Cari...">
        </form>

        <table class="table table-bordered table-striped table-hover mb-2">
          <thead class="bg-secondary">
            <tr>
              <th style="width:60px">No.</th>
              <th style="width:160px">NIS</th>
              <th>Nama Siswa</th>
              <th style="width:80px" class="text-center">L/P</th>
              <th style="width:140px">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($kandidat as $i => $s)
              <tr>
                <td>{{ $kandidat->firstItem() + $i }}</td>
                <td>{{ $s->nis }}</td>
                <td>{{ $s->nama_siswa }}</td>
                <td class="text-center">{{ $s->jenis_kelamin }}</td>
                <td>
                  <form method="POST" action="{{ route('admin.kokurikuler.kelompok.anggota.store', $kelompok->id) }}">
                    @csrf
                    <input type="hidden" name="data_siswa_id" value="{{ $s->id }}">
                    <button class="btn btn-primary btn-xs">
                      <i class="fas fa-plus"></i> Tambahkan
                    </button>
                  </form>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center text-muted">Tidak ada kandidat.</td>
              </tr>
            @endforelse
          </tbody>
        </table>

        <div class="d-flex justify-content-between align-items-center">
          <div class="text-muted">
            Menampilkan {{ $kandidat->firstItem() ?? 0 }} - {{ $kandidat->lastItem() ?? 0 }} dari {{ $kandidat->total() }} data
          </div>
          <div>
            {{ $kandidat->onEachSide(1)->links() }}
          </div>
        </div>

      </div>

      <div class="modal-footer d-flex justify-content-between">
        <form method="POST" action="{{ route('admin.kokurikuler.kelompok.anggota.addAll', $kelompok->id) }}">
          @csrf
          <button type="submit" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Tambahkan Semua
          </button>
        </form>

        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>

@if($kq !== '')
@push('scripts')
<script>
$(function(){ $('#modalTambahAnggota').modal('show'); });
</script>
@endpush
@endif

@endsection