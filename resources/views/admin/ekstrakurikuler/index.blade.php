@extends('layouts.adminlte')

@section('title','Data Ekstrakurikuler')
@section('page_title','Data Ekstrakurikuler')

@section('content')
<div class="card card-dark">
  <div class="card-header">
    <h3 class="card-title">Data Ekstrakurikuler</h3>
  </div>

  <div class="card-body">

    {{-- TOOLBAR ATAS --}}
    <div class="d-flex justify-content-between mb-3">
      <div>
        <button type="button" class="btn btn-primary btn-sm" id="btn-open-create">
          <i class="fas fa-plus"></i> Tambah Ekstrakurikuler
        </button>
      </div>
      <div>
        <button type="button" class="btn btn-info btn-sm" id="btn-open-filter">
          <i class="fas fa-filter"></i> Filter Data
        </button>
      </div>
    </div>

    {{-- TABEL --}}
    <table id="table-ekskul" class="table table-bordered table-striped table-hover w-100">
      <thead class="bg-secondary">
        <tr>
          <th style="width:60px">No.</th>
          <th>Nama Ekstrakurikuler</th>
          <th>Pembina</th>
          <th style="width:160px">Jumlah Anggota</th>
          <th style="width:180px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($ekskul as $i => $e)
          <tr data-pembina="{{ $e->pembina_id ?? '' }}"
              data-status="{{ (int) $e->status_aktif }}">
            <td>{{ $i + 1 }}</td>
            <td>{{ $e->nama_ekskul }}</td>
            <td>{{ $e->pembina->pengguna->nama ?? '-' }}</td>
            <td>{{ $e->anggota_count ?? 0 }}</td>
            <td>
              <button type="button"
                      class="btn btn-warning btn-xs btn-edit"
                      data-id="{{ $e->id }}">
                <i class="fas fa-edit"></i> Edit
              </button>

              {{-- HAPUS (akan berfungsi kalau destroy() ada) --}}
              <form action="{{ route('admin.ekstrakurikuler.destroy',$e->id) }}"
                    method="POST"
                    class="d-inline">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger btn-xs"
                        onclick="return confirm('Hapus ekstrakurikuler ini?')">
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
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Tambah Data Ekstrakurikuler</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>

      <form method="POST" action="{{ route('admin.ekstrakurikuler.store') }}" id="form-create">
        @csrf
        <div class="modal-body">

          <div class="alert alert-info py-2 mb-3">
            * adalah kolom yang wajib diisi!
          </div>

          <div class="form-group">
            <label>Nama Ekstrakurikuler <span class="text-danger">*</span></label>
            <input type="text"
                   name="nama_ekskul"
                   class="form-control"
                   placeholder="Ketik Nama Ekstrakurikuler"
                   required>
          </div>

          <div class="form-group">
            <label>Pembina</label>
            <select name="pembina_id" class="form-control">
              <option value="">-- Pilih --</option>
              @foreach($pembina as $p)
                <option value="{{ $p->id }}">{{ $p->pengguna->nama ?? '-' }}</option>
              @endforeach
            </select>
          </div>

          <div class="form-group">
            <label>Status <span class="text-danger">*</span></label>
            <select name="status_aktif" class="form-control" required>
              <option value="1">Aktif</option>
              <option value="0">Non Aktif</option>
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
          <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
          <button type="submit" class="btn btn-primary" id="btn-submit-create" disabled>Simpan</button>
        </div>
      </form>

    </div>
  </div>
</div>

{{-- ========================= MODAL EDIT ========================= --}}
<div class="modal fade" id="modalEdit" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Edit Data Ekstrakurikuler</h5>
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
            <label>Nama Ekstrakurikuler <span class="text-danger">*</span></label>
            <input type="text" name="nama_ekskul" id="edit_nama" class="form-control" required>
          </div>

          <div class="form-group">
            <label>Pembina</label>
            <select name="pembina_id" id="edit_pembina" class="form-control">
              <option value="">-- Pilih --</option>
              @foreach($pembina as $p)
                <option value="{{ $p->id }}">{{ $p->pengguna->nama ?? '-' }}</option>
              @endforeach
            </select>
          </div>

          <div class="form-group">
            <label>Status <span class="text-danger">*</span></label>
            <select name="status_aktif" id="edit_status" class="form-control" required>
              <option value="1">Aktif</option>
              <option value="0">Non Aktif</option>
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
          <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
          <button type="submit" class="btn btn-primary" id="btn-submit-edit" disabled>Simpan</button>
        </div>
      </form>

    </div>
  </div>
</div>

{{-- ========================= MODAL FILTER ========================= --}}
<div class="modal fade" id="modalFilter" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Filter Data</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <div class="form-group">
          <label>Pembina</label>
          <select class="form-control" id="filter_pembina">
            <option value="">-- Pilih --</option>
            @foreach($pembina as $p)
              <option value="{{ $p->id }}">{{ $p->pengguna->nama ?? '-' }}</option>
            @endforeach
          </select>
        </div>

        <div class="form-group">
          <label>Status</label>
          <select class="form-control" id="filter_status">
            <option value="">-- Pilih --</option>
            <option value="1">Aktif</option>
            <option value="0">Non Aktif</option>
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
  $.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
  });

  const table = $('#table-ekskul').DataTable({
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

  // OPEN MODAL CREATE
  $('#btn-open-create').on('click', function () {
    $('#form-create')[0].reset();
    $('#check-create').prop('checked', false);
    $('#btn-submit-create').prop('disabled', true);
    $('#modalCreate').modal('show');
  });

  // OPEN MODAL FILTER
  $('#btn-open-filter').on('click', function () {
    $('#modalFilter').modal('show');
  });

  // checkbox enable submit
  $('#check-create').on('change', function(){
    $('#btn-submit-create').prop('disabled', !this.checked);
  });
  $('#check-edit').on('change', function(){
    $('#btn-submit-edit').prop('disabled', !this.checked);
  });

  // OPEN MODAL EDIT + isi data via AJAX
  $('.btn-edit').on('click', function () {
    const id = $(this).data('id');

    $('#form-edit').attr('action', "{{ url('admin/ekstrakurikuler') }}/" + id);

    $('#check-edit').prop('checked', false);
    $('#btn-submit-edit').prop('disabled', true);

    $.get("{{ url('admin/ekstrakurikuler') }}/" + id + "/json", function (res) {
      $('#edit_nama').val(res.nama_ekskul);
      $('#edit_pembina').val(res.pembina_id ?? '');
      $('#edit_status').val(res.status_aktif);
      $('#modalEdit').modal('show');
    }).fail(function(){
      alert('Gagal mengambil data ekstrakurikuler.');
    });
  });

  // FILTER client-side
  function clearEkskulFilter(){
    $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(fn => fn.name !== 'ekskulFilter');
  }

  function applyFilter(){
    const fPembina = $('#filter_pembina').val();
    const fStatus  = $('#filter_status').val();

    clearEkskulFilter();

    const ekskulFilter = function(settings, data, dataIndex) {
      const row = table.row(dataIndex).node();
      const pembina = ($(row).data('pembina') ?? '').toString();
      const status  = ($(row).data('status') ?? '').toString();

      if (fPembina && pembina !== fPembina) return false;
      if (fStatus  && status  !== fStatus)  return false;

      return true;
    };
    ekskulFilter.name = 'ekskulFilter';

    $.fn.dataTable.ext.search.push(ekskulFilter);
    table.draw();
  }

  $('#btn-apply-filter').on('click', function(){
    applyFilter();
    $('#modalFilter').modal('hide');
  });

  $('#btn-reset-filter').on('click', function(){
    $('#filter_pembina').val('');
    $('#filter_status').val('');
    clearEkskulFilter();
    table.draw();
  });
});
</script>
@endpush