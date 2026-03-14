@extends('layouts.adminlte')

@section('page_title', $ekskul ? 'Edit Ekstrakurikuler' : 'Tambah Ekstrakurikuler')

@section('content')
<div class="card card-dark">
  <div class="card-header">
    <h3 class="card-title">
      {{ $ekskul ? 'Edit Ekstrakurikuler' : 'Tambah Ekstrakurikuler' }}
    </h3>
  </div>

  <form
    method="POST"
    action="{{ $ekskul
      ? route('admin.ekstrakurikuler.update', $ekskul->id)
      : route('admin.ekstrakurikuler.store') }}"
  >
    @csrf
    @if($ekskul)
      @method('PUT')
    @endif

    <div class="card-body">

      <div class="form-group">
        <label>Nama Ekstrakurikuler</label>
        <input
          type="text"
          name="nama_ekskul"
          class="form-control"
          value="{{ old('nama_ekskul', $ekskul->nama_ekskul ?? '') }}"
          required
        >
      </div>

      <div class="form-group">
        <label>Pembina</label>
        <select name="pembina_id" class="form-control">
          <option value="">-- Pilih Guru --</option>
          @foreach($pembina as $g)
            <option value="{{ $g->pengguna_id }}"
              @selected(old('pembina_id', $ekskul->pembina_id ?? '') == $g->pengguna_id)>
              {{ $g->pengguna->nama ?? '-' }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="form-group">
        <label>Status</label>
        <select name="status_aktif" class="form-control" required>
          <option value="1" @selected(old('status_aktif', $ekskul->status_aktif ?? 1) == 1)>
            Aktif
          </option>
          <option value="0" @selected(old('status_aktif', $ekskul->status_aktif ?? 1) == 0)>
            Non Aktif
          </option>
        </select>
      </div>

    </div>

    <div class="card-footer d-flex justify-content-between">
      <a href="{{ route('admin.ekstrakurikuler.index') }}" class="btn btn-secondary">
        Kembali
      </a>

      {{-- 🔴 INI YANG WAJIB --}}
      <button type="submit" class="btn btn-primary">
        Simpan
      </button>
    </div>

  </form>
</div>
@endsection
