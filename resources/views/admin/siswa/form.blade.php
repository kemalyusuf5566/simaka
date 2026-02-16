@extends('layouts.adminlte')

@section('page_title')
  {{ $mode === 'create'
      ? 'Tambah Data Siswa'
      : ($mode === 'edit' ? 'Edit Data Siswa' : 'Detail Data Siswa') }}
@endsection

@section('content')

<form method="POST"
      action="{{ $mode === 'create'
          ? route('admin.siswa.store')
          : ($mode === 'edit'
              ? route('admin.siswa.update', $siswa->id)
              : '#') }}">

@csrf
@if($mode === 'edit')
  @method('PUT')
@endif

<div class="card card-dark">

<div class="card-body">

{{-- ================= A. DATA PRIBADI ================= --}}
<h5 class="text-info mb-3">A. Data Pribadi Siswa</h5>

<div class="row">
  <div class="col-md-6">
    <label>Nama Siswa *</label>
    <input type="text" name="nama_siswa" class="form-control"
           value="{{ old('nama_siswa',$siswa->nama_siswa) }}"
           {{ $mode==='detail'?'readonly':'' }}>
  </div>

  <div class="col-md-6">
    <label>Kelas *</label>
    <select name="data_kelas_id" class="form-control" {{ $mode==='detail'?'disabled':'' }}>
      <option value="">-- Pilih --</option>
      @foreach($kelas as $k)
        <option value="{{ $k->id }}"
          {{ $siswa->data_kelas_id==$k->id?'selected':'' }}>
          {{ $k->nama_kelas }}
        </option>
      @endforeach
    </select>
  </div>
</div>

<div class="row mt-2">
  <div class="col-md-6">
    <label>NIS</label>
    <input type="text" name="nis" class="form-control"
           value="{{ $siswa->nis }}"
           {{ $mode==='detail'?'readonly':'' }}>
  </div>
  <div class="col-md-6">
    <label>NISN</label>
    <input type="text" name="nisn" class="form-control"
           value="{{ $siswa->nisn }}"
           {{ $mode==='detail'?'readonly':'' }}>
  </div>
</div>

<div class="row mt-2">
  <div class="col-md-6">
    <label>Tempat Lahir *</label>
    <input type="text" name="tempat_lahir" class="form-control"
           value="{{ $siswa->tempat_lahir }}"
           {{ $mode==='detail'?'readonly':'' }}>
  </div>
  <div class="col-md-6">
    <label>Tanggal Lahir *</label>
    <input type="date" name="tanggal_lahir" class="form-control"
           value="{{ $siswa->tanggal_lahir }}"
           {{ $mode==='detail'?'readonly':'' }}>
  </div>
</div>

<div class="row mt-2">
  <div class="col-md-6">
    <label>Jenis Kelamin *</label>
    <select name="jenis_kelamin" class="form-control" {{ $mode==='detail'?'disabled':'' }}>
      <option value="L" {{ $siswa->jenis_kelamin=='L'?'selected':'' }}>Laki-laki</option>
      <option value="P" {{ $siswa->jenis_kelamin=='P'?'selected':'' }}>Perempuan</option>
    </select>
  </div>

  <div class="col-md-6">
    <label>Agama *</label>
    <select name="agama" class="form-control" {{ $mode==='detail'?'disabled':'' }}>
      @php
        $agama = ['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu'];
      @endphp
      <option value="">-- Pilih Agama --</option>
      @foreach($agama as $a)
        <option value="{{ $a }}" {{ $siswa->agama==$a?'selected':'' }}>{{ $a }}</option>
      @endforeach
    </select>
  </div>

  <div class="col-md-6">
    <label>Status Dalam Keluarga</label>
    <select name="status_dalam_keluarga" class="form-control" {{ $mode==='detail'?'disabled':'' }}>
      <option value="">-- Pilih --</option>
      <option value="Anak Kandung" {{ $siswa->status_dalam_keluarga=='Anak Kandung'?'selected':'' }}>Anak Kandung</option>
      <option value="Anak Angkat" {{ $siswa->status_dalam_keluarga=='Anak Angkat'?'selected':'' }}>Anak Angkat</option>
      <option value="Anak Tiri" {{ $siswa->status_dalam_keluarga=='Anak Tiri'?'selected':'' }}>Anak Tiri</option>
    </select>
  </div>
  <div class="col-md-6">
    <label>Anak Ke *</label>
    <input type="text" name="anak_ke" class="form-control"
           value="{{ old('anak_ke',$siswa->anak_ke) }}"
           {{ $mode==='detail'?'readonly':'' }}>
  </div>
</div>

<div class="row mt-2">
  <div class="col-md-6">
    <label>Alamat Siswa</label>
    <textarea name="alamat" class="form-control"
      {{ $mode==='detail'?'readonly':'' }}>{{ $siswa->alamat }}</textarea>
  </div>
  <div class="col-md-6">
    <label>Telepon Siswa</label>
    <input type="text" name="telepon" class="form-control"
           value="{{ $siswa->telepon }}"
           {{ $mode==='detail'?'readonly':'' }}>
  </div>
</div>

<hr>

{{-- ================= B. DATA PENDIDIKAN ================= --}}
<h5 class="text-info mb-3">B. Data Pendidikan</h5>

<div class="row">
  <div class="col-md-6">
    <label>Sekolah Asal</label>
    <input type="text" name="sekolah_asal" class="form-control"
           value="{{ $siswa->sekolah_asal }}"
           {{ $mode==='detail'?'readonly':'' }}>
  </div>
  <div class="col-md-3">
    <label>Diterima di Kelas</label>
    <input type="text" name="diterima_di_kelas" class="form-control"
           value="{{ $siswa->diterima_di_kelas }}"
           {{ $mode==='detail'?'readonly':'' }}>
  </div>
  <div class="col-md-3">
    <label>Tanggal Diterima</label>
    <input type="date" name="tanggal_diterima" class="form-control"
           value="{{ $siswa->tanggal_diterima }}"
           {{ $mode==='detail'?'readonly':'' }}>
  </div>
</div>

<hr>

{{-- ================= C. ORANG TUA ================= --}}
<h5 class="text-info mb-3">C. Data Orang Tua</h5>

<div class="row">
  <div class="col-md-6">
    <label>Nama Ayah</label>
    <input type="text" name="nama_ayah" class="form-control"
           value="{{ $siswa->nama_ayah }}"
           {{ $mode==='detail'?'readonly':'' }}>
  </div>
  <div class="col-md-6">
    <label>Pekerjaan Ayah</label>
    <input type="text" name="pekerjaan_ayah" class="form-control"
           value="{{ $siswa->pekerjaan_ayah }}"
           {{ $mode==='detail'?'readonly':'' }}>
  </div>
</div>

<div class="row mt-2">
  <div class="col-md-6">
    <label>Nama Ibu</label>
    <input type="text" name="nama_ibu" class="form-control"
           value="{{ $siswa->nama_ibu }}"
           {{ $mode==='detail'?'readonly':'' }}>
  </div>
  <div class="col-md-6">
    <label>Pekerjaan Ibu</label>
    <input type="text" name="pekerjaan_ibu" class="form-control"
           value="{{ $siswa->pekerjaan_ibu }}"
           {{ $mode==='detail'?'readonly':'' }}>
  </div>
</div>

<div class="row mt-2">
  <div class="col-md-6">
    <label>Alamat Orang Tua</label>
    <textarea name="alamat_orang_tua" class="form-control"
      {{ $mode==='detail'?'readonly':'' }}>{{ $siswa->alamat_orang_tua }}</textarea>
  </div>
  <div class="col-md-6">
    <label>Telepon Orang Tua</label>
    <input type="text" name="telepon_orang_tua" class="form-control"
           value="{{ $siswa->telepon_orang_tua }}"
           {{ $mode==='detail'?'readonly':'' }}>
  </div>
</div>

<hr>

{{-- ================= D. WALI ================= --}}
<h5 class="text-info mb-3">D. Data Wali</h5>

<div class="row">
  <div class="col-md-6">
    <label>Nama Wali</label>
    <input type="text" name="nama_wali" class="form-control"
           value="{{ $siswa->nama_wali }}"
           {{ $mode==='detail'?'readonly':'' }}>
  </div>
  <div class="col-md-6">
    <label>Pekerjaan Wali</label>
    <input type="text" name="pekerjaan_wali" class="form-control"
           value="{{ $siswa->pekerjaan_wali }}"
           {{ $mode==='detail'?'readonly':'' }}>
  </div>
</div>

<div class="row mt-2">
  <div class="col-md-6">
    <label>Alamat Wali</label>
    <textarea name="alamat_wali" class="form-control"
      {{ $mode==='detail'?'readonly':'' }}>{{ $siswa->alamat_wali }}</textarea>
  </div>
  <div class="col-md-6">
    <label>Telepon Wali</label>
    <input type="text" name="telepon_wali" class="form-control"
           value="{{ $siswa->telepon_wali }}"
           {{ $mode==='detail'?'readonly':'' }}>
  </div>
</div>

</div>

<div class="card-footer">
  <a href="{{ $back_url ?? route('admin.siswa.index') }}" class="btn btn-secondary">Kembali</a>
  @if($mode !== 'detail')
    <button class="btn btn-primary float-right">Simpan</button>
  @endif
</div>

</div>
</form>
@endsection
