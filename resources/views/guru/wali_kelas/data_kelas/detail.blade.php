@extends('layouts.adminlte')
@section('title','Wali Kelas - Data Siswa')

@section('content')
<div class="container-fluid">

  {{-- HEADER --}}
  <div class="d-flex align-items-center mb-3">
    <a href="{{ route('guru.wali-kelas.data-kelas.index') }}"
       class="btn btn-link p-0 mr-2" title="Kembali">
      <i class="fas fa-arrow-left"></i>
    </a>
    <h4 class="mb-0">Data Siswa</h4>
  </div>

  {{-- INFO KELAS --}}
  <div class="card mb-3">
    <div class="card-body">
      <div class="row">
        <div class="col-md-3 font-weight-bold">Kelas</div>
        <div class="col-md-9">
          : {{ $kelas->nama_kelas }}
        </div>

        <div class="col-md-3 font-weight-bold mt-2">Wali Kelas</div>
        <div class="col-md-9 mt-2">
          : {{ $namaWali ?? '-' }}
        </div>
      </div>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  {{-- TABLE --}}
  <div class="card">

    {{-- Toolbar atas (Tampilkan + Cari + Filter) --}}
    <div class="card-body pb-2">
      <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center" style="gap:10px;">
          <span class="text-muted">Tampilkan</span>
          <select class="form-control form-control-sm" style="width:85px;" disabled>
            <option selected>10</option>
          </select>
          <span class="text-muted">data</span>
        </div>

        <div class="d-flex align-items-center" style="gap:10px;">
          <button type="button"
                  class="btn btn-info btn-sm"
                  data-toggle="modal"
                  data-target="#modalFilter">
            <i class="fas fa-filter"></i> Filter Data
          </button>

          <div style="width:220px;">
            <input type="text"
                   class="form-control form-control-sm"
                   id="searchSiswa"
                   placeholder="Cari...">
          </div>
        </div>
      </div>
    </div>

    {{-- Tabel --}}
    <div class="card-body pt-0 table-responsive p-0">
      <table class="table table-bordered table-sm mb-0" id="tableSiswa">
        <thead class="bg-dark text-white">
          <tr>
            <th style="width:60px;">No.</th>
            <th style="width:180px;">Nama</th>
            <th style="width:180px;">Kelas</th>
            <th style="width:200px;">NIS/NISN</th>
            <th style="width:90px;">L/P</th>
            <th style="width:130px;">Status Siswa</th>
            <th style="width:190px;">Aksi</th>
          </tr>
        </thead>

        <tbody>
          @php
            $startNo = method_exists($siswa,'firstItem')
                ? ($siswa->firstItem() ?? 1)
                : 1;
          @endphp

          @forelse($siswa as $i => $s)
            @php
              $rawStatus = $s->status_siswa ?? $s->status ?? (isset($s->status_aktif) ? ((int)$s->status_aktif === 1 ? 'AKTIF' : 'NONAKTIF') : 'AKTIF');
              $statusUpper = strtoupper(trim((string)$rawStatus));

              $badgeClass = 'badge-success';
              if (in_array($statusUpper, ['NONAKTIF','TIDAK AKTIF','KELUAR','MUTASI','LULUS'], true)) {
                $badgeClass = 'badge-danger';
              } elseif (!in_array($statusUpper, ['AKTIF'], true)) {
                $badgeClass = 'badge-secondary';
              }

              $jk = strtoupper(trim((string)($s->jenis_kelamin ?? $s->jk ?? '-')));
              $jk = in_array($jk, ['L','P'], true) ? $jk : '-';

              // TTL rapi
              $ttlText = trim(($s->tempat_lahir ?? '-').', '.($s->tanggal_lahir ?? '-'));
              $ttlText = $ttlText === ',' ? '-' : $ttlText;

              $editUrl = route('guru.wali-kelas.data-kelas.siswa.edit', $s->id);
            @endphp

            <tr
              data-nama="{{ strtolower($s->nama_siswa ?? '') }}"
              data-nis="{{ strtolower($s->nis ?? '') }}"
              data-nisn="{{ strtolower($s->nisn ?? '') }}"
              data-jk="{{ $jk }}"
              data-status="{{ $statusUpper }}"
            >
              <td class="text-center align-middle">
                {{ $startNo + $i }}
              </td>

              <td class="align-middle">
                {{ $s->nama_siswa }}
              </td>

              <td class="align-middle">
                {{ $kelas->nama_kelas }}
              </td>

              <td class="align-middle">
                {{ $s->nis ?? '-' }}/{{ $s->nisn ?? '-' }}
              </td>

              <td class="text-center align-middle">
                {{ $jk }}
              </td>

              <td class="align-middle">
                <span class="badge {{ $badgeClass }}">
                  {{ $statusUpper ?: 'AKTIF' }}
                </span>
              </td>

              <td class="align-middle">

                {{-- DETAIL --}}
                <button type="button"
                        class="btn btn-success btn-sm btnDetail"
                        data-toggle="modal"
                        data-target="#modalDetailSiswa"

                        data-editurl="{{ $editUrl }}"

                        data-nama="{{ e($s->nama_siswa ?? '-') }}"
                        data-status="{{ e($statusUpper ?: 'AKTIF') }}"
                        data-kelas="{{ e($kelas->nama_kelas ?? '-') }}"
                        data-nis="{{ e($s->nis ?? '-') }}"
                        data-nisn="{{ e($s->nisn ?? '-') }}"
                        data-ttl="{{ e($ttlText) }}"
                        data-jk="{{ e($jk) }}"
                        data-agama="{{ e($s->agama ?? '-') }}"

                        data-status-keluarga="{{ e($s->status_dalam_keluarga ?? '-') }}"
                        data-anak-ke="{{ e($s->anak_ke ?? '-') }}"

                        data-alamat="{{ e($s->alamat ?? '-') }}"
                        data-telepon="{{ e($s->telepon ?? '-') }}"

                        data-sekolah-asal="{{ e($s->sekolah_asal ?? '-') }}"
                        data-diterima-kelas="{{ e($s->diterima_di_kelas ?? '-') }}"
                        data-diterima-tanggal="{{ e($s->tanggal_diterima ?? '-') }}"

                        data-nama-ayah="{{ e($s->nama_ayah ?? '-') }}"
                        data-pekerjaan-ayah="{{ e($s->pekerjaan_ayah ?? '-') }}"
                        data-nama-ibu="{{ e($s->nama_ibu ?? '-') }}"
                        data-pekerjaan-ibu="{{ e($s->pekerjaan_ibu ?? '-') }}"
                        data-alamat-ortu="{{ e($s->alamat_orang_tua ?? '-') }}"
                        data-telepon-ortu="{{ e($s->telepon_orang_tua ?? '-') }}"

                        data-nama-wali="{{ e($s->nama_wali ?? '-') }}"
                        data-pekerjaan-wali="{{ e($s->pekerjaan_wali ?? '-') }}"
                        data-alamat-wali="{{ e($s->alamat_wali ?? '-') }}"
                        data-telepon-wali="{{ e($s->telepon_wali ?? '-') }}"
                >
                  <i class="fas fa-eye"></i> Detail
                </button>

                {{-- EDIT --}}
                <a href="{{ $editUrl }}"
                   class="btn btn-warning btn-sm">
                  <i class="fas fa-edit"></i> Edit
                </a>

              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7"
                  class="text-center text-muted py-4">
                Tidak ada siswa di kelas ini.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Footer pagination (style mirip gambar) --}}
    @if(method_exists($siswa,'links'))
    <div class="card-footer d-flex justify-content-between align-items-center">
      <div class="text-muted small">
        Menampilkan
        {{ $siswa->firstItem() ?? 0 }} -
        {{ $siswa->lastItem() ?? 0 }}
        dari {{ $siswa->total() }} data
      </div>

      <div class="pagination-wrap">
        {{ $siswa->links() }}
      </div>
    </div>
    @endif

  </div>
</div>


{{-- =========================
     MODAL FILTER
   ========================= --}}
<div class="modal fade" id="modalFilter" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Filter Data</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <div class="form-group">
          <label class="mb-1">Jenis Kelamin</label>
          <select class="form-control" id="filterJk">
            <option value="">Semua</option>
            <option value="L">Laki-laki (L)</option>
            <option value="P">Perempuan (P)</option>
          </select>
        </div>

        <div class="form-group">
          <label class="mb-1">Status Siswa</label>
          <select class="form-control" id="filterStatus">
            <option value="">Semua</option>
            <option value="AKTIF">AKTIF</option>
            <option value="NONAKTIF">NONAKTIF</option>
            <option value="KELUAR">KELUAR</option>
            <option value="MUTASI">MUTASI</option>
            <option value="LULUS">LULUS</option>
          </select>
          <small class="text-muted">Jika status di DB beda penulisan, pilih “Semua”.</small>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" id="btnResetFilter">
          Reset
        </button>
        <button type="button" class="btn btn-primary" id="btnApplyFilter" data-dismiss="modal">
          Terapkan
        </button>
      </div>

    </div>
  </div>
</div>


{{-- ================= MODAL DETAIL (SAMA SEPERTI GAMBAR) ================= --}}
<div class="modal fade" id="modalDetailSiswa" tabindex="-1">
  <div class="modal-dialog modal-md"> {{-- UKURAN PAS SEPERTI GAMBAR --}}
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Detail Siswa</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>

      <div class="modal-body">

        {{-- Avatar --}}
        <div class="text-center mb-3">
          <div class="avatar-circle mb-2">
            <i class="fas fa-user"></i>
          </div>
          <h5 id="dNamaTop" class="mb-0">-</h5>
        </div>

        <div class="detail-scroll">
          <table class="table table-borderless detail-table mb-0">
            <tr><td>Status Siswa</td><td>:</td><td><span id="dStatus" class="badge badge-success">-</span></td></tr>
            <tr><td>Kelas</td><td>:</td><td id="dKelas"></td></tr>
            <tr><td>NIS</td><td>:</td><td id="dNis"></td></tr>
            <tr><td>NISN</td><td>:</td><td id="dNisn"></td></tr>
            <tr><td>Tempat, Tanggal Lahir</td><td>:</td><td id="dTtl"></td></tr>
            <tr><td>Jenis Kelamin</td><td>:</td><td id="dJk"></td></tr>
            <tr><td>Agama</td><td>:</td><td id="dAgama"></td></tr>
            <tr><td>Status Dalam Keluarga</td><td>:</td><td id="dStatusKeluarga"></td></tr>
            <tr><td>Anak Ke</td><td>:</td><td id="dAnakKe"></td></tr>
            <tr><td>Alamat Siswa</td><td>:</td><td id="dAlamat"></td></tr>
            <tr><td>Telepon Siswa</td><td>:</td><td id="dTelepon"></td></tr>
            <tr><td>Sekolah Asal</td><td>:</td><td id="dSekolahAsal"></td></tr>
            <tr><td>Diterima di Kelas</td><td>:</td><td id="dDiterimaKelas"></td></tr>
            <tr><td>Diterima di Tanggal</td><td>:</td><td id="dDiterimaTanggal"></td></tr>
            <tr><td>Nama Ayah</td><td>:</td><td id="dNamaAyah"></td></tr>
            <tr><td>Pekerjaan Ayah</td><td>:</td><td id="dPekerjaanAyah"></td></tr>
            <tr><td>Nama Ibu</td><td>:</td><td id="dNamaIbu"></td></tr>
            <tr><td>Pekerjaan Ibu</td><td>:</td><td id="dPekerjaanIbu"></td></tr>
            <tr><td>Alamat Orang Tua</td><td>:</td><td id="dAlamatOrtu"></td></tr>
            <tr><td>Telepon Orang Tua</td><td>:</td><td id="dTeleponOrtu"></td></tr>
            <tr><td>Nama Wali</td><td>:</td><td id="dNamaWali"></td></tr>
            <tr><td>Pekerjaan Wali</td><td>:</td><td id="dPekerjaanWali"></td></tr>
            <tr><td>Alamat Wali</td><td>:</td><td id="dAlamatWali"></td></tr>
            <tr><td>Telepon Wali</td><td>:</td><td id="dTeleponWali"></td></tr>
          </table>
        </div>

      </div>

      <div class="modal-footer d-flex justify-content-between">
        <button class="btn btn-light" data-dismiss="modal">Batal</button>
        <a href="#" id="btnEditFromModal" class="btn btn-warning px-4">Edit</a>
      </div>

    </div>
  </div>
</div>


@push('styles')
<style>
  /* pagination mirip gambar */
  .pagination-wrap .pagination{ margin:0; }
  .pagination-wrap .page-link{ padding:.35rem .65rem; font-size:.875rem; }
  .pagination-wrap .page-item.active .page-link{ font-weight:700; }

  /* ===== UI MODAL DETAIL SESUAI GAMBAR ===== */
  .detail-top{
    padding: 26px 0 18px;
    border-bottom: 1px solid #e9e9e9;
  }
  .detail-avatar{
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 3px solid #e0e0e0;
    background: #f5f5f5;
    display:flex;
    align-items:center;
    justify-content:center;
    color:#bdbdbd;
    font-size: 44px;
  }
  .detail-name{
    margin-top: 14px;
    font-size: 20px;
    font-weight: 500;
    color: #333;
  }

  .detail-scroll{
    max-height: 60vh;
    overflow-y: auto;
  }

  .detail-table{
    width: 100%;
  }
  .detail-table tr{
    border-bottom: 1px solid #ededed;
  }
  .detail-table td{
    padding: 12px 18px;
    vertical-align: middle;
    font-size: 15px;
    color:#222;
  }
  .detail-table .lbl{
    width: 260px;
    font-weight: 600;
    color:#2b2b2b;
  }
  .detail-table .sep{
    width: 14px;
    color:#777;
  }
  .detail-table tr.hl{
    background: #f1f1f1;
  }
</style>
@endpush

@push('scripts')
<script>
(function () {
  const searchInput = document.getElementById('searchSiswa');
  const table = document.getElementById('tableSiswa');
  const rows = () => table.querySelectorAll('tbody tr');

  const filterJk = document.getElementById('filterJk');
  const filterStatus = document.getElementById('filterStatus');
  const btnApply = document.getElementById('btnApplyFilter');
  const btnReset = document.getElementById('btnResetFilter');

  let activeFilter = { jk: '', status: '' };

  function applyAllFilters() {
    const q = (searchInput.value || '').toLowerCase().trim();

    rows().forEach(tr => {
      const nama = tr.getAttribute('data-nama') || '';
      const nis  = tr.getAttribute('data-nis') || '';
      const nisn = tr.getAttribute('data-nisn') || '';
      const jk   = tr.getAttribute('data-jk') || '';
      const st   = tr.getAttribute('data-status') || '';

      const matchSearch = !q || (nama.includes(q) || nis.includes(q) || nisn.includes(q));
      const matchJk = !activeFilter.jk || activeFilter.jk === jk;
      const matchStatus = !activeFilter.status || activeFilter.status === st;

      tr.style.display = (matchSearch && matchJk && matchStatus) ? '' : 'none';
    });
  }

  searchInput?.addEventListener('input', applyAllFilters);

  btnApply?.addEventListener('click', function() {
    activeFilter.jk = (filterJk.value || '').trim();
    activeFilter.status = (filterStatus.value || '').trim();
    applyAllFilters();
  });

  btnReset?.addEventListener('click', function() {
    filterJk.value = '';
    filterStatus.value = '';
    activeFilter = { jk:'', status:'' };
    applyAllFilters();
  });

  // modal detail populate (lengkap)
  const setText = (id, val) => {
    const el = document.getElementById(id);
    if (el) el.textContent = (val !== undefined && val !== null && String(val).trim() !== '') ? val : '-';
  };

  document.querySelectorAll('.btnDetail').forEach(btn => {
    btn.addEventListener('click', function () {
      const status = (this.dataset.status || 'AKTIF').toUpperCase();
      const badge = document.getElementById('dStatus');

      setText('dNamaTop', this.dataset.nama);

      const editUrl = this.dataset.editurl || '#';
      const btnEdit = document.getElementById('btnEditFromModal');
      if (btnEdit) btnEdit.setAttribute('href', editUrl);

      if (badge) {
        badge.textContent = status;
        badge.classList.remove('badge-success','badge-danger','badge-secondary');

        if (['NONAKTIF','TIDAK AKTIF','KELUAR','MUTASI','LULUS'].includes(status)) {
          badge.classList.add('badge-danger');
        } else if (status === 'AKTIF') {
          badge.classList.add('badge-success');
        } else {
          badge.classList.add('badge-secondary');
        }
      }

      setText('dKelas', this.dataset.kelas);
      setText('dNis', this.dataset.nis);
      setText('dNisn', this.dataset.nisn);
      setText('dTtl', this.dataset.ttl);
      setText('dJk', this.dataset.jk);
      setText('dAgama', this.dataset.agama);

      setText('dStatusKeluarga', this.dataset.statusKeluarga);
      setText('dAnakKe', this.dataset.anakKe);

      setText('dAlamat', this.dataset.alamat);
      setText('dTelepon', this.dataset.telepon);

      setText('dSekolahAsal', this.dataset.sekolahAsal);
      setText('dDiterimaKelas', this.dataset.diterimaKelas);
      setText('dDiterimaTanggal', this.dataset.diterimaTanggal);

      setText('dNamaAyah', this.dataset.namaAyah);
      setText('dPekerjaanAyah', this.dataset.pekerjaanAyah);
      setText('dNamaIbu', this.dataset.namaIbu);
      setText('dPekerjaanIbu', this.dataset.pekerjaanIbu);
      setText('dAlamatOrtu', this.dataset.alamatOrtu);
      setText('dTeleponOrtu', this.dataset.teleponOrtu);

      setText('dNamaWali', this.dataset.namaWali);
      setText('dPekerjaanWali', this.dataset.pekerjaanWali);
      setText('dAlamatWali', this.dataset.alamatWali);
      setText('dTeleponWali', this.dataset.teleponWali);
    });
  });

  applyAllFilters();
})();
</script>
@endpush

@endsection
