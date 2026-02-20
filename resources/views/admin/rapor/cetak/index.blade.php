@extends('layouts.adminlte')

@section('page_title','Cetak Rapor')

@section('content')

<div class="card card-dark">
  <div class="card-header">
    <h3 class="card-title">Daftar Kelas</h3>
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
        @forelse($kelas as $i => $k)
          <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $k->nama_kelas }}</td>
            <td>{{ $k->wali->pengguna->nama ?? '-' }}</td>
            <td class="text-center">{{ $k->tingkat }}</td>
            <td class="text-center">{{ $k->siswa_count ?? 0 }}</td>
            <td class="text-center">
              <a href="{{ route('admin.rapor.cetak.detail', $k->id) }}"
                 class="btn btn-primary btn-xs">
                <i class="fas fa-eye"></i> Detail
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center text-muted">
              Data kelas belum tersedia
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@endsection
