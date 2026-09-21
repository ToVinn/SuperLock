<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Penitipan;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    private function out(array $data, int $code = 200): JsonResponse
    {
        return response()->json($data, $code);
    }

    private function siswa(Request $request, string $tanggal, string $nis)
    {
        return Siswa::join('kelas', 'kelas.id', '=', 'siswa.kelas_id')
            ->leftJoin('penitipan', function ($join) use ($tanggal) {
                $join->on('penitipan.siswa_id', '=', 'siswa.id')
                    ->where('penitipan.tanggal', '=', $tanggal);
            })
            ->where('siswa.nis', $nis)
            ->select(
                'siswa.id', 'siswa.nis', 'siswa.nama', 'kelas.nama as kelas',
                DB::raw("COALESCE(penitipan.status,'belum') as status"),
                'penitipan.jam_kumpul', 'penitipan.jam_ambil', 'penitipan.jam_pinjam', 'penitipan.jam_kembali', 'penitipan.guru_nama',
            )
            ->first();
    }

    public function index(Request $request): JsonResponse
    {
        $tanggal = $request->query('tanggal', now()->toDateString());
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
            return $this->out(['ok' => false, 'error' => 'tanggal tidak valid'], 400);
        }

        // Scan QR KM: ambil semua sekaligus
        if ($request->isMethod('post') && $request->filled('ambil_semua')) {
            $km = User::where('role', 'km')->where('id', (int) $request->query('km_id', 0))->first(['nama', 'kelas_id']);
            if (! $km) {
                return $this->out(['ok' => false, 'error' => 'KM tidak ditemukan'], 404);
            }
            if (! $km->kelas_id) {
                return $this->out(['ok' => false, 'error' => 'KM tidak terikat kelas'], 400);
            }
            $count = Penitipan::join('siswa', 'siswa.id', '=', 'penitipan.siswa_id')
                ->where('siswa.kelas_id', $km->kelas_id)
                ->where('penitipan.tanggal', $tanggal)
                ->where('penitipan.status', 'kumpul')
                ->whereNull('penitipan.jam_ambil')
                ->update(['penitipan.jam_ambil' => now()->format('H:i:s')]);
            $kelasNama = Kelas::find($km->kelas_id)?->nama;

            return $this->out([
                'ok' => true,
                'pesan' => "HP $count siswa ditandai SUDAH DIAMBIL",
                'km' => $km->nama,
                'kelas' => $kelasNama,
                'jumlah' => $count,
            ]);
        }

        $nis = trim((string) $request->query('nis', ''));
        if ($nis === '') {
            return $this->out(['ok' => false, 'error' => 'nis wajib diisi'], 400);
        }

        $siswa = $this->siswa($request, $tanggal, $nis);
        if (! $siswa) {
            return $this->out(['ok' => false, 'error' => "Siswa dengan NIS $nis tidak ditemukan"], 404);
        }

        $info = ['nama' => $siswa->nama, 'kelas' => $siswa->kelas];

        if ($request->isMethod('post') && $request->filled('pinjam')) {
            $guru = trim((string) $request->query('guru', ''));
            if ($guru === '') {
                return $this->out(['ok' => false, 'error' => 'Nama guru piket wajib diisi'], 400);
            }
            if ($siswa->status !== 'kumpul') {
                return $this->out(['ok' => false, 'error' => 'Hanya siswa berstatus Sudah Mengumpulkan yang bisa izin ambil HP', 'siswa' => $info, 'status' => $siswa->status], 409);
            }
            Penitipan::where('siswa_id', $siswa->id)->where('tanggal', $tanggal)
                ->update(['status' => 'pinjam', 'jam_pinjam' => now()->format('H:i:s'), 'guru_nama' => $guru]);

            return $this->out(['ok' => true, 'pesan' => 'Izin ambil HP tercatat. Status: SEDANG DIPINJAM', 'siswa' => $info, 'jam_pinjam' => now()->format('H:i'), 'guru' => $guru]);
        }

        if ($request->isMethod('post') && $request->filled('kembali')) {
            $guru = trim((string) $request->query('guru', ''));
            if ($guru === '') {
                return $this->out(['ok' => false, 'error' => 'Nama guru piket wajib diisi'], 400);
            }
            if ($siswa->status !== 'pinjam') {
                return $this->out(['ok' => false, 'error' => 'Hanya siswa berstatus Sedang Dipinjam yang bisa dikembalikan', 'siswa' => $info, 'status' => $siswa->status], 409);
            }
            Penitipan::where('siswa_id', $siswa->id)->where('tanggal', $tanggal)
                ->update(['status' => 'kumpul', 'jam_kembali' => now()->format('H:i:s'), 'guru_nama' => $guru]);

            return $this->out(['ok' => true, 'pesan' => 'HP dikembalikan. Status: SUDAH MENGUMPULKAN', 'siswa' => $info, 'jam_kembali' => now()->format('H:i'), 'guru' => $guru]);
        }

        if ($request->isMethod('post') && $request->filled('ambil')) {
            if ($siswa->status !== 'kumpul') {
                return $this->out(['ok' => false, 'error' => 'PERINGATAN: siswa ini TIDAK tercatat menitipkan HP hari ini', 'siswa' => $info, 'status' => $siswa->status], 409);
            }
            if ($siswa->jam_ambil) {
                return $this->out(['ok' => false, 'error' => 'HP siswa ini sudah diambil pada jam '.$siswa->jam_ambil, 'siswa' => $info], 409);
            }
            Penitipan::where('siswa_id', $siswa->id)->where('tanggal', $tanggal)
                ->update(['jam_ambil' => now()->format('H:i:s')]);

            return $this->out(['ok' => true, 'pesan' => 'HP diserahkan. Status: SUDAH DIAMBIL', 'siswa' => $info, 'jam_kumpul' => $siswa->jam_kumpul, 'jam_ambil' => now()->format('H:i')]);
        }

        $statusNama = ['belum' => 'Belum Ditentukan', 'kumpul' => 'Sudah Mengumpulkan', 'tidak' => 'Tidak Mengumpulkan', 'pinjam' => 'Sedang Dipinjam'][$siswa->status] ?? '?';

        return $this->out([
            'ok' => true,
            'siswa' => $info,
            'status' => $siswa->status,
            'status_nama' => $statusNama,
            'jam_kumpul' => $siswa->jam_kumpul,
            'jam_ambil' => $siswa->jam_ambil,
            'jam_pinjam' => $siswa->jam_pinjam,
            'jam_kembali' => $siswa->jam_kembali,
            'guru' => $siswa->guru_nama,
        ]);
    }
}
