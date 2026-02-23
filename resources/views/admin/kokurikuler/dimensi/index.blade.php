@extends('layouts.adminlte')

@section('title','Data Dimensi')
@section('page_title','Data Dimensi')

@section('content')
<div class="card">
  <div class="card-body">

    {{-- TOOLBAR ATAS (sesuai gambar: tombol kiri) --}}
    <div class="mb-3">
      <button type="button" class="btn btn-primary btn-sm" id="btn-open-create">
        <i class="fas fa-plus"></i> Tambah Dimensi
      </button>
    </div>

    {{-- TABEL (DataTables akan otomatis munculkan "Tampilkan" & "Cari" seperti screenshot) --}}
    <table id="table-dimensi" class="table table-bordered table-striped table-hover w-100">
      <thead class="bg-secondary">
        <tr>
          <th style="width:60px">No.</th>
          <th>Nama Dimensi</th>
          <th style="width:220px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($dimensi as $i => $d)
          <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $d->nama_dimensi }}</td>
            <td>
              <button type="button"
                      class="btn btn-warning btn-xs btn-edit"
                      data-id="{{ $d->id }}"
                      data-nama="{{ $d->nama_dimensi }}">
                <i class="fas fa-edit"></i> Edit
              </button>

              <form action="{{ route('admin.kokurikuler.dimensi.destroy',$d->id) }}"
                    method="POST"
                    class="d-inline"
                    onsubmit="return confirm('Hapus dimensi ini?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger btn-xs">
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

{{-- =========================
| MODAL TAMBAH (kecil seperti screenshot)
========================= --}}
<div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Tambah Data Dimensi</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>

      <form method="POST" action="{{ route('admin.kokurikuler.dimensi.store') }}" id="form-create">
        @csrf
        <div class="modal-body">

          <div class="form-group mb-2">
            <label>Nama Dimensi <span class="text-danger">*</span></label>
            <textarea name="nama_dimensi"
                      class="form-control"
                      rows="2"
                      placeholder="Ketik Nama Dimensi"
                      required></textarea>
          </div>

          <div class="d-flex align-items-center justify-content-between mt-3">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="check-create">
              <label class="custom-control-label" for="check-create">
                Saya yakin sudah mengisi dengan benar
              </label>
            </div>

            <button type="submit" class="btn btn-primary" id="btn-submit-create" disabled>
              Simpan
            </button>
          </div>

        </div>
      </form>

    </div>
  </div>
</div>

{{-- =========================
| MODAL EDIT (kecil seperti screenshot)
========================= --}}
<div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Edit Data Dimensi</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>

      <form method="POST" id="form-edit">
        @csrf
        @method('PUT')

        <div class="modal-body">

          <div class="form-group mb-2">
            <label>Nama Dimensi <span class="text-danger">*</span></label>
            <textarea name="nama_dimensi"
                      id="edit_nama"
                      class="form-control"
                      rows="2"
                      required></textarea>
          </div>

          <div class="mt-3">
            <div class="custom-control custom-checkbox mb-2">
              <input type="checkbox" class="custom-control-input" id="check-edit">
              <label class="custom-control-label" for="check-edit">
                Saya yakin sudah mengisi dengan benar
              </label>
            </div>

            <button type="submit" class="btn btn-primary" id="btn-submit-edit" disabled>
              Simpan Perubahan
            </button>
          </div>

        </div>
      </form>

    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
  // DataTables: bikin sama seperti modul lain (10-100 + search kanan)
  const table = $('#table-dimensi').DataTable({
    paging: true,
    searching: true,
    ordering: true,
    lengthChange: true,
    info: true,
    responsive: true,
    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
    language: {
      lengthMenu: "Tampilkan _MENU_ data",
      search: "Cari...",
      searchPlaceholder: "Cari...",
      info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
      paginate: { previous: "‹", next: "›" }
    }
  });

  // rapikan label "Cari..." biar mirip screenshot (input kecil kanan)
  // (DataTables default label: "Cari:". Ini mengubah label jadi placeholder.)
  $('#table-dimensi_filter label').contents().filter(function() {
    return this.nodeType === 3;
  }).remove();

  $('#table-dimensi_filter input')
    .addClass('form-control form-control-sm')
    .css('width', '140px');

  $('#table-dimensi_length select')
    .addClass('form-control form-control-sm')
    .css('width', '70px');

  // OPEN CREATE
  $('#btn-open-create').on('click', function () {
    $('#form-create')[0].reset();
    $('#check-create').prop('checked', false);
    $('#btn-submit-create').prop('disabled', true);
    $('#modalCreate').modal('show');
  });

  // enable submit create
  $('#check-create').on('change', function(){
    $('#btn-submit-create').prop('disabled', !this.checked);
  });

  // OPEN EDIT (tanpa ubah logic: tetap submit ke route update)
  $('.btn-edit').on('click', function () {
    const id = $(this).data('id');
    const nama = $(this).data('nama');

    // route resource update: admin/kokurikuler/dimensi/{id}
    $('#form-edit').attr('action', "{{ url('admin/kokurikuler/dimensi') }}/" + id);

    $('#edit_nama').val(nama);
    $('#check-edit').prop('checked', false);
    $('#btn-submit-edit').prop('disabled', true);

    $('#modalEdit').modal('show');
  });

  // enable submit edit
  $('#check-edit').on('change', function(){
    $('#btn-submit-edit').prop('disabled', !this.checked);
  });
});
</script>
@endpush