@extends('layouts.adminlte')

@section('title', 'Data Ekstrakurikuler')

@section('content')
<div class="container-fluid">

  <h4 class="mb-3">Data Ekstrakurikuler</h4>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="card">
    <div class="card-body">

      {{-- TOOLBAR (UI) --}}
      <div class="d-flex justify-content-between align-items-center mb-3">
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

      <div class="table-responsive p-0">
        <table class="table table-bordered table-sm mb-0">
          <thead class="bg-dark text-white">
            <tr>
              <th style="width:60px;">No.</th>
              <th>Nama Ekstrakurikuler</th>
              <th style="width:260px;">Pembina</th>
              <th style="width:160px;" class="text-center">Jumlah Anggota</th>
              <th style="width:140px;" class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($ekskul as $i => $row)
              <tr>
                <td class="text-center">
                  {{-- kalau paginator: nomor mengikuti halaman --}}
                  @if(method_exists($ekskul, 'firstItem'))
                    {{ $ekskul->firstItem() + $i }}
                  @else
                    {{ $i + 1 }}
                  @endif
                </td>
                <td>{{ $row->nama_ekskul ?? $row->nama_ekstrakurikuler ?? '-' }}</td>
                <td>{{ $row->pembina_nama ?? '-' }}</td>
                <td class="text-center">
                  {{-- hasil withCount('anggota') --}}
                  {{ $row->anggota_count ?? 0 }}
                </td>
                <td class="text-center">
                  <a href="{{ route('guru.ekskul.anggota.index', $row->id) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-cog"></i> Kelola
                  </a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center text-muted">
                  Anda belum ditetapkan sebagai pembina ekskul.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

    </div>

    {{-- FOOTER: kiri info, kanan pagination (pojok kanan) --}}
    <div class="card-footer d-flex justify-content-between align-items-center">

      <div class="text-muted small">
        @if(method_exists($ekskul, 'total'))
          Menampilkan
          {{ $ekskul->total() ? $ekskul->firstItem().' - '.$ekskul->lastItem().' dari '.$ekskul->total().' data' : '0 data' }}
        @else
          Menampilkan {{ $ekskul->count() ? '1 - '.$ekskul->count().' dari '.$ekskul->count().' data' : '0 data' }}
        @endif
      </div>

      <div class="ml-auto">
        @if(method_exists($ekskul, 'links'))
          <div class="d-flex justify-content-end">
            {{ $ekskul->links('pagination::bootstrap-4') }}
          </div>
        @else
          {{-- kalau belum paginate, ini placeholder UI --}}
          <nav>
            <ul class="pagination pagination-sm mb-0 justify-content-end">
              <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
              <li class="page-item active"><span class="page-link">1</span></li>
              <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
            </ul>
          </nav>
        @endif
      </div>

    </div>

  </div>
</div>
@endsection
