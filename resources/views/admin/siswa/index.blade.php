@extends('layouts.adminlte')

@section('page_title','Data Siswa')

@section('content')

@php
  // ambil query agar persist di UI
  $limit  = (int) request('limit', $limit ?? 10);
  $q      = (string) request('q', $q ?? '');

  // filter tambahan (nanti dipakai controller)
  $kelas  = (string) request('kelas', $kelas ?? '');
  $jk     = (string) request('jk', $jk ?? '');          // L / P / ''
  $status = (string) request('status', $status ?? '');  // aktif / tidak aktif / ''
@endphp

@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
  <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if(session('warning'))
  <div class="alert alert-warning">{!! session('warning') !!}</div>
@endif

@if($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
    </ul>
  </div>
@endif

<div class="card card-dark">
  <div class="card-header">
    <h3 class="card-title">Data Siswa</h3>
  </div>

  <div class="card-body">

    {{-- TOOLBAR ATAS --}}
    <div class="d-flex justify-content-between mb-3">
      <div>
        <a href="{{ route('admin.siswa.create') }}" class="btn btn-primary btn-sm">
          <i class="fas fa-plus"></i> Tambah Siswa
        </a>

        <button id="btnHapusBeberapa" class="btn btn-danger btn-sm" disabled>
          <i class="fas fa-trash"></i> Hapus Beberapa
        </button>
      </div>

      <div>
        <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalFilter">
          <i class="fas fa-filter"></i> Filter Data
        </button>

        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modalImportSiswa">
          <i class="fas fa-file-import"></i> Import Data Siswa
        </button>
      </div>
    </div>

    {{-- FILTER BAR (SERVER SIDE) --}}
    <form id="formFilterBar" method="GET" action="{{ route('admin.siswa.index') }}"
          class="d-flex justify-content-between align-items-center mb-2">

      <div>
        <label class="mb-0">
          Tampilkan
          <select name="limit" class="custom-select custom-select-sm w-auto" onchange="this.form.submit()">
            <option value="10"  {{ $limit===10 ? 'selected' : '' }}>10</option>
            <option value="25"  {{ $limit===25 ? 'selected' : '' }}>25</option>
            <option value="50"  {{ $limit===50 ? 'selected' : '' }}>50</option>
            <option value="100" {{ $limit===100 ? 'selected' : '' }}>100</option>
          </select>
          data
        </label>

        {{-- persist filter modal --}}
        <input type="hidden" name="kelas" value="{{ $kelas }}">
        <input type="hidden" name="jk" value="{{ $jk }}">
        <input type="hidden" name="status" value="{{ $status }}">
      </div>

      <div class="d-flex">
        <input type="text" name="q" value="{{ $q }}"
               class="form-control form-control-sm"
               placeholder="Cari nama / NIS / NISN..."
               style="width:260px">
        <button class="btn btn-sm btn-secondary ml-2" type="submit">
          <i class="fas fa-search"></i>
        </button>
      </div>
    </form>

    {{-- FORM HAPUS BEBERAPA --}}
    <form id="formHapusMassal" action="{{ route('admin.siswa.destroyMultiple') }}" method="POST">
      @csrf
      @method('DELETE')

      <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
          <thead>
            <tr>
              <th width="45" class="text-center">
                <input type="checkbox" id="checkAll">
              </th>
              <th width="60">No</th>
              <th>Nama</th>
              <th width="90">Kelas</th>
              <th>NIS</th>
              <th>NISN</th>
              <th width="60" class="text-center">L/P</th>
              <th width="110" class="text-center">Status</th>
              <th width="200">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($siswa as $i => $s)
              @php
                $kelasNama = optional($s->kelas)->nama_kelas ?? '-';
                $jkVal = $s->jenis_kelamin ?? '-';
                $st = strtolower(trim((string)($s->status_siswa ?? 'aktif')));
                if ($st === 'nonaktif' || $st === 'non aktif') $st = 'tidak aktif';
                $stLabel = ($st === 'aktif') ? 'AKTIF' : 'TIDAK AKTIF';
              @endphp
              <tr>
                <td class="text-center">
                  <input type="checkbox" class="checkItem" name="ids[]" value="{{ $s->id }}">
                </td>
                <td>{{ ($siswa->firstItem() ?? 1) + $i }}</td>
                <td>{{ $s->nama_siswa ?? '-' }}</td>
                <td>{{ $kelasNama }}</td>
                <td>{{ $s->nis ?? '-' }}</td>
                <td>{{ $s->nisn ?? '-' }}</td>
                <td class="text-center">{{ $jkVal }}</td>
                <td class="text-center">
                  <span class="badge {{ $st === 'aktif' ? 'badge-success' : 'badge-secondary' }}">
                    {{ $stLabel }}
                  </span>
                </td>
                <td>
                  <a href="{{ route('admin.siswa.show', $s->id) }}" class="btn btn-success btn-xs">
                    <i class="fas fa-eye"></i> Detail
                  </a>

                  <a href="{{ route('admin.siswa.edit', $s->id) }}" class="btn btn-warning btn-xs">
                    <i class="fas fa-edit"></i> Edit
                  </a>

                  <form action="{{ route('admin.siswa.destroy', $s->id) }}"
                        method="POST" class="d-inline"
                        onsubmit="return confirm('Hapus data siswa ini?')">
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
                <td colspan="9" class="text-center text-muted">
                  Data siswa belum tersedia
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </form>

    {{-- PAGINATION RAPI --}}
    <div class="d-flex justify-content-between align-items-center">
      <div class="text-muted">
        Menampilkan
        {{ $siswa->count() ? $siswa->firstItem() : 0 }} - {{ $siswa->count() ? $siswa->lastItem() : 0 }}
        dari {{ $siswa->total() }} data
      </div>
      <div>
        {{ $siswa->onEachSide(1)->links('pagination::bootstrap-4') }}
      </div>
    </div>

  </div>
</div>

{{-- ================= MODAL FILTER (SERVER SIDE) ================= --}}
<div class="modal fade" id="modalFilter" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-info">
        <h4 class="modal-title"><i class="fas fa-filter"></i> Filter Data Siswa</h4>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>

      <form method="GET" action="{{ route('admin.siswa.index') }}">
        <div class="modal-body">

          <div class="row">
            <div class="col-md-6">
              <label>Kelas</label>
              <input type="text" name="kelas" class="form-control"
                     value="{{ $kelas }}" placeholder="Contoh: VII A">
              <small class="text-muted">Boleh sebagian (mis: VII)</small>
            </div>

            <div class="col-md-3">
              <label>Jenis Kelamin</label>
              <select name="jk" class="form-control">
                <option value=""  {{ $jk==='' ? 'selected' : '' }}>Semua</option>
                <option value="L" {{ strtoupper($jk)==='L' ? 'selected' : '' }}>L</option>
                <option value="P" {{ strtoupper($jk)==='P' ? 'selected' : '' }}>P</option>
              </select>
            </div>

            <div class="col-md-3">
              <label>Status</label>
              <select name="status" class="form-control">
                <option value="" {{ $status==='' ? 'selected' : '' }}>Semua</option>
                <option value="aktif" {{ strtolower($status)==='aktif' ? 'selected' : '' }}>AKTIF</option>
                <option value="tidak aktif" {{ strtolower($status)==='tidak aktif' ? 'selected' : '' }}>TIDAK AKTIF</option>
              </select>
            </div>
          </div>

          <hr>

          {{-- bawa param yang sudah ada agar tidak hilang --}}
          <input type="hidden" name="limit" value="{{ $limit }}">
          <input type="hidden" name="q" value="{{ $q }}">

        </div>

        <div class="modal-footer d-flex justify-content-between">
          <a href="{{ route('admin.siswa.index') }}" class="btn btn-secondary">Reset</a>

          <div>
            <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
            <button type="submit" class="btn btn-primary">Terapkan</button>
          </div>
        </div>
      </form>

    </div>
  </div>
</div>

{{-- ================= MODAL IMPORT SISWA ================= --}}
<div class="modal fade" id="modalImportSiswa" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title">
          <i class="fas fa-file-import"></i> Import Data Siswa (XLSX)
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form action="{{ route('admin.siswa.import') }}" method="POST" enctype="multipart/form-data" id="formImportSiswa">
        @csrf

        <div class="modal-body">
          <div class="alert alert-info mb-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
              <div class="mb-2 mb-md-0">
                Download format terlebih dahulu, isi sesuai header, lalu upload file XLSX.
              </div>
              <a href="{{ route('admin.siswa.import.format') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-download"></i> Download Format
              </a>
            </div>
          </div>

          <div class="form-group">
            <label>File XLSX</label>
            <div class="custom-file">
              <input type="file" name="file" class="custom-file-input" id="importFile" accept=".xlsx" required>
              <label class="custom-file-label" for="importFile">Pilih file...</label>
            </div>
            <small class="text-muted">Hanya .xlsx</small>
          </div>

          <div class="form-group mb-0">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="confirmImport" name="confirm" value="1" required>
              <label class="custom-control-label" for="confirmImport">
                Saya sudah memastikan format sesuai & data yang diimport benar.
              </label>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-warning" id="btnSubmitImport" disabled>
            <i class="fas fa-upload"></i> Upload & Import
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
(function(){
  const btnHapusBeberapa = document.getElementById('btnHapusBeberapa');
  const formHapusMassal  = document.getElementById('formHapusMassal');
  const checkAll         = document.getElementById('checkAll');

  function items(){ return Array.from(document.querySelectorAll('.checkItem')); }

  function refreshBtn(){
    const checked = items().filter(x => x.checked).length;
    btnHapusBeberapa.disabled = (checked === 0);
  }

  if(checkAll){
    checkAll.addEventListener('change', function(){
      items().forEach(x => x.checked = checkAll.checked);
      refreshBtn();
    });
  }

  document.addEventListener('change', function(e){
    if(e.target.classList.contains('checkItem')){
      const list = items();
      const allChecked = list.length > 0 && list.every(x => x.checked);
      if(checkAll) checkAll.checked = allChecked;
      refreshBtn();
    }
  });

  if(btnHapusBeberapa){
    btnHapusBeberapa.addEventListener('click', function(){
      const checked = items().filter(x => x.checked);
      if(checked.length === 0) return;

      if(confirm('Yakin hapus ' + checked.length + ' data siswa terpilih?')){
        formHapusMassal.submit();
      }
    });
  }

  // ===== IMPORT MODAL =====
  const importFile = document.getElementById('importFile');
  const confirmImport = document.getElementById('confirmImport');
  const btnSubmitImport = document.getElementById('btnSubmitImport');

  function updateImportButton(){
    const hasFile = importFile && importFile.files && importFile.files.length > 0;
    const ok = confirmImport && confirmImport.checked;
    if(btnSubmitImport) btnSubmitImport.disabled = !(hasFile && ok);
  }

  if(importFile){
    importFile.addEventListener('change', function(e){
      const label = document.querySelector('label[for="importFile"]');
      if(label && e.target.files.length){
        label.textContent = e.target.files[0].name;
      }
      updateImportButton();
    });
  }

  if(confirmImport){
    confirmImport.addEventListener('change', updateImportButton);
  }

  $('#modalImportSiswa').on('hidden.bs.modal', function(){
    const form = document.getElementById('formImportSiswa');
    if(form) form.reset();
    const label = document.querySelector('label[for="importFile"]');
    if(label) label.textContent = 'Pilih file...';
    updateImportButton();
  });

  refreshBtn();
  updateImportButton();
})();
</script>
@endpush
