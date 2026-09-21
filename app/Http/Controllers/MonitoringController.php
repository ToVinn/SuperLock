<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Penitipan;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MonitoringController extends Controller
{
    public function __invoke(Request $request)
    {
        $tanggal = $request->query('tanggal', now()->toDateString());
        $kelasId = (int) $request->query('kelas', 0);
        $hari = (int) $request->query('hari', 7);
        if (! in_array($hari, [7, 14, 30], true)) {
            $hari = 7;
        }

        $kelasList = Kelas::orderBy('nama')->get();
        $siswaCount = Siswa::selectRaw('kelas_id, COUNT(*) AS c')->groupBy('kelas_id')->pluck('c', 'kelas_id');
        $kmPerKelas = User::where('role', 'km')->pluck('nama', 'kelas_id');
        $totalSiswa = $kelasId ? Siswa::where('kelas_id', $kelasId)->count() : Siswa::count();

        $hariIni = Penitipan::join('siswa', 'siswa.id', '=', 'penitipan.siswa_id')
            ->where('penitipan.tanggal', $tanggal)
            ->when($kelasId, fn ($q) => $q->where('siswa.kelas_id', $kelasId))
            ->get(['penitipan.status', 'penitipan.jam_kumpul', 'penitipan.jam_ambil']);

        $kumpul = $hariIni->where('status', 'kumpul')->count();
        $pinjam = $hariIni->where('status', 'pinjam')->count();
        $tidak = $hariIni->where('status', 'tidak')->count();
        $menitip = $kumpul + $pinjam;
        $sudahAmbil = $hariIni->whereNotNull('jam_ambil')->count();
        $pctMenitip = $totalSiswa ? (int) round($menitip / $totalSiswa * 100) : 0;
        $pctAmbil = $menitip ? (int) round($sudahAmbil / $menitip * 100) : 0;
        $pctTidak = $totalSiswa ? (int) round($tidak / $totalSiswa * 100) : 0;

        $firstKumpul = $hariIni->whereNotNull('jam_kumpul')->min('jam_kumpul');
        $firstAmbil = $hariIni->whereNotNull('jam_ambil')->min('jam_ambil');

        $mulai = Carbon::parse($tanggal)->subDays($hari - 1);
        $trendRaw = Penitipan::join('siswa', 'siswa.id', '=', 'penitipan.siswa_id')
            ->whereBetween('penitipan.tanggal', [$mulai->toDateString(), $tanggal])
            ->when($kelasId, fn ($q) => $q->where('siswa.kelas_id', $kelasId))
            ->get(['penitipan.tanggal', 'penitipan.status', 'penitipan.jam_ambil']);

        $trend = [];
        for ($i = $hari - 1; $i >= 0; $i--) {
            $d = Carbon::parse($tanggal)->subDays($i);
            $rows = $trendRaw->filter(fn ($r) => $r->tanggal->eq($d));
            $k = $rows->whereIn('status', ['kumpul', 'pinjam'])->count();
            $ambil = $rows->whereNotNull('jam_ambil')->count();
            $trend[] = [
                'label' => $d->format('j/n'),
                'kumpul' => $k,
                'pct' => $k ? (int) round($ambil / $k * 100) : 0,
            ];
        }

        $menitipPerKelas = Penitipan::join('siswa', 'siswa.id', '=', 'penitipan.siswa_id')
            ->where('penitipan.tanggal', $tanggal)
            ->whereIn('penitipan.status', ['kumpul', 'pinjam'])
            ->selectRaw('siswa.kelas_id, COUNT(*) AS j')
            ->groupBy('siswa.kelas_id')->pluck('j', 'kelas_id');

        $tabel = Penitipan::join('siswa', 'siswa.id', '=', 'penitipan.siswa_id')
            ->join('kelas', 'kelas.id', '=', 'siswa.kelas_id')
            ->where('penitipan.tanggal', $tanggal)
            ->whereIn('penitipan.status', ['kumpul', 'pinjam'])
            ->when($kelasId, fn ($q) => $q->where('siswa.kelas_id', $kelasId))
            ->orderByRaw('penitipan.jam_ambil IS NOT NULL')
            ->orderByRaw("penitipan.status = 'pinjam' DESC")
            ->orderBy('siswa.nama')
            ->limit(6)
            ->get(['siswa.nama', 'kelas.nama AS kelas', 'penitipan.jam_kumpul', 'penitipan.jam_ambil', 'penitipan.status']);

        $siswaRows = collect();
        if ($kelasId) {
            $siswaRows = Siswa::where('kelas_id', $kelasId)
                ->orderBy('nama')
                ->with(['penitipan' => fn ($q) => $q->where('tanggal', $tanggal)])
                ->get();
        }
        $namaKelas = $kelasList->firstWhere('id', $kelasId)?->nama;

        return view('monitoring', compact(
            'tanggal', 'kelasId', 'hari', 'kelasList', 'siswaCount', 'kmPerKelas',
            'totalSiswa', 'kumpul', 'pinjam', 'tidak', 'menitip', 'sudahAmbil',
            'pctMenitip', 'pctAmbil', 'pctTidak', 'firstKumpul', 'firstAmbil',
            'trend', 'menitipPerKelas', 'tabel', 'siswaRows', 'namaKelas',
        ));
    }
}
