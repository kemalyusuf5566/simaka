@extends('layouts.adminlte')

@section('page_title','Data Guru')

@section('content')

@php
  // Ambil filter dari query string agar persist
  $status = request('status', ''); // 1 / 0 / ''
  $jk     = request('jk', '');     // L / P / ''
@endphp

<div class="card card-dark">
  <div class="card-header">
    <h3 class="card-title">Data Guru</h3>
  </div>

  <div class="card-body">

    {{-- TOOLBAR ATAS --}}
    <div class="d-flex justify-content-between mb-3">
      <div>
        <a href="{{ route('admin.guru.create') }}" class="btn btn-primary btn-sm">
          <i class="fas fa-plus"></i> Tambah Guru
        </a>

        <button id="btnHapusBeberapa" class="btn btn-danger btn-sm" disabled>
          <i class="fas fa-trash"></i> Hapus Beberapa
        </button>
      </div>

      <div>
        <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalFilter">
          <i class="fas fa-filter"></i> Filter Data
        </button>

        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modalImportGuru">
          <i class="fas fa-file-import"></i> Import Data Guru
        </button>
      </div>
    </div>

    {{-- FILTER BAR --}}
    <form id="formFilterBar" method="GET" action="{{ route('admin.guru.index') }}" class="d-flex justify-content-between align-items-center mb-2">
      <div>
        <label class="mb-0">
          Tampilkan
          <select name="limit" class="custom-select custom-select-sm w-auto" onchange="this.form.submit()">
            <option value="10"  {{ ($limit ?? 10)==10 ? 'selected' : '' }}>10</option>
            <option value="25"  {{ ($limit ?? 10)==25 ? 'selected' : '' }}>25</option>
            <option value="50"  {{ ($limit ?? 10)==50 ? 'selected' : '' }}>50</option>
            <option value="100" {{ ($limit ?? 10)==100 ? 'selected' : '' }}>100</option>
          </select>
          data
        </label>

        {{-- Persist filter lanjutan dari modal --}}
        <input type="hidden" name="status" value="{{ $status }}">
        <input type="hidden" name="jk" value="{{ $jk }}">
      </div>

      <div class="d-flex">
        <input type="text" name="q" value="{{ $q ?? '' }}"
               class="form-control form-control-sm"
               placeholder="Cari..."
               style="width:220px">
        <button class="btn btn-sm btn-secondary ml-2" type="submit">
          <i class="fas fa-search"></i>
        </button>
      </div>
    </form>

    {{-- FORM HAPUS BEBERAPA --}}
    <form id="formDestroyMultiple" method="POST" action="{{ route('admin.guru.destroyMultiple') }}">
      @csrf
      @method('DELETE')

      <table class="table table-bordered table-hover">
        <thead class="bg-secondary">
          <tr>
            <th width="40" class="text-center">
              <input type="checkbox" id="checkAll">
            </th>
            <th width="60">No</th>
            <th>Nama</th>
            <th width="60">L/P</th>
            <th>NIP</th>
            <th>NUPTK</th>
            <th>Status</th>
            <th width="220">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($guru as $i => $g)
          <tr>
            <td class="text-center">
              <input type="checkbox" class="checkItem" name="ids[]" value="{{ $g->id }}">
            </td>
            <td>{{ $guru->firstItem() + $i }}</td>
            <td>{{ $g->pengguna->nama ?? '-' }}</td>
            <td class="text-center">{{ $g->jenis_kelamin ?? '-' }}</td>
            <td>{{ $g->nip ?? '-' }}</td>
            <td>{{ $g->nuptk ?? '-' }}</td>
            <td>
              @php $aktif = (bool)($g->pengguna->status_aktif ?? false); @endphp
              <span class="badge {{ $aktif ? 'badge-success' : 'badge-danger' }}">
                {{ $aktif ? 'AKTIF' : 'TIDAK AKTIF' }}
              </span>
            </td>
            <td>
              <button type="button"
                      class="btn btn-success btn-xs btn-detail-guru"
                      data-id="{{ $g->id }}">
                <i class="fas fa-eye"></i> Detail
              </button>

              <a href="{{ route('admin.guru.edit',$g->id) }}"
                 class="btn btn-warning btn-xs">
                <i class="fas fa-edit"></i> Edit
              </a>

              <form action="{{ route('admin.guru.destroy',$g->id) }}"
                    method="POST" class="d-inline"
                    onsubmit="return confirm('Hapus data guru ini?')">
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
            <td colspan="8" class="text-center text-muted">
              Data guru belum tersedia
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </form>

    {{-- PAGINATION RAPI --}}
    <div class="d-flex justify-content-between align-items-center">
      <div class="text-muted">
        Menampilkan {{ $guru->count() ? $guru->firstItem() : 0 }} - {{ $guru->count() ? $guru->lastItem() : 0 }}
        dari {{ $guru->total() }} data
      </div>
      <div>
        {{ $guru->links() }}
      </div>
    </div>

  </div>
</div>

{{-- MODAL FILTER (AKTIF) --}}
<div class="modal fade" id="modalFilter" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Filter Data Guru</h4>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>

      <form id="formModalFilter" method="GET" action="{{ route('admin.guru.index') }}">
        <div class="modal-body">

          <div class="row">
            <div class="col-md-6">
              <label>Status Guru</label>
              <select name="status" class="form-control">
                <option value=""  {{ $status==='' ? 'selected' : '' }}>Semua</option>
                <option value="1" {{ $status==='1' ? 'selected' : '' }}>AKTIF</option>
                <option value="0" {{ $status==='0' ? 'selected' : '' }}>TIDAK AKTIF</option>
              </select>
            </div>

            <div class="col-md-6">
              <label>Jenis Kelamin</label>
              <select name="jk" class="form-control">
                <option value=""  {{ $jk==='' ? 'selected' : '' }}>Semua</option>
                <option value="L" {{ $jk==='L' ? 'selected' : '' }}>Laki-laki</option>
                <option value="P" {{ $jk==='P' ? 'selected' : '' }}>Perempuan</option>
              </select>
            </div>
          </div>

          <hr>

          {{-- Bawa parameter yang sudah ada agar tidak hilang --}}
          <input type="hidden" name="limit" value="{{ $limit ?? 10 }}">
          <input type="hidden" name="q" value="{{ $q ?? '' }}">

        </div>

        <div class="modal-footer d-flex justify-content-between">
          <a href="{{ route('admin.guru.index') }}" class="btn btn-secondary">
            Reset
          </a>

          <div>
            <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
            <button type="submit" class="btn btn-primary">Terapkan</button>
          </div>
        </div>
      </form>

    </div>
  </div>
</div>

{{-- MODAL IMPORT (samakan dengan siswa) --}}
<div class="modal fade" id="modalImportGuru" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Import Data Guru</h4>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>

      <form method="POST" action="{{ route('admin.guru.import') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">

          <div class="alert alert-warning">
            <b>Penting!</b> File yang diunggah harus berupa dokumen Microsoft Excel dengan ekstensi <b>.xlsx</b><br>
            <a href="{{ route('admin.guru.import.format') }}">Download Format Import</a>
          </div>

          <div class="form-group">
            <input type="file" name="file" class="form-control" accept=".xlsx" required>
          </div>

          <div class="form-group d-flex justify-content-between align-items-center mt-3">
            <div>
              <label class="mb-0">
                <input type="checkbox" name="yakin" value="1" required>
                Saya yakin sudah mengisi dengan benar
              </label>
            </div>
            <button class="btn btn-primary">
              Simpan
            </button>
          </div>

        </div>
      </form>

    </div>
  </div>
</div>

{{-- MODAL DETAIL GURU --}}
<div class="modal fade" id="modalDetailGuru" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Detail Guru</h4>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>

      <div class="modal-body" id="detailGuruContent">
        <div class="text-center text-muted">Memuat data...</div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <a href="#" id="btnEditGuru" class="btn btn-warning">Edit</a>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
  const btn = document.getElementById('btnHapusBeberapa');
  const checkAll = document.getElementById('checkAll');

  function refreshBtn() {
    const any = document.querySelectorAll('.checkItem:checked').length > 0;
    btn.disabled = !any;
  }

  if (checkAll) {
    checkAll.addEventListener('change', function () {
      document.querySelectorAll('.checkItem').forEach(cb => cb.checked = checkAll.checked);
      refreshBtn();
    });
  }

  document.addEventListener('change', function (e) {
    if (e.target.classList.contains('checkItem')) {
      refreshBtn();
      const total = document.querySelectorAll('.checkItem').length;
      const checked = document.querySelectorAll('.checkItem:checked').length;
      if (checkAll) checkAll.checked = (total === checked);
    }
  });

  btn.addEventListener('click', function () {
    if (!confirm('Hapus beberapa data guru yang dipilih?')) return;
    document.getElementById('formDestroyMultiple').submit();
  });

  // DETAIL MODAL AJAX
  $(document).on('click', '.btn-detail-guru', function () {
    let id = $(this).data('id');
    $('#modalDetailGuru').modal('show');
    $('#detailGuruContent').html('<div class="p-5 text-center text-muted">Loading...</div>');

    $.get('/admin/guru/' + id + '/detail', function (res) {
      $('#detailGuruContent').html(res);
      $('#btnEditGuru').attr('href', '/admin/guru/' + id + '/edit');
    });
  });
})();
</script>
@endpush
