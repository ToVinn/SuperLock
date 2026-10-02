<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Penitipan;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KmController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $tanggal = now()->toDateString();

        $kelasId = $user->role === 'km' ? (int) $user->kelas_id : (int) $request->query('kelas', 0);
        $kelasList = Kelas::orderBy('nama')->get();
        $kmList = User::where('role', 'km')->orderBy('nama')->get(['id', 'nama', 'kelas_id']);
        $kmDefault = $kmList->firstWhere('kelas_id', $kelasId) ?? $kmList->first();
        $rows = collect();
        $namaKelas = $kelasId ? Kelas::find($kelasId)?->nama : null;

        if ($kelasId) {
            $rows = Siswa::where('kelas_id', $kelasId)
                ->orderBy('nama')
                ->with(['penitipan' => fn ($q) => $q->where('tanggal', $tanggal)])
                ->get();
        }

        return view('km', compact('tanggal', 'kelasId', 'kelasList', 'kmList', 'kmDefault', 'rows', 'namaKelas'));
    }

    public function simpan(Request $request)
    {
        $user = $request->user();
        $tanggal = now()->toDateString();
        $kelasId = $user->role === 'km' ? (int) $user->kelas_id : (int) $request->input('kelas', 0);

        $data = $request->validate([
            'kelas' => ['nullable', 'integer'],
            'km_nama' => ['required', 'string', 'max:100'],
            'status' => ['required', 'array'],
            'status.*' => ['in:kumpul,tidak'],
        ]);

        $kmNama = trim($data['km_nama']);
        $jmlKumpul = 0;
        $jmlTidak = 0;

        foreach ($data['status'] as $siswaId => $st) {
            Penitipan::updateOrCreate(
                ['siswa_id' => (int) $siswaId, 'tanggal' => $tanggal],
                [
                    'status' => $st,
                    'jam_kumpul' => $st === 'kumpul' ? now()->format('H:i:s') : null,
                    'km_nama' => $kmNama,
                ],
            );
            if ($st === 'kumpul') $jmlKumpul++; else $jmlTidak++;
        }

        $namaKelas = Kelas::find($kelasId)?->nama ?? 'Tidak Diketahui';
        \App\Models\Aktivitas::catat('Input Pagi', "Menyimpan data pengecekan HP kelas $namaKelas ($jmlKumpul terkumpul, $jmlTidak tidak). Oleh: $kmNama");

        return redirect()->route('km', ['kelas' => $kelasId ?: null])->with('pesan', 'Data pengecekan tersimpan.');
    }
}
