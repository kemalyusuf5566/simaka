@extends('layouts.adminlte')

@section('title', 'Data Pembelajaran')

@section('content')
<div class="container-fluid">

  {{-- Header + tombol Filter Data (UI saja) --}}
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Data Pembelajaran</h4>

    <button type="button" class="btn btn-sm btn-info">
      <i class="fas fa-filter mr-1"></i> Filter Data
    </button>
  </div>

  <div class="card">
    <div class="card-body">

      {{-- Bar kontrol (UI saja): page size + search --}}
      <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center mb-2 mb-md-0">
          <span class="mr-2">Tampilkan</span>
          <select class="custom-select custom-select-sm" style="width: 80px;">
            <option selected>10</option>
            <option>25</option>
            <option>50</option>
            <option>100</option>
          </select>
          <span class="ml-2">data</span>
        </div>

        <div class="input-group input-group-sm" style="width: 220px;">
          <input type="text" class="form-control" placeholder="Cari...">
          <div class="input-group-append">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
          </div>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-bordered table-hover table-sm mb-0 table-fixed">
          <thead class="thead-dark text-center">
            <tr>
              <th class="col-no">No</th>
              <th class="col-mapel">Mata Pelajaran</th>
              <th class="col-kelas">Kelas</th>
              <th class="col-guru">Guru Pengampu</th>
              <th class="col-aksi">Aksi</th>
            </tr>
          </thead>

          <tbody>
            @forelse ($pembelajaran as $i => $row)
              <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $row->mapel->nama_mapel ?? '-' }}</td>
                <td>{{ $row->kelas->nama_kelas ?? '-' }}</td>
                <td>{{ $row->guru->nama ?? '-' }}</td>
                <td>
                  {{-- tombol rapih seperti di gambar: 2 tombol sejajar --}}
                  <div class="d-flex flex-wrap" style="gap:6px;">
                    <a href="{{ route('guru.tp.index', $row->id) }}"
                       class="btn btn-info btn-sm">
                      <i class="fas fa-tasks mr-1"></i> Kelola Tujuan Pembelajaran
                    </a>

                    <a href="{{ route('guru.nilai_akhir.index', $row->id) }}"
                       class="btn btn-primary btn-sm">
                      <i class="fas fa-pen mr-1"></i> Input Nilai dan Deskripsi
                    </a>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center text-muted">
                  Tidak ada data pembelajaran untuk guru ini.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Footer info + pagination (UI mengikuti gambar, logic tidak diubah) --}}
      <div class="d-flex justify-content-between align-items-center mt-2">
        <div class="text-muted small">
          Menampilkan 1 - {{ count($pembelajaran) }} dari {{ count($pembelajaran) }} data
        </div>
        <div>
          {{-- kalau nanti $pembelajaran sudah paginate(), ini otomatis muncul --}}
          @if(method_exists($pembelajaran, 'links'))
            {{ $pembelajaran->links() }}
          @else
            {{-- UI placeholder pagination biar mirip gambar --}}
            <nav>
              <ul class="pagination pagination-sm mb-0">
                <li class="page-item disabled"><span class="page-link">«</span></li>
                <li class="page-item active"><span class="page-link">1</span></li>
                <li class="page-item disabled"><span class="page-link">»</span></li>
              </ul>
            </nav>
          @endif
        </div>
      </div>

    </div>
  </div>

</div>
{{-- CSS khusus halaman ini, biar kolom ikut proporsi & mapel/guru tidak melebar --}}
<style>
  .table-fixed {
    table-layout: fixed;
    width: 100%;
  }

  /* proporsi kolom: yang diperkecil = kelas & guru, mapel dibatasi, aksi tetap cukup */
  .col-no   { width: 55px; }
  .col-kelas{ width: 90px; }
  .col-guru { width: 150px; }
  .col-aksi { width: 300px; }
  .col-mapel{ width: 150px; }

  .text-ellipsis{
    display: block;
    max-width: 100%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  /* biar tombol tidak pecah jadi tinggi banget */
  .col-aksi .btn{
    white-space: nowrap;
  }
</style>
@endsection
