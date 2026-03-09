@extends('layouts.adminlte')

@section('page_title', 'Pengaturan Admin BK & Hubin')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Penugasan Penanggung Jawab</h3>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 pl-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.bk-hubin-settings.update') }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="bk_admin_user_id">Admin Modul BK (User Guru)</label>
                <select name="bk_admin_user_id" id="bk_admin_user_id" class="form-control" required>
                    <option value="">-- Pilih Guru --</option>
                    @foreach($guruUsers as $guru)
                        <option
                            value="{{ $guru->id }}"
                            @selected((string) old('bk_admin_user_id', $selectedBkAdminId) === (string) $guru->id)
                        >
                            {{ $guru->nama }} ({{ $guru->email }})
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">
                    User terpilih akan diberi role tambahan <code>bk</code>.
                </small>
            </div>

            <div class="form-group">
                <label for="hubin_admin_user_id">Admin Modul Hubin (User Guru)</label>
                <select name="hubin_admin_user_id" id="hubin_admin_user_id" class="form-control" required>
                    <option value="">-- Pilih Guru --</option>
                    @foreach($guruUsers as $guru)
                        <option
                            value="{{ $guru->id }}"
                            @selected((string) old('hubin_admin_user_id', $selectedHubinAdminId) === (string) $guru->id)
                        >
                            {{ $guru->nama }} ({{ $guru->email }})
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">
                    User terpilih akan diberi role tambahan <code>pembimbing_pkl</code>.
                </small>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Simpan Pengaturan
            </button>
        </form>
    </div>
</div>
@endsection
