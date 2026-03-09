<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Peran;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BkHubinAdminSettingController extends Controller
{
    public function index(): View
    {
        $guruUsers = User::query()
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('data_guru')
                    ->whereColumn('data_guru.pengguna_id', 'pengguna.id');
            })
            ->orderBy('nama')
            ->get(['id', 'nama', 'email']);

        $bkRoleId = Peran::query()->where('nama_peran', 'bk')->value('id');
        $hubinRoleId = Peran::query()->where('nama_peran', 'pembimbing_pkl')->value('id');

        $selectedBkAdminId = $bkRoleId
            ? DB::table('pengguna_peran')
                ->where('peran_id', $bkRoleId)
                ->orderByDesc('id')
                ->value('pengguna_id')
            : null;

        $selectedHubinAdminId = $hubinRoleId
            ? DB::table('pengguna_peran')
                ->where('peran_id', $hubinRoleId)
                ->orderByDesc('id')
                ->value('pengguna_id')
            : null;

        return view('admin.bk_hubin.settings', [
            'guruUsers' => $guruUsers,
            'selectedBkAdminId' => $selectedBkAdminId,
            'selectedHubinAdminId' => $selectedHubinAdminId,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bk_admin_user_id' => 'required|integer|exists:pengguna,id',
            'hubin_admin_user_id' => 'required|integer|exists:pengguna,id',
        ]);

        $guruIds = User::query()
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('data_guru')
                    ->whereColumn('data_guru.pengguna_id', 'pengguna.id');
            })
            ->pluck('id')
            ->all();

        abort_unless(
            in_array((int) $validated['bk_admin_user_id'], $guruIds, true)
            && in_array((int) $validated['hubin_admin_user_id'], $guruIds, true),
            422,
            'User yang dipilih harus berasal dari data guru.'
        );

        DB::transaction(function () use ($validated, $guruIds) {
            $bkRoleId = Peran::query()->firstOrCreate(
                ['nama_peran' => 'bk'],
                ['created_at' => now(), 'updated_at' => now()]
            )->id;

            $hubinRoleId = Peran::query()->firstOrCreate(
                ['nama_peran' => 'pembimbing_pkl'],
                ['created_at' => now(), 'updated_at' => now()]
            )->id;

            DB::table('pengguna_peran')
                ->where('peran_id', $bkRoleId)
                ->whereIn('pengguna_id', $guruIds)
                ->delete();

            DB::table('pengguna_peran')
                ->where('peran_id', $hubinRoleId)
                ->whereIn('pengguna_id', $guruIds)
                ->delete();

            DB::table('pengguna_peran')->updateOrInsert(
                [
                    'pengguna_id' => (int) $validated['bk_admin_user_id'],
                    'peran_id' => $bkRoleId,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::table('pengguna_peran')->updateOrInsert(
                [
                    'pengguna_id' => (int) $validated['hubin_admin_user_id'],
                    'peran_id' => $hubinRoleId,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        });

        return redirect()
            ->route('admin.bk-hubin-settings.index')
            ->with('success', 'Penanggung jawab BK dan Hubin berhasil diperbarui.');
    }
}
