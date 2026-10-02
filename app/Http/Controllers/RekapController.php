<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use Illuminate\Http\Request;

class RekapController extends Controller
{
    public function __invoke(Request $request)
    {
        $tanggal = $request->query('tanggal', now()->toDateString());

        $kelasData = Kelas::with(['siswa' => function ($q) {
            $q->orderBy('nama');
        }, 'siswa.penitipan' => function ($q) use ($tanggal) {
            $q->where('tanggal', $tanggal);
        }])->orderBy('nama')->get();

        $rekap = [];
        $rincian = [];
        $adaData = false;

        foreach ($kelasData as $k) {
            $stat = ['kumpul' => 0, 'pinjam' => 0, 'tidak' => 0, 'belum' => 0, 'ambil' => 0];
            $listSiswa = [];

            foreach ($k->siswa as $s) {
                $p = $s->penitipan->first();
                $st = $p->status ?? 'belum';
                
                $stat[$st]++;
                if ($st !== 'belum') {
                    $adaData = true;
                }
                
                if ($st === 'kumpul' && $p && $p->jam_ambil) {
                    $stat['ambil']++;
                }

                if ($st !== 'belum') {
                    $listSiswa[] = [
                        'nama' => $s->nama,
                        'nis' => $s->nis,
                        'status' => $st,
                        'penitipan' => $p
                    ];
                }
            }

            // Tetap tampilkan rekap walau kelas kosong penitipan (status belum semua)
            // agar tabel progres per kelas tetap muncul utuh seperti sebelumnya
            $rekap[$k->nama] = $stat;
            // Tampilkan rincian hanya jika ada data selain belum
            $rincian[$k->nama] = $listSiswa;
        }

        return view('rekap', compact('tanggal', 'rekap', 'rincian', 'adaData'));
    }
}
