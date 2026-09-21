<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RekapController extends Controller
{
    public function __invoke(Request $request)
    {
        $tanggal = $request->query('tanggal', now()->toDateString());

        $rows = DB::table('siswa')
            ->join('kelas', 'kelas.id', '=', 'siswa.kelas_id')
            ->leftJoin('penitipan', function ($join) use ($tanggal) {
                $join->on('penitipan.siswa_id', '=', 'siswa.id')
                    ->where('penitipan.tanggal', '=', $tanggal);
            })
            ->selectRaw("kelas.nama AS kelas, COALESCE(penitipan.status,'belum') AS status, COUNT(*) AS jumlah,
                SUM(CASE WHEN penitipan.jam_ambil IS NOT NULL THEN 1 ELSE 0 END) AS sudah_ambil")
            ->groupBy('kelas.id', 'kelas.nama', DB::raw("COALESCE(penitipan.status,'belum')"))
            ->orderBy('kelas.nama')
            ->get();

        $rekap = [];
        $adaData = false;
        foreach ($rows as $r) {
            if ($r->status !== 'belum') {
                $adaData = true;
            }
            $rekap[$r->kelas][$r->status] = (int) $r->jumlah;
            if ($r->status === 'kumpul') {
                $rekap[$r->kelas]['ambil'] = (int) $r->sudah_ambil;
            }
        }

        return view('rekap', compact('tanggal', 'rekap', 'adaData'));
    }
}
