@extends('layouts.app')

@section('title', 'Pengecekan KM')

@section('content')
<h1>Pengecekan Ketua Kelas — {{ now()->translatedFormat('j M Y') }}</h1>

@if (auth()->user()->role === 'admin')
<form method="get" class="card">
    Kelas
    <select name="kelas" onchange="this.form.submit()"><option value="">-- Pilih Kelas --</option>
        @foreach ($kelasList as $k)
            <option value="{{ $k->id }}" @selected($k->id === $kelasId)>{{ $k->nama }}</option>
        @endforeach
    </select>
</form>
@elseif ($kelasId && $namaKelas)
<div class="card">Kelas: <b>{{ $namaKelas }}</b></div>
@endif

@if (session('pesan'))<div class="msg">{{ session('pesan') }}</div>@endif

@if ($kelasId && $rows->count())
<form method="post" action="{{ route('km') }}" class="card">
    @csrf
    <input type="hidden" name="kelas" value="{{ $kelasId }}">
    <label>Ketua Kelas:
        <select name="km_id" onchange="this.form.elements['km_nama'].value = this.options[this.selectedIndex].dataset.nama">
            @foreach ($kmList as $km)
                <option value="{{ $km->id }}" data-nama="{{ $km->nama }}" @selected($kmDefault && $km->id === $kmDefault->id)>{{ $km->nama }}</option>
            @endforeach
        </select>
    </label>
    <input type="hidden" name="km_nama" value="{{ $kmDefault->nama ?? $kmList->first()->nama }}">
    <p style="margin-top:10px;font-size:13px;color:#616161">
        Pilih <b>Sudah/Tidak Mengumpulkan</b> per siswa.
    </p>

    <table>
        <tr><th>No</th><th>Nama</th><th>Status Kehadiran &amp; HP</th></tr>
        @foreach ($rows as $i => $r)
            @php $st = $r->penitipan->first()?->status ?? 'belum'; @endphp
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $r->nama }}</td>
            <td>
                @foreach (['kumpul' => 'Sudah Mengumpulkan', 'tidak' => 'Tidak Mengumpulkan'] as $val => $label)
                    @php [$_, $warna] = $statusMap = ['kumpul' => ['Sudah Mengumpulkan', '#2e7d32'], 'tidak' => ['Tidak Mengumpulkan', '#c62828']][$val]; @endphp
                    <label class="chip @if($st === $val) pilih @endif" @if($st === $val) style="border-color:{{ $warna }};background:{{ $warna }}" @endif>
                        <input type="radio" name="status[{{ $r->id }}]" id="st_{{ $r->id }}_{{ $val }}" value="{{ $val }}" @checked($st === $val)>
                        {{ $label }}
                    </label>
                @endforeach
            </td>
        </tr>
        @endforeach
    </table>
    <p><button type="submit">Simpan Pengecekan</button></p>
</form>

<script>
document.querySelectorAll('input[type=radio]').forEach(function(r) {
    r.addEventListener('change', function() {
        var tr = r.closest('tr');
        tr.querySelectorAll('.chip').forEach(function(c) {
            c.classList.remove('pilih');
            c.style.borderColor = '#e0e0e0';
            c.style.background = '';
            c.style.color = '#616161';
        });
        var chip = r.closest('.chip');
        var warna = { kumpul: '#2e7d32', tidak: '#c62828' }[r.value];
        chip.classList.add('pilih');
        chip.style.borderColor = warna;
        chip.style.background = warna;
        chip.style.color = '#fff';
    });
});
</script>
@endif
@endsection
