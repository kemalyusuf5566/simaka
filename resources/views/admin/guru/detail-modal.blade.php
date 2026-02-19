@php
  $p = $guru->pengguna;
  $aktif = (bool)($p->status_aktif ?? false);
@endphp

<div class="text-center mb-3">
  <div style="font-size:70px;color:#ccc;">
    <i class="fas fa-user-circle"></i>
  </div>
  <h4 class="mb-0">{{ $p->nama ?? '-' }}</h4>
</div>

<table class="table table-bordered">
  <tr>
    <td width="35%"><b>Status Guru</b></td>
    <td>
      <span class="badge {{ $aktif ? 'badge-success' : 'badge-danger' }}">
        {{ $aktif ? 'AKTIF' : 'TIDAK AKTIF' }}
      </span>
    </td>
  </tr>
  <tr>
    <td><b>NIP</b></td>
    <td>{{ $guru->nip ?? '-' }}</td>
  </tr>
  <tr>
    <td><b>NUPTK</b></td>
    <td>{{ $guru->nuptk ?? '-' }}</td>
  </tr>
  <tr>
    <td><b>Tempat, Tanggal Lahir</b></td>
    <td>{{ $guru->tempat_lahir ?? '-' }}, {{ $guru->tanggal_lahir ?? '-' }}</td>
  </tr>
  <tr>
    <td><b>Jenis Kelamin</b></td>
    <td>
      @if(($guru->jenis_kelamin ?? '') === 'L') LAKI-LAKI
      @elseif(($guru->jenis_kelamin ?? '') === 'P') PEREMPUAN
      @else -
      @endif
    </td>
  </tr>
  <tr>
    <td><b>Telepon</b></td>
    <td>{{ $guru->telepon ?? '-' }}</td>
  </tr>
  <tr>
    <td><b>Alamat</b></td>
    <td>{{ $guru->alamat ?? '-' }}</td>
  </tr>
</table>
