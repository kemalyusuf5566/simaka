@extends('layouts.adminlte')

@section('page_title','Data Sekolah')

@section('content')

@if(!$sekolah)
<div class="card">
    <div class="card-body text-center">
        <i class="fas fa-school fa-3x mb-3 text-muted"></i>
        <p>Data sekolah belum tersedia.</p>
        <form method="POST" action="{{ route('admin.sekolah.store') }}">
            @csrf
            <input type="hidden" name="nama_sekolah" value="SMK BUDI PERKASA">
            <button class="btn btn-primary">
                Buat Data Sekolah
            </button>
        </form>
    </div>
</div>
@else

<div class="row">

  {{-- ================= KOLOM KIRI ================= --}}
  <div class="col-md-8">
    <div class="card card-primary">
      <div class="card-header">
        <h3 class="card-title">Edit Data Sekolah</h3>
      </div>

      <form method="POST"
            action="{{ route('admin.sekolah.update', $sekolah->id) }}">
        @csrf
        @method('PUT')

        <div class="card-body">

          <div class="form-group">
            <label>Nama Sekolah</label>
            <input type="text" name="nama_sekolah"
                   class="form-control"
                   value="{{ $sekolah->nama_sekolah }}" required>
          </div>

          <div class="form-row">
              <div class="form-group col-md-6">
                  <label>NPSN</label>
                  <input type="text" name="npsn"
                         class="form-control"
                         value="{{ $sekolah->npsn }}">
              </div>

              <div class="form-group col-md-6">
                  <label>Kode POS</label>
                  <input type="text" name="kode_pos"
                         class="form-control"
                         value="{{ $sekolah->kode_pos }}">
              </div>
          </div>

          <div class="form-group">
            <label>Telepon</label>
            <input type="text" name="telepon"
                   class="form-control"
                   value="{{ $sekolah->telepon }}">
          </div>

          <div class="form-group">
            <label>Alamat</label>
            <textarea name="alamat"
                      class="form-control">{{ $sekolah->alamat }}</textarea>
          </div>

          <div class="form-row">
              <div class="form-group col-md-6">
                  <label>Desa / Kelurahan</label>
                  <input type="text" name="desa"
                         class="form-control"
                         value="{{ $sekolah->desa }}">
              </div>

              <div class="form-group col-md-6">
                  <label>Kecamatan</label>
                  <input type="text" name="kecamatan"
                         class="form-control"
                         value="{{ $sekolah->kecamatan }}">
              </div>
          </div>

          <div class="form-row">
              <div class="form-group col-md-6">
                  <label>Kota / Kabupaten</label>
                  <input type="text" name="kota"
                         class="form-control"
                         value="{{ $sekolah->kota }}">
              </div>

              <div class="form-group col-md-6">
                  <label>Provinsi</label>
                  <input type="text" name="provinsi"
                         class="form-control"
                         value="{{ $sekolah->provinsi }}">
              </div>
          </div>

          <div class="form-row">
              <div class="form-group col-md-6">
                  <label>Email</label>
                  <input type="email" name="email"
                         class="form-control"
                         value="{{ $sekolah->email }}">
              </div>

              <div class="form-group col-md-6">
                  <label>Website</label>
                  <input type="text" name="website"
                         class="form-control"
                         value="{{ $sekolah->website }}">
              </div>
          </div>

          <div class="form-group">
            <label>Kepala Sekolah</label>
            <input type="text" name="kepala_sekolah"
                   class="form-control"
                   value="{{ $sekolah->kepala_sekolah }}">
          </div>

          <div class="form-group">
            <label>NIP Kepala Sekolah</label>
            <input type="text" name="nip_kepala_sekolah"
                   class="form-control"
                   value="{{ $sekolah->nip_kepala_sekolah }}">
          </div>

        </div>

        <div class="card-footer text-right">
          <button class="btn btn-primary">
              Simpan Data Sekolah
          </button>
        </div>
      </form>
    </div>
  </div>


  {{-- ================= KOLOM KANAN ================= --}}
  <div class="col-md-4">
    <div class="card card-secondary">
      <div class="card-header">
        <h3 class="card-title">Edit Logo Sekolah</h3>
      </div>

      <form method="POST"
            action="{{ route('admin.sekolah.updateLogo', $sekolah->id) }}"
            enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card-body text-center">

          @if($sekolah->logo)
            <img src="{{ asset('storage/'.$sekolah->logo) }}"
                 style="max-height:150px;">
          @else
            <p class="text-muted">Belum ada logo</p>
          @endif

          <hr>

          <input type="file" name="logo" class="form-control">

        </div>

        <div class="card-footer text-right">
          <button class="btn btn-primary">
              Update Logo
          </button>
        </div>
      </form>
    </div>
  </div>

</div>

@endif

@endsection