@extends('layouts.adminlte')

@section('title','Data Kegiatan')
@section('page_title','Data Kegiatan Kokurikuler')

@section('content')
<div class="card">
  <div class="card-body">

    {{-- TOOLBAR ATAS (sesuai screenshot) --}}
    <div class="mb-3">
      <button type="button" class="btn btn-primary btn-sm" id="btn-open-create">
        <i class="fas fa-plus"></i> Tambah Kegiatan
      </button>
    </div>

    {{-- TABEL --}}
    <table id="table-kegiatan" class="table table-bordered table-striped table-hover w-100">
      <thead class="bg-secondary">
        <tr>
          <th style="width:60px">No.</th>
          <th style="width:260px">Tema</th>
          <th>Nama Kegiatan</th>
          <th>Deskripsi</th>
          <th style="width:220px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($kegiatan as $i => $k)
          <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $k->tema }}</td>
            <td>{{ $k->nama_kegiatan }}</td>
            <td class="text-truncate" style="max-width:420px">
              {{ $k->deskripsi ?? '-' }}
            </td>
            <td>
              <button type="button"
                      class="btn btn-success btn-xs btn-detail"
                      data-tema="{{ e($k->tema) }}"
                      data-nama="{{ e($k->nama_kegiatan) }}"
                      data-deskripsi="{{ e($k->deskripsi ?? '-') }}">
                <i class="fas fa-eye"></i> Detail
              </button>

              <button type="button"
                      class="btn btn-warning btn-xs btn-edit"
                      data-id="{{ $k->id }}"
                      data-tema="{{ e($k->tema) }}"
                      data-nama="{{ e($k->nama_kegiatan) }}"
                      data-deskripsi="{{ e($k->deskripsi ?? '') }}">
                <i class="fas fa-edit"></i> Edit
              </button>

              <form action="{{ route('admin.kokurikuler.kegiatan.destroy', $k->id) }}"
                    method="POST"
                    class="d-inline"
                    onsubmit="return confirm('Hapus kegiatan ini?')">
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
| MODAL TAMBAH (layout 2 kolom seperti screenshot)
========================= --}}
<div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Tambah Data Kegiatan</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>

      <form method="POST" action="{{ route('admin.kokurikuler.kegiatan.store') }}" id="form-create">
        @csrf

        <div class="modal-body">
          <div class="form-group row">
            <label class="col-sm-3 col-form-label">Tema <span class="text-danger">*</span></label>
            <div class="col-sm-9">
              <input type="text"
                     name="tema"
                     class="form-control"
                     placeholder="Ketik Tema Kegiatan"
                     required>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-sm-3 col-form-label">Nama Kegiatan <span class="text-danger">*</span></label>
            <div class="col-sm-9">
              <input type="text"
                     name="nama_kegiatan"
                     class="form-control"
                     placeholder="Ketik Nama Kegiatan"
                     required>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-sm-3 col-form-label">Deskripsi Kegiatan <span class="text-danger">*</span></label>
            <div class="col-sm-9">
              <textarea name="deskripsi"
                        class="form-control"
                        rows="4"
                        placeholder="Ketik Deskripsi Kegiatan"
                        required></textarea>
              <small class="text-muted">Jika tidak wajib di sistem kamu, ubah required di atas menjadi optional.</small>
            </div>
          </div>
        </div>

        <div class="modal-footer d-flex justify-content-between">
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
      </form>

    </div>
  </div>
</div>

{{-- =========================
| MODAL DETAIL (seperti screenshot: tabel ringkas + tombol tutup)
========================= --}}
<div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Detail Kegiatan</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <table class="table table-borderless mb-0">
          <tr>
            <th style="width:160px">Tema</th>
            <td style="width:20px">:</td>
            <td id="detail-tema"></td>
          </tr>
          <tr>
            <th>Nama Kegiatan</th>
            <td>:</td>
            <td id="detail-nama"></td>
          </tr>
          <tr>
            <th>Deskripsi Kegiatan</th>
            <td>:</td>
            <td id="detail-deskripsi"></td>
          </tr>
        </table>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
      </div>

    </div>
  </div>
</div>

{{-- =========================
| MODAL EDIT (layout 2 kolom seperti screenshot)
========================= --}}
<div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Edit Data Kegiatan</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>

      <form method="POST" id="form-edit">
        @csrf
        @method('PUT')

        <div class="modal-body">
          <div class="form-group row">
            <label class="col-sm-3 col-form-label">Tema Kegiatan <span class="text-danger">*</span></label>
            <div class="col-sm-9">
              <input type="text" name="tema" id="edit_tema" class="form-control" required>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-sm-3 col-form-label">Nama Kegiatan <span class="text-danger">*</span></label>
            <div class="col-sm-9">
              <input type="text" name="nama_kegiatan" id="edit_nama" class="form-control" required>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-sm-3 col-form-label">Deskripsi Kegiatan <span class="text-danger">*</span></label>
            <div class="col-sm-9">
              <textarea name="deskripsi" id="edit_deskripsi" class="form-control" rows="4" required></textarea>
            </div>
          </div>
        </div>

        <div class="modal-footer d-flex justify-content-between">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="check-edit">
            <label class="custom-control-label" for="check-edit">
              Saya yakin sudah mengisi dengan benar
            </label>
          </div>

          <button type="submit" class="btn btn-primary" id="btn-submit-edit" disabled>
            Simpan Perubahan
          </button>
        </div>
      </form>

    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {

  // DataTables: tampilkan dropdown 10-100 & search kanan seperti screenshot
  const table = $('#table-kegiatan').DataTable({
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

  // rapikan input cari + dropdown length biar mirip
  $('#table-kegiatan_filter label').contents().filter(function() { return this.nodeType === 3; }).remove();
  $('#table-kegiatan_filter input').addClass('form-control form-control-sm').css('width', '140px');
  $('#table-kegiatan_length select').addClass('form-control form-control-sm').css('width', '70px');

  // OPEN CREATE
  $('#btn-open-create').on('click', function () {
    $('#form-create')[0].reset();
    $('#check-create').prop('checked', false);
    $('#btn-submit-create').prop('disabled', true);
    $('#modalCreate').modal('show');
  });

  // enable submit create by checkbox
  $('#check-create').on('change', function(){
    $('#btn-submit-create').prop('disabled', !this.checked);
  });

  // DETAIL
  $('.btn-detail').on('click', function(){
    $('#detail-tema').text($(this).data('tema'));
    $('#detail-nama').text($(this).data('nama'));
    $('#detail-deskripsi').text($(this).data('deskripsi'));
    $('#modalDetail').modal('show');
  });

  // EDIT
  $('.btn-edit').on('click', function(){
    const id = $(this).data('id');
    $('#form-edit').attr('action', "{{ url('admin/kokurikuler/kegiatan') }}/" + id);

    $('#edit_tema').val($(this).data('tema'));
    $('#edit_nama').val($(this).data('nama'));
    $('#edit_deskripsi').val($(this).data('deskripsi'));

    $('#check-edit').prop('checked', false);
    $('#btn-submit-edit').prop('disabled', true);

    $('#modalEdit').modal('show');
  });

  // enable submit edit by checkbox
  $('#check-edit').on('change', function(){
    $('#btn-submit-edit').prop('disabled', !this.checked);
  });
});
</script>
@endpush