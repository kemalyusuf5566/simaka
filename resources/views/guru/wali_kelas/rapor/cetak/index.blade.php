@extends('layouts.adminlte')

@section('page_title','Cetak Rapor')

@section('content')

<div class="card card-dark">
  <div class="card-header">
    <h3 class="card-title">Kelas Wali Anda</h3>
  </div>

  <div class="card-body p-0">
    <table class="table table-bordered table-striped mb-0">
      <thead class="bg-secondary">
        <tr>
          <th width="50">No</th>
          <th>Nama Kelas</th>
          <th>Wali Kelas</th>
          <th class="text-center">Tingkat</th>
          <th class="text-center">Jumlah Siswa</th>
          <th width="120" class="text-center">Aksi</th>
        </tr>
      </thead>

      <tbody>
        @if(!empty($kelas))
          <tr>
            <td>1</td>
            <td>{{ $kelas->nama_kelas ?? '-' }}</td>
            <td>{{ $kelas->wali?->pengguna?->nama ?? '-' }}</td>
            <td class="text-center">{{ $kelas->tingkat ?? '-' }}</td>
            <td class="text-center">{{ $kelas->siswa()->count() }}</td>
            <td class="text-center">
              <a href="{{ route('guru.wali-kelas.rapor.cetak.detail', $kelas->id) }}"
                 class="btn btn-primary btn-xs">
                <i class="fas fa-eye"></i> Detail
              </a>
            </td>
          </tr>
        @else
          <tr>
            <td colspan="6" class="text-center text-muted">
              Anda belum ditetapkan sebagai Wali Kelas oleh Admin
            </td>
          </tr>
        @endif
      </tbody>
    </table>
  </div>
</div>

@endsection
