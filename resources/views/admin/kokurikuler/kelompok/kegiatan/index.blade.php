@extends('layouts.adminlte')
@section('title','Kegiatan Pilihan Kelompok')
@section('page_title','Kegiatan Pilihan Kelompok')

@section('content')
<div class="mb-3">
  <a href="{{ route('admin.kokurikuler.kelompok.index') }}" class="btn btn-link p-0">
    <i class="fas fa-arrow-left"></i> Back
  </a>
</div>

{{-- HEADER INFO --}}
<div class="card mb-3">
  <div class="card-body">
    <div class="row">
      <div class="col-md-3 font-weight-bold">Nama Kelompok</div>
      <div class="col-md-9">: {{ $kelompok->nama_kelompok }}</div>

      <div class="col-md-3 font-weight-bold">Kelas</div>
      <div class="col-md-9">: {{ $kelompok->kelas->nama_kelas ?? '-' }}</div>

      <div class="col-md-3 font-weight-bold">Guru/Koordinator</div>
      <div class="col-md-9">: {{ $kelompok->koordinator->nama ?? '-' }}</div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-body">

    <div class="d-flex justify-content-between mb-3">
      <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalTambahKegiatan">
        <i class="fas fa-plus"></i> Tambah Kegiatan
      </button>

      <a href="{{ route('admin.kokurikuler.kelompok.anggota.index', $kelompok->id) }}" class="btn btn-info btn-sm">
        <i class="fas fa-users"></i> Anggota Kelompok
      </a>
    </div>

    {{-- BAR (TAMPILKAN + CARI) UNTUK TABEL UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
      <div class="d-flex align-items-center">
        <span class="mr-2 text-muted">Tampilkan</span>
        <select id="main_show" class="form-control form-control-sm" style="width:80px">
          <option value="10">10</option>
          <option value="25">25</option>
          <option value="50">50</option>
          <option value="100">100</option>
        </select>
        <span class="ml-2 text-muted">data</span>
      </div>

      <input type="text" id="main_search" class="form-control form-control-sm" style="width:160px" placeholder="Cari...">
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-striped table-hover" id="tableMainKegiatan">
        <thead class="bg-secondary">
          <tr>
            <th style="width:60px">No.</th>
            <th style="width:180px">Tema</th>
            <th>Nama Kegiatan</th>
            <th>Deskripsi</th>
            <th style="width:220px">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($items as $i => $it)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td>{{ $it->kegiatan->tema ?? '-' }}</td>
              <td>{{ $it->kegiatan->nama_kegiatan ?? '-' }}</td>
              <td>{{ \Illuminate\Support\Str::limit($it->kegiatan->deskripsi ?? '-', 50) }}</td>
              <td>
                <button class="btn btn-success btn-xs" data-toggle="modal" data-target="#modalDetail{{ $it->id }}">
                  <i class="fas fa-eye"></i> Detail
                </button>

                <form method="POST"
                      action="{{ route('admin.kokurikuler.kelompok.kegiatan.destroy', [$kelompok->id, $it->id]) }}"
                      onsubmit="return confirm('Hapus kegiatan dari kelompok?')"
                      class="d-inline">
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
              <td colspan="5" class="text-center text-muted">Belum ada kegiatan dipilih.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- INFO + PAGER TABEL UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mt-2">
      <div class="text-muted" id="main_info"></div>
      <div id="main_pager" class="btn-group btn-group-sm"></div>
    </div>

  </div>
</div>


{{-- =========================
  MODAL DETAIL (PENTING: LETAKKAN DI LUAR TABLE!)
========================= --}}
@foreach($items as $it)
  <div class="modal fade" id="modalDetail{{ $it->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Detail Kegiatan</h5>
          <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body">
          <table class="table table-borderless mb-0">
            <tr>
              <td style="width:180px"><b>Tema</b></td>
              <td style="width:20px">:</td>
              <td>{{ $it->kegiatan->tema ?? '-' }}</td>
            </tr>
            <tr>
              <td><b>Nama Kegiatan</b></td>
              <td>:</td>
              <td>{{ $it->kegiatan->nama_kegiatan ?? '-' }}</td>
            </tr>
            <tr>
              <td><b>Deskripsi Kegiatan</b></td>
              <td>:</td>
              <td>{{ $it->kegiatan->deskripsi ?? '-' }}</td>
            </tr>
          </table>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>
@endforeach


{{-- =========================
  MODAL TAMBAH KEGIATAN
  - list kegiatan yang SUDAH DIPILIH disembunyikan (UI saja)
========================= --}}
@php
  $selectedIds = $items->pluck('kk_kegiatan_id')->filter()->values()->all();
@endphp

<div class="modal fade" id="modalTambahKegiatan" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Tambah Kegiatan Pilihan Kelompok</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>

      <div class="modal-body">

        {{-- filter bar modal --}}
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div class="d-flex align-items-center">
            <span class="mr-2 text-muted">Tampilkan</span>
            <select id="keg_show" class="form-control form-control-sm" style="width:80px">
              <option value="10">10</option>
              <option value="25">25</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
            <span class="ml-2 text-muted">data</span>
          </div>

          <input type="text" id="keg_search" class="form-control form-control-sm" style="width:160px" placeholder="Cari...">
        </div>

        <div class="table-responsive">
          <table class="table table-bordered table-striped table-hover" id="tableKegModal">
            <thead class="bg-secondary">
              <tr>
                <th style="width:60px">No.</th>
                <th style="width:180px">Tema</th>
                <th>Nama Kegiatan</th>
                <th>Deskripsi</th>
                <th style="width:140px">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @php $no = 1; @endphp
              @forelse($kegiatanList as $k)
                @continue(in_array($k->id, $selectedIds, true))
                <tr>
                  <td>{{ $no++ }}</td>
                  <td>{{ $k->tema }}</td>
                  <td>{{ $k->nama_kegiatan }}</td>
                  <td>{{ \Illuminate\Support\Str::limit($k->deskripsi ?? '-', 80) }}</td>
                  <td>
                    <form method="POST" action="{{ route('admin.kokurikuler.kelompok.kegiatan.store', $kelompok->id) }}">
                      @csrf
                      <input type="hidden" name="kk_kegiatan_id" value="{{ $k->id }}">
                      <button type="submit" class="btn btn-primary btn-xs">
                        <i class="fas fa-plus"></i> Tambahkan
                      </button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center text-muted">Tidak ada kegiatan tersedia.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-2">
          <div class="text-muted" id="keg_info"></div>
          <div id="keg_pager" class="btn-group btn-group-sm"></div>
        </div>

      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
$(function(){

  // ==========================
  // helper pager/search client-side (reusable)
  // ==========================
  function initClientPager(cfg){
    const $table = $(cfg.table);
    const $rows  = $table.find('tbody tr');
    let page = 1;

    function render(){
      const perPage = parseInt($(cfg.selShow).val(), 10);
      const q = ($(cfg.selSearch).val() || '').toLowerCase();

      let filtered = [];
      $rows.each(function(){
        const text = $(this).text().toLowerCase();
        const ok = (q === '' || text.includes(q));
        $(this).toggle(ok);
        if (ok) filtered.push(this);
      });

      const total = filtered.length;
      const pages = Math.max(1, Math.ceil(total / perPage));
      if (page > pages) page = pages;

      $(filtered).hide();
      const start = (page - 1) * perPage;
      const end   = start + perPage;
      $(filtered.slice(start, end)).show();

      const startInfo = total === 0 ? 0 : start + 1;
      const endInfo   = Math.min(end, total);
      $(cfg.selInfo).text(`Menampilkan ${startInfo} - ${endInfo} dari ${total} data`);

      const $pager = $(cfg.selPager).empty();
      const btn = (label, p, disabled=false, active=false) => {
        const $b = $('<button type="button" class="btn btn-outline-primary"></button>').text(label);
        if (active) $b.addClass('active');
        if (disabled) $b.prop('disabled', true);
        $b.on('click', ()=>{ page = p; render(); });
        $pager.append($b);
      };

      btn('«', Math.max(1, page-1), page===1);

      // tampilkan angka page secukupnya biar rapi
      for (let i=1;i<=pages;i++){
        if (i<=3 || i>pages-3 || Math.abs(i-page)<=1){
          btn(String(i), i, false, i===page);
        }
      }

      btn('»', Math.min(pages, page+1), page===pages);
    }

    $(cfg.selShow).on('change', function(){ page=1; render(); });
    $(cfg.selSearch).on('keyup', function(){ page=1; render(); });

    // init
    render();

    return { render };
  }

  // ==========================
  // TABEL UTAMA (WAJIB ADA SEARCH + LENGTH + PAGING)
  // ==========================
  const mainPager = initClientPager({
    table: '#tableMainKegiatan',
    selShow: '#main_show',
    selSearch: '#main_search',
    selInfo: '#main_info',
    selPager: '#main_pager'
  });

  // ==========================
  // MODAL TAMBAH KEGIATAN (init saat modal dibuka)
  // ==========================
  let modalPagerInited = false;
  let modalPager = null;

  $('#modalTambahKegiatan').on('shown.bs.modal', function(){
    if (!modalPagerInited) {
      modalPager = initClientPager({
        table: '#tableKegModal',
        selShow: '#keg_show',
        selSearch: '#keg_search',
        selInfo: '#keg_info',
        selPager: '#keg_pager'
      });
      modalPagerInited = true;
    } else {
      modalPager.render();
    }
  });

});
</script>
@endpush