<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Penitipan;
use App\Models\Siswa;
use Illuminate\Http\Request;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $kelasId = (int) $request->query('kelas', 0);

        $siswaCount = Siswa::selectRaw('kelas_id, COUNT(*) AS c')
            ->groupBy('kelas_id')->pluck('c', 'kelas_id');
        $kelasList = Kelas::orderBy('nama')->get(['id', 'nama'])
            ->map(fn ($k) => $k->setAttribute('jumlah', $siswaCount[$k->id] ?? 0));

        $siswaList = $kelasId
            ? Siswa::where('kelas_id', $kelasId)->orderBy('nama')->get()
            : collect();
        $kelasAktif = $kelasId ? $kelasList->firstWhere('id', $kelasId) : null;

        return view('siswa', compact('kelasList', 'siswaList', 'kelasId', 'kelasAktif'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nis' => ['required', 'string', 'max:20', 'unique:siswa,nis'],
            'nama' => ['required', 'string', 'max:100'],
            'kelas_id' => ['required', 'integer', 'exists:kelas,id'],
        ]);

        Siswa::create($data);

        $namaKelas = Kelas::find($data['kelas_id'])?->nama;
        \App\Models\Aktivitas::catat('Tambah Siswa', "Menambahkan siswa baru: {$data['nama']} (NIS: {$data['nis']}) ke kelas $namaKelas.");

        return redirect()->route('siswa', ['kelas' => $data['kelas_id']])
            ->with('pesan', "Siswa {$data['nama']} (NIS {$data['nis']}) berhasil ditambahkan.");
    }

    public function destroy(Request $request)
    {
        $data = $request->validate([
            'kelas_id' => ['required', 'integer', 'exists:kelas,id'],
            'ids' => ['nullable', 'array'],
            'ids.*' => ['integer', 'exists:siswa,id'],
        ]);

        $query = Siswa::where('kelas_id', $data['kelas_id']);
        if (! empty($data['ids'])) $query = $query->whereIn('id', $data['ids']);
        $count = $query->count();

        if ($count) {
            Penitipan::whereIn('siswa_id', $query->get()->modelKeys())->delete();
            $query->delete();
            $namaKelas = Kelas::find($data['kelas_id'])?->nama;
            \App\Models\Aktivitas::catat('Hapus Siswa', "Menghapus $count siswa dari kelas $namaKelas beserta data penitipannya.");
        }

        return redirect()->route('siswa', ['kelas' => $data['kelas_id']])
            ->with('pesan', "$count siswa dihapus.");
    }

    public function import(Request $request)
    {
        $data = $request->validate([
            'kelas_id' => ['required', 'integer', 'exists:kelas,id'],
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:2048'],
        ]);

        $xlsx = \Shuchkin\SimpleXLSX::parse($data['file']->getRealPath());
        if (!$xlsx) {
            return back()->with('pesan', 'Gagal membaca file Excel: ' . \Shuchkin\SimpleXLSX::parseError());
        }

        $tambah = 0;
        $lewati = 0;
        foreach ($xlsx->rows() as $i => $cells) {
            $cells = array_map(function($c) { return trim((string) $c); }, $cells);
            
            $nis = $cells[0] ?? '';
            $nama = $cells[1] ?? '';

            if ($nis === '' || $nama === '') { $lewati++; continue; }
            if ($i === 0 && stripos($nis.$nama, 'nis') !== false) continue;
            
            Siswa::insertOrIgnore(['nis' => $nis, 'nama' => $nama, 'kelas_id' => $data['kelas_id']])
                ? $tambah++ : $lewati++;
        }

        if ($tambah > 0) {
            $namaKelas = Kelas::find($data['kelas_id'])?->nama;
            \App\Models\Aktivitas::catat('Impor Siswa', "Mengimpor data siswa ke kelas $namaKelas ($tambah ditambahkan).");
        }

        return redirect()->route('siswa', ['kelas' => $data['kelas_id']])
            ->with('pesan', "Impor selesai: $tambah siswa ditambahkan, $lewati dilewati (NIS sudah ada / baris tidak valid).");
    }

    public function export(Request $request)
    {
        $data = $request->validate(['kelas_id' => ['required', 'integer', 'exists:kelas,id']]);
        $namaKelas = str_replace(['\\', '/', ':', ' '], '_', Kelas::find($data['kelas_id'])->nama);

        $rows = Siswa::where('kelas_id', $data['kelas_id'])->orderBy('nama')->get(['nis', 'nama']);
        $csv = "No;NIS;Nama\n";
        foreach ($rows as $i => $r) $csv .= ($i + 1) . ';' . $r->nis . ';' . $r->nama . "\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=daftar-siswa-$namaKelas.csv",
        ]);
    }
}
