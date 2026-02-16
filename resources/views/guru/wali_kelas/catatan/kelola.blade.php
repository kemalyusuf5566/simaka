@extends('layouts.adminlte')
@section('title','Kelola Catatan Wali Kelas')

@section('content')
<div class="container-fluid">

  {{-- Header back + title --}}
  <div class="d-flex align-items-center mb-3">
    <a href="{{ route('guru.wali-kelas.catatan.index') }}"
       class="btn btn-link p-0 mr-2" title="Kembali">
      <i class="fas fa-arrow-left"></i>
    </a>
    <h4 class="mb-0">Data Catatan Wali Kelas</h4>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  {{-- Info box --}}
  @php
    // ambil nama wali tanpa ubah model: coba dari relasi ->wali->pengguna
    $waliNama = '-';
    if (isset($kelas->wali) && isset($kelas->wali->pengguna) && !empty($kelas->wali->pengguna->name)) {
      $waliNama = $kelas->wali->pengguna->name;
    } elseif (isset($kelas->wali) && isset($kelas->wali->pengguna) && !empty($kelas->wali->pengguna->nama)) {
      $waliNama = $kelas->wali->pengguna->nama;
    } elseif (!empty($namaWali ?? null)) {
      $waliNama = $namaWali;
    }

    $tahunText = ($tahunAktif->tahun_pelajaran ?? '-') . ' - ' . ($tahunAktif->semester ?? '-');
    $isGenap = (string)($tahunAktif->semester ?? '') === 'Genap';
  @endphp

  <div class="card mb-3">
    <div class="card-body">
      <div class="row">
        <div class="col-md-2 font-weight-bold">Kelas</div>
        <div class="col-md-10">: {{ $kelas->nama_kelas }}</div>

        <div class="col-md-2 font-weight-bold mt-2">Wali Kelas</div>
        <div class="col-md-10 mt-2">: {{ $waliNama }}</div>

        <div class="col-md-2 font-weight-bold mt-2">Tahun Pelajaran</div>
        <div class="col-md-10 mt-2">: {{ $tahunText }}</div>
      </div>
    </div>
  </div>

  <div class="card">

    {{-- Search kanan (client-side) --}}
    <div class="card-body pb-2">
      <div class="d-flex justify-content-end">
        <div style="width:220px;">
          <input type="text" class="form-control form-control-sm" id="searchSiswa" placeholder="Cari...">
        </div>
      </div>
    </div>

    <div class="card-body pt-0 table-responsive p-0">
      <form method="POST" action="{{ route('guru.wali-kelas.catatan.update', $kelas->id) }}">
        @csrf

        <table class="table table-bordered table-sm mb-0" id="tableSiswa">
          <thead class="bg-dark text-white">
            <tr>
              <th style="width:60px;">No.</th>
              <th>Nama</th>
              <th style="width:140px;">NIS</th>
              <th style="width:90px;">L/P</th>
              <th>Catatan</th>
              @if($isGenap)
                <th style="width:170px;">Kenaikan Kelas</th>
              @endif
            </tr>
          </thead>

          <tbody>
            @foreach($siswa as $i => $s)
              @php
                $row = $catatan[$s->id] ?? null;
                $nama = $s->nama_siswa ?? '';
                $nis  = $s->nis ?? '';
                $jk   = $s->jenis_kelamin ?? '';
              @endphp

              <tr data-nama="{{ strtolower($nama) }}" data-nis="{{ strtolower($nis) }}">
                <td class="text-center align-middle">{{ $i+1 }}</td>
                <td class="align-middle">{{ $nama }}</td>
                <td class="align-middle">{{ $nis }}</td>
                <td class="text-center align-middle">{{ $jk }}</td>

                <td class="align-middle">
                  {{-- textarea seperti gambar --}}
                  <textarea class="form-control" rows="2"
                            name="catatan[{{ $s->id }}]"
                            placeholder="Tulis catatan...">{{ old("catatan.$s->id", $row?->catatan) }}</textarea>
                </td>

                @if($isGenap)
                  <td class="align-middle">
                    <select class="form-control" name="status_kenaikan_kelas[{{ $s->id }}]">
                      <option value="">-</option>
                      <option value="naik" @selected(old("status_kenaikan_kelas.$s->id", $row?->status_kenaikan_kelas)==='naik')>
                        Naik Kelas
                      </option>
                      <option value="tinggal" @selected(old("status_kenaikan_kelas.$s->id", $row?->status_kenaikan_kelas)==='tinggal')>
                        Tinggal Kelas
                      </option>
                    </select>
                  </td>
                @endif
              </tr>
            @endforeach
          </tbody>
        </table>

        <div class="p-3">
          <button class="btn btn-success">
            <i class="fas fa-save"></i> Perbarui
          </button>
        </div>

      </form>
    </div>

  </div>
</div>

@push('scripts')
<script>
(function () {
  const searchInput = document.getElementById('searchSiswa');
  const table = document.getElementById('tableSiswa');
  if (!searchInput || !table) return;

  const rows = () => table.querySelectorAll('tbody tr');

  function applySearch() {
    const q = (searchInput.value || '').toLowerCase().trim();
    rows().forEach(tr => {
      const nama = tr.getAttribute('data-nama') || '';
      const nis  = tr.getAttribute('data-nis') || '';
      const show = !q || nama.includes(q) || nis.includes(q);
      tr.style.display = show ? '' : 'none';
    });
  }

  searchInput.addEventListener('input', applySearch);
  applySearch();
})();
</script>
@endpush

@endsection
