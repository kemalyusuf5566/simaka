@extends('layouts.adminlte')

@section('page_title','Data Admin')

@section('content')

<div class="card card-dark">
  <div class="card-header">
    <h3 class="card-title">Data Admin</h3>
  </div>

  <div class="card-body">

    {{-- TOOLBAR ATAS (SAMA KAYA GURU & SISWA) --}}
    <div class="d-flex justify-content-between mb-3">
      <div>
        <a href="{{ route('admin.admin.create') }}" class="btn btn-primary btn-sm">
          <i class="fas fa-plus"></i> Tambah Admin
        </a>
        <button class="btn btn-danger btn-sm" disabled>
          <i class="fas fa-trash"></i> Hapus Beberapa
        </button>
      </div>

      <div>
        <button class="btn btn-info btn-sm" disabled>
          <i class="fas fa-filter"></i> Filter Data
        </button>
      </div>
    </div>

    {{-- FILTER BAR (SAMA) --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
      <div>
        <label class="mb-0">
          Tampilkan
          <select class="custom-select custom-select-sm w-auto">
            <option selected>10</option>
            <option>25</option>
            <option>50</option>
          </select>
          data
        </label>
      </div>

      <div>
        <input type="text"
               class="form-control form-control-sm"
               placeholder="Cari..."
               style="width:200px">
      </div>
    </div>

    {{-- TABEL (STYLE SAMA KAYA GURU) --}}
    <div class="table-responsive">
      <table id="table-admin" class="table table-bordered table-hover mb-0">
        <thead>
          <tr>
            <th width="50">No</th>
            <th>Nama</th>
            <th>Email</th>
            <th>Status Admin</th>
            <th width="200">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($admin as $i => $a)
          <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $a->nama }}</td>
            <td>{{ $a->email }}</td>
            <td>
              <span class="badge {{ $a->status_aktif ? 'badge-success' : 'badge-secondary' }}">
                {{ $a->status_aktif ? 'AKTIF' : 'NON AKTIF' }}
              </span>
            </td>
            <td>
              <a href="{{ route('admin.admin.edit',$a->id) }}"
                 class="btn btn-warning btn-xs">
                <i class="fas fa-edit"></i> Edit
              </a>

              <form action="{{ route('admin.admin.destroy',$a->id) }}"
                    method="POST"
                    class="d-inline"
                    onsubmit="return confirm('Hapus admin ini?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger btn-xs">
                  <i class="fas fa-trash"></i> Hapus
                </button>
              </form>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="text-center text-muted">
              Data admin belum tersedia
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

  </div>
</div>

@endsection
