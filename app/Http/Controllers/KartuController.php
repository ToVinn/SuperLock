<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class KartuController extends Controller
{
    public function __invoke(Request $request)
    {
        $kelasId = (int) $request->query('kelas', 0);
        $kelas = Kelas::orderBy('nama')->get();
        $rows = collect();
        $kmRows = collect();
        $namaKelas = null;

        if ($kelasId) {
            $namaKelas = Kelas::find($kelasId)?->nama;
            $rows = Siswa::where('kelas_id', $kelasId)->orderBy('nama')->get(['nis', 'nama']);
            $kmRows = User::where('role', 'km')->where('kelas_id', $kelasId)->orderBy('nama')->get(['id', 'nama']);
        }

        return view('kartu', compact('kelasId', 'kelas', 'rows', 'kmRows', 'namaKelas'));
    }
}
