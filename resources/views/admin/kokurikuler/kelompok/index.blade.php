@extends('layouts.adminlte')

@section('title','Kelompok Kokurikuler')
@section('page_title','Kelompok Kokurikuler')

@section('content')
<div class="card">
  <div class="card-body">

    {{-- TOOLBAR --}}
    <div class="d-flex justify-content-between mb-3">
      <button type="button" class="btn btn-primary btn-sm" id="btn-open-create">
        <i class="fas fa-plus"></i> Tambah Kelompok Kegiatan
      </button>

      <button type="button" class="btn btn-info btn-sm" id="btn-open-filter">
        <i class="fas fa-filter"></i> Filter Data
      </button>
    </div>

    <table id="table-kelompok" class="table table-bordered table-striped table-hover w-100">
      <thead class="bg-secondary">
        <tr>
          <th style="width:60px">No.</th>
          <th>Nama Kelompok Kegiatan</th>
          <th style="width:120px">Kelas</th>
          <th style="width:220px">Koordinator</th>
          <th style="width:420px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($kelompok as $i => $k)
          <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $k->nama_kelompok }}</td>
            <td>{{ $k->kelas->nama_kelas ?? '-' }}</td>
            <td>{{ $k->koordinator->nama ?? '-' }}</td>
            <td>

              {{-- NOTE: Route ADMIN untuk anggota/kegiatan belum ada di routes kamu --}}
              <a href="{{ route('admin.kokurikuler.kelompok.anggota.index', $k->id) }}" class="btn btn-info btn-xs">
                <i class="fas fa-users"></i> Anggota Kelompok
              </a>

              <a href="{{ route('admin.kokurikuler.kelompok.kegiatan.index', $k->id) }}" class="btn btn-success btn-xs">
                <i class="fas fa-tasks"></i> Kelola Kegiatan & Input Nilai
              </a>

              <button type="button"
                      class="btn btn-warning btn-xs btn-edit"
                      data-id="{{ $k->id }}"
                      data-nama="{{ e($k->nama_kelompok) }}"
                      data-kelas="{{ $k->data_kelas_id }}"
                      data-koordinator="{{ $k->koordinator_id }}">
                <i class="fas fa-edit"></i> Edit
              </button>

              <form action="{{ route('admin.kokurikuler.kelompok.destroy',$k->id) }}"
                    method="POST"
                    class="d-inline"
                    onsubmit="return confirm('Hapus kelompok ini?')">
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

{{-- ================= MODAL TAMBAH ================= --}}
<div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Tambah Data Kelompok Kegiatan</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>

      <form method="POST" action="{{ route('admin.kokurikuler.kelompok.store') }}" id="form-create">
        @csrf
        <div class="modal-body">

          <div class="form-group row">
            <label class="col-sm-3 col-form-label">Nama Kelompok Kegiatan <span class="text-danger">*</span></label>
            <div class="col-sm-9">
              <input type="text"
                     name="nama_kelompok"
                     class="form-control"
                     placeholder="Ketik Nama Kelompok Kegiatan"
                     required>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-sm-3 col-form-label">Kelas <span class="text-danger">*</span></label>
            <div class="col-sm-9">
              <select name="data_kelas_id" class="form-control" required>
                <option value="">-- Pilih --</option>
                @foreach($kelas as $kl)
                  <option value="{{ $kl->id }}">{{ $kl->nama_kelas }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-sm-3 col-form-label">Guru/Koordinator <span class="text-danger">*</span></label>
            <div class="col-sm-9">
              <select name="koordinator_id" class="form-control" required>
                <option value="">-- Pilih --</option>
                @foreach($guru as $g)
                  <option value="{{ $g->pengguna_id }}">{{ $g->pengguna->nama }}</option>
                @endforeach
              </select>
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

{{-- ================= MODAL EDIT ================= --}}
<div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Edit Data Kelompok Kegiatan</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>

      <form method="POST" id="form-edit">
        @csrf
        @method('PUT')

        <div class="modal-body">

          <div class="form-group row">
            <label class="col-sm-3 col-form-label">Nama Kelompok Kegiatan <span class="text-danger">*</span></label>
            <div class="col-sm-9">
              <input type="text" name="nama_kelompok" id="edit_nama" class="form-control" required>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-sm-3 col-form-label">Kelas <span class="text-danger">*</span></label>
            <div class="col-sm-9">
              <select name="data_kelas_id" id="edit_kelas" class="form-control" required>
                @foreach($kelas as $kl)
                  <option value="{{ $kl->id }}">{{ $kl->nama_kelas }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-sm-3 col-form-label">Guru/Koordinator <span class="text-danger">*</span></label>
            <div class="col-sm-9">
              <select name="koordinator_id" id="edit_koordinator" class="form-control" required>
                @foreach($guru as $g)
                  <option value="{{ $g->pengguna_id }}">{{ $g->pengguna->nama }}</option>
                @endforeach
              </select>
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

{{-- ================= MODAL FILTER ================= --}}
<div class="modal fade" id="modalFilter" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Filter Data</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>

      <div class="modal-body">
        <div class="form-group">
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text" style="min-width:110px">Kelas</span>
            </div>
            <select id="filter_kelas" class="form-control">
              <option value="">-- Pilih --</option>
              @foreach($kelas as $kl)
                <option value="{{ $kl->nama_kelas }}">{{ $kl->nama_kelas }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="form-group mb-0">
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text" style="min-width:110px">Guru/Koordinator</span>
            </div>
            <select id="filter_koordinator" class="form-control">
              <option value="">-- Pilih --</option>
              @foreach($guru as $g)
                <option value="{{ $g->pengguna->nama }}">{{ $g->pengguna->nama }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="text-right mt-3">
          <button class="btn btn-primary" id="btn-apply-filter">Terapkan</button>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection

@push('scripts')
{{-- Pastikan DataTables sudah kamu include (css/js). Kalau belum, bilang ya nanti aku bantu --}}
<script>
$(function () {
  const dt = $('#table-kelompok').DataTable({
    paging: true,
    searching: true,
    ordering: true,
    lengthChange: true,
    info: true,
    responsive: true,
    lengthMenu: [[10, 25, 50, 100],[10, 25, 50, 100]],
    language: {
      lengthMenu: "Tampilkan _MENU_ data",
      search: "Cari...",
      searchPlaceholder: "Cari...",
      info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
      paginate: { previous: "‹", next: "›" }
    }
  });

  // rapikan UI search & length agar mirip screenshot
  $('#table-kelompok_filter label').contents().filter(function(){ return this.nodeType === 3; }).remove();
  $('#table-kelompok_filter input').addClass('form-control form-control-sm').css('width','140px');
  $('#table-kelompok_length select').addClass('form-control form-control-sm').css('width','70px');

  // open create
  $('#btn-open-create').on('click', function(){
    $('#form-create')[0].reset();
    $('#check-create').prop('checked', false);
    $('#btn-submit-create').prop('disabled', true);
    $('#modalCreate').modal('show');
  });

  // enable create submit
  $('#check-create').on('change', function(){
    $('#btn-submit-create').prop('disabled', !this.checked);
  });

  // open edit
  $('.btn-edit').on('click', function(){
    const id = $(this).data('id');
    $('#form-edit').attr('action', "{{ url('admin/kokurikuler/kelompok') }}/" + id);

    $('#edit_nama').val($(this).data('nama'));
    $('#edit_kelas').val($(this).data('kelas'));
    $('#edit_koordinator').val($(this).data('koordinator'));

    $('#check-edit').prop('checked', false);
    $('#btn-submit-edit').prop('disabled', true);

    $('#modalEdit').modal('show');
  });

  // enable edit submit
  $('#check-edit').on('change', function(){
    $('#btn-submit-edit').prop('disabled', !this.checked);
  });

  // filter modal
  $('#btn-open-filter').on('click', function(){
    $('#modalFilter').modal('show');
  });

  // apply filter -> kolom kelas(2) & koordinator(3)
  $('#btn-apply-filter').on('click', function(){
    dt.column(2).search($('#filter_kelas').val() || '', true, false);
    dt.column(3).search($('#filter_koordinator').val() || '', true, false);
    dt.draw();
    $('#modalFilter').modal('hide');
  });
});
</script>
@endpush