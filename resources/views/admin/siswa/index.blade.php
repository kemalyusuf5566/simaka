@extends('layouts.adminlte')

@section('page_title','Data Siswa')

@section('content')

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

    {{-- FILTER BAR --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
      <div>
        <label class="mb-0">
          Tampilkan
          <select id="limitData" class="custom-select custom-select-sm w-auto">
            <option value="10" selected>10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="9999">Semua</option>
          </select>
          data
        </label>
      </div>

      <div>
        <input type="text" id="searchData"
               class="form-control form-control-sm"
               placeholder="Cari..."
               style="width:220px">
      </div>
    </div>



    {{-- FORM HAPUS MASSAL --}}
    <form id="formHapusMassal" action="{{ route('admin.siswa.destroyMultiple') }}" method="POST">
      @csrf
      @method('DELETE')

      <table id="table-siswa" class="table table-bordered table-hover">
        <thead class="bg-secondary">
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
            $jk = $s->jenis_kelamin ?? '-';
            $status = strtolower(trim((string)($s->status_siswa ?? 'aktif')));
            $statusLabel = ($status === 'aktif') ? 'AKTIF' : 'TIDAK AKTIF';
          @endphp
          <tr class="row-siswa"
              data-nama="{{ strtolower($s->nama_siswa ?? '') }}"
              data-kelas="{{ strtolower($kelasNama) }}"
              data-jk="{{ strtolower($jk) }}"
              data-status="{{ $status }}">
            <td class="text-center">
              <input type="checkbox" class="checkItem" name="ids[]" value="{{ $s->id }}">
            </td>
            <td>{{ $i + 1 }}</td>
            <td>{{ $s->nama_siswa ?? '-' }}</td>
            <td>{{ $kelasNama }}</td>
            <td>{{ $s->nis ?? '-' }}</td>
            <td>{{ $s->nisn ?? '-' }}</td>
            <td class="text-center">{{ $jk }}</td>
            <td class="text-center">
              @if($status === 'aktif')
                <span class="badge badge-success">{{ $statusLabel }}</span>
              @else
                <span class="badge badge-secondary">{{ $statusLabel }}</span>
              @endif
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
    </form>

  </div>
</div>


{{-- ================= MODAL FILTER ================= --}}
<div class="modal fade" id="modalFilter" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info">
        <h5 class="modal-title"><i class="fas fa-filter"></i> Filter Data</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">

        <div class="form-group">
          <label>Kelas</label>
          <input type="text" id="filterKelas" class="form-control" placeholder="Contoh: VII A">
        </div>

        <div class="form-group">
          <label>Jenis Kelamin</label>
          <select id="filterJk" class="form-control">
            <option value="">Semua</option>
            <option value="l">L</option>
            <option value="p">P</option>
          </select>
        </div>

        <div class="form-group mb-0">
          <label>Status Siswa</label>
          <select id="filterStatus" class="form-control">
            <option value="">Semua</option>
            <option value="aktif">Aktif</option>
            <option value="tidak aktif">Tidak Aktif</option>
            <option value="nonaktif">Nonaktif</option>
          </select>
          <small class="text-muted">* disamakan otomatis saat filter (aktif vs tidak aktif/nonaktif)</small>
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="btnResetFilter">
          Reset
        </button>
        <button type="button" class="btn btn-info" id="btnApplyFilter" data-dismiss="modal">
          Terapkan
        </button>
      </div>
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

          {{-- area pesan error server (opsional) --}}
          @if ($errors->any())
            <div class="alert alert-danger mt-3 mb-0">
              <ul class="mb-0">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          @if (session('error'))
            <div class="alert alert-danger mt-3 mb-0">{!! session('error') !!}</div>
          @endif

          @if (session('warning'))
            <div class="alert alert-warning mt-3 mb-0">{!! session('warning') !!}</div>
          @endif

          @if (session('success'))
            <div class="alert alert-success mt-3 mb-0">{!! session('success') !!}</div>
          @endif
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

{{-- ================= SCRIPT ================= --}}
@push('scripts')
<script>
(function(){
  const table = document.getElementById('table-siswa');
  const rows = Array.from(table.querySelectorAll('tbody tr.row-siswa'));

  const searchEl = document.getElementById('searchData');
  const limitEl = document.getElementById('limitData');

  const checkAll = document.getElementById('checkAll');
  const checkItems = () => Array.from(document.querySelectorAll('.checkItem'));
  const btnHapusBeberapa = document.getElementById('btnHapusBeberapa');
  const formHapusMassal = document.getElementById('formHapusMassal');

  const filterKelas = document.getElementById('filterKelas');
  const filterJk = document.getElementById('filterJk');
  const filterStatus = document.getElementById('filterStatus');

  const btnResetFilter = document.getElementById('btnResetFilter');
  const btnApplyFilter = document.getElementById('btnApplyFilter');

  let activeFilter = { kelas:'', jk:'', status:'' };

  function normalizeStatus(s){
    s = (s || '').toLowerCase().trim();
    if(s === '') return '';
    if(s === 'aktif') return 'aktif';
    if(s === 'tidak aktif' || s === 'nonaktif' || s === 'non aktif') return 'tidak aktif';
    return s;
  }

  function updateHapusButton(){
    const checked = checkItems().filter(ch => ch.checked).length;
    btnHapusBeberapa.disabled = checked === 0;
  }

  function applyView(){
    const q = (searchEl.value || '').toLowerCase().trim();
    const limit = parseInt(limitEl.value || '10', 10);

    let shownCount = 0;

    rows.forEach(r => {
      const nama = r.dataset.nama || '';
      const kelas = r.dataset.kelas || '';
      const jk = r.dataset.jk || '';
      const status = normalizeStatus(r.dataset.status || '');

      const matchSearch = (q === '') || (nama.includes(q) || kelas.includes(q));
      const matchKelas = (activeFilter.kelas === '') || kelas.includes(activeFilter.kelas);
      const matchJk = (activeFilter.jk === '') || jk === activeFilter.jk;
      const matchStatus = (activeFilter.status === '') || status === activeFilter.status;

      const match = matchSearch && matchKelas && matchJk && matchStatus;

      if(match && (limit === 9999 || shownCount < limit)){
        r.style.display = '';
        shownCount++;
      } else {
        r.style.display = 'none';
      }
    });
  }

  // events search/limit
  searchEl.addEventListener('input', applyView);
  limitEl.addEventListener('change', applyView);

  // checkbox all
  checkAll.addEventListener('change', function(){
    const items = checkItems();
    items.forEach(ch => ch.checked = checkAll.checked);
    updateHapusButton();
  });

  document.addEventListener('change', function(e){
    if(e.target.classList.contains('checkItem')){
      // kalau ada 1 saja tidak checked -> checkAll false
      const items = checkItems();
      const allChecked = items.length > 0 && items.every(x => x.checked);
      checkAll.checked = allChecked;
      updateHapusButton();
    }
  });

  // hapus massal
  btnHapusBeberapa.addEventListener('click', function(){
    const items = checkItems().filter(ch => ch.checked);
    if(items.length === 0) return;

    if(confirm('Yakin hapus ' + items.length + ' data siswa terpilih?')){
      formHapusMassal.submit();
    }
  });

  // filter modal
  btnApplyFilter.addEventListener('click', function(){
    activeFilter.kelas = (filterKelas.value || '').toLowerCase().trim();
    activeFilter.jk = (filterJk.value || '').toLowerCase().trim();
    activeFilter.status = normalizeStatus(filterStatus.value || '');
    applyView();
  });

  btnResetFilter.addEventListener('click', function(){
    filterKelas.value = '';
    filterJk.value = '';
    filterStatus.value = '';
    activeFilter = { kelas:'', jk:'', status:'' };
    applyView();
  });

  // init
  applyView();
  updateHapusButton();
})();

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
      // update label custom-file
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

  // saat modal ditutup, reset form
  $('#modalImportSiswa').on('hidden.bs.modal', function(){
    const form = document.getElementById('formImportSiswa');
    if(form) form.reset();
    const label = document.querySelector('label[for="importFile"]');
    if(label) label.textContent = 'Pilih file...';
    updateImportButton();
  });
</script>
@endpush

@endsection
