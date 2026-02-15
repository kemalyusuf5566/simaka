{{-- resources/views/guru/ekskul/anggota/index.blade.php --}}
@extends('layouts.adminlte')

@section('title', 'Anggota Ekskul')

@section('content')
<div class="container-fluid">

  {{-- FIX CSS kecil untuk pagination biar tidak ganggu footer --}}
  @push('styles')
  <style>
    .card-footer .pagination { margin-bottom: 0 !important; }
    .card-footer .page-item .page-link { padding: .25rem .55rem; }
    .card-footer nav { line-height: 1; }
  </style>
  @endpush

  {{-- HEADER: back + judul --}}
  <div class="d-flex align-items-center mb-3">
    <a href="{{ route('guru.ekskul.index') }}" class="btn btn-link p-0 mr-2" title="Kembali">
      <i class="fas fa-arrow-left"></i>
    </a>
    <h4 class="mb-0">
      Anggota {{ $ekskul->nama_ekskul ?? $ekskul->nama_ekstrakurikuler ?? 'Ekskul' }}
    </h4>
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

  {{-- CARD INFO --}}
  <div class="card mb-3">
    <div class="card-body">
      <div class="row">
        <div class="col-md-3 font-weight-bold">Nama Ekstrakurikuler</div>
        <div class="col-md-9">: {{ $ekskul->nama_ekskul ?? $ekskul->nama_ekstrakurikuler ?? '-' }}</div>

        <div class="col-md-3 font-weight-bold mt-2">Pembina</div>
        <div class="col-md-9 mt-2">: {{ $ekskul->pembina_nama ?? ($ekskul->pembina?->pengguna?->nama ?? '-') }}</div>
      </div>
    </div>
  </div>

  {{-- TOOLBAR BUTTONS --}}
  <div class="d-flex justify-content-between align-items-center mb-2">
    <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalTambahAnggota">
      <i class="fas fa-plus"></i> Tambah Anggota
    </button>

    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#modalHapusAnggota">
      <i class="fas fa-trash"></i> Hapus Anggota
    </button>
  </div>

  {{-- SEARCH (UI mirip gambar) --}}
  <div class="d-flex justify-content-end mb-2">
    <div style="width:220px;">
      <input type="text" class="form-control form-control-sm" id="searchMain" placeholder="Cari...">
    </div>
  </div>

  {{-- FORM UPDATE (BULK SIMPAN NILAI/DESKRIPSI) --}}
  <form method="POST" action="{{ route('guru.ekskul.anggota.update', $ekskul->id) }}">
    @csrf

    <div class="card">
      <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-sm mb-0" id="tableMain">
          <thead class="bg-dark text-white">
            <tr>
              <th style="width:60px;">No.</th>
              <th>Nama</th>
              <th style="width:170px;">NIS</th>
              <th style="width:140px;">Kelas</th>
              <th style="width:180px;">Predikat</th>
              <th>Deskripsi</th>
            </tr>
          </thead>
          <tbody>
            @php
              $opsiPredikat = ['Sangat Baik', 'Baik', 'Cukup', 'Kurang'];
              $startNo = 1;
              if (method_exists($anggota, 'firstItem') && $anggota->firstItem()) {
                $startNo = $anggota->firstItem();
              }
            @endphp

            @forelse($anggota as $i => $a)
              @php
                $s = $a->siswa;
                $no = $startNo + $i;
              @endphp

              <tr>
                <td class="text-center align-top">{{ $no }}</td>
                <td class="align-top">{{ $s->nama_siswa ?? '-' }}</td>
                <td class="align-top">{{ $s->nis ?? '-' }}</td>
                <td class="align-top">{{ $s->kelas->nama_kelas ?? '-' }}</td>

                <td class="align-top">
                  <select name="nilai[{{ $a->id }}][predikat]" class="form-control form-control-sm">
                    <option value="">-- Pilih --</option>
                    @foreach($opsiPredikat as $op)
                      <option value="{{ $op }}" @selected(($a->predikat ?? '') === $op)>{{ $op }}</option>
                    @endforeach
                  </select>
                </td>

                <td class="align-top">
                  <textarea
                    name="nilai[{{ $a->id }}][deskripsi]"
                    class="form-control form-control-sm"
                    rows="2"
                    placeholder="Deskripsi...">{{ old("nilai.$a->id.deskripsi", $a->deskripsi ?? '') }}</textarea>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center text-muted py-4">Belum ada anggota.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- FOOTER FIX: info kiri, kanan = pagination + button (button selalu pojok kanan) --}}
      <div class="card-footer d-flex align-items-center">
        <div class="text-muted small">
          @if(method_exists($anggota, 'total'))
            Menampilkan {{ $anggota->firstItem() ?? 0 }} - {{ $anggota->lastItem() ?? 0 }} dari {{ $anggota->total() }} data
          @else
            Menampilkan {{ $anggota->count() ? '1 - '.$anggota->count().' dari '.$anggota->count().' data' : '0 data' }}
          @endif
        </div>

        <div class="ml-auto d-flex align-items-center">
          @if(method_exists($anggota, 'links'))
            <div class="mr-3">
              {{-- biar pagination tidak “ngangkat” footer --}}
              {{ $anggota->onEachSide(1)->links() }}
            </div>
          @endif

          <button class="btn btn-primary px-4" type="submit">
            Simpan Perubahan
          </button>
        </div>
      </div>
    </div>
  </form>

</div>

{{-- =========================
     MODAL: TAMBAH ANGGOTA
     ========================= --}}
<div class="modal fade" id="modalTambahAnggota" tabindex="-1" aria-labelledby="modalTambahAnggotaLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="modalTambahAnggotaLabel">
          Tambah Anggota {{ $ekskul->nama_ekskul ?? $ekskul->nama_ekstrakurikuler ?? 'Ekskul' }}
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div class="d-flex align-items-center" style="gap:10px;">
            <span class="text-muted">Tampilkan</span>
            <select class="form-control form-control-sm" style="width:90px;" disabled>
              <option selected>10</option>
              <option>25</option>
              <option>50</option>
              <option>100</option>
            </select>
            <span class="text-muted">data</span>
          </div>

          <div style="width:240px;">
            <input type="text" class="form-control form-control-sm" id="searchTambah" placeholder="Cari...">
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-bordered table-sm mb-0" id="tableTambah">
            <thead class="bg-dark text-white">
              <tr>
                <th style="width:60px;">No.</th>
                <th>Nama</th>
                <th style="width:170px;">NIS</th>
                <th style="width:140px;">Kelas</th>
                <th style="width:140px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @php
                $kandidatData = $kandidat ?? ($siswa ?? collect());
                $startNoK = 1;
                if (method_exists($kandidatData, 'firstItem') && $kandidatData->firstItem()) {
                  $startNoK = $kandidatData->firstItem();
                }
              @endphp

              @forelse($kandidatData as $i => $s)
                <tr>
                  <td class="text-center align-top">{{ $startNoK + $i }}</td>
                  <td class="align-top">{{ $s->nama_siswa ?? '-' }}</td>
                  <td class="align-top">{{ $s->nis ?? '-' }}</td>
                  <td class="align-top">{{ $s->kelas->nama_kelas ?? '-' }}</td>
                  <td class="text-center align-top">
                    <form method="POST" action="{{ route('guru.ekskul.anggota.store', $ekskul->id) }}" class="d-inline">
                      @csrf
                      <input type="hidden" name="data_siswa_id" value="{{ $s->id }}">
                      <button class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambahkan
                      </button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center text-muted py-4">Tidak ada siswa kandidat.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-2">
          <div class="text-muted small">
            @if(method_exists($kandidatData, 'total'))
              Menampilkan {{ $kandidatData->firstItem() ?? 0 }} - {{ $kandidatData->lastItem() ?? 0 }} dari {{ $kandidatData->total() }} data
            @else
              Menampilkan {{ $kandidatData->count() ? '1 - '.$kandidatData->count().' dari '.$kandidatData->count().' data' : '0 data' }}
            @endif
          </div>

          @if(method_exists($kandidatData, 'links'))
            <div class="mb-0">
              {{ $kandidatData->onEachSide(1)->links() }}
            </div>
          @endif
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-light" type="button" data-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>

{{-- =========================
     MODAL: HAPUS ANGGOTA
     ========================= --}}
<div class="modal fade" id="modalHapusAnggota" tabindex="-1" aria-labelledby="modalHapusAnggotaLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="modalHapusAnggotaLabel">
          Hapus Anggota {{ $ekskul->nama_ekskul ?? $ekskul->nama_ekstrakurikuler ?? 'Ekskul' }}
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div class="d-flex align-items-center" style="gap:10px;">
            <span class="text-muted">Tampilkan</span>
            <select class="form-control form-control-sm" style="width:90px;" disabled>
              <option selected>10</option>
              <option>25</option>
              <option>50</option>
              <option>100</option>
            </select>
            <span class="text-muted">data</span>
          </div>

          <div style="width:240px;">
            <input type="text" class="form-control form-control-sm" id="searchHapus" placeholder="Cari...">
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-bordered table-sm mb-0" id="tableHapus">
            <thead class="bg-dark text-white">
              <tr>
                <th style="width:60px;">No.</th>
                <th>Nama</th>
                <th style="width:170px;">NIS</th>
                <th style="width:140px;">Kelas</th>
                <th style="width:140px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @php
                $startNoA = 1;
                if (method_exists($anggota, 'firstItem') && $anggota->firstItem()) {
                  $startNoA = $anggota->firstItem();
                }
              @endphp

              @forelse($anggota as $i => $a)
                @php $s = $a->siswa; @endphp
                <tr>
                  <td class="text-center align-top">{{ $startNoA + $i }}</td>
                  <td class="align-top">{{ $s->nama_siswa ?? '-' }}</td>
                  <td class="align-top">{{ $s->nis ?? '-' }}</td>
                  <td class="align-top">{{ $s->kelas->nama_kelas ?? '-' }}</td>
                  <td class="text-center align-top">
                    <form method="POST"
                          action="{{ route('guru.ekskul.anggota.destroy', [$ekskul->id, $a->id]) }}"
                          class="d-inline"
                          onsubmit="return confirm('Hapus anggota ini?')">
                      @csrf
                      @method('DELETE')
                      <button class="btn btn-danger btn-sm">
                        <i class="fas fa-trash"></i> Hapus
                      </button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center text-muted py-4">Belum ada anggota.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-2">
          <div class="text-muted small">
            @if(method_exists($anggota, 'total'))
              Menampilkan {{ $anggota->firstItem() ?? 0 }} - {{ $anggota->lastItem() ?? 0 }} dari {{ $anggota->total() }} data
            @else
              Menampilkan {{ $anggota->count() ? '1 - '.$anggota->count().' dari '.$anggota->count().' data' : '0 data' }}
            @endif
          </div>

          @if(method_exists($anggota, 'links'))
            <div class="mb-0">
              {{ $anggota->onEachSide(1)->links() }}
            </div>
          @endif
        </div>

      </div>

      <div class="modal-footer">
        <button class="btn btn-light" type="button" data-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>

@push('scripts')
<script>
(function () {
  function simpleFilter(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    if (!input || !table) return;

    input.addEventListener('input', function () {
      const q = (this.value || '').toLowerCase();
      const rows = table.querySelectorAll('tbody tr');

      rows.forEach(tr => {
        const text = (tr.innerText || '').toLowerCase();
        tr.style.display = text.includes(q) ? '' : 'none';
      });
    });
  }

  simpleFilter('searchMain', 'tableMain');
  simpleFilter('searchTambah', 'tableTambah');
  simpleFilter('searchHapus', 'tableHapus');
})();
</script>
@endpush

@endsection
