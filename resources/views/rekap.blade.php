@extends('layouts.app')

@section('title', 'Rekap Harian')

@push('head')
<style>
.legenda { display: flex; gap: 14px; flex-wrap: wrap; font-size: 12px; color: var(--abu); align-items: center; }
.legenda i { display: inline-block; width: 11px; height: 11px; border-radius: 3px; margin-right: 5px; }

.rekap-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 18px; }
@media (max-width: 1100px) { .rekap-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 560px) { .rekap-grid { grid-template-columns: 1fr; } }

.rekap-stat { border-radius: 12px; padding: 14px 18px; color: #fff; position: relative; overflow: hidden; }
.rekap-stat b { display: block; font-size: 26px; line-height: 1.1; }
.rekap-stat span { font-size: 12px; opacity: .9; }
.rekap-stat svg { position: absolute; right: 12px; top: 12px; width: 22px; height: 22px; opacity: .35; }

.bar-stack { display: flex; height: 12px; border-radius: 999px; overflow: hidden; background: #e2e8f0; margin: 10px 0 6px; }
.bar-stack i { display: block; height: 100%; }

.kelas-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
.pct-pill { padding: 3px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; }

.tbl-rekap th { width: 46%; }
.tbl-rekap td b { font-size: 15px; }
.mini-dot { display: inline-block; width: 9px; height: 9px; border-radius: 999px; margin-right: 8px; }
</style>
@endpush

@section('content')
@php
    $statusMap = [
        'kumpul' => ['Sudah Mengumpulkan', '#10b981'],
        'pinjam' => ['Sedang Dipinjam', '#f59e0b'],
        'tidak'  => ['Tidak Mengumpulkan', '#ef4444'],
        'belum'  => ['Belum Ditentukan', '#94a3b8'],
    ];
    $urutan = ['kumpul', 'pinjam', 'tidak', 'belum'];
    $warna = array_column($statusMap, 1);

    // agregat semua kelas
    $total = ['kumpul' => 0, 'pinjam' => 0, 'tidak' => 0, 'belum' => 0, 'ambil' => 0];
    foreach ($rekap as $stat) {
        foreach ($urutan as $k) { $total[$k] += $stat[$k] ?? 0; }
        $total['ambil'] += $stat['ambil'] ?? 0;
    }
    $totalSiswa = array_sum(array_map(fn ($k) => $total[$k], $urutan));
    $pct = fn ($n) => $totalSiswa ? round($n / $totalSiswa * 100) : 0;
@endphp

<div class="kartu-head no-print" style="margin-bottom:16px">
    <div>
        <h1 style="margin:0 0 4px">Rekap Neper PhoneLock</h1>
        <p style="font-size:13px;color:var(--abu)">{{ \Illuminate\Support\Carbon::parse($tanggal)->translatedFormat('l, j F Y') }}</p>
    </div>
    <form method="get" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <input type="date" name="tanggal" value="{{ $tanggal }}">
        <button type="submit">Tampilkan</button>
        <button type="button" onclick="print()">Cetak</button>
    </form>
</div>

{{-- ===== KARTU STATISTIK TOTAL ===== --}}
<section class="rekap-grid no-print">
    @php $ikon = ['kumpul' => 'M9 16.2 4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z', 'pinjam' => 'M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm-1 5h2v7h-2V7zm0 9h2v2h-2v-2z', 'tidak' => 'M19 6.4 17.6 5 12 10.6 6.4 5 5 6.4 10.6 12 5 17.6 6.4 19 12 13.4 17.6 19 19 17.6 13.4 12 19 6.4z', 'belum' => 'M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z']; @endphp
    @foreach ($urutan as $k)
    <div class="rekap-stat" style="background:{{ $statusMap[$k][1] }}">
        <svg viewBox="0 0 24 24" fill="#fff"><path d="{{ $ikon[$k] }}"/></svg>
        <b>{{ $total[$k] }}</b>
        <span>{{ $statusMap[$k][0] }} &middot; {{ $pct($total[$k]) }}%</span>
    </div>
    @endforeach
</section>

{{-- ===== PENGINGAT ===== --}}
<div class="card no-print" style="padding:12px 18px">
    <div class="legenda">
        <b style="color:var(--teks);font-size:12.5px">Pengingat:</b>
        @foreach ($urutan as $k)
            <span><i style="background:{{ $statusMap[$k][1] }}"></i>{{ $statusMap[$k][0] }}</span>
        @endforeach
        <span><i style="background:#fff;border:2px solid #10b981;box-sizing:border-box"></i>Sudah Mengambil (dari yang mengumpulkan)</span>
    </div>
</div>

@if (! $adaData)
<div class="card"><p style="color:var(--abu)">Belum ada data pengecekan pada tanggal ini.</p></div>
@endif

{{-- ===== REKAP PER KELAS ===== --}}
@foreach ($rekap as $kelas => $stat)
    @php
        $jmlKelas = array_sum(array_map(fn ($k) => $stat[$k] ?? 0, $urutan));
        $kumpul = $stat['kumpul'] ?? 0;
        $ambil = $stat['ambil'] ?? 0;
        $pctKumpul = $jmlKelas ? round($kumpul / $jmlKelas * 100) : 0;
    @endphp
<div class="card" style="margin-top:4px">
    <div class="kelas-head">
        <h2 style="margin:0;font-size:15px">{{ $kelas }} <small style="color:var(--abu);font-weight:400">({{ $jmlKelas }} siswa)</small></h2>
        @if ($pctKumpul >= 80)
        @php [$bgP, $fgP] = ['#ecfdf5', '#059669']; @endphp
    @elseif ($pctKumpul >= 50)
        @php [$bgP, $fgP] = ['#fffbeb', '#b45309']; @endphp
    @else
        @php [$bgP, $fgP] = ['#fef2f2', '#dc2626']; @endphp
    @endif
    <span class="pct-pill" style="background:{{ $bgP }};color:{{ $fgP }}">{{ $pctKumpul }}% mengumpulkan</span>
    </div>

    <div class="bar-stack">
        @foreach ($urutan as $k)
            @if (($stat[$k] ?? 0) > 0)
                <i style="width:{{ round(($stat[$k] / $jmlKelas) * 100) }}%;background:{{ $statusMap[$k][1] }}"></i>
            @endif
        @endforeach
    </div>

    <table class="tbl-rekap" style="margin-top:8px">
        @foreach ($urutan as $k)
            @if (! empty($stat[$k]))
            <tr>
                <th><span class="mini-dot" style="background:{{ $statusMap[$k][1] }}"></span>{{ $statusMap[$k][0] }}</th>
                <td><b>{{ $stat[$k] }}</b> <small style="color:var(--abu)">siswa ({{ round($stat[$k] / $jmlKelas * 100) }}%)</small></td>
            </tr>
            @endif
        @endforeach
        <tr><th>Sudah Mengambil</th><td><b>{{ $ambil }}</b> <small style="color:var(--abu)">dari {{ $kumpul }} yang mengumpulkan</small></td></tr>
        <tr><th>Belum Mengambil</th><td><b>{{ max(0, $kumpul - $ambil) }}</b> <small style="color:var(--abu)">HP masih dititipkan</small></td></tr>
    </table>
</div>
@endforeach
@endsection
