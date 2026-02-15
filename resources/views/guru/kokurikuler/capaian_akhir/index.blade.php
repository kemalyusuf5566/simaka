@extends('layouts.adminlte')

@section('title', 'Data Capaian Akhir')

@section('content')
<div class="container-fluid">

  {{-- HEADER: back + judul --}}
  <div class="d-flex align-items-center mb-3">
    <a href="{{ route('guru.kokurikuler.kegiatan.index', $kelompok->id) }}"
       class="btn btn-link p-0 mr-2" title="Kembali">
      <i class="fas fa-arrow-left"></i>
    </a>
    <h4 class="mb-0">Data Capaian Akhir</h4>
  </div>

  {{-- INFO ALERT (dismissible) --}}
  <div class="alert alert-info alert-dismissible fade show mb-3" role="alert">
    Capaian akhir digunakan sebagai indikator penilaian kokurikuler. Disarankan mencantumkan dua capaian akhir per kegiatan.
    <button type="button" class="close" data-dismiss="alert" aria-label="Tutup">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>

  {{-- CARD INFO KELOMPOK --}}
  <div class="card mb-3">
    <div class="card-body">
      <div class="row">
        <div class="col-md-3 font-weight-bold">Nama Kelompok</div>
        <div class="col-md-9">: {{ $kelompok->nama_kelompok }}</div>

        <div class="col-md-3 font-weight-bold mt-2">Kelas</div>
        <div class="col-md-9 mt-2">: {{ $kelompok->kelas?->nama_kelas }}</div>

        <div class="col-md-3 font-weight-bold mt-2">Guru/Koordinator</div>
        <div class="col-md-9 mt-2">: {{ $kelompok->koordinator?->nama }}</div>

        <div class="col-md-3 font-weight-bold mt-2">Nama Kegiatan</div>
        <div class="col-md-9 mt-2">: {{ $kelompokKegiatan->kegiatan?->nama_kegiatan }}</div>
      </div>
    </div>
  </div>

  {{-- FLASH --}}
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

  {{-- TOOLBAR: tambah capaian akhir --}}
  <div class="d-flex justify-content-between align-items-center mb-2">
    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalTambahCapaian">
      <i class="fas fa-plus"></i> Tambah Capaian Akhir
    </button>
    <div></div>
  </div>

  {{-- BAR FILTER (UI SAJA) --}}
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

  {{-- TABEL --}}
  <div class="card">
    <div class="card-body table-responsive p-0">
      <table class="table table-bordered table-sm mb-0">
        <thead class="bg-dark text-white">
          <tr>
            <th style="width:60px;" class="text-center">No.</th>
            <th style="width:320px;">Dimensi</th>
            <th>Capaian Akhir</th>
            <th style="width:180px;" class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($items as $i => $it)
            <tr>
              <td class="text-center align-middle">{{ $i + 1 }}</td>
              <td class="align-middle">{{ $it->dimensi?->nama_dimensi }}</td>
              <td class="align-middle">{{ $it->capaian }}</td>
              <td class="text-center align-middle">
                <div class="d-inline-flex" style="gap:6px;">
                  <button type="button"
                          class="btn btn-warning btn-sm"
                          data-toggle="modal"
                          data-target="#modalEditCapaian-{{ $it->id }}">
                    <i class="fas fa-edit"></i> Edit
                  </button>

                  <form method="POST"
                        action="{{ route('guru.kokurikuler.capaian_akhir.destroy', [$kelompok->id, $kelompokKegiatan->id, $it->id]) }}"
                        onsubmit="return confirm('Hapus capaian ini?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger btn-sm">
                      <i class="fas fa-trash"></i> Hapus
                    </button>
                  </form>
                </div>
              </td>
            </tr>

            {{-- MODAL EDIT --}}
            <div class="modal fade" id="modalEditCapaian-{{ $it->id }}" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">

                  <div class="modal-header">
                    <h5 class="modal-title">Edit Data Capaian Akhir</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>

                  <form method="POST"
                        action="{{ route('guru.kokurikuler.capaian_akhir.update', [$kelompok->id, $kelompokKegiatan->id, $it->id]) }}">
                    @csrf
                    @method('PUT')

                    <div class="modal-body">
                      <div class="form-group">
                        <label class="mb-1">Dimensi</label>
                        <select name="kk_dimensi_id" class="form-control" required>
                          @foreach($dimensi as $d)
                            <option value="{{ $d->id }}" @selected($it->kk_dimensi_id == $d->id)>
                              {{ $d->nama_dimensi }}
                            </option>
                          @endforeach
                        </select>
                      </div>

                      <div class="form-group">
                        <label class="mb-1">Capaian Akhir <span class="text-danger">*</span></label>
                        <textarea name="capaian" class="form-control" rows="3" required>{{ $it->capaian }}</textarea>
                      </div>

                      <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="checkEdit{{ $it->id }}" required>
                        <label class="form-check-label" for="checkEdit{{ $it->id }}">
                          Saya yakin sudah mengisi dengan benar
                        </label>
                      </div>
                    </div>

                    <div class="modal-footer">
                      <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                      <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                  </form>

                </div>
              </div>
            </div>
          @empty
            <tr>
              <td colspan="4" class="text-center text-muted">Belum ada capaian akhir.</td>
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

{{-- MODAL TAMBAH --}}
<div class="modal fade" id="modalTambahCapaian" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Tambah Data Capaian Akhir</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form method="POST" action="{{ route('guru.kokurikuler.capaian_akhir.store', [$kelompok->id, $kelompokKegiatan->id]) }}">
        @csrf

        <div class="modal-body">
          <div class="form-group">
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text">Dimensi</span>
              </div>
              <select name="kk_dimensi_id" class="form-control" required>
                <option value="">-- Pilih --</option>
                @foreach($dimensi as $d)
                  <option value="{{ $d->id }}">{{ $d->nama_dimensi }}</option>
                @endforeach
              </select>
            </div>
            @error('kk_dimensi_id')
              <small class="text-danger">{{ $message }}</small>
            @enderror
          </div>

          <div class="form-group">
            <label class="mb-1">Capaian Akhir <span class="text-danger">*</span></label>
            <textarea name="capaian" class="form-control" rows="3" placeholder="Ketik Capaian Akhir" required></textarea>
            @error('capaian')
              <small class="text-danger">{{ $message }}</small>
            @enderror
          </div>

          <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" id="checkTambah" required>
            <label class="form-check-label" for="checkTambah">
              Saya yakin sudah mengisi dengan benar
            </label>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>

      </form>

    </div>
  </div>
</div>
@endsection
