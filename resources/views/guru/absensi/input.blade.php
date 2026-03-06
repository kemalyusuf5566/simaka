@extends('layouts.adminlte')
@section('title','Input Absensi')

@section('content')
<div class="container-fluid">
  <div class="d-flex align-items-center mb-3">
    <a href="{{ route('guru.absensi.index', ['tanggal' => $tanggal]) }}" class="btn btn-link p-0 mr-2">
      <i class="fas fa-arrow-left"></i>
    </a>
    <h4 class="mb-0">Input Absensi</h4>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="card mb-3">
    <div class="card-body">
      <div class="row">
        <div class="col-md-2 font-weight-bold">Kelas</div>
        <div class="col-md-10">: {{ $jadwal->kelas->nama_kelas ?? '-' }}</div>
        <div class="col-md-2 font-weight-bold mt-2">Mapel</div>
        <div class="col-md-10 mt-2">: {{ $jadwal->mapel->nama_mapel ?? '-' }}</div>
        <div class="col-md-2 font-weight-bold mt-2">Hari/Tanggal</div>
        <div class="col-md-10 mt-2">: {{ $hari }}, {{ \Carbon\Carbon::parse($tanggal)->format('d-m-Y') }}</div>
        <div class="col-md-2 font-weight-bold mt-2">Jam Ke</div>
        <div class="col-md-10 mt-2">: {{ $jadwal->jam_ke }}</div>
      </div>
    </div>
  </div>

  <div class="card">
    <form method="POST" action="{{ route('guru.absensi.store', $jadwal->id) }}">
      @csrf
      <input type="hidden" name="tanggal" value="{{ $tanggal }}">

      <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-sm mb-0">
          <thead class="bg-dark text-white">
            <tr>
              <th style="width:50px;">#</th>
              <th>Nama Siswa</th>
              <th style="width:140px;">NIS</th>
              <th style="width:120px;">Status</th>
              <th>Catatan</th>
            </tr>
          </thead>
          <tbody>
            @forelse($siswa as $i => $s)
              @php $old = $existing[$s->id] ?? null; @endphp
              <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $s->nama_siswa }}</td>
                <td>{{ $s->nis ?? '-' }}</td>
                <td>
                  <select name="status[{{ $s->id }}]" class="form-control form-control-sm">
                    @php $val = old("status.{$s->id}", $old?->status ?? 'H'); @endphp
                    <option value="H" {{ $val==='H' ? 'selected' : '' }}>Hadir</option>
                    <option value="S" {{ $val==='S' ? 'selected' : '' }}>Sakit</option>
                    <option value="I" {{ $val==='I' ? 'selected' : '' }}>Izin</option>
                    <option value="A" {{ $val==='A' ? 'selected' : '' }}>Alpa</option>
                  </select>
                </td>
                <td>
                  <input type="text" name="catatan[{{ $s->id }}]" class="form-control form-control-sm"
                         value="{{ old("catatan.{$s->id}", $old?->catatan) }}" maxlength="255">
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center text-muted py-4">Tidak ada siswa pada kelas ini.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="card-footer">
        <button class="btn btn-primary"><i class="fas fa-save"></i> Simpan Absensi</button>
      </div>
    </form>
  </div>
</div>
@endsection

