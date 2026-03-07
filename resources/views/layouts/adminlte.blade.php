<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title','SIMAKA')</title>

  <meta name="csrf-token" content="{{ csrf_token() }}">

  {{-- FONT AWESOME --}}
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/fontawesome-free/css/all.min.css') }}">
  <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.min.css') }}">

  {{-- DATATABLES --}}
<link rel="stylesheet" href="{{ asset('adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
  <style>
    :root {
      --simaka-primary: #1f4e79;
      --simaka-muted-bg: #f4f6f9;
      --simaka-card-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08);
      --simaka-border: #dee2e6;
      --simaka-head-bg: #e9ecef;
      --simaka-head-fg: #2f3542;
    }

    body {
      background-color: var(--simaka-muted-bg);
    }

    .content-header h1 {
      font-size: 1.4rem;
      font-weight: 700;
      color: #2c3e50;
      margin-bottom: 0.25rem;
    }

    .content .card {
      border: 0;
      border-top: 3px solid var(--simaka-primary);
      border-radius: 0.5rem;
      box-shadow: var(--simaka-card-shadow);
      overflow: hidden;
    }

    .content .card > .card-header {
      background-color: #fff;
      border-bottom: 1px solid var(--simaka-border);
      padding: 0.85rem 1rem;
    }

    .content .card > .card-header .card-title {
      font-size: 1rem;
      font-weight: 700;
      margin: 0;
      color: #1f2937;
    }

    .content .card.card-dark,
    .content .card.card-primary,
    .content .card.card-secondary {
      border-top-color: var(--simaka-primary);
    }

    .content .card.card-dark > .card-header,
    .content .card.card-primary > .card-header,
    .content .card.card-secondary > .card-header,
    .content .card > .card-header.bg-primary,
    .content .card > .card-header.bg-info,
    .content .card > .card-header.bg-warning,
    .content .card > .card-header.bg-secondary,
    .content .card > .card-header.bg-dark {
      background: #fff !important;
      color: #1f2937 !important;
      border-bottom: 1px solid var(--simaka-border) !important;
    }

    .content .card > .card-header .btn,
    .content .card > .card-header .btn i {
      color: inherit;
    }

    .content .card > .card-body {
      padding: 1rem;
    }

    .content .card > .card-footer {
      background: #fff;
      border-top: 1px solid var(--simaka-border);
      padding: 0.85rem 1rem;
    }

    .content .table {
      margin-bottom: 0;
      font-size: 0.92rem;
    }

    .content .table thead th {
      background: var(--simaka-head-bg);
      color: var(--simaka-head-fg);
      border-color: var(--simaka-border);
      vertical-align: middle;
      font-weight: 700;
    }

    .content .table td {
      vertical-align: middle;
    }

    .content .table thead.bg-secondary th,
    .content .table thead.bg-dark th {
      background: var(--simaka-head-bg) !important;
      color: var(--simaka-head-fg) !important;
    }

    .content .card .table-responsive {
      border-radius: 0.45rem;
      border: 1px solid var(--simaka-border);
    }

    .content .card .card-body.table-responsive,
    .content .card .card-body.table-responsive.p-0,
    .content .card .card-body.pt-0.table-responsive,
    .content .card .card-body.pt-0.table-responsive.p-0 {
      padding: 0 !important;
    }

    .content .card .card-body.table-responsive .table,
    .content .card .card-body.table-responsive.p-0 .table {
      margin-bottom: 0;
    }

    .content .alert {
      border: 0;
      border-radius: 0.45rem;
      box-shadow: 0 0.25rem 0.7rem rgba(0, 0, 0, 0.06);
    }

    .content .custom-select,
    .content .form-control {
      border-color: #ced4da;
    }

    .content .btn.btn-light {
      border-color: #d4d8dd;
    }

    .content .btn-xs {
      padding: 0.2rem 0.45rem;
      font-size: 0.72rem;
      line-height: 1.2;
    }

    .content .badge {
      font-size: 0.72rem;
      padding: 0.45em 0.55em;
      letter-spacing: 0.02em;
    }

    .content .pagination {
      margin-bottom: 0;
    }

    .content .modal .modal-header {
      border-bottom: 1px solid var(--simaka-border);
    }

    .content .modal .modal-footer {
      border-top: 1px solid var(--simaka-border);
    }

    .content .dataTables_wrapper .dataTables_filter input,
    .content .dataTables_wrapper .dataTables_length select {
      border: 1px solid #ced4da;
      border-radius: 0.2rem;
      padding: 0.25rem 0.45rem;
    }

    .content .dataTables_wrapper .dataTables_info,
    .content .dataTables_wrapper .dataTables_paginate {
      margin-top: 0.75rem;
    }

    @media (max-width: 767.98px) {
      .content .card > .card-body {
        padding: 0.85rem;
      }

      .content .card > .card-header,
      .content .card > .card-footer {
        padding: 0.75rem 0.85rem;
      }
    }
  </style>
  @stack('styles')
</head>

<body class="hold-transition sidebar-mini">
<div class="wrapper">

  {{-- NAVBAR --}}
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#">
          <i class="fas fa-bars"></i>
        </a>
      </li>
    </ul>

    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button class="btn btn-link nav-link">Logout</button>
        </form>
      </li>
    </ul>
  </nav>

  {{-- SIDEBAR --}}
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="#" class="brand-link text-center">
      <span class="brand-text font-weight-light">S I M A K A</span>
    </a>

    <div class="sidebar">
      @php
        $user = auth()->user();
        $role = $user?->peran?->nama_peran;

        // ====== ROLE DINAMIS (SAMAKAN POLA WALI KELAS) ======
        $isWali = $user
            ? \App\Models\DataKelas::where('wali_kelas_id', $user->id)->exists()
            : false;

        $isKoordinator = $user
            ? \App\Models\KkKelompok::where('koordinator_id', $user->id)->exists()
            : false;

        $isPembina = false;

        if ($user) {

            $guruRow = \App\Models\DataGuru::where('pengguna_id', $user->id)->first();
            $guruId  = $guruRow?->id;

            $isPembinaByUserId = \App\Models\DataEkstrakurikuler::where('pembina_id', $user->id)->exists();

            $isPembinaByGuruId = $guruId
                ? \App\Models\DataEkstrakurikuler::where('pembina_id', $guruId)->exists()
                : false;

            $isPembina = $isPembinaByUserId || $isPembinaByGuruId;
        }

      @endphp

      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">

          {{-- ================= ADMIN ================= --}}
          @if($role === 'admin')

          {{-- DASHBOARD --}}
          <li class="nav-item">
            <a href="{{ route('admin.dashboard') }}" class="nav-link">
              <i class="nav-icon fas fa-home"></i>
              <p>Dashboard</p>
            </a>
          </li>

          {{-- PENGGUNA --}}
          <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-users"></i>
              <p>
                Pengguna
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ route('admin.siswa.index') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Data Siswa</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('admin.guru.index') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Data Guru</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('admin.admin.index') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Data Admin</p>
                </a>
              </li>
            </ul>
          </li>

          {{-- ADMINISTRASI --}}
          <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-cogs"></i>
              <p>
                Administrasi
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ route('admin.sekolah.index') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Data Sekolah</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('admin.tahun.index') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Tahun Pelajaran</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('admin.jurusan.index') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Data Jurusan</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('admin.kelas.index') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Data Kelas</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('admin.mapel.index') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Data Mapel</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('admin.pembelajaran.index') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Data Pembelajaran</p>
                </a>
              </li>
               <li class="nav-item">
                <a href="{{ route('admin.absensi.jadwal') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Jadwal Pelajaran</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('admin.hari-libur.index') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Data Hari Libur</p>
                </a>
              </li>
            </ul>
          </li>

          {{-- EKSTRAKURIKULER --}}
          <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-futbol"></i>
              <p>
                Ekstrakurikuler
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ route('admin.ekstrakurikuler.index') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Data Ekstrakurikuler</p>
                </a>
              </li>
            </ul>
          </li>

          {{-- ABSENSI --}}
          <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-book"></i>
              <p>
                Absensi
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ route('admin.absensi.index') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Absensi</p>
                </a>
              </li>
            </ul>
          </li>

          {{-- BK --}}
          <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-user-shield"></i>
              <p>
                BK
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ route('admin.bk.index') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Catatan BK</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('admin.bk.sikap.index') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Sikap Siswa</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('admin.bk.pelanggaran.index') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Daftar Pelanggaran</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('admin.bk.pembinaan.index') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Laporan Pembinaan</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('admin.bk.home-visit.index') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Laporan Home Visit</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('admin.bk.pengunduran-diri.index') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Pengunduran Diri</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('admin.bk.perjanjian-siswa.index') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Perjanjian Siswa</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('admin.bk.peminatan.index') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Peminatan Siswa</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('admin.bk.absensi-bulanan.index') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Absensi Bulanan</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('admin.bk.pemanggilan-ortu.index') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Pemanggilan Ortu</p>
                </a>
              </li>
            </ul>
          </li>

          {{-- KOKURIKULER --}}
          <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-layer-group"></i>
              <p>
                Kokurikuler
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ route('admin.kokurikuler.dimensi.index') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Dimensi Profil</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('admin.kokurikuler.kegiatan.index') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Data Kegiatan</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('admin.kokurikuler.kelompok.index') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Kelompok Kokurikuler</p>
                </a>
              </li>
            </ul>
          </li>

          {{-- RAPOR --}}
          <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-file-alt"></i>
              <p>
                Rapor
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ route('admin.rapor.leger') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Leger Nilai</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('admin.rapor.cetak') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Cetak Rapor</p>
                </a>
              </li>
            </ul>
          </li>

          @endif
          {{-- =============== END ADMIN =============== --}}

          {{-- ================= BK ================= --}}
          @if($role === 'bk')
          <li class="nav-item">
            <a href="{{ route('bk.dashboard') }}" class="nav-link">
              <i class="nav-icon fas fa-home"></i>
              <p>Dashboard BK</p>
            </a>
          </li>

          <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-user-shield"></i>
              <p>
                Modul BK
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ route('bk.data-bk.index') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Catatan BK</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('bk.sikap.index') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Sikap Siswa</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('bk.pelanggaran.index') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Daftar Pelanggaran</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('bk.pembinaan.index') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Laporan Pembinaan</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('bk.home-visit.index') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Laporan Home Visit</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('bk.pengunduran-diri.index') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Pengunduran Diri</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('bk.perjanjian-siswa.index') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Perjanjian Siswa</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('bk.peminatan.index') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Peminatan Siswa</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('bk.absensi-bulanan.index') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Absensi Bulanan</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('bk.pemanggilan-ortu.index') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Pemanggilan Ortu</p>
                </a>
              </li>
            </ul>
          </li>
          @endif
          {{-- =============== END BK =============== --}}


          {{-- ================= GURU ================= --}}
          @if($role === 'guru_mapel')

          {{-- DASHBOARD GURU --}}
          <li class="nav-item">
            <a href="{{ route('guru.dashboard') }}" class="nav-link">
              <i class="nav-icon fas fa-home"></i>
              <p>Dashboard</p>
            </a>
          </li>

          {{-- GURU MAPEL (WAJIB) --}}
          <li class="nav-item">
            <a href="{{ route('guru.pembelajaran.index') }}" class="nav-link">
              <i class="nav-icon fas fa-book"></i>
              <p>Guru Mapel</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="{{ route('guru.absensi.index') }}" class="nav-link">
              <i class="nav-icon fas fa-clipboard-check"></i>
              <p>Absensi Mapel</p>
            </a>
          </li>

          {{-- KOKURIKULER --}}
          @if($isKoordinator)
          <li class="nav-item">
            <a href="{{ route('guru.kokurikuler.index') }}" class="nav-link">
              <i class="nav-icon fas fa-layer-group"></i>
              <p>Kokurikuler</p>
            </a>
          </li>
          @endif

          {{-- PEMBINA EKSKUL --}}
          @if($isPembina)
          <li class="nav-item">
            <a href="{{ route('guru.ekskul.index') }}" class="nav-link">
              <i class="nav-icon fas fa-futbol"></i>
              <p>Pembina Ekskul</p>
            </a>
          </li>
          @endif


          {{-- ===================== WALI KELAS (REVISI MENU) ===================== --}}
          @if($user && ($user->hasRole('wali_kelas') || $isWali))
          <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-user-tie"></i>
              <p>
                Wali Kelas
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>

            <ul class="nav nav-treeview">

              {{-- SUBMENU 1: DATA-DATA --}}
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>
                    Data-data
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>

                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{ route('guru.wali-kelas.data-kelas.index') }}" class="nav-link">
                      <i class="far fa-dot-circle nav-icon"></i>
                      <p>Data Kelas</p>
                    </a>
                  </li>

                  <li class="nav-item">
                    <a href="{{ route('guru.wali-kelas.absensi.index') }}" class="nav-link">
                      <i class="far fa-dot-circle nav-icon"></i>
                      <p>Absensi</p>
                    </a>
                  </li>

                  <li class="nav-item">
                    <a href="{{ route('guru.wali-kelas.catatan.index') }}" class="nav-link">
                      <i class="far fa-dot-circle nav-icon"></i>
                      <p>Catatan Wali Kelas</p>
                    </a>
                  </li>

                  <li class="nav-item">
                    <a href="{{ route('guru.wali-kelas.bk.index') }}" class="nav-link">
                      <i class="far fa-dot-circle nav-icon"></i>
                      <p>BK</p>
                    </a>
                  </li>
                </ul>
              </li>

              {{-- SUBMENU 2: RAPOR --}}
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>
                    Rapor
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>

                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{ route('guru.wali-kelas.rapor.leger.index') }}" class="nav-link">
                      <i class="far fa-dot-circle nav-icon"></i>
                      <p>Leger Nilai</p>
                    </a>
                  </li>

                  <li class="nav-item">
                    <a href="{{ route('guru.wali-kelas.rapor.cetak.index') }}" class="nav-link">
                      <i class="far fa-dot-circle nav-icon"></i>
                      <p>Cetak Rapor</p>
                    </a>
                  </li>
                </ul>
              </li>

            </ul>
          </li>
          @endif
          {{-- =================== END WALI KELAS (REVISI) =================== --}}

          @endif
          {{-- =============== END GURU =============== --}}

        </ul>
      </nav>
    </div>
  </aside>

  {{-- CONTENT --}}
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <h1>@yield('page_title')</h1>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        @yield('content')
      </div>
    </section>
  </div>

  <footer class="main-footer text-center">
    <strong>© {{ date('Y') }} SIMAKA | SMK PK Budi Perkasa</strong>
  </footer>

</div>

<script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('adminlte/dist/js/adminlte.min.js') }}"></script>

{{-- DATATABLES --}}
<script src="{{ asset('adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

<script>
/**
 * GLOBAL INIT DATATABLES (LEBIH AMAN)
 * - Hanya tabel dengan class .datatable yang akan di-init
 * - Skip jika tabel ada attribute data-no-datatable="true"
 */
$(function () {
  $('.datatable').each(function () {
    if ($(this).data('no-datatable') === true) return;

    $(this).DataTable({
      responsive: true,
      autoWidth: false,
      ordering: false,
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100],
      language: {
        search: "Cari:",
        lengthMenu: "Tampilkan _MENU_ data",
        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
        infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
        zeroRecords: "Data tidak ditemukan",
        paginate: {
          first: "Awal",
          last: "Akhir",
          next: "Berikutnya",
          previous: "Sebelumnya"
        }
      }
    });
  });
});
</script>

@stack('scripts')
</body>
</html>
