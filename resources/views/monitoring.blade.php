@extends('layouts.app')

@section('title', 'Monitoring — Neper PhoneLock')

@php
    $statusMap = ['kumpul' => ['Sudah Mengumpulkan', '#10b981'], 'pinjam' => ['Sedang Dipinjam', '#f59e0b'], 'tidak' => ['Tidak Mengumpulkan', '#ef4444'], 'belum' => ['Belum Ditentukan', '#94a3b8']];
    $tglIndo = \Illuminate\Support\Carbon::parse($tanggal)->translatedFormat('l, j F Y');
    $namaBln = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    $t = strtotime($tanggal);
    $tglIndo2 = date('j', $t) . ' ' . $namaBln[(int)date('n', $t)] . ' ' . date('Y', $t);
@endphp

@section('content')
<div class="no-print" style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:18px">
    <div>
        <h1 style="margin-bottom:2px">Dashboard Neper PhoneLock</h1>
        <p style="font-size:13px;color:#64748b">{{ $tglIndo }} @if($namaKelas) &middot; {{ $namaKelas }} @endif</p>
    </div>
    <form method="get" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <input type="date" name="tanggal" value="{{ $tanggal }}">
        <select name="kelas"><option value="">Semua Kelas</option>
            @foreach ($kelasList as $k)
                <option value="{{ $k->id }}" @selected($k->id === $kelasId)>{{ $k->nama }}</option>
            @endforeach
        </select>
        <button type="submit">Tampilkan</button>
    </form>
</div>

{{--
    ===== BARIS 1 : KARTU STATISTIK (4 kolom) =====
--}}
<section class="dash-baris dash-stats no-print">

    {{-- Kartu 1: Kehadiran menitipkan (donut) --}}
    <div class="card stat-kartu">
        <div class="ikon" style="background:#dbeafe">
            <svg viewBox="0 0 24 24" fill="#2563eb"><path d="M7 2v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2V2h-2v2H9V2H7zm12 8v10H5V10h14z"/></svg>
        </div>
        <span class="label">Menitipkan HP Hari Ini</span>
        <div style="display:flex;align-items:center;gap:10px">
            <span class="angka">{{ $pctMenitip }}%</span>
            <span class="stat-pill" style="background:#ecfdf5;color:#059669">{{ $menitip }}/{{ $totalSiswa }} siswa</span>
        </div>
        <div class="sub">
            @if ($pctMenitip >= 80)
                <span class="stat-pill" style="background:#ecfdf5;color:#059669">Sangat Baik</span>
            @elseif ($pctMenitip >= 50)
                <span class="stat-pill" style="background:#fffbeb;color:#b45309">Cukup</span>
            @else
                <span class="stat-pill" style="background:#fef2f2;color:#dc2626">Rendah</span>
            @endif
            @if ($firstKumpul) <span>Kumpul pertama {{ \Illuminate\Support\Carbon::parse($firstKumpul)->format('H:i') }}</span> @endif
        </div>
    </div>

    {{-- Kartu 2: Sudah diambil --}}
    <div class="card stat-kartu">
        <div class="ikon" style="background:#d1fae5">
            <svg viewBox="0 0 24 24" fill="#10b981"><path d="M16 13a3 3 0 1 0 0 6 3 3 0 0 0 0-6zm-8 4H4a1 1 0 0 1 0-2h4a1 1 0 0 1 0 2zm8-11a3 3 0 1 0 0 6 3 3 0 0 0 0-6zM6 9H4a1 1 0 0 1 0-2h2a1 1 0 0 1 0 2zm14.7 1.7-2 2a1 1 0 0 1-1.4-1.4l2-2a1 1 0 0 1 1.4 1.4zm-6 10-1.3-1.3a1 1 0 0 1 1.4-1.4l1.3 1.3a1 1 0 0 1-1.4 1.4zM7.7 4.3 9 5.6a1 1 0 0 1-1.4 1.4L6.3 5.7a1 1 0 0 1 1.4-1.4z"/></svg>
        </div>
        <span class="label">Sudah Diambil Kembali</span>
        <div style="display:flex;align-items:center;gap:10px">
            <span class="angka">{{ $sudahAmbil }}</span>
            <span class="stat-pill" style="background:#ecfdf5;color:#059669">{{ $pctAmbil }}%</span>
        </div>
        <div class="sub">
            dari {{ $menitip }} yang menitipkan
            @if ($firstAmbil) <span>&middot; ambil pertama {{ \Illuminate\Support\Carbon::parse($firstAmbil)->format('H:i') }}</span> @endif
        </div>
    </div>

    {{-- Kartu 3: Sedang dipinjam (progress bar) --}}
    <div class="card stat-kartu">
        <div class="ikon" style="background:#fef3c7">
            <svg viewBox="0 0 24 24" fill="#f59e0b"><path d="M7 2v2H5a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2V2h-2v2H9V2H7zM5 9h14v10H5V9zm2 3v2h2v-2H7zm4 0v2h4v-2h-4z"/></svg>
        </div>
        <span class="label">Sedang Dipinjam (Izin Guru)</span>
        <span class="angka">{{ $pinjam }} <small style="font-size:14px;color:#94a3b8">siswa</small></span>
        <div>
            <div class="bar-tipis amber"><i style="width:{{ $pctMenitip ? min(100, (int)round($pinjam / max($menitip,1) * 100)) : 0 }}%"></i></div>
            <span class="sub">{{ $menitip ? (int)round($pinjam / max($menitip,1) * 100) : 0 }}% dari yang menitipkan</span>
        </div>
    </div>

    {{-- Kartu 4: Tidak menitipkan (progress bar ungu) --}}
    <div class="card stat-kartu">
        <div class="ikon" style="background:#ede9fe">
            <svg viewBox="0 0 24 24" fill="#8b5cf6"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zM4 12a8 8 0 1 1 16 0 8 8 0 0 1-16 0zm8-4a4 4 0 0 0-4 4h2a2 2 0 1 1 4 0c0 2-3 1.75-3 5h2c0-2.25 3-2.5 3-5a4 4 0 0 0-4-4zm-1 10v2h2v-2h-2z"/></svg>
        </div>
        <span class="label">Tidak Menitipkan</span>
        <span class="angka">{{ $tidak }} <small style="font-size:14px;color:#94a3b8">siswa</small></span>
        <div>
            <div class="bar-tipis"><i style="width:{{ $pctTidak }}%"></i></div>
            <span class="sub">Selesai dicek &middot; {{ $pctTidak }}% dari total siswa</span>
        </div>
    </div>
</section>

{{--
    ===== BARIS 2 : GRAFIK (8) + PROFIL & AKTIVITAS (4) =====
--}}
<section class="dash-baris dash-8-4 no-print">
    <div class="card">
        <div class="kartu-head">
            <h2>Tren Penitipan &amp; Pengambilan</h2>
            <form method="get" style="display:flex;gap:8px">
                <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                @if ($kelasId)<input type="hidden" name="kelas" value="{{ $kelasId }}">@endif
                <select name="hari" onchange="this.form.submit()">
                    <option value="7" @selected($hari === 7)>7 Hari</option>
                    <option value="14" @selected($hari === 14)>14 Hari</option>
                    <option value="30" @selected($hari === 30)>30 Hari</option>
                </select>
            </form>
        </div>
        @php
            $maxK = max(1, collect($trend)->max('kumpul'));
            $w = 100; $h = 42; $padX = 6; $padY = 4;
            $n = count($trend);
            $langkah = ($w - 2 * $padX) / max($n - 1, 1);
            $barW = max(0.9, min(3.5, $langkah * 0.45));
            $titik = collect($trend)->map(fn ($d, $i) => [$padX + $i * $langkah, $h - $padY - ($d['kumpul'] / $maxK) * ($h - 2 * $padY - 4) - 2]);
            $path = $titik->map(fn ($p, $i) => ($i ? 'L' : 'M') . number_format($p[0], 2) . ' ' . number_format($p[1], 2))->implode(' ');
        @endphp
        <svg class="grafik" viewBox="0 0 {{ $w }} {{ $h + 10 }}" preserveAspectRatio="none" style="aspect-ratio:100/52">
            @for ($y = 0; $y <= 4; $y++)
                <line class="grid" x1="0" x2="{{ $w }}" y1="{{ $padY + ($h - 2 * $padY) / 4 * $y }}" y2="{{ $padY + ($h - 2 * $padY) / 4 * $y }}" stroke-width="0.15"/>
            @endfor
            @foreach ($trend as $i => $d)
                <rect class="bar" x="{{ number_format($padX + $i * $langkah - $barW / 2, 2) }}"
                      y="{{ number_format($h - $padY - ($d['kumpul'] / $maxK) * ($h - 2 * $padY - 4) - 2, 2) }}"
                      width="{{ $barW }}" height="{{ number_format(($d['kumpul'] / $maxK) * ($h - 2 * $padY - 4) + 2, 2) }}"
                      rx="0.5"><title>{{ $d['kumpul'] }} siswa menitip</title></rect>
            @endforeach
            <path class="line" d="{{ $path }}"/>
            @foreach ($titik as $i => $p)
                <circle class="dot" cx="{{ number_format($p[0], 2) }}" cy="{{ number_format($p[1], 2) }}" r="0.7"><title>{{ $trend[$i]['pct'] }}% diambil</title></circle>
            @endforeach
        </svg>
        <div style="display:flex;justify-content:space-between;margin-top:6px;flex-wrap:wrap;gap:6px">
            <div class="grafik-legend">
                <span><i style="background:#bfdbfe"></i>Menitipkan</span>
                <span><i style="background:#2563eb"></i>Titik = jumlah siswa</span>
            </div>
            <div style="display:flex;gap:6px;flex-wrap:wrap">
                @foreach (array_slice($trend, 0, $hari) as $d)
                    <span class="axis" style="min-width:26px;text-align:center">{{ $d['label'] }}</span>
                @endforeach
            </div>
        </div>
    </div>

    <div>
        {{-- profil mini kelas --}}
        <div class="card profil-mini">
            @if ($namaKelas)
                <div class="profil-foto">{{ substr($namaKelas, 0, 1) }}</div>
                <div style="flex:1">
                    <div class="profil-nama">{{ $namaKelas }}</div>
                    <div class="profil-sub">{{ $siswaCount[$kelasId] ?? 0 }} siswa &middot; KM: {{ $kmPerKelas[$kelasId] ?? '-' }}</div>
                </div>
                <span class="stat-pill" style="background:#eff6ff;color:#2563eb">Aktif</span>
            @else
                <div class="profil-foto">S</div>
                <div style="flex:1">
                    <div class="profil-nama">Seluruh Sekolah</div>
                    <div class="profil-sub">{{ $totalSiswa }} siswa &middot; {{ $kelasList->count() }} kelas</div>
                </div>
                <span class="stat-pill" style="background:#eff6ff;color:#2563eb">Semua</span>
            @endif
        </div>

        {{-- timeline aktivitas hari ini --}}
        <div class="card">
            <div class="kartu-head"><h2>Aktivitas Hari Ini</h2></div>
            <ul class="timeline">
                @if ($firstKumpul)
                    <li><span class="jam">{{ \Illuminate\Support\Carbon::parse($firstKumpul)->format('H:i') }}</span><br><span class="isi">HP pertama dikumpulkan</span></li>
                @endif
                @if ($pinjam > 0)
                    <li><span class="isi">{{ $pinjam }} siswa izin pinjam HP</span></li>
                @endif
                @if ($firstAmbil)
                    <li><span class="jam">{{ \Illuminate\Support\Carbon::parse($firstAmbil)->format('H:i') }}</span><br><span class="isi">HP mulai diambil kembali</span></li>
                @endif
                <li><span class="isi">{{ $sudahAmbil }} dari {{ $menitip }} HP sudah diambil</span></li>
                @if ($kumpul - $sudahAmbil > 0)
                    <li><span class="isi">{{ $kumpul - $sudahAmbil }} HP masih disimpan</span></li>
                @endif
                @if ($menitip === 0 && $tidak === 0)
                    <li><span class="isi">Belum ada pengecekan hari ini</span></li>
                @endif
            </ul>
        </div>
    </div>
</section>

{{--
    ===== BARIS 3 : DAFTAR KELAS (7) + TABEL STATUS (5) =====
--}}
<section class="dash-baris dash-7-5">
    <div class="card">
        <div class="kartu-head"><h2>Rekap Per Kelas — {{ $tglIndo2 }}</h2></div>
        <ul class="daftar-mapel">
            @forelse ($kelasList as $i => $k)
                @php
                    $tot = $siswaCount[$k->id] ?? 0;
                    $m = $menitipPerKelas[$k->id] ?? 0;
                    $pct = $tot ? (int)round($m / $tot * 100) : 0;
                @endphp
                <li>
                    <span class="no-urut">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    <div class="daftar-body">
                        <div class="daftar-nama">{{ $k->nama }}</div>
                        <div class="daftar-sub">{{ $m }}/{{ $tot }} siswa menitipkan &middot; KM: {{ $kmPerKelas[$k->id] ?? '-' }}</div>
                    </div>
                    <span class="stat-pill" style="background:{{ $pct >= 80 ? '#ecfdf5' : ($pct >= 50 ? '#fffbeb' : '#f1f5f9') }};color:{{ $pct >= 80 ? '#059669' : ($pct >= 50 ? '#b45309' : '#64748b') }}">{{ $pct }}%</span>
                    <a class="btn" style="padding:6px 12px;font-size:12px" href="{{ route('monitoring', ['tanggal' => $tanggal, 'kelas' => $k->id]) }}">Detail</a>
                </li>
            @empty
                <li><span class="daftar-sub">Belum ada kelas.</span></li>
            @endforelse
        </ul>
    </div>

    <div class="card">
        <div class="kartu-head"><h2>Status HP Terkini</h2></div>
        <table>
            <tr><th>Siswa</th><th>Status</th><th>Ambil</th></tr>
            @forelse ($tabel as $r)
                <tr>
                    <td>
                        <div style="font-weight:600;font-size:13.5px">{{ $r->nama }}</div>
                        <div style="font-size:11.5px;color:#94a3b8">{{ $r->kelas }}</div>
                    </td>
                    <td><span class="badge" style="background:{{ $statusMap[$r->status][1] }}">{{ $statusMap[$r->status][0] }}</span></td>
                    <td style="font-size:12.5px;color:#64748b">{{ $r->jam_ambil ? \Illuminate\Support\Carbon::parse($r->jam_ambil)->format('H:i') : '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="3" style="color:#94a3b8;font-size:13px">Belum ada yang menitipkan hari ini.</td></tr>
            @endforelse
        </table>
    </div>
</section>

{{-- detail siswa per kelas (tetap ada untuk cetak/cek detail) --}}
@if ($kelasId && $siswaRows->count())
<div class="card" style="margin-top:4px">
    <h2 style="margin-top:0">{{ $namaKelas }} — Detail Siswa ({{ $siswaRows->count() }})</h2>
    <table>
        <tr><th>No</th><th>NIS</th><th>Nama</th><th>Status</th><th>Jam Kumpul</th><th>Jam Pinjam</th><th>Jam Kembali</th><th>Jam Ambil</th><th>Dicek oleh</th></tr>
        @foreach ($siswaRows as $i => $r)
            @php $p = $r->penitipan->first(); $st = $p->status ?? 'belum'; @endphp
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $r->nis }}</td>
            <td>{{ $r->nama }}</td>
            <td><span class="badge" style="background:{{ $statusMap[$st][1] }}">{{ $statusMap[$st][0] }}</span></td>
            <td>{{ $p->jam_kumpul ?? '-' }}</td>
            <td>{{ $p->jam_pinjam ?? '-' }}</td>
            <td>{{ $p->jam_kembali ?? '-' }}</td>
            <td>{{ $p->jam_ambil ?? '-' }}</td>
            <td>{{ $p->guru_nama ?? $p->km_nama ?? '-' }}</td>
        </tr>
        @endforeach
    </table>
</div>
@endif
@endsection
