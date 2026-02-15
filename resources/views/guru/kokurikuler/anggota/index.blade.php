@extends('layouts.adminlte')

@section('title', 'Anggota Kelompok')

@section('content')
<div class="container-fluid">

  {{-- HEADER: tombol back + judul --}}
  <div class="d-flex align-items-center mb-3">
    <a href="{{ route('guru.kokurikuler.index') }}" class="btn btn-link p-0 mr-2" title="Kembali">
      <i class="fas fa-arrow-left"></i>
    </a>
    <h4 class="mb-0">Anggota Kelompok</h4>
  </div>

  {{-- INFO KELOMPOK --}}
  <div class="card mb-3">
    <div class="card-body">
      <div class="row">
        <div class="col-md-3 font-weight-bold">Nama Kelompok</div>
        <div class="col-md-9">: {{ $kelompok->nama_kelompok }}</div>

        <div class="col-md-3 font-weight-bold mt-2">Kelas</div>
        <div class="col-md-9 mt-2">: {{ $kelompok->kelas?->nama_kelas ?? '-' }}</div>

        <div class="col-md-3 font-weight-bold mt-2">Guru/Koordinator</div>
        <div class="col-md-9 mt-2">: {{ $kelompok->koordinator?->nama ?? '-' }}</div>
      </div>
    </div>
  </div>

  {{-- ALERT --}}
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  {{-- TOOLBAR: kiri tambah anggota (modal) + kanan kegiatan pilihan --}}
  <div class="d-flex justify-content-between align-items-center mb-2">
    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalTambahAnggota">
      <i class="fas fa-plus"></i> Tambah Anggota Kelompok
    </button>

    <a href="{{ route('guru.kokurikuler.kegiatan.index', $kelompok->id) }}" class="btn btn-warning btn-sm">
      <i class="fas fa-cog"></i> Kegiatan Pilihan
    </a>
  </div>

  {{-- BAR FILTER ANGGOTA (REAL) --}}
  <form method="GET" action="{{ url()->current() }}" class="d-flex justify-content-between align-items-center mb-2">
    <div class="d-flex align-items-center">
      <span class="mr-2">Tampilkan</span>
      <select name="per_page" class="form-control form-control-sm" style="width:90px;" onchange="this.form.submit()">
        @foreach([10,25,50,100] as $pp)
          <option value="{{ $pp }}" {{ (int)$perPage === $pp ? 'selected' : '' }}>{{ $pp }}</option>
        @endforeach
      </select>
      <span class="ml-2">data</span>
    </div>

    <div class="d-flex align-items-center" style="gap:8px;">
      <input type="text" name="q" value="{{ $q }}" class="form-control form-control-sm" style="width:220px;" placeholder="Cari...">
      <button class="btn btn-secondary btn-sm" type="submit">Cari</button>
    </div>
  </form>

  {{-- TABEL ANGGOTA --}}
  <div class="card">
    <div class="card-body table-responsive p-0">
      <table class="table table-bordered table-sm mb-0">
        <thead class="bg-dark text-white">
          <tr>
            <th style="width:60px;" class="text-center">No.</th>
            <th style="width:160px;">NIS</th>
            <th>Nama Siswa</th>
            <th style="width:90px;" class="text-center">L/P</th>
            <th style="width:120px;" class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($anggota as $i => $a)
            <tr>
              <td class="text-center">{{ $anggota->firstItem() + $i }}</td>
              <td>{{ $a->siswa?->nis ?? '-' }}</td>
              <td>{{ $a->siswa?->nama_siswa ?? '-' }}</td>
              <td class="text-center">{{ $a->siswa?->jenis_kelamin ?? '-' }}</td>
              <td class="text-center">
                <form method="POST"
                      action="{{ route('guru.kokurikuler.anggota.destroy', [$kelompok->id, $a->id]) }}"
                      onsubmit="return confirm('Hapus anggota ini?')">
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
              <td colspan="5" class="text-center text-muted">Belum ada anggota.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- FOOTER PAGINATION (SEPERTI DATA KELOMPOK KEGIATAN) --}}
    <div class="card-footer d-flex justify-content-between align-items-center">
      <div class="text-muted">
        @if($anggota->total() > 0)
          Menampilkan {{ $anggota->firstItem() }} - {{ $anggota->lastItem() }} dari {{ $anggota->total() }} data
        @else
          Menampilkan 0 data
        @endif
      </div>
      <div>
        {{ $anggota->links() }}
      </div>
    </div>
  </div>

</div>

{{-- ================= MODAL TAMBAH ANGGOTA ================= --}}
<div class="modal fade" id="modalTambahAnggota" tabindex="-1" aria-labelledby="modalTambahAnggotaLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="modalTambahAnggotaLabel">Tambah Anggota Kelompok</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">

        {{-- BAR FILTER KANDIDAT (REAL) --}}
        <form method="GET" action="{{ url()->current() }}" class="d-flex justify-content-between align-items-center mb-2">
          {{-- keep filter anggota --}}
          <input type="hidden" name="per_page" value="{{ $perPage }}">
          <input type="hidden" name="q" value="{{ $q }}">

          <div class="d-flex align-items-center">
            <span class="mr-2">Tampilkan</span>
            <select name="per_page" class="form-control form-control-sm" style="width:90px;" onchange="this.form.submit()">
              @foreach([10,25,50,100] as $pp)
                <option value="{{ $pp }}" {{ (int)$perPage === $pp ? 'selected' : '' }}>{{ $pp }}</option>
              @endforeach
            </select>
            <span class="ml-2">data</span>
          </div>

          <div class="d-flex align-items-center" style="gap:8px;">
            <input type="text" name="kq" value="{{ $kq }}" class="form-control form-control-sm" style="width:220px;" placeholder="Cari...">
            <button class="btn btn-secondary btn-sm" type="submit">Cari</button>
          </div>
        </form>

        <div class="table-responsive">
          <table class="table table-bordered table-sm mb-0">
            <thead class="bg-dark text-white">
              <tr>
                <th style="width:60px;" class="text-center">No.</th>
                <th style="width:160px;">NIS</th>
                <th>Nama Siswa</th>
                <th style="width:90px;" class="text-center">L/P</th>
                <th style="width:140px;" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($kandidat as $i => $s)
                <tr>
                  <td class="text-center">{{ $kandidat->firstItem() + $i }}</td>
                  <td>{{ $s->nis ?? '-' }}</td>
                  <td>{{ $s->nama_siswa ?? '-' }}</td>
                  <td class="text-center">{{ $s->jenis_kelamin ?? '-' }}</td>
                  <td class="text-center">
                    <form method="POST" action="{{ route('guru.kokurikuler.anggota.store', $kelompok->id) }}">
                      @csrf
                      <input type="hidden" name="data_siswa_id" value="{{ $s->id }}">
                      <button class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambahkan
                      </button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center text-muted">Tidak ada kandidat.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-2">
          <div class="text-muted">
            @if($kandidat->total() > 0)
              Menampilkan {{ $kandidat->firstItem() }} - {{ $kandidat->lastItem() }} dari {{ $kandidat->total() }} data
            @else
              Menampilkan 0 data
            @endif
          </div>
          <div>
            {{ $kandidat->links() }}
          </div>
        </div>

      </div>

      <div class="modal-footer d-flex justify-content-between">
        <div>
          {{-- OPTIONAL: tombol "Tambahkan Semua" kalau route & method ada --}}
          @if (Route::has('guru.kokurikuler.anggota.addAll'))
            <form method="POST" action="{{ route('guru.kokurikuler.anggota.addAll', $kelompok->id) }}"
                  onsubmit="return confirm('Tambahkan semua kandidat ke kelompok?')">
              @csrf
              <button class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambahkan Semua
              </button>
            </form>
          @else
            {{-- kalau belum bikin route addAll, tombolnya disembunyikan biar tidak error --}}
          @endif
        </div>

        <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>

{{-- Auto open modal kalau user lagi di halaman kandidat (kpage/kq) --}}
@push('scripts')
<script>
  (function () {
    const url = new URL(window.location.href);
    const hasKPage = url.searchParams.has('kpage');
    const hasKq    = url.searchParams.has('kq');
    if (hasKPage || hasKq) {
      $('#modalTambahAnggota').modal('show');
    }
  })();
</script>
@endpush

@endsection
