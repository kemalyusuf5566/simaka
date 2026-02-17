@extends('layouts.adminlte')

@section('title','Data Mata Pelajaran')

@section('content')
<div class="container-fluid">

  <div class="d-flex align-items-center mb-3">
    <h4 class="mb-0">Data Mata Pelajaran</h4>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="card">

    {{-- Toolbar atas (sesuai gambar) --}}
    <div class="card-body pb-2">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalTambahMapel">
            <i class="fas fa-plus"></i> Tambah Mapel
          </button>
        </div>

        <div class="d-flex align-items-center" style="gap:10px;">
          <button class="btn btn-info btn-sm" disabled>
            <i class="fas fa-filter"></i> Filter Data
          </button>

          <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modalKelompokMapel">
            <i class="fas fa-cog"></i> Kelompok Mapel
          </button>
        </div>
      </div>
    </div>

    {{-- Bar tampilkan + cari (UI mirip gambar) --}}
    <div class="card-body pt-0 pb-2">
      <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center" style="gap:10px;">
          <span class="text-muted">Tampilkan</span>
          <select class="form-control form-control-sm" style="width:85px;" disabled>
            <option selected>10</option>
          </select>
          <span class="text-muted">data</span>
        </div>

        <div style="width:220px;">
          <input type="text" class="form-control form-control-sm" id="searchMapel" placeholder="Cari...">
        </div>
      </div>
    </div>

    {{-- Tabel --}}
    <div class="card-body pt-0 table-responsive p-0">
      <table class="table table-bordered table-sm mb-0" id="tableMapel">
        <thead class="bg-dark text-white">
          <tr>
            <th style="width:60px;">No.</th>
            <th>Nama Mapel</th>
            <th style="width:150px;">Singkatan</th>
            <th style="width:220px;">Kelompok</th>
            <th style="width:130px;">Urutan Cetak</th>
            <th style="width:190px;">Aksi</th>
          </tr>
        </thead>

        <tbody>
          @php $startNo = ($mapel->currentPage() - 1) * $mapel->perPage(); @endphp

          @forelse($mapel as $i => $m)
            <tr
              data-nama="{{ strtolower($m->nama_mapel ?? '') }}"
              data-singkatan="{{ strtolower($m->singkatan ?? '') }}"
              data-kelompok="{{ strtolower($m->kelompok_mapel ?? '') }}"
            >
              <td class="text-center align-middle">{{ $startNo + $i + 1 }}</td>
              <td class="align-middle">{{ $m->nama_mapel }}</td>
              <td class="align-middle">{{ $m->singkatan }}</td>
              <td class="align-middle">{{ $m->kelompok_mapel }}</td>
              <td class="align-middle">{{ $m->urutan_cetak }}</td>
              <td class="align-middle">

                {{-- EDIT (popup) --}}
                <button type="button"
                        class="btn btn-warning btn-sm btnEditMapel"
                        data-toggle="modal"
                        data-target="#modalEditMapel"
                        data-id="{{ $m->id }}"
                        data-nama="{{ e($m->nama_mapel) }}"
                        data-singkatan="{{ e($m->singkatan) }}"
                        data-urutan="{{ e($m->urutan_cetak) }}"
                        data-kelompok="{{ e($m->kelompok_mapel) }}">
                  <i class="fas fa-edit"></i> Edit
                </button>

                {{-- HAPUS --}}
                <form action="{{ route('admin.mapel.destroy', $m->id) }}"
                      method="POST"
                      class="d-inline"
                      onsubmit="return confirm('Yakin hapus mapel ini?')">
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
              <td colspan="6" class="text-center text-muted py-4">
                Data mata pelajaran belum tersedia
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Footer pagination (mirip gambar) --}}
    <div class="card-footer d-flex justify-content-between align-items-center">
      <div class="text-muted small">
        Menampilkan {{ $mapel->firstItem() ?? 0 }} - {{ $mapel->lastItem() ?? 0 }} dari {{ $mapel->total() }} data
      </div>
      <div class="pagination-wrap ml-auto">
        {{ $mapel->onEachSide(1)->links('pagination::bootstrap-4') }}
      </div>
    </div>

  </div>
</div>


{{-- =========================
   MODAL: TAMBAH MAPEL (sesuai gambar)
========================= --}}
<div class="modal fade" id="modalTambahMapel" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('admin.mapel.store') }}" class="modal-content" id="formTambahMapel">
      @csrf

      <div class="modal-header">
        <h5 class="modal-title">Tambah Data Mapel</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>

      <div class="modal-body">

        <div class="alert alert-info d-flex justify-content-between align-items-center">
          <div><b>*</b> adalah kolom yang wajib diisi!</div>
          <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>

        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Nama Mapel <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <input type="text" name="nama_mapel" class="form-control" placeholder="Ketik Nama Mapel" required>
          </div>
        </div>

        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Singkatan <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <input type="text" name="singkatan" class="form-control" placeholder="Ketik Singkatan" required>
          </div>
        </div>

        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Urutan Cetak <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <input type="number" name="urutan_cetak" class="form-control" placeholder="Ketik Urutan Cetak" min="1" max="9999" required>
          </div>
        </div>

        <div class="form-group row mb-2">
          <label class="col-sm-4 col-form-label">Kelompok Mapel <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <select name="kelompok_mapel" class="form-control" required>
              <option value="">-- Pilih --</option>
              <option value="Mata Pelajaran Umum">Mata Pelajaran Umum</option>
              <option value="Mata Pelajaran Pilihan">Mata Pelajaran Pilihan</option>
              <option value="Muatan Lokal">Muatan Lokal</option>
            </select>
          </div>
        </div>

        <div class="form-check mt-3">
          <input class="form-check-input" type="checkbox" id="chkYakinTambah">
          <label class="form-check-label" for="chkYakinTambah">
            Saya yakin sudah mengisi dengan benar
          </label>
        </div>

      </div>

      <div class="modal-footer">
        <button class="btn btn-primary px-4" id="btnSimpanTambah" disabled>Simpan</button>
      </div>

    </form>
  </div>
</div>


{{-- =========================
   MODAL: EDIT MAPEL (popup)
========================= --}}
<div class="modal fade" id="modalEditMapel" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="#" class="modal-content" id="formEditMapel">
      @csrf
      @method('PUT')

      <div class="modal-header">
        <h5 class="modal-title">Edit Data Mapel</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>

      <div class="modal-body">

        <div class="alert alert-info d-flex justify-content-between align-items-center">
          <div><b>*</b> adalah kolom yang wajib diisi!</div>
          <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>

        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Nama Mapel <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <input type="text" name="nama_mapel" class="form-control" id="eNama" required>
          </div>
        </div>

        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Singkatan <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <input type="text" name="singkatan" class="form-control" id="eSingkatan" required>
          </div>
        </div>

        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Urutan Cetak <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <input type="number" name="urutan_cetak" class="form-control" id="eUrutan" min="1" max="9999" required>
          </div>
        </div>

        <div class="form-group row mb-2">
          <label class="col-sm-4 col-form-label">Kelompok Mapel <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <select name="kelompok_mapel" class="form-control" id="eKelompok" required>
              <option value="">-- Pilih --</option>
              <option value="Mata Pelajaran Umum">Mata Pelajaran Umum</option>
              <option value="Mata Pelajaran Pilihan">Mata Pelajaran Pilihan</option>
              <option value="Muatan Lokal">Muatan Lokal</option>
            </select>
          </div>
        </div>

        <div class="form-check mt-3">
          <input class="form-check-input" type="checkbox" id="chkYakinEdit">
          <label class="form-check-label" for="chkYakinEdit">
            Saya yakin sudah mengisi dengan benar
          </label>
        </div>

      </div>

      <div class="modal-footer">
        <button class="btn btn-primary px-4" id="btnSimpanEdit" disabled>Simpan</button>
      </div>

    </form>
  </div>
</div>


{{-- =========================
   MODAL: FILTER KELOMPOK (tombol "Kelompok Mapel")
========================= --}}
<div class="modal fade" id="modalKelompokMapel" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Kelompok Mapel</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <div class="form-group mb-0">
          <label class="mb-1">Filter Kelompok</label>
          <select class="form-control" id="filterKelompok">
            <option value="">Semua</option>
            <option value="mata pelajaran umum">Mata Pelajaran Umum</option>
            <option value="mata pelajaran pilihan">Mata Pelajaran Pilihan</option>
            <option value="muatan lokal">Muatan Lokal</option>
          </select>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-light" id="btnResetKelompok">Reset</button>
        <button class="btn btn-primary" data-dismiss="modal" id="btnApplyKelompok">Terapkan</button>
      </div>

    </div>
  </div>
</div>


@push('styles')
<style>
/* ==========================================
   FIX PAGINATION LARAVEL (AdminLTE/Bootstrap)
   KHUSUS di card-footer .pagination-wrap
   ========================================== */

.pagination-wrap .pagination{
  margin: 0 !important;
}

.pagination-wrap .page-item .page-link{
  padding: .25rem .5rem !important;     /* kecil */
  font-size: 12px !important;
  line-height: 1.1 !important;
}

.pagination-wrap .page-item.active .page-link{
  font-weight: 600 !important;
}

/* Paksa Previous / Next jangan jadi gede */
.pagination-wrap .page-item:first-child .page-link,
.pagination-wrap .page-item:last-child .page-link{
  padding: .25rem .5rem !important;
  font-size: 12px !important;
}

/* Kalau ada svg/icon aneh kebesaran */
.pagination-wrap svg,
.pagination-wrap i{
  width: 12px !important;
  height: 12px !important;
  font-size: 12px !important;
}

/* Hilangkan kemungkinan theme kasih ukuran jumbo */
.pagination-wrap .pagination-lg .page-link{
  padding: .25rem .5rem !important;
  font-size: 12px !important;
}
</style>
@endpush





@push('scripts')
<script>
(function () {
  const searchInput = document.getElementById('searchMapel');
  const table = document.getElementById('tableMapel');
  const rows = () => table.querySelectorAll('tbody tr');

  const filterKelompok = document.getElementById('filterKelompok');
  const btnApplyKelompok = document.getElementById('btnApplyKelompok');
  const btnResetKelompok = document.getElementById('btnResetKelompok');

  let activeKelompok = '';

  function applyFilter() {
    const q = (searchInput.value || '').toLowerCase().trim();

    rows().forEach(tr => {
      const nama = tr.getAttribute('data-nama') || '';
      const singkatan = tr.getAttribute('data-singkatan') || '';
      const kelompok = tr.getAttribute('data-kelompok') || '';

      const matchSearch = !q || nama.includes(q) || singkatan.includes(q) || kelompok.includes(q);
      const matchKelompok = !activeKelompok || kelompok === activeKelompok;

      tr.style.display = (matchSearch && matchKelompok) ? '' : 'none';
    });
  }

  searchInput?.addEventListener('input', applyFilter);

  btnApplyKelompok?.addEventListener('click', function () {
    activeKelompok = (filterKelompok.value || '').trim();
    applyFilter();
  });

  btnResetKelompok?.addEventListener('click', function () {
    filterKelompok.value = '';
    activeKelompok = '';
    applyFilter();
  });

  // ===== modal tambah: checkbox enable simpan
  const chkTambah = document.getElementById('chkYakinTambah');
  const btnTambah = document.getElementById('btnSimpanTambah');
  chkTambah?.addEventListener('change', () => btnTambah.disabled = !chkTambah.checked);

  // ===== modal edit: checkbox enable simpan
  const chkEdit = document.getElementById('chkYakinEdit');
  const btnEdit = document.getElementById('btnSimpanEdit');
  chkEdit?.addEventListener('change', () => btnEdit.disabled = !chkEdit.checked);

  // ===== isi modal edit + set action
  const formEdit = document.getElementById('formEditMapel');
  document.querySelectorAll('.btnEditMapel').forEach(btn => {
    btn.addEventListener('click', function () {
      const id = this.dataset.id;

      // reset checkbox
      if (chkEdit) chkEdit.checked = false;
      if (btnEdit) btnEdit.disabled = true;

      // action update (sesuai resource route: admin/mapel/{id})
      formEdit.setAttribute('action', `{{ url('admin/mapel') }}/${id}`);

      document.getElementById('eNama').value = this.dataset.nama || '';
      document.getElementById('eSingkatan').value = this.dataset.singkatan || '';
      document.getElementById('eUrutan').value = this.dataset.urutan || '';
      document.getElementById('eKelompok').value = this.dataset.kelompok || '';
    });
  });

  applyFilter();
})();
</script>
@endpush

@endsection
