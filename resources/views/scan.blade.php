@extends('layouts.app')

@section('title', 'Pengambilan HP')

@push('head')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
@endpush

@section('content')
<h1>Pengambilan HP — {{ now()->translatedFormat('j M Y') }}</h1>

<div class="card">
    <h2 style="margin-top:0">1. Scan QR KM — ambil semua sekaligus</h2>
    <div id="reader" style="max-width:340px"></div>
    <p style="margin-top:12px;font-size:13px;color:#616161">QR berisi <b>KM:id</b> (cetak di Kartu QR). Satu scan menandai semua HP kelas yang Sudah Mengumpulkan jadi Sudah Diambil.</p>
    <div id="hasil"></div>
</div>

<div class="card">
    <h2 style="margin-top:0">2. Izin ambil / kembalikan per siswa</h2>
    <form onsubmit="prosesPinjam(this.mode.value, this.nis.value, this.guru.value); this.nis.value=''; return false">
        <label><input type="radio" name="mode" value="pinjam" checked style="display:inline"> Izin Ambil</label>
        <label><input type="radio" name="mode" value="kembali" style="display:inline"> Kembalikan</label>
        <br><br>
        <input type="text" name="guru" placeholder="Nama guru piket">
        <input type="text" name="nis" placeholder="NIS siswa" inputmode="numeric" autocomplete="off">
        <button type="submit">Proses</button>
    </form>
    <div id="hasil2"></div>
</div>

<script>
var TERAKHIR = null, WAKTU = 0;

function tampil(id, cls, html) {
    var el = document.getElementById(id);
    el.className = cls;
    el.innerHTML = html;
}

function prosesQR(teks) {
    teks = (teks || '').trim();
    var now = Date.now();
    if (teks === TERAKHIR && now - WAKTU < 4000) return;
    TERAKHIR = teks; WAKTU = now;

    var m = teks.match(/^KM:(\d+)$/i);
    if (!m) { tampil('hasil', 'gagal', 'QR tidak dikenal. Scan QR KM (format KM:id).'); return; }

    fetch('{{ route('api') }}?ambil_semua=1&km_id=' + encodeURIComponent(m[1]), {method: 'POST'})
        .then(function(r) { return r.json(); })
        .then(function(j) {
            if (j.ok) {
                tampil('hasil', 'ok', '<b>' + j.kelas + '</b><br>' + j.pesan + '<br>KM: ' + j.km);
            } else {
                tampil('hasil', 'gagal', j.error);
            }
        })
        .catch(function() { tampil('hasil', 'gagal', 'Gagal terhubung ke server.'); });
}

function prosesPinjam(mode, nis, guru) {
    nis = (nis || '').trim();
    guru = (guru || '').trim();
    if (!nis) return;
    if (!guru) { tampil('hasil2', 'gagal', 'Isi nama guru piket dulu.'); return; }
    var url = '{{ route('api') }}?nis=' + encodeURIComponent(nis) +
        (mode === 'kembali' ? '&kembali=1' : '&pinjam=1') + '&guru=' + encodeURIComponent(guru);
    fetch(url, {method: 'POST'})
        .then(function(r) { return r.json().then(function(j) { j._code = r.status; return j; }); })
        .then(function(j) {
            if (j.ok) {
                tampil('hasil2', 'ok', '<b>' + j.siswa.nama + '</b> (' + j.siswa.kelas + ')<br>' + j.pesan);
            } else if (j._code === 409 && j.status) {
                tampil('hasil2', 'info', '<b>' + (j.siswa ? j.siswa.nama : '') + '</b><br>' + j.error +
                    '<br>Status hari ini: ' + (j.status_nama || j.status));
            } else {
                tampil('hasil2', 'gagal', j.error);
            }
        })
        .catch(function() { tampil('hasil2', 'gagal', 'Gagal terhubung ke server.'); });
}

var scanner = new Html5Qrcode('reader');
scanner.start(
    { facingMode: 'environment' },
    { fps: 10, qrbox: 220 },
    function(teks) { prosesQR(teks); },
    function() {}
).catch(function(err) {
    document.getElementById('reader').innerHTML =
        '<p style="color:#c62828;font-size:13px">Kamera tidak tersedia (' + err + '). Gunakan input manual di bawah.</p>';
});
</script>
@endsection
