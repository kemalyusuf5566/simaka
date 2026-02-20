@php
  $mode = $mode ?? 'create';
  $isEdit = $mode === 'edit';
@endphp

<div class="modal-body">

  @if ($errors->any())
    <div class="alert alert-info">
      * adalah kolom yang wajib diisi!
    </div>
  @else
    <div class="alert alert-info">
      * adalah kolom yang wajib diisi!
    </div>
  @endif

  <form id="formKelasModal"
        method="POST"
        action="{{ $isEdit ? route('admin.kelas.update', $kelas->id) : route('admin.kelas.store') }}">

    @csrf
    @if($isEdit)
      @method('PUT')
    @endif

    {{-- tahun pelajaran: tetap pakai hidden seperti controller kamu sebelumnya --}}
    <input type="hidden" name="data_tahun_pelajaran_id" value="{{ $tahunAktif->id ?? '' }}">

    <div class="form-group">
      <label>Nama Kelas <span class="text-danger">*</span></label>
      <input type="text"
             name="nama_kelas"
             class="form-control"
             value="{{ old('nama_kelas', $kelas->nama_kelas ?? '') }}"
             placeholder="Ketik Nama Kelas"
             required>
    </div>

    <div class="form-group">
      <label>Wali Kelas</label>
      <select name="wali_kelas_id" class="form-control">
        <option value="">-- Pilih --</option>
        @foreach(($wali ?? []) as $g)
          <option value="{{ $g->pengguna_id }}"
            @selected(old('wali_kelas_id', $kelas->wali_kelas_id ?? '') == $g->pengguna_id)>
            {{ $g->pengguna->nama }}
          </option>
        @endforeach
      </select>
    </div>

    <div class="form-group">
      <label>Tahun Pelajaran <span class="text-danger">*</span></label>
      {{-- karena pilihannya cuma 1 (tahun aktif), tampilkan readonly/select disabled biar sesuai UI --}}
      <select class="form-control" disabled>
        <option selected>
          {{ ($tahunAktif->tahun_pelajaran ?? '-') . ' - ' . ($tahunAktif->semester ?? '-') }}
        </option>
      </select>
      {{-- hidden sudah ada di atas --}}
    </div>

    <div class="form-group">
      <label>Tingkat <span class="text-danger">*</span></label>
      <select name="tingkat" class="form-control" required>
        <option value="">-- Pilih --</option>
        @for($i=7;$i<=9;$i++)
          <option value="{{ $i }}" @selected(old('tingkat', $kelas->tingkat ?? '') == $i)>
            {{ $i }}
          </option>
        @endfor
      </select>
    </div>

    <hr>

    <div class="form-group mb-0 d-flex justify-content-between align-items-center">
      <label class="mb-0">
        <input type="checkbox" name="yakin" value="1" required>
        Saya yakin sudah mengisi dengan benar
      </label>

      <div>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button class="btn btn-primary">Simpan</button>
      </div>
    </div>

  </form>
</div>