@extends('layouts.adminlte')

@section('title','Data Pembelajaran')
@section('page_title','Data Pembelajaran')

@section('content')
<div class="card card-dark">
  <div class="card-header">
    <h3 class="card-title">Data Pembelajaran</h3>
  </div>

  <div class="card-body">

    {{-- TOOLBAR ATAS --}}
    <div class="d-flex justify-content-between mb-3">
      <div>
        <button type="button" class="btn btn-primary btn-sm" id="btn-open-create">
          <i class="fas fa-plus"></i> Tambah Pembelajaran
        </button>
      </div>
      <div>
        <button type="button" class="btn btn-info btn-sm" id="btn-open-filter">
          <i class="fas fa-filter"></i> Filter Data
        </button>
      </div>
    </div>

    {{-- TABEL --}}
    <table id="table-pembelajaran" class="table table-bordered table-striped table-hover w-100">
      <thead class="bg-secondary">
        <tr>
          <th style="width:60px">No.</th>
          <th>Mata Pelajaran</th>
          <th style="width:120px">Kelas</th>
          <th>Guru Pengampu</th>
          <th style="width:160px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($pembelajaran as $i => $p)
          <tr data-kelas="{{ $p->data_kelas_id }}"
              data-mapel="{{ $p->data_mapel_id }}"
              data-guru="{{ $p->guru_id }}">
            <td>{{ $i + 1 }}</td>
            <td>{{ $p->mapel->nama_mapel }}</td>
            <td>{{ $p->kelas->nama_kelas }}</td>
            <td>{{ $p->guru->nama }}</td>
            <td>
              <button type="button"
                      class="btn btn-warning btn-xs btn-edit"
                      data-id="{{ $p->id }}">
                <i class="fas fa-edit"></i> Edit
              </button>

              <form action="{{ route('admin.pembelajaran.destroy',$p->id) }}"
                    method="POST"
                    class="d-inline">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger btn-xs"
                        onclick="return confirm('Hapus pembelajaran ini?')">
                  <i class="fas fa-trash"></i> Hapus
                </button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>

  </div>
</div>

{{-- ========================= MODAL CREATE ========================= --}}
<div class="modal fade" id="modalCreate" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Tambah Data Pembelajaran</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>

      <form method="POST" action="{{ route('admin.pembelajaran.store') }}" id="form-create">
        @csrf
        <div class="modal-body">

          <div class="form-group">
            <label>Kelas <span class="text-danger">*</span></label>
            <select name="data_kelas_id" class="form-control" required>
              <option value="" selected disabled>-- Pilih --</option>
              @foreach($kelas as $k)
                <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
              @endforeach
            </select>
          </div>

          <div class="form-group">
            <label>Mata Pelajaran <span class="text-danger">*</span></label>
            <select name="data_mapel_id" class="form-control" required>
              <option value="" selected disabled>-- Pilih --</option>
              @foreach($mapel as $m)
                <option value="{{ $m->id }}">{{ $m->nama_mapel }}</option>
              @endforeach
            </select>
          </div>

          <div class="form-group">
            <label>Guru Pengampu <span class="text-danger">*</span></label>
            <select name="guru_id" class="form-control" required>
              <option value="" selected disabled>-- Pilih --</option>
              @foreach($guru as $g)
                <option value="{{ $g->id }}">{{ $g->nama }}</option>
              @endforeach
            </select>
          </div>

          <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="check-create">
            <label class="custom-control-label" for="check-create">
              Saya yakin sudah mengisi dengan benar
            </label>
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
          <button type="submit" class="btn btn-primary" id="btn-submit-create" disabled>Simpan</button>
        </div>
      </form>

    </div>
  </div>
</div>

{{-- ========================= MODAL EDIT ========================= --}}
<div class="modal fade" id="modalEdit" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Edit Data Pembelajaran</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>

      <form method="POST" id="form-edit">
        @csrf
        @method('PUT')

        <div class="modal-body">
          <div class="alert alert-info py-2 mb-3">
            * adalah kolom yang wajib diisi!
          </div>

          <div class="form-group">
            <label>Kelas <span class="text-danger">*</span></label>
            <select name="data_kelas_id" class="form-control" id="edit_kelas" required>
              @foreach($kelas as $k)
                <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
              @endforeach
            </select>
          </div>

          <div class="form-group">
            <label>Mata Pelajaran <span class="text-danger">*</span></label>
            <select name="data_mapel_id" class="form-control" id="edit_mapel" required>
              @foreach($mapel as $m)
                <option value="{{ $m->id }}">{{ $m->nama_mapel }}</option>
              @endforeach
            </select>
          </div>

          <div class="form-group">
            <label>Guru Pengampu <span class="text-danger">*</span></label>
            <select name="guru_id" class="form-control" id="edit_guru" required>
              @foreach($guru as $g)
                <option value="{{ $g->id }}">{{ $g->nama }}</option>
              @endforeach
            </select>
          </div>

          <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="check-edit">
            <label class="custom-control-label" for="check-edit">
              Saya yakin sudah mengisi dengan benar
            </label>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
          <button type="submit" class="btn btn-primary" id="btn-submit-edit" disabled>Simpan</button>
        </div>
      </form>

    </div>
  </div>
</div>

{{-- ========================= MODAL FILTER ========================= --}}
<div class="modal fade" id="modalFilter" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Filter Data</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <div class="form-group">
          <label>Mata Pelajaran</label>
          <select class="form-control" id="filter_mapel">
            <option value="">-- Pilih --</option>
            @foreach($mapel as $m)
              <option value="{{ $m->id }}">{{ $m->nama_mapel }}</option>
            @endforeach
          </select>
        </div>

        <div class="form-group">
          <label>Kelas</label>
          <select class="form-control" id="filter_kelas">
            <option value="">-- Pilih --</option>
            @foreach($kelas as $k)
              <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
            @endforeach
          </select>
        </div>

        <div class="form-group">
          <label>Guru</label>
          <select class="form-control" id="filter_guru">
            <option value="">-- Pilih --</option>
            @foreach($guru as $g)
              <option value="{{ $g->id }}">{{ $g->nama }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" id="btn-reset-filter">Reset</button>
        <button type="button" class="btn btn-primary" id="btn-apply-filter">Terapkan</button>
      </div>

    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
  // CSRF untuk request AJAX (aman untuk Laravel)
  $.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
  });

  const table = $('#table-pembelajaran').DataTable({
    paging: true,
    searching: true,
    ordering: true,
    lengthChange: true,
    info: true,
    responsive: true,
    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
    language: {
      lengthMenu: "Tampilkan _MENU_ data",
      search: "Cari:",
      info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
      paginate: { previous: "‹", next: "›" }
    }
  });

  // Open modal create
  $('#btn-open-create').on('click', function () {
    $('#form-create')[0].reset();
    $('#check-create').prop('checked', false);
    $('#btn-submit-create').prop('disabled', true);
    $('#modalCreate').modal('show');
  });

  // Open modal filter
  $('#btn-open-filter').on('click', function () {
    $('#modalFilter').modal('show');
  });

  // enable tombol simpan berdasarkan checkbox
  $('#check-create').on('change', function(){
    $('#btn-submit-create').prop('disabled', !this.checked);
  });
  $('#check-edit').on('change', function(){
    $('#btn-submit-edit').prop('disabled', !this.checked);
  });

  // Edit modal (fetch json)
  $('.btn-edit').on('click', function () {
    const id = $(this).data('id');

    // action update tetap ke route resource (tidak ubah logic)
    $('#form-edit').attr('action', "{{ url('admin/pembelajaran') }}/" + id);

    $('#check-edit').prop('checked', false);
    $('#btn-submit-edit').prop('disabled', true);

    $.get("{{ url('admin/pembelajaran') }}/" + id + "/json", function (res) {
      $('#edit_kelas').val(res.data_kelas_id);
      $('#edit_mapel').val(res.data_mapel_id);
      $('#edit_guru').val(res.guru_id);
      $('#modalEdit').modal('show');
    }).fail(function(){
      alert('Gagal mengambil data pembelajaran.');
    });
  });

  // FILTER client-side via data-atribut row
  function clearPembelajaranFilter(){
    $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(fn => fn.name !== 'pembelajaranFilter');
  }

  function applyFilter(){
    const fMapel = $('#filter_mapel').val();
    const fKelas = $('#filter_kelas').val();
    const fGuru  = $('#filter_guru').val();

    clearPembelajaranFilter();

    const pembelajaranFilter = function(settings, data, dataIndex) {
      const row = table.row(dataIndex).node();
      const mapel = ($(row).data('mapel') ?? '').toString();
      const kelas = ($(row).data('kelas') ?? '').toString();
      const guru  = ($(row).data('guru') ?? '').toString();

      if (fMapel && mapel !== fMapel) return false;
      if (fKelas && kelas !== fKelas) return false;
      if (fGuru  && guru  !== fGuru)  return false;

      return true;
    };
    pembelajaranFilter.name = 'pembelajaranFilter';

    $.fn.dataTable.ext.search.push(pembelajaranFilter);
    table.draw();
  }

  $('#btn-apply-filter').on('click', function(){
    applyFilter();
    $('#modalFilter').modal('hide');
  });

  $('#btn-reset-filter').on('click', function(){
    $('#filter_mapel').val('');
    $('#filter_kelas').val('');
    $('#filter_guru').val('');
    clearPembelajaranFilter();
    table.draw();
  });
});
</script>
@endpush