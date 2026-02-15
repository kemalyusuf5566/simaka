@extends('layouts.adminlte')

@section('title', 'Kelola Nilai Akhir')

@section('content')
<div class="container-fluid">

  {{-- HEADER: tombol kembali + judul --}}
  <div class="d-flex align-items-center mb-3">
    <a href="{{ route('guru.pembelajaran.index') }}" class="btn btn-link p-0 mr-2" title="Kembali">
      <i class="fas fa-arrow-left"></i>
    </a>
    <h4 class="mb-0">Kelola Nilai Akhir</h4>
  </div>

  {{-- INFO PEMBELAJARAN --}}
  <div class="card mb-3">
    <div class="card-body">
      <div class="row">
        <div class="col-md-3 font-weight-bold">Mata Pelajaran</div>
        <div class="col-md-9">: {{ $pembelajaran->mapel->nama_mapel ?? '-' }}</div>

        <div class="col-md-3 font-weight-bold mt-2">Kelas</div>
        <div class="col-md-9 mt-2">: {{ $pembelajaran->kelas->nama_kelas ?? '-' }}</div>

        <div class="col-md-3 font-weight-bold mt-2">Guru Pengampu</div>
        <div class="col-md-9 mt-2">: {{ $pembelajaran->guru->nama ?? '-' }}</div>
      </div>
    </div>
  </div>

  {{-- TOOLBAR: Edit Deskripsi (kiri) + Terapkan Rata (kanan) --}}
  <div class="d-flex justify-content-between align-items-center mb-2">
    <a href="{{ route('guru.deskripsi.index', $pembelajaran->id) }}" class="btn btn-warning btn-sm">
      <i class="fas fa-edit"></i> Edit Deskripsi Capaian
    </a>

    <form action="{{ route('guru.nilai_akhir.applyAverage', $pembelajaran->id) }}"
          method="POST"
          onsubmit="return confirmApplyAverage(this)"
          class="m-0">
      @csrf
      <input type="hidden" name="nilai_rata" id="nilai_rata_input">
      <button type="submit" class="btn btn-info btn-sm">
        <i class="fas fa-calculator"></i> Terapkan Nilai Rata
      </button>
    </form>
  </div>

  {{-- ALERT --}}
  @if (session('success'))
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

  {{-- BAR FILTER (UI SAJA biar mirip gambar) --}}
  <div class="d-flex justify-content-between align-items-center mb-2">
    <div style="width:220px;" class="ml-auto">
      <input type="text" class="form-control form-control-sm" placeholder="Cari...">
    </div>
  </div>

  <form action="{{ route('guru.nilai_akhir.update', $pembelajaran->id) }}" method="POST">
    @csrf

    <div class="card">
      <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-sm mb-0 align-top">
          <thead class="bg-dark text-white">
            <tr>
              <th style="width:55px;" class="text-center">No.</th>
              <th style="width:140px;">NIS</th>
              <th style="width:220px;">Nama Siswa</th>
              <th style="width:90px;" class="text-center">Nilai</th>
              <th style="min-width:420px;">Capaian TP Optimal</th>
              <th style="min-width:420px;">Capaian TP Perlu Peningkatan</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($siswa as $i => $s)
              @php
                $nilai = $nilaiRows[$s->id] ?? null;
                $nilaiId = $nilai->id ?? null;
              @endphp

              <tr>
                <td class="text-center align-top">{{ $i + 1 }}</td>
                <td class="align-top">{{ $s->nis ?? '-' }}</td>
                <td class="align-top">{{ $s->nama_siswa ?? '-' }}</td>

                <td class="align-top">
                  <input type="number"
                         class="form-control form-control-sm text-center"
                         name="nilai[{{ $s->id }}]"
                         value="{{ old('nilai.'.$s->id, $nilai->nilai_angka ?? '') }}"
                         min="0" max="100">
                </td>

                {{-- OPTIMAL --}}
                <td class="align-middle">
                  @if ($tpList->count() === 0)
                    <span class="text-muted">Belum ada TP. Isi dulu di Tujuan Pembelajaran.</span>
                  @else
                    @foreach ($tpList as $tp)
                      @php
                        $checkedOptimal = false;
                        if ($nilaiId && isset($checkMap[$nilaiId][$tp->id])) {
                          $checkedOptimal = $checkMap[$nilaiId][$tp->id] === 'optimal';
                        }
                      @endphp

                      <div class="form-check mb-1">
                        <input
                          class="form-check-input tp-check tp-optimal"
                          type="checkbox"
                          data-siswa="{{ $s->id }}"
                          data-tp="{{ $tp->id }}"
                          name="optimal[{{ $s->id }}][]"
                          value="{{ $tp->id }}"
                          {{ $checkedOptimal ? 'checked' : '' }}
                        >
                        <label class="form-check-label tp-label">
                          {{ $tp->tujuan }}
                        </label>
                      </div>
                    @endforeach
                  @endif
                </td>

                {{-- PERLU --}}
                <td class="align-middle">
                  @if ($tpList->count() === 0)
                    <span class="text-muted">Belum ada TP.</span>
                  @else
                    @foreach ($tpList as $tp)
                      @php
                        $checkedPerlu = false;
                        if ($nilaiId && isset($checkMap[$nilaiId][$tp->id])) {
                          $checkedPerlu = $checkMap[$nilaiId][$tp->id] === 'perlu';
                        }
                      @endphp

                      <div class="form-check mb-1">
                        <input
                          class="form-check-input tp-check tp-perlu"
                          type="checkbox"
                          data-siswa="{{ $s->id }}"
                          data-tp="{{ $tp->id }}"
                          name="perlu[{{ $s->id }}][]"
                          value="{{ $tp->id }}"
                          {{ $checkedPerlu ? 'checked' : '' }}
                        >
                        <label class="form-check-label tp-label">
                          {{ $tp->tujuan }}
                        </label>
                      </div>
                    @endforeach
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center text-muted">Tidak ada siswa pada kelas ini.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="card-footer d-flex justify-content-end">
        <button type="submit" class="btn btn-primary">
          Simpan Perubahan
        </button>
      </div>
    </div>
  </form>

</div>
@endsection

@push('scripts')
<script>
  function confirmApplyAverage() {
    const nilai = prompt('Masukkan nilai rata-rata (0-100) untuk diterapkan ke semua siswa:');
    if (nilai === null) return false;
    const n = parseInt(nilai, 10);
    if (isNaN(n) || n < 0 || n > 100) {
      alert('Nilai harus angka 0 sampai 100.');
      return false;
    }
    document.getElementById('nilai_rata_input').value = n;
    return true;
  }

  // === mutual exclusive checkbox optimal vs perlu (per siswa + per TP) ===
  function applyMutualExclusionFor(siswaId, tpId) {
    const opt = document.querySelector(`.tp-optimal[data-siswa="${siswaId}"][data-tp="${tpId}"]`);
    const prl = document.querySelector(`.tp-perlu[data-siswa="${siswaId}"][data-tp="${tpId}"]`);

    if (!opt || !prl) return;

    // jika optimal checked => disable perlu
    if (opt.checked) {
      prl.checked = false;
      prl.disabled = true;
      prl.closest('.form-check')?.classList.add('text-muted');
    } else {
      prl.disabled = false;
      prl.closest('.form-check')?.classList.remove('text-muted');
    }

    // jika perlu checked => disable optimal
    if (prl.checked) {
      opt.checked = false;
      opt.disabled = true;
      opt.closest('.form-check')?.classList.add('text-muted');
    } else {
      opt.disabled = false;
      opt.closest('.form-check')?.classList.remove('text-muted');
    }
  }

  function initMutualExclusion() {
    document.querySelectorAll('.tp-check').forEach(cb => {
      cb.addEventListener('change', function () {
        const siswaId = this.getAttribute('data-siswa');
        const tpId    = this.getAttribute('data-tp');
        applyMutualExclusionFor(siswaId, tpId);
      });
    });

    // initial load: pastikan yang sudah checked langsung ngunci pasangan
    const pairs = new Set();
    document.querySelectorAll('.tp-check').forEach(cb => {
      pairs.add(cb.getAttribute('data-siswa') + ':' + cb.getAttribute('data-tp'));
    });

    pairs.forEach(key => {
      const [siswaId, tpId] = key.split(':');
      applyMutualExclusionFor(siswaId, tpId);
    });
  }

  document.addEventListener('DOMContentLoaded', initMutualExclusion);
</script>
@endpush
