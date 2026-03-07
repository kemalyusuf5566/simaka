@extends('layouts.adminlte')

@section('page_title','Data Kelas')

@section('content')

<div class="card card-dark">
  <div class="card-header">
    <h3 class="card-title">Data Kelas</h3>
  </div>

  <div class="card-body">

    {{-- TOOLBAR ATAS --}}
    <div class="d-flex justify-content-between mb-3">
      <div>
        {{-- WAJIB ada data-url --}}
        <button type="button"
                class="btn btn-primary btn-sm"
                id="btnTambahKelas"
                data-url="{{ route('admin.kelas.create') }}">
          <i class="fas fa-plus"></i> Tambah Kelas
        </button>
      </div>

      <div>
        <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalFilter">
          <i class="fas fa-filter"></i> Filter Data
        </button>
      </div>
    </div>

    {{-- FILTER BAR --}}
    <form id="formFilterBar" method="GET" action="{{ route('admin.kelas.index') }}"
          class="d-flex justify-content-between align-items-center mb-2">

      <div>
        <label class="mb-0">
          Tampilkan
          <select name="limit" class="custom-select custom-select-sm w-auto" onchange="this.form.submit()">
            @php $limitVal = (int)($limit ?? 10); @endphp
            <option value="10"  {{ $limitVal===10 ? 'selected' : '' }}>10</option>
            <option value="25"  {{ $limitVal===25 ? 'selected' : '' }}>25</option>
            <option value="50"  {{ $limitVal===50 ? 'selected' : '' }}>50</option>
            <option value="100" {{ $limitVal===100 ? 'selected' : '' }}>100</option>
          </select>
          data
        </label>
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

    {{-- TABEL --}}
    <div class="table-responsive">
      <table class="table table-bordered table-striped table-hover mb-0">
        <thead>
          <tr>
            <th style="width:30px">No</th>
            {{-- <th style="width:90px">ID Kelas</th> --}}
            <th class="text-center" style="width:20px">Nama Kelas</th>
            <th class="text-center" style="width:120px">Wali Kelas</th>
            <th class="text-center" style="width:20px">Tingkat</th>
            <th class="text-center" style="width:180px">Jurusan</th>
            <th class="text-center" style="width:20px">Jumlah Siswa</th>
            <th class="text-center" style="width:40px">Aksi</th>
          </tr>
        </thead>

        <tbody>
          @forelse($kelas as $i => $k)
          <tr>
            <td>{{ $kelas->firstItem() + $i }}</td>
            {{-- <td>{{ $k->id }}</td> --}}
            <td>{{ $k->nama_kelas }}</td>
            <td>{{ $k->wali->pengguna->nama ?? '-' }}</td>
            <td class="text-center">{{ $k->tingkat }}</td>
            <td>{{ $k->jurusan ? ($k->jurusan->kode_jurusan.' - '.$k->jurusan->nama_jurusan) : '-' }}</td>
            <td class="text-center">{{ $k->siswa_count ?? 0 }}</td>
            <td class="text-center">

              {{-- WAJIB ada data-url --}}
              <button type="button"
                      class="btn btn-warning btn-xs btn-edit-kelas"
                      data-url="{{ route('admin.kelas.edit', $k->id) }}">
                <i class="fas fa-edit"></i> Edit
              </button>

              <form action="{{ route('admin.kelas.destroy',$k->id) }}"
                    method="POST"
                    class="d-inline"
                    onsubmit="return confirm('Hapus kelas ini?')">
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
            <td colspan="7" class="text-center text-muted">
              Data kelas belum tersedia
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- PAGINATION RAPI --}}
    <div class="d-flex justify-content-between align-items-center mt-3">
      <div class="text-muted">
        Menampilkan {{ $kelas->count() ? $kelas->firstItem() : 0 }} - {{ $kelas->count() ? $kelas->lastItem() : 0 }}
        dari {{ $kelas->total() }} data
      </div>
      <div>
        {{ $kelas->onEachSide(1)->links('pagination::bootstrap-4') }}
      </div>
    </div>

  </div>
</div>

{{-- MODAL FILTER (Tingkat Kelas saja) --}}
<div class="modal fade" id="modalFilter" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Filter Data</h4>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>

      <form method="GET" action="{{ route('admin.kelas.index') }}">
        <div class="modal-body">
          <input type="hidden" name="limit" value="{{ $limit ?? 10 }}">
          <input type="hidden" name="q" value="{{ $q ?? '' }}">

          <div class="form-group">
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text">Tingkat Kelas</span>
              </div>

              @php $tingkatVal = (string)($tingkat ?? ''); @endphp
              <select name="tingkat" class="form-control">
                <option value="" {{ $tingkatVal==='' ? 'selected' : '' }}>-- Pilih --</option>
                <option value="all" {{ $tingkatVal==='all' ? 'selected' : '' }}>Semua</option>
                <option value="X" {{ $tingkatVal==='X' ? 'selected' : '' }}>7</option>
                <option value="XI" {{ $tingkatVal==='XI' ? 'selected' : '' }}>8</option>
                <option value="XII" {{ $tingkatVal==='XII' ? 'selected' : '' }}>9</option>
              </select>

            </div>
          </div>
        </div>

        <div class="modal-footer">
          <a href="{{ route('admin.kelas.index') }}" class="btn btn-secondary">Reset</a>
          <button class="btn btn-primary">Terapkan</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- MODAL TAMBAH / EDIT KELAS --}}
<div class="modal fade" id="modalKelas" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      {{-- header modal supaya title bisa di-set --}}
      <div class="modal-header">
        <h4 class="modal-title" id="modalKelasLabel">Form Kelas</h4>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>

      {{-- konten form ajax --}}
      <div id="modalKelasContent">
        <div class="p-5 text-center text-muted">Memuat...</div>
      </div>

    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
function openKelasModal(url, title){
  $('#modalKelasLabel').text(title || 'Form Kelas');
  $('#modalKelasContent').html('<div class="p-4 text-center text-muted">Memuat...</div>');
  $('#modalKelas').modal('show');

  $.get(url)
    .done(function(res){
      $('#modalKelasContent').html(res);
    })
    .fail(function(xhr){
      // tampilkan error asli biar ketahuan 404/500
      let msg = '<div class="p-3">';
      msg += '<div class="text-danger font-weight-bold mb-2">Gagal memuat form (' + xhr.status + ')</div>';
      msg += '<pre style="white-space:pre-wrap;max-height:300px;overflow:auto;background:#f8f9fa;padding:10px;border:1px solid #ddd;">'
          + (xhr.responseText || 'Tidak ada responseText')
          + '</pre></div>';
      $('#modalKelasContent').html(msg);
    });
}

$(document).on('click', '#btnTambahKelas', function(e){
  e.preventDefault();
  openKelasModal($(this).data('url'), 'Tambah Data Kelas');
});

$(document).on('click', '.btn-edit-kelas', function(e){
  e.preventDefault();
  openKelasModal($(this).data('url'), 'Edit Data Kelas');
});
</script>
@endpush
