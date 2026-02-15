@extends('layouts.adminlte')

@section('title', 'Tujuan Pembelajaran')

@section('content')
<div class="container-fluid">

  {{-- HEADER --}}
  <div class="d-flex align-items-center mb-3">
    <a href="{{ route('guru.pembelajaran.index') }}" class="btn btn-link p-0 mr-2" title="Kembali">
      <i class="fas fa-arrow-left"></i>
    </a>
    <h4 class="mb-0">Tujuan Pembelajaran</h4>
  </div>

  {{-- INFO --}}
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

  {{-- TOOLBAR --}}
  <div class="d-flex justify-content-between align-items-center mb-2">
    <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalTambahTp">
      <i class="fas fa-plus"></i> Tambah Tujuan Pembelajaran
    </button>

    <a href="{{ route('guru.nilai_akhir.index', $pembelajaran->id) }}" class="btn btn-warning btn-sm">
      <i class="fas fa-cog"></i> Nilai
    </a>
  </div>

  {{-- FILTER BAR (UI) --}}
  <div class="d-flex justify-content-between align-items-center mb-2">
    <div class="d-flex align-items-center">
      <span class="mr-2">Tampilkan</span>
      <select class="form-control form-control-sm" style="width:80px;">
        <option selected>10</option>
        <option>25</option>
        <option>50</option>
        <option>100</option>
      </select>
      <span class="ml-2">data</span>
    </div>

    <div style="width:220px;">
      <input type="text" class="form-control form-control-sm" placeholder="Cari...">
    </div>
  </div>

  {{-- =========================
      FORM SIMPAN (POST ONLY)
      ========================= --}}
  <form id="form-simpan-tp" method="POST" action="{{ route('guru.tp.store', $pembelajaran->id) }}">
    @csrf

    <div class="card">
      <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-sm mb-0">
          <thead class="bg-dark text-white">
            <tr>
              <th style="width:60px;">No.</th>
              <th>Tujuan Pembelajaran (Maksimal 150 karakter)</th>
              <th style="width:110px;" class="text-center">Hapus</th>
            </tr>
          </thead>
          <tbody>
            @forelse($tujuan as $i => $tp)
              <tr>
                <td class="text-center align-middle">{{ $i+1 }}</td>

                <td class="align-middle">
                  <textarea
                    name="tujuan_existing[{{ $tp->id }}]"
                    class="form-control form-control-sm"
                    rows="2"
                    maxlength="150"
                    style="resize:vertical;"
                    required
                  >{{ old('tujuan_existing.'.$tp->id, $tp->tujuan) }}</textarea>
                </td>

                <td class="text-center align-middle">
                  {{-- Tombol ini SAMA SEKALI tidak submit form simpan --}}
                  <button
                    type="button"
                    class="btn btn-danger btn-sm"
                    onclick="hapusTp({{ $tp->id }})">
                    Hapus
                  </button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="3" class="text-center text-muted">Belum ada tujuan pembelajaran.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="card-footer d-flex justify-content-end">
        {{-- Ini satu-satunya submit yang ada --}}
        <button type="submit" class="btn btn-primary">
          Simpan Perubahan
        </button>
      </div>
    </div>
  </form>

  {{-- =========================
      FORM DELETE HIDDEN (DI LUAR FORM SIMPAN)
      ========================= --}}
  @foreach($tujuan as $tp)
    <form id="form-hapus-tp-{{ $tp->id }}"
          action="{{ route('guru.tp.destroy', $tp->id) }}"
          method="POST"
          style="display:none;">
      @csrf
      @method('DELETE')
    </form>
  @endforeach

</div>

{{-- MODAL TAMBAH TP --}}
<div class="modal fade" id="modalTambahTp" tabindex="-1" aria-labelledby="modalTambahTpLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="modalTambahTpLabel">Tambah Tujuan Pembelajaran</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form method="POST" action="{{ route('guru.tp.store', $pembelajaran->id) }}">
        @csrf

        <div class="modal-body">
          <div id="tpInputs">
            <div class="tp-row mb-2">
              <textarea
                name="tujuan_new[]"
                class="form-control"
                maxlength="150"
                rows="2"
                placeholder="Isi Tujuan Pembelajaran (maks 150 karakter)..."
                required
              ></textarea>
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-center mt-2">
            <div>
              <button type="button" class="btn btn-success btn-sm" id="btnTambahTp">
                <i class="fas fa-plus"></i> Tambah TP
              </button>

              <button type="button" class="btn btn-secondary btn-sm d-none" id="btnResetTp">
                <i class="fas fa-undo"></i> Reset
              </button>
            </div>

            <small class="text-muted">* Bisa tambah banyak sekaligus, lalu simpan.</small>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>

    </div>
  </div>
</div>

@push('scripts')
<script>
  function hapusTp(id) {
    if (!confirm('Hapus tujuan ini?')) return;

    const f = document.getElementById('form-hapus-tp-' + id);
    if (!f) {
      alert('Form hapus tidak ditemukan. (cek view:clear + hard refresh)');
      return;
    }
    f.submit();
  }

  (function () {
    const tpInputs   = document.getElementById('tpInputs');
    const btnTambah  = document.getElementById('btnTambahTp');
    const btnReset   = document.getElementById('btnResetTp');

    function refreshResetButton() {
      const count = tpInputs.querySelectorAll('.tp-row').length;
      if (count > 1) btnReset.classList.remove('d-none');
      else btnReset.classList.add('d-none');
    }

    btnTambah.addEventListener('click', function () {
      const div = document.createElement('div');
      div.className = 'tp-row mb-2';
      div.innerHTML = `
        <textarea
          name="tujuan_new[]"
          class="form-control"
          maxlength="150"
          rows="2"
          placeholder="Isi Tujuan Pembelajaran (maks 150 karakter)..."
          required
        ></textarea>
      `;
      tpInputs.appendChild(div);
      refreshResetButton();
    });

    btnReset.addEventListener('click', function () {
      tpInputs.innerHTML = `
        <div class="tp-row mb-2">
          <textarea
            name="tujuan_new[]"
            class="form-control"
            maxlength="150"
            rows="2"
            placeholder="Isi Tujuan Pembelajaran (maks 150 karakter)..."
            required
          ></textarea>
        </div>
      `;
      refreshResetButton();
    });

    $('#modalTambahTp').on('shown.bs.modal', function () {
      refreshResetButton();
    });
  })();
</script>
@endpush

@endsection
