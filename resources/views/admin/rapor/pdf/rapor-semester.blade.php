<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Rapor - {{ $siswa->nama_siswa }}</title>

<style>
@page { size: A4 portrait; margin: 12mm 12mm; }
body { font-family: "Times New Roman", serif; font-size: 12pt; color:#333; }

.page { page-break-after: always; }
.page:last-child { page-break-after: auto; }

table { border-collapse: collapse; width:100%; }

.title{
    text-align:center;
    font-weight:bold;
    font-size:14pt;
    margin:3mm 0 4mm;
}

/* ================= HEADER ================= */
.idtbl td{
    padding:0.8mm 0;   /* dirapetin */
    vertical-align:top;
}
.idtbl .label{ width:32mm; }
.idtbl .sep{ width:4mm; text-align:center; }
.idtbl .val{ width:58mm; }

.header-line{
    border-top:1px solid #333;
    margin:3mm 0 4mm;
}

/* ================= TABLE NILAI ================= */
.tbl th, .tbl td{
    border:1px solid #333;
    padding:2.5mm 2mm;
    vertical-align:top;
}
.tbl th{
    text-align:center;
    font-weight:bold;
}
.w-no{ width:10mm; text-align:center; }
.w-mapel{ width:60mm; }
.w-nilai{ width:22mm; text-align:center; }

.section-row td{
    font-weight:bold;
    text-align:left;
}

/* ================= KOTAK BAWAH ================= */
.mini-title{
    font-weight:bold;
    text-align:center;
    border:1px solid #333;
    padding:1.5mm 0;
}
.mini-box{
    border:1px solid #333;
    border-top:none;
    padding:2mm;
    min-height:22mm;   /* dipendekin supaya muat */
}

.mini-row td{ padding:1mm 0; }
.mini-row .k{ width:40mm; }
.mini-row .sep{ width:4mm; text-align:center; }
.mini-row .v{ width:25mm; }

.two-col td{ width:50%; vertical-align:top; }

.status-title{
    font-weight:bold;
    text-align:center;
    border:1px solid #333;
    padding:1.5mm 0;
    margin-top:4mm;
}
.status-body{
    border:1px solid #333;
    border-top:none;
    padding:3mm;
    text-align:center;
    font-weight:bold;
}

.box-title{
    font-weight:bold;
    text-align:center;
    border:1px solid #333;
    padding:1.5mm 0;
    margin-top:4mm;
}
.box-body{
    border:1px solid #333;
    border-top:none;
    min-height:14mm;  /* dirapetin */
}

.sign-tbl td{
    width:50%;
    text-align:center;
    vertical-align:top;
}
.spacer{ height:16mm; }
.name{ font-weight:bold; text-decoration:underline; }

/* ====== TAMBAHAN: tempat & tanggal pengambilan rapor (posisi di atas ttd wali kelas) ====== */
.ambil-rapor{
    width:50%;              /* sejajar dengan kolom kanan (wali kelas) */
    margin-left:50%;        /* geser ke kanan */
    text-align:center;      /* center di atas "Wali Kelas" */
    margin-top:2mm;
    margin-bottom:1mm;
}

/* ================= FOOTER ================= */
.footer{
    position:fixed;
    bottom:8mm;
    left:12mm;
    right:12mm;
    font-style:italic;
    font-weight:bold;
    font-size:10.5pt;
}
.footer .line{
    border-top:1px solid #333;
    margin-bottom:2mm;
}
.footer .row{
    display:flex;
    justify-content:space-between;
}
</style>
</head>

@php
$fase ='D';
$kelas = $siswa->kelas ?? null;
$semesterLabel = (($semester ?? 'Ganjil') === 'Genap') ? '2 (Genap)' : '1 (Ganjil)';
$tapel = $tahun->tahun_pelajaran ?? '-';

$tingkat = (int)($kelas->tingkat ?? 7);

$wali = $kelas?->wali?->pengguna;
$namaOrtu = $siswa->nama_ayah ?? '-';

$kepsekNama = $sekolah->kepala_sekolah ?? '-';
$kepsekNip  = $sekolah->nip_kepala_sekolah ?? '-';

/* ==== STATUS KENAIKAN ==== */
if(($semester ?? 'Ganjil') === 'Genap'){
    if($tingkat == 9){
        $labelStatusAkhir = "Kelulusan";
        $statusAkhir = "Berdasarkan hasil pembelajaran yang dicapai peserta didik ditetapkan LULUS.";
    }else{
        $labelStatusAkhir = "Kenaikan Kelas";
        $naikKe = $tingkat + 1;
        $statusAkhir = "Berdasarkan hasil pembelajaran yang dicapai peserta didik ditetapkan naik ke kelas {$naikKe}.";
    }
}
@endphp

<body>

<div class="page">

<table class="idtbl">
<tr>
<td class="label">Nama</td><td class="sep">:</td><td class="val">{{ $siswa->nama_siswa }}</td>
<td class="label">Kelas</td><td class="sep">:</td><td class="val">{{ $kelas?->nama_kelas }}</td>
</tr>
<tr>
<td class="label">NIS/NISN</td><td class="sep">:</td><td class="val">{{ $siswa->nis }} / {{ $siswa->nisn }}</td>
<td class="label">Fase</td><td class="sep">:</td><td class="val">{{ $fase }}</td>
</tr>
<tr>
<td class="label">Nama Sekolah</td><td class="sep">:</td><td class="val">{{ $sekolah->nama_sekolah }}</td>
<td class="label">Semester</td><td class="sep">:</td><td class="val">{{ $semesterLabel }}</td>
</tr>
<tr>
<td class="label">Alamat</td><td class="sep">:</td><td class="val">{{ $sekolah->alamat }}</td>
<td class="label">Tahun Pelajaran</td><td class="sep">:</td><td class="val">{{ $tapel }}</td>
</tr>
</table>

<div class="header-line"></div>

<div class="title">LAPORAN HASIL BELAJAR</div>

<table class="tbl">
<thead>
<tr>
<th class="w-no">No</th>
<th class="w-mapel">Mata Pelajaran</th>
<th class="w-nilai">Nilai Akhir</th>
<th>Capaian Kompetensi</th>
</tr>
</thead>
<tbody>

{{-- ================= MATA PELAJARAN UMUM ================= --}}
<tr class="section-row">
    <td colspan="4">Mata Pelajaran Umum</td>
</tr>

@php $no=1; @endphp
@foreach(($mapelUmum ?? []) as $m)
<tr>
    <td class="w-no">{{ $no++ }}</td>
    <td class="w-mapel">{{ $m['nama'] }}</td>
    <td class="w-nilai">{{ $m['nilai'] ?? '-' }}</td>
    <td>{{ $m['capaian'] ?? '-' }}</td>
</tr>
@endforeach


{{-- ================= MATA PELAJARAN PILIHAN ================= --}}
@if(count($mapelPilihan ?? []) > 0)
<tr class="section-row">
    <td colspan="4">Mata Pelajaran Pilihan</td>
</tr>

@php $no=1; @endphp
@foreach($mapelPilihan as $m)
<tr>
    <td class="w-no">{{ $no++ }}</td>
    <td class="w-mapel">{{ $m['nama'] }}</td>
    <td class="w-nilai">{{ $m['nilai'] ?? '-' }}</td>
    <td>{{ $m['capaian'] ?? '-' }}</td>
</tr>
@endforeach
@endif

</tbody>
</table>

<div class="footer">
<div class="line"></div>
<div class="row">
<div>{{ $kelas?->nama_kelas }} | {{ $siswa->nama_siswa }} | {{ $siswa->nis }}</div>
<div>Halaman 1</div>
</div>
</div>

</div>


{{-- ================= PAGE 2 ================= --}}
<div class="page">

<table class="idtbl">
<tr>
<td class="label">Nama</td><td class="sep">:</td><td class="val">{{ $siswa->nama_siswa }}</td>
<td class="label">Kelas</td><td class="sep">:</td><td class="val">{{ $kelas?->nama_kelas }}</td>
</tr>
<tr>
<td class="label">NIS/NISN</td><td class="sep">:</td><td class="val">{{ $siswa->nis }} / {{ $siswa->nisn }}</td>
<td class="label">Fase</td><td class="sep">:</td><td class="val">{{ $fase }}</td>
</tr>
<tr>
<td class="label">Nama Sekolah</td><td class="sep">:</td><td class="val">{{ $sekolah->nama_sekolah }}</td>
<td class="label">Semester</td><td class="sep">:</td><td class="val">{{ $semesterLabel }}</td>
</tr>
<tr>
<td class="label">Alamat</td><td class="sep">:</td><td class="val">{{ $sekolah->alamat }}</td>
<td class="label">Tahun Pelajaran</td><td class="sep">:</td><td class="val">{{ $tapel }}</td>
</tr>
</table>

<div class="header-line"></div>

{{-- Kokurikuler jadi tabel --}}
<table class="tbl">
<tr class="section-row"><td colspan="4">Kokurikuler</td></tr>
<tr>
<td colspan="4">{{ $kokurikulerText ?? '-' }}</td>
</tr>
</table>

<br>

<table class="tbl">
<thead>
<tr>
<th style="width:10mm;">No</th>
<th>Ekstrakurikuler</th>
<th style="width:25mm;">Predikat</th>
<th>Keterangan</th>
</tr>
</thead>
<tbody>
@forelse(($ekskul ?? []) as $i=>$e)
<tr>
<td style="text-align:center;">{{ $i+1 }}</td>
<td>{{ $e->ekskul->nama_ekskul ?? '-' }}</td>
<td style="text-align:center;">{{ $e->predikat ?? '-' }}</td>
<td>{{ $e->deskripsi ?? '-' }}</td>
</tr>
@empty
<tr><td colspan="4" style="text-align:center;">-</td></tr>
@endforelse
</tbody>
</table>

<table class="two-col" style="margin-top:4mm;">
<tr>
<td style="padding-right:3mm;">
<div class="mini-title">Ketidakhadiran</div>
<div class="mini-box">
<table class="mini-row">
<tr><td class="k">Sakit</td><td class="sep">:</td><td class="v">{{ $absensi->sakit ?? 0 }} hari</td></tr>
<tr><td class="k">Izin</td><td class="sep">:</td><td class="v">{{ $absensi->izin ?? 0 }} hari</td></tr>
<tr><td class="k">Tanpa Keterangan</td><td class="sep">:</td><td class="v">{{ $absensi->tanpa_keterangan ?? 0 }} hari</td></tr>
</table>
</div>
</td>

<td style="padding-left:3mm;">
<div class="mini-title">Catatan Wali Kelas</div>
<div class="mini-box">
{{ $catatan->catatan ?? '-' }}
</div>
</td>
</tr>
</table>

@if(($semester ?? 'Ganjil') === 'Genap')
<div class="status-title">{{ $labelStatusAkhir }}</div>
<div class="status-body">{{ $statusAkhir }}</div>
@endif

<div class="box-title">Tanggapan Orang Tua/Wali Murid</div>
<div class="box-body"></div>

@php
  // ambil dari tabel tahun pelajaran (yang kamu bilang)
  $tempatAmbil = $tahun->tempat_pembagian_rapor ?? '';
  $tglAmbilRaw = $tahun->tanggal_pembagian_rapor ?? '';

  $tglAmbil = '';
  if($tglAmbilRaw){
    try {
      $tglAmbil = \Carbon\Carbon::parse($tglAmbilRaw)->translatedFormat('d F Y');
    } catch (\Throwable $e) {
      $tglAmbil = $tglAmbilRaw;
    }
  }
@endphp

{{-- POSISI: di atas kolom ttd Wali Kelas, text center --}}
@if($tempatAmbil || $tglAmbil)
  <div class="ambil-rapor">
    {{ $tempatAmbil }}{{ ($tempatAmbil && $tglAmbil) ? ', ' : '' }}{{ $tglAmbil }}
  </div>
@endif

<br>

<table class="sign-tbl">
<tr>
<td>
Orang Tua/Wali
<div class="spacer"></div>
<div class="name">{{ $namaOrtu }}</div>
</td>

<td>
Wali Kelas
<div class="spacer"></div>
<div class="name">{{ $wali?->nama }}</div>
<div>NIP. {{ $wali?->nip }}</div>
</td>
</tr>
</table>

<div style="text-align:center; margin-top:6mm;">
Mengetahui<br>
Kepala {{ $sekolah->nama_sekolah }},
<div class="spacer"></div>
<div class="name">{{ $kepsekNama }}</div>
<div>NIP. {{ $kepsekNip }}</div>
</div>

<div class="footer">
<div class="line"></div>
<div class="row">
<div>{{ $kelas?->nama_kelas }} | {{ $siswa->nama_siswa }} | {{ $siswa->nis }}</div>
<div>Halaman 2</div>
</div>
</div>

</div>

</body>
</html>
