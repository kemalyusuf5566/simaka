@extends('layouts.adminlte')

@section('page_title',
  $mode === 'create' ? 'Tambah Data Guru' :
  ($mode === 'edit' ? 'Edit Data Guru' : 'Detail Data Guru')
)

@section('content')
@php
  $guru = $guru ?? null;
  $readonly = $mode === 'detail';
  $p = $guru?->pengguna;
@endphp

<div class="card card-dark">
  <div class="card-header bg-primary">
    <h3 class="card-title">
      {{ $mode === 'create' ? 'Tambah Data Guru' :
         ($mode === 'edit' ? 'Edit Data Guru' : 'Detail Data Guru') }}
    </h3>
  </div>

  <form method="POST"
        action="{{ $mode === 'edit'
          ? route('admin.guru.update', $guru->id)
          : route('admin.guru.store') }}">

    @csrf
    @if($mode === 'edit')
      @method('PUT')
    @endif

    <div class="card-body">
      <div class="row">

        {{-- ================= KIRI ================= --}}
        <div class="col-md-6">

          <div class="form-group">
            <label>Nama *</label>
            <input type="text"
                   name="nama"
                   class="form-control"
                   value="{{ old('nama', $p->nama ?? '') }}"
                   {{ $readonly ? 'readonly' : '' }}
                   required>
          </div>

          <div class="form-group">
            <label>NIP</label>
            <input type="text"
                   name="nip"
                   class="form-control"
                   value="{{ old('nip', $guru->nip ?? '') }}"
                   {{ $readonly ? 'readonly' : '' }}>
          </div>

          <div class="form-group">
            <label>NUPTK</label>
            <input type="text"
                   name="nuptk"
                   class="form-control"
                   value="{{ old('nuptk', $guru->nuptk ?? '') }}"
                   {{ $readonly ? 'readonly' : '' }}>
          </div>

          <div class="form-group">
            <label>Tempat Lahir</label>
            <input type="text"
                   name="tempat_lahir"
                   class="form-control"
                   value="{{ old('tempat_lahir', $guru->tempat_lahir ?? '') }}"
                   {{ $readonly ? 'readonly' : '' }}>
          </div>

          <div class="form-group">
            <label>Tanggal Lahir</label>
            <input type="date"
                   name="tanggal_lahir"
                   class="form-control"
                   value="{{ old('tanggal_lahir', $guru->tanggal_lahir ?? '') }}"
                   {{ $readonly ? 'readonly' : '' }}>
          </div>

          <div class="form-group">
            <label>Jenis Kelamin *</label>
            <select name="jenis_kelamin"
                    class="form-control"
                    {{ $readonly ? 'disabled' : '' }}
                    required>
              <option value="">-- Pilih --</option>
              <option value="L" @selected(old('jenis_kelamin', $guru->jenis_kelamin ?? '') === 'L')>Laki-laki</option>
              <option value="P" @selected(old('jenis_kelamin', $guru->jenis_kelamin ?? '') === 'P')>Perempuan</option>
            </select>
          </div>

          <div class="form-group">
            <label>Alamat</label>
            <textarea name="alamat"
                      class="form-control"
                      {{ $readonly ? 'readonly' : '' }}>{{ old('alamat', $guru->alamat ?? '') }}</textarea>
          </div>

          <div class="form-group">
            <label>Telepon</label>
            <input type="text"
                   name="telepon"
                   class="form-control"
                   value="{{ old('telepon', $guru->telepon ?? '') }}"
                   {{ $readonly ? 'readonly' : '' }}>
          </div>

        </div>

        {{-- ================= KANAN ================= --}}
        <div class="col-md-6">

          <div class="form-group">
            <label>Status Guru *</label>
            @php
              $statusVal = old('status_aktif', isset($p) ? ((int)$p->status_aktif) : 1);
            @endphp
            <select name="status_aktif" class="form-control" {{ $readonly ? 'disabled' : '' }} required>
              <option value="1" {{ (int)$statusVal === 1 ? 'selected' : '' }}>AKTIF</option>
              <option value="0" {{ (int)$statusVal === 0 ? 'selected' : '' }}>TIDAK AKTIF</option>
            </select>
          </div>

          <div class="form-group">
            <label>Email Akun *</label>
            <input type="email"
                   name="email"
                   class="form-control"
                   value="{{ old('email', $p->email ?? '') }}"
                   {{ $readonly ? 'readonly' : '' }}
                   required>
          </div>

          @if($mode === 'create')
            <div class="form-group">
              <label>Password Akun *</label>
              <input type="password" name="password" class="form-control" required>
            </div>
          @endif

          @if($mode === 'edit')
            <div class="form-group">
              <label>Password Baru (opsional)</label>
              <input type="password"
                     name="password"
                     class="form-control"
                     placeholder="Kosongkan jika tidak diubah">
              <small class="text-muted">Isi hanya jika ingin mengganti password</small>
            </div>
          @endif

        </div>

      </div>
    </div>

    <div class="card-footer d-flex justify-content-between">
      @if($mode !== 'detail')
        <button type="submit" class="btn btn-primary">Simpan</button>
      @endif

      <a href="{{ route('admin.guru.index') }}" class="btn btn-secondary">Kembali</a>
    </div>

  </form>
</div>
@endsection
