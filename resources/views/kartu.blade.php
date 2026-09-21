@extends('layouts.app')

@section('title', 'Kartu QR Siswa')

@section('content')
<h1>Kartu QR Siswa</h1>

<form method="get" class="card no-print">
    Kelas
    <select name="kelas"><option value="">-- Pilih Kelas --</option>
        @foreach ($kelas as $k)
            <option value="{{ $k->id }}" @selected($k->id === $kelasId)>{{ $k->nama }}</option>
        @endforeach
    </select>
    <button type="submit" class="no-print">Tampilkan</button>
    @if ($rows->count())<button type="button" onclick="print()" class="no-print">Cetak</button>@endif
</form>

@if ($rows->count())
<h2>{{ $namaKelas }} — {{ $rows->count() }} siswa</h2>
@if ($kmRows->count())
<div class="card no-print" style="background:#e3f2fd;border:2px solid #1565c0">
    <h3 style="margin:0 0 10px">QR Ketua Kelas (scan untuk ambil semua HP)</h3>
    <div class="grid-kartu">
        @foreach ($kmRows as $km)
        <div class="kartu" style="border-color:#1565c0">
            <div class="qr">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data={{ urlencode('KM:'.$km->id) }}"
                     alt="QR KM {{ $km->nama }}" width="140" height="140">
            </div>
            <div class="nama">{{ $km->nama }}</div>
            <div class="nis">QR ambil semua</div>
        </div>
        @endforeach
    </div>
</div>
@endif
<div class="grid-kartu">
    @foreach ($rows as $r)
    <div class="kartu">
        <div class="qr">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data={{ urlencode($r->nis) }}"
                 alt="QR {{ $r->nis }}" width="140" height="140">
        </div>
        <div class="nama">{{ $r->nama }}</div>
        <div class="nis">NIS {{ $r->nis }}</div>
    </div>
    @endforeach
</div>
@endif
@endsection
