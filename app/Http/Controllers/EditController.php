<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Penitipan;
use App\Models\Siswa;
use Illuminate\Http\Request;

class EditController extends Controller
{
    public function index(Request $request)
    {
        $kelasId = (int) $request->query('kelas', 0);
        $kelasList = Kelas::orderBy('nama')->get();
        $siswaCount = Siswa::selectRaw('kelas_id, COUNT(*) AS c')->groupBy('kelas_id')->pluck('c', 'kelas_id');

        $rows = collect();
        $namaKelas = null;
        if ($kelasId) {
            $namaKelas = Kelas::find($kelasId)?->nama;
            if ($namaKelas) {
                $rows = Siswa::where('kelas_id', $kelasId)
                    ->orderBy('nama')
                    ->with(['penitipan' => fn ($q) => $q->where('tanggal', today())])
                    ->get();
            } else {
                $kelasId = 0;
            }
        }

        $statusMap = [
            'belum' => ['Belum', '#e5e7eb'],
            'kumpul' => ['Terkumpul', '#dcfce7'],
            'pinjam' => ['Dipinjam', '#fef9c3'],
            'tidak' => ['Tidak Bawa', '#f3f4f6'],
        ];

        return view('edit', compact('kelasId', 'kelasList', 'siswaCount', 'rows', 'namaKelas', 'statusMap'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'kelas' => ['nullable', 'integer'],
            'aksi' => ['required', 'in:meminjam,mengembalikan,mengambil'],
            'siswa' => ['required', 'array', 'min:1'],
            'siswa.*' => ['integer', 'exists:siswa,id'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $tanggal = today()->toDateString();
        $now = now()->format('H:i:s');
        $guru = $request->user()->nama;
        $keterangan = $data['keterangan'] ?? null;
        $berhasil = 0;
        $dilewati = 0;

        $siswaList = Siswa::whereIn('id', $data['siswa'])
            ->with(['penitipan' => fn ($q) => $q->where('tanggal', $tanggal)])
            ->get();

        $namaSiswaBerhasil = [];

        foreach ($siswaList as $s) {
            $p = $s->penitipan->first();
            $st = $p->status ?? 'belum';
            $diambil = $p && $p->jam_ambil !== null;

            [$ok, $changes] = match ($data['aksi']) {
                'meminjam' => [$st === 'kumpul' && !$diambil, ['status' => 'pinjam', 'jam_pinjam' => $now, 'jam_kembali' => null]],
                'mengembalikan' => [$st === 'pinjam', ['status' => 'kumpul', 'jam_kembali' => $now]],
                'mengambil' => [$st === 'kumpul' && !$diambil, ['jam_ambil' => $now]],
            };

            if (!$ok) {
                $dilewati++;
                continue;
            }

            Penitipan::updateOrCreate(
                ['siswa_id' => $s->id, 'tanggal' => $tanggal],
                $changes + ['guru_nama' => $guru, 'keterangan' => $keterangan],
            );
            $berhasil++;
            $namaSiswaBerhasil[] = $s->nama;
        }

        if ($berhasil > 0) {
            $aksiMap = [
                'meminjam' => 'meminjam HP',
                'mengembalikan' => 'mengembalikan HP',
                'mengambil' => 'mengambil HP',
            ];
            $deskripsi = 'Memproses ' . $berhasil . ' siswa (' . $aksiMap[$data['aksi']] . '): ' . implode(', ', $namaSiswaBerhasil);
            if ($keterangan) $deskripsi .= " | Ket: $keterangan";
            \App\Models\Aktivitas::catat('Edit Status', $deskripsi);
        }

        $pesan = $berhasil.' data diperbarui.';
        if ($dilewati) {
            $pesan .= " $dilewati dilewati (status tidak sesuai).";
        }

        return redirect()->route('edit', ['kelas' => $data['kelas'] ?: null])->with('pesan', $pesan);
    }
}
