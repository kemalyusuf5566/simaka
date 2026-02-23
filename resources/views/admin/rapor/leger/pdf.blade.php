<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Leger {{ $kelas->nama_kelas }}</title>
  <style>
    * { font-family: DejaVu Sans, sans-serif; }
    body { font-size: 10px; }
    .title { font-size: 14px; font-weight: bold; text-align:center; margin-bottom: 6px; }
    .sub { text-align:center; margin-bottom: 10px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #000; padding: 4px 5px; }
    th { background: #eaeaea; }
    .text-center { text-align:center; }
    .nowrap { white-space: nowrap; }
  </style>
</head>
<body>

  <div class="title">LEGER NILAI</div>
  <div class="sub">
    Kelas: <b>{{ $kelas->nama_kelas }}</b>
    &nbsp; | &nbsp;
    Wali Kelas: <b>{{ (optional(optional($kelas->wali)->pengguna)->nama) ?? '-' }}</b>
    &nbsp; | &nbsp;
    TP: <b>{{ $tahunAktif->tahun_pelajaran ?? '-' }}</b>
    &nbsp; | &nbsp;
    Semester: <b>{{ $semester ?? '-' }}</b>
  </div>

  <table>
    <thead>
      <tr>
        <th rowspan="2" class="text-center nowrap" style="width:30px;">No</th>
        <th rowspan="2" class="text-center nowrap" style="width:70px;">NIS</th>
        <th rowspan="2" style="width:160px;">Nama</th>
        <th rowspan="2" class="text-center nowrap" style="width:20px;">L/P</th>

        <th colspan="{{ $mapel->count() }}" class="text-center">NILAI</th>

        <th rowspan="2" class="text-center nowrap" style="width:30px;">Total</th>
        <th rowspan="2" class="text-center nowrap" style="width:40px;">Rata</th>
        <th rowspan="2" class="text-center nowrap" style="width:40px;">Rank</th>
      </tr>
      <tr>
        @foreach($mapel as $m)
          <th class="text-center nowrap" style="width:40px;">{{ $m->singkatan ?? '-' }}</th>
        @endforeach
      </tr>
    </thead>
    <tbody>
      @foreach($rows as $i => $r)
        @php
          $s = $r['siswa'];
          $jk = strtoupper($s->jenis_kelamin ?? '-');
          $jk = in_array($jk, ['L','P'], true) ? $jk : '-';
        @endphp
        <tr>
          <td class="text-center">{{ $i+1 }}</td>
          <td class="text-center">{{ $s->nis ?? '-' }}</td>
          <td>{{ $s->nama_siswa ?? '-' }}</td>
          <td class="text-center">{{ $jk }}</td>

          @foreach($mapel as $m)
            @php $v = $nilaiMap[(int)$s->id][(int)$m->id] ?? null; @endphp
            <td class="text-center">{{ is_numeric($v) ? (int)$v : '-' }}</td>
          @endforeach

          <td class="text-center"><b>{{ $r['total'] }}</b></td>
          <td class="text-center">{{ is_numeric($r['rata']) ? number_format($r['rata'], 1) : '-' }}</td>
          <td class="text-center">{{ $r['rank'] ?? '-' }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

</body>
</html>