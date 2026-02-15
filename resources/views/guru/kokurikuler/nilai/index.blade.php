@extends('layouts.adminlte')

@section('title', 'Kelola Nilai Kokurikuler')

@section('content')
<div class="container-fluid">

  <div class="d-flex align-items-center mb-3">
    <a href="{{ route('guru.kokurikuler.kegiatan.index', $kelompok->id) }}" class="btn btn-link p-0 mr-2" title="Kembali">
      <i class="fas fa-arrow-left"></i>
    </a>
    <h4 class="mb-0">Kelola Nilai Kokurikuler</h4>
  </div>

  <div class="card mb-3">
    <div class="card-body">

      <div class="row">
        <div class="col-md-3 font-weight-bold">Nama Kelompok</div>
        <div class="col-md-9">: {{ $kelompok->nama_kelompok ?? '-' }}</div>

        <div class="col-md-3 font-weight-bold mt-2">Kelas</div>
        <div class="col-md-9 mt-2">: {{ $kelompok->kelas->nama_kelas ?? '-' }}</div>

        <div class="col-md-3 font-weight-bold mt-2">Guru/Koordinator</div>
        <div class="col-md-9 mt-2">: {{ $kelompok->koordinator->nama ?? '-' }}</div>

        <div class="col-md-3 font-weight-bold mt-2">Nama Kegiatan</div>
        <div class="col-md-9 mt-2">: {{ $kegiatan->nama_kegiatan ?? '-' }}</div>

        <div class="col-md-3 font-weight-bold mt-2">Capaian Profil</div>
        <div class="col-md-9 mt-2">
          <div class="d-flex align-items-center" style="gap:10px;">
            <select id="capaianSelect" class="form-control form-control-sm" style="max-width: 820px;">
              @foreach($capaianAkhir as $ca)
                <option
                  value="{{ $ca->id }}"
                  data-dimensi="{{ e($ca->dimensi->nama_dimensi ?? '-') }}"
                  data-capaian="{{ e($ca->capaian ?? '-') }}"
                  {{ (int)$selectedCapaianId === (int)$ca->id ? 'selected' : '' }}
                >
                  {{ $loop->iteration }}. {{ \Illuminate\Support\Str::limit(($ca->capaian ?? '-'), 80) }}
                </option>
              @endforeach
            </select>

            <button type="button" class="btn btn-warning btn-sm" id="btnDetailCapaian" title="Lihat Detail">
              <i class="fas fa-eye"></i>
            </button>
          </div>
          <small class="text-muted d-block mt-1">
            Pilih capaian profil untuk menampilkan dan menginput predikat per siswa.
          </small>
        </div>
      </div>

    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="d-flex justify-content-between align-items-center mb-2">
    <a href="{{ route('guru.kokurikuler.deskripsi.index', [$kelompok->id, $kegiatan->id]) }}"
       class="btn btn-warning btn-sm">
      <i class="fas fa-pen"></i> Deskripsi Capaian Kokurikuler
    </a>

    <button type="button" class="btn btn-info btn-sm" id="btnApplyPredikatRata">
      <i class="fas fa-clipboard-check"></i> Terapkan Predikat Rata
    </button>
  </div>

  <div class="d-flex justify-content-end align-items-center mb-2">
    <div style="width:220px;">
      <input type="text" class="form-control form-control-sm" placeholder="Cari...">
    </div>
  </div>

  <form method="POST" action="{{ route('guru.kokurikuler.nilai.update', [$kelompok->id, $kegiatan->id]) }}">
    @csrf

    <div class="card">
      <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-sm mb-0">
          <thead class="bg-dark text-white">
            <tr>
              <th style="width:60px;">No.</th>
              <th style="width:160px;">NIS</th>
              <th>Nama</th>
              <th style="width:80px;">L/P</th>
              <th style="width:240px;">Predikat</th>
            </tr>
          </thead>

          <tbody>
            @forelse($anggota as $i => $a)
              @php
                $siswa = $a->siswa;
                $nilai = $nilaiRows[$siswa->id] ?? null;
              @endphp

              <tr>
                <td class="text-center align-top">{{ $i+1 }}</td>
                <td class="align-top">{{ $siswa->nis ?? '-' }}</td>
                <td class="align-top">{{ $siswa->nama_siswa ?? '-' }}</td>
                <td class="text-center align-top">
                  {{ $siswa->jenis_kelamin ?? ($siswa->jk ?? '-') }}
                </td>

                <td class="align-top">
                  <select
                    name="nilai[{{ $siswa->id }}][predikat]"
                    class="form-control form-control-sm predikat-select"
                  >
                    <option value="">- pilih predikat -</option>
                    @foreach($opsiPredikat as $val => $label)
                      <option value="{{ $val }}"
                        {{ (string)($nilai->predikat ?? '') === (string)$val ? 'selected' : '' }}>
                        {{ $label }}
                      </option>
                    @endforeach
                  </select>

                  <input type="hidden"
                         name="nilai[{{ $siswa->id }}][kk_capaian_akhir_id]"
                         value="{{ $selectedCapaianId }}">
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center text-muted">Belum ada anggota di kelompok ini.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- FIX: Bootstrap 4 -> pakai ml-auto, bukan ms-auto --}}
      <div class="card-footer d-flex align-items-center">
        <label class="mb-0">
          <input type="checkbox" id="confirmCheck">
          Saya yakin sudah mengisi dengan benar
        </label>

        <button type="submit" class="btn btn-primary px-4 ml-auto" id="btnSubmit" disabled>
          Simpan Perubahan
        </button>
      </div>

    </div>
  </form>

</div>

<div class="modal fade" id="modalDetailCapaian" tabindex="-1" aria-labelledby="modalDetailCapaianLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="modalDetailCapaianLabel">Detail Capaian Profil</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <table class="table table-borderless mb-0">
          <tr>
            <td style="width:160px;" class="font-weight-bold">Dimensi</td>
            <td style="width:10px;">:</td>
            <td id="detailDimensi">-</td>
          </tr>
          <tr>
            <td class="font-weight-bold">Capaian Akhir</td>
            <td>:</td>
            <td id="detailCapaian">-</td>
          </tr>
        </table>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
      </div>

    </div>
  </div>
</div>

@push('scripts')
<script>
(function () {
  const select = document.getElementById('capaianSelect');
  const btnEye = document.getElementById('btnDetailCapaian');
  const dimEl  = document.getElementById('detailDimensi');
  const capEl  = document.getElementById('detailCapaian');

  const confirmCheck = document.getElementById('confirmCheck');
  const btnSubmit    = document.getElementById('btnSubmit');

  confirmCheck?.addEventListener('change', function () {
    btnSubmit.disabled = !this.checked;
  });

  // ganti capaian -> reload halaman + query param, supaya nilaiRows ikut ganti dari controller
  select?.addEventListener('change', function () {
    const url = new URL(window.location.href);
    url.searchParams.set('kk_capaian_akhir_id', this.value);
    window.location.href = url.toString();
  });

  btnEye?.addEventListener('click', function () {
    const opt = select.options[select.selectedIndex];
    if (!opt || !opt.value) {
      alert('Pilih capaian profil terlebih dahulu.');
      return;
    }
    dimEl.textContent = opt.getAttribute('data-dimensi') || '-';
    capEl.textContent = opt.getAttribute('data-capaian') || '-';
    $('#modalDetailCapaian').modal('show');
  });

  const btnApply = document.getElementById('btnApplyPredikatRata');
  btnApply?.addEventListener('click', function () {
    const input = prompt('Masukkan predikat rata (contoh: SB/B/C/PB atau Sangat Baik/Baik/Cukup/Perlu Bimbingan):');
    if (input === null) return;

    const val = (input || '').trim().toLowerCase();
    if (!val) return;

    document.querySelectorAll('.predikat-select').forEach(sel => {
      let found = false;

      [...sel.options].forEach(o => {
        if (!found && String(o.value).toLowerCase() === val) {
          sel.value = o.value; found = true;
        }
      });

      if (!found) {
        [...sel.options].forEach(o => {
          if (!found && String(o.textContent).trim().toLowerCase() === val) {
            sel.value = o.value; found = true;
          }
        });
      }
    });
  });

})();
</script>
@endpush

@endsection
