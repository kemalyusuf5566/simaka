@extends('layouts.adminlte')

@section('title', 'Kegiatan Pilihan Kelompok')

@section('content')
<div class="container-fluid">

  {{-- HEADER: back + judul --}}
  <div class="d-flex align-items-center mb-3">
    <a href="{{ route('guru.kokurikuler.index') }}" class="btn btn-link p-0 mr-2" title="Kembali">
      <i class="fas fa-arrow-left"></i>
    </a>
    <h4 class="mb-0">Kegiatan Pilihan Kelompok</h4>
  </div>

  {{-- INFO KELOMPOK --}}
  <div class="card mb-3">
    <div class="card-body">
      <div class="row">
        <div class="col-md-3 font-weight-bold">Nama Kelompok</div>
        <div class="col-md-9">: {{ $kelompok->nama_kelompok ?? '-' }}</div>

        <div class="col-md-3 font-weight-bold mt-2">Kelas</div>
        <div class="col-md-9 mt-2">: {{ $kelompok->kelas->nama_kelas ?? '-' }}</div>

        <div class="col-md-3 font-weight-bold mt-2">Guru/Koordinator</div>
        <div class="col-md-9 mt-2">: {{ $kelompok->koordinator->nama ?? '-' }}</div>
      </div>
    </div>
  </div>

  {{-- ALERT --}}
  @if(session('success'))
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

  {{-- TOOLBAR: tambah kegiatan (modal) + anggota kelompok --}}
  <div class="d-flex justify-content-between align-items-center mb-2">
    @if(\Illuminate\Support\Facades\Route::has('guru.kokurikuler.kegiatan.store'))
      <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalTambahKegiatan">
        <i class="fas fa-plus"></i> Tambah Kegiatan
      </button>
    @else
      <button type="button" class="btn btn-primary btn-sm" disabled title="Route store belum tersedia">
        <i class="fas fa-plus"></i> Tambah Kegiatan
      </button>
    @endif

    <a href="{{ route('guru.kokurikuler.anggota.index', $kelompok->id) }}" class="btn btn-info btn-sm">
      <i class="fas fa-users"></i> Anggota Kelompok
    </a>
  </div>

  {{-- BAR FILTER (UI seperti gambar) --}}
  <div class="d-flex justify-content-between align-items-center mb-2">
    <div class="d-flex align-items-center">
      <span class="mr-2">Tampilkan</span>
      <select class="form-control form-control-sm" style="width:80px;">
        <option selected>10</option>
        <option>25</option>
        <option>50</option>
        <option>100</option>
      </select>
      <span class="ml-2">data</span>
    </div>

    <div style="width:220px;">
      <input type="text" class="form-control form-control-sm" placeholder="Cari...">
    </div>
  </div>

  {{-- TABLE --}}
  <div class="card">
    <div class="card-body table-responsive p-0">
      <table class="table table-bordered table-sm mb-0">
        <thead class="bg-dark text-white">
          <tr>
            <th style="width:60px;">No.</th>
            <th style="width:220px;">Tema</th>
            <th>Nama Kegiatan</th>
            <th>Deskripsi</th>
            <th style="width:520px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($items as $i => $row)
            @php
              // $row = pivot kk_kelompok_kegiatan
              $pivotId   = $row->id; // dipakai untuk Capaian Akhir & Hapus pivot
              $kegiatan  = $row->kegiatan; // relasi ke kk_kegiatan
              $kegiatanId = $kegiatan?->id;
              $tema      = $kegiatan->tema ?? '-';
              $namaKeg   = $kegiatan->nama_kegiatan ?? '-';
              $deskKeg   = $kegiatan->deskripsi ?? '-';
            @endphp

            <tr>
              <td class="text-center align-middle">{{ $i+1 }}</td>
              <td class="align-middle">{{ $tema }}</td>
              <td class="align-middle">{{ $namaKeg }}</td>

              {{-- Deskripsi biar ringkas (detail lihat modal) --}}
              <td class="align-middle">
                <span title="{{ $deskKeg }}">
                  {{ \Illuminate\Support\Str::limit($deskKeg, 80) }}
                </span>
              </td>

              <td class="align-middle">
                <div class="d-flex flex-wrap" style="gap:6px;">
                  {{-- CAPAIAN AKHIR (pivot id) --}}
                  <a href="{{ route('guru.kokurikuler.capaian_akhir.index', [$kelompok->id, $pivotId]) }}"
                     class="btn btn-info btn-sm">
                    <i class="fas fa-bullseye mr-1"></i> Capaian Akhir
                  </a>

                  {{-- INPUT NILAI (kegiatan id) --}}
                  <a href="{{ route('guru.kokurikuler.nilai.index', [$kelompok->id, $kegiatanId]) }}"
                     class="btn btn-warning btn-sm">
                    <i class="fas fa-pen mr-1"></i> Input Nilai
                  </a>

                  {{-- DESKRIPSI (kegiatan id) --}}
                  <a href="{{ route('guru.kokurikuler.deskripsi.index', [$kelompok->id, $kegiatanId]) }}"
                     class="btn btn-success btn-sm">
                    <i class="fas fa-align-left mr-1"></i> Deskripsi
                  </a>

                  {{-- DETAIL (modal) --}}
                  <button type="button"
                          class="btn btn-primary btn-sm btn-detail"
                          data-toggle="modal"
                          data-target="#modalDetailKegiatan"
                          data-tema="{{ e($tema) }}"
                          data-nama="{{ e($namaKeg) }}"
                          data-deskripsi="{{ e($deskKeg) }}">
                    <i class="fas fa-eye mr-1"></i> Detail
                  </button>

                  {{-- HAPUS pivot --}}
                  @if(\Illuminate\Support\Facades\Route::has('guru.kokurikuler.kegiatan.destroy'))
                    <form method="POST"
                          action="{{ route('guru.kokurikuler.kegiatan.destroy', [$kelompok->id, $pivotId]) }}"
                          class="d-inline"
                          onsubmit="return confirm('Hapus kegiatan dari kelompok ini?')">
                      @csrf
                      @method('DELETE')
                      <button class="btn btn-danger btn-sm">
                        <i class="fas fa-trash mr-1"></i> Hapus
                      </button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center text-muted py-4">Belum ada kegiatan di kelompok ini.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- FOOTER PAGINATION (UI like AdminLTE datatable) --}}
    <div class="card-footer">
      <div class="d-flex justify-content-between align-items-center">
        <div class="text-muted">
          @php
            $total = is_countable($items) ? count($items) : 0;
            $start = $total ? 1 : 0;
            $end   = $total;
          @endphp
          Menampilkan {{ $start }} - {{ $end }} dari {{ $total }} data
        </div>

        {{-- Pagination harus nempel kanan --}}
        <nav aria-label="Pagination">
          <ul class="pagination pagination-sm mb-0 justify-content-end">
            <li class="page-item disabled"><a class="page-link" href="#">&laquo;</a></li>
            <li class="page-item active"><a class="page-link" href="#">1</a></li>
            <li class="page-item disabled"><a class="page-link" href="#">&raquo;</a></li>
          </ul>
        </nav>
      </div>
    </div>

  </div>

</div>

{{-- ===================== MODAL DETAIL KEGIATAN ===================== --}}
<div class="modal fade" id="modalDetailKegiatan" tabindex="-1" aria-labelledby="modalDetailKegiatanLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="modalDetailKegiatanLabel">Detail Kegiatan</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <div class="row mb-2">
          <div class="col-md-3 font-weight-bold">Tema</div>
          <div class="col-md-9">: <span id="detailTema"></span></div>
        </div>
        <hr class="my-2">
        <div class="row mb-2">
          <div class="col-md-3 font-weight-bold">Nama Kegiatan</div>
          <div class="col-md-9">: <span id="detailNama"></span></div>
        </div>
        <hr class="my-2">
        <div class="row">
          <div class="col-md-3 font-weight-bold">Deskripsi Kegiatan</div>
          <div class="col-md-9">: <span id="detailDeskripsi"></span></div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

{{-- ===================== MODAL TAMBAH KEGIATAN ===================== --}}
<div class="modal fade" id="modalTambahKegiatan" tabindex="-1" aria-labelledby="modalTambahKegiatanLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="modalTambahKegiatanLabel">Tambah Kegiatan Pilihan Kelompok</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      {{-- NOTE: action tetap pakai route store yg sudah ada --}}
      <form method="POST" action="{{ route('guru.kokurikuler.kegiatan.store', $kelompok->id) }}">
        @csrf

        <div class="modal-body">

          {{-- BAR FILTER (UI saja) --}}
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex align-items-center">
              <span class="mr-2">Tampilkan</span>
              <select class="form-control form-control-sm" style="width:80px;">
                <option selected>10</option>
                <option>25</option>
                <option>50</option>
                <option>100</option>
              </select>
              <span class="ml-2">data</span>
            </div>

            <div style="width:220px;">
              <input type="text" class="form-control form-control-sm" placeholder="Cari...">
            </div>
          </div>

          {{-- TABLE LIST KEGIATAN DARI ADMIN --}}
          <div class="table-responsive" style="max-height:520px; overflow:auto;">
            <table class="table table-bordered table-sm mb-0">
              <thead class="bg-dark text-white">
                <tr>
                  <th style="width:60px;">No.</th>
                  <th style="width:220px;">Tema</th>
                  <th>Nama Kegiatan</th>
                  <th>Deskripsi</th>
                  <th style="width:140px;">Aksi</th>
                </tr>
              </thead>
              <tbody>
                @forelse(($kegiatanList ?? []) as $idx => $k)
                  <tr>
                    <td class="text-center align-middle">{{ $idx+1 }}</td>
                    <td class="align-middle">{{ $k->tema ?? '-' }}</td>
                    <td class="align-middle">{{ $k->nama_kegiatan ?? '-' }}</td>
                    <td class="align-middle">
                      <span title="{{ $k->deskripsi ?? '-' }}">
                        {{ \Illuminate\Support\Str::limit($k->deskripsi ?? '-', 90) }}
                      </span>
                    </td>
                    <td class="text-center align-middle">
                      {{-- LOGIC: biar tetap POST ke store, kita kirim kk_kegiatan_id per klik --}}
                      <button type="submit"
                              name="kk_kegiatan_id"
                              value="{{ $k->id }}"
                              class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambahkan
                      </button>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                      Tidak ada data kegiatan yang bisa dipilih.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
        </div>
      </form>

    </div>
  </div>
</div>

@push('scripts')
<script>
  // isi modal detail
  $(document).on('click', '.btn-detail', function () {
    $('#detailTema').text($(this).data('tema') || '-');
    $('#detailNama').text($(this).data('nama') || '-');
    $('#detailDeskripsi').text($(this).data('deskripsi') || '-');
  });
</script>
@endpush

@endsection
