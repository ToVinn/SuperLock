@extends('layouts.app')

@section('title', 'Kelola Siswa')

@push('head')
<style>
.form-siswa { display: grid; gap: 12px; }
.form-siswa label { display: flex; flex-direction: column; gap: 5px; font-size: 12.5px; color: var(--abu); font-weight: 600; }
.form-siswa .kiri { display: grid; grid-template-columns: 1fr 2fr; gap: 12px; }
@media (max-width: 720px) { .form-siswa .kiri { grid-template-columns: 1fr; } }
.form-siswa input { width: 100%; }
.form-siswa:disabled input, .form-siswa:disabled button { opacity: .5; cursor: not-allowed; }

.grid-kelas { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px; }
.card-kelas {
    display: flex; flex-direction: column; gap: 6px;
    background: #fff; border: 2px solid var(--garis); border-radius: 12px;
    padding: 18px 16px; text-decoration: none; color: var(--teks);
    transition: border-color .12s, box-shadow .12s;
}
.card-kelas:hover { border-color: var(--biru); box-shadow: 0 2px 8px rgba(21,101,192,.12); }
.card-kelas .nama { font-weight: 700; font-size: 15px; }
.card-kelas .jumlah { font-size: 12.5px; color: var(--abu); }
.card-kelas.aktif { border-color: var(--biru); background: var(--biru-muda); }
.card-kelas.aktif .nama { color: var(--biru-tua); }
.card-kelas.aktif .jumlah { color: var(--biru); }

.aksi-baris { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; margin: 0 0 12px; }
.aksi-kiri { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.btn-kecil { padding: 7px 14px; font-size: 12.5px; border-radius: 8px; }
.btn-merah { background: #fee2e2; border: 1px solid #fca5a5; color: #b91c1c; }
.btn-merah:hover { background: #fecaca; }
.cek { width: 16px; height: 16px; accent-color: var(--biru); cursor: pointer; }
#pilih-semua:checked { accent-color: var(--biru); }
</style>
@endpush

@section('content')
<div class="kartu-head" style="margin-bottom:16px">
    <div>
        <h1 style="margin:0 0 4px">Kelola Siswa</h1>
        <p style="font-size:13px;color:var(--abu)">Tambah siswa baru &amp; kelola daftar siswa per kelas.</p>
    </div>
</div>

@if (session('pesan'))<div class="msg">{{ session('pesan') }}</div>@endif

@if ($kelasAktif)
<form method="post" action="{{ route('siswa', ['kelas' => $kelasId]) }}" class="card form-siswa">
    @csrf
    <input type="hidden" name="kelas_id" value="{{ $kelasId }}">
    <h2 style="margin:0">Tambah Siswa Baru</h2>
    <p style="margin:0;font-size:12.5px;color:var(--abu)">Siswa akan dimasukkan ke kelas <b style="color:var(--biru-tua)">{{ $kelasAktif->nama }}</b>.</p>
    <div class="kiri">
        <label>NIS<input type="text" name="nis" required placeholder="cth: 2437"></label>
        <label>Nama Lengkap<input type="text" name="nama" required placeholder="cth: Ahmad Fauzi"></label>
    </div>
    <div><button type="submit">+ Tambah Siswa</button></div>
</form>
@else
<div class="card form-siswa" style="opacity:.65">
    <h2 style="margin:0">Tambah Siswa Baru</h2>
    <p style="margin:0;font-size:12.5px;color:var(--abu)">Pilih kelas terlebih dahulu di bawah untuk menambah siswa.</p>
</div>
@endif

<div class="card" style="margin-top:4px">
    <h2 style="margin:0 0 4px">{{ $kelasAktif ? 'Pilih Kelas' : 'Pilih Kelas Terlebih Dahulu' }}</h2>
    <p style="margin:0 0 14px;font-size:12.5px;color:var(--abu)">{{ $kelasAktif ? 'Pindah kelas lain untuk melihat daftar siswanya.' : 'Daftar siswa hanya tampil setelah kelas dipilih.' }}</p>
    <div class="grid-kelas">
        @foreach ($kelasList as $k)
        <a href="{{ route('siswa', ['kelas' => $k->id]) }}" class="card-kelas {{ $kelasId === $k->id ? 'aktif' : '' }}">
            <span class="nama">{{ $k->nama }}</span>
            <span class="jumlah">{{ $k->jumlah }} Siswa</span>
        </a>
        @endforeach
    </div>
</div>

@if ($kelasAktif)
<form id="frm-hapus" method="post" action="{{ route('siswa.hapus') }}">
    @csrf
    <input type="hidden" name="kelas_id" value="{{ $kelasId }}">
</form>
<div class="card" style="margin-top:4px">
    <div class="aksi-baris">
        <h2 style="margin:0">Daftar Siswa — {{ $kelasAktif->nama }} ({{ $siswaList->count() }})</h2>
        <div class="aksi-kiri">
            <a class="btn btn-kecil" href="{{ route('siswa.export', ['kelas_id' => $kelasId]) }}">Ekspor CSV</a>
            <form method="post" action="{{ route('siswa.import') }}" enctype="multipart/form-data" style="display:flex;align-items:center;gap:6px">
                @csrf
                <input type="hidden" name="kelas_id" value="{{ $kelasId }}">
                <input type="file" name="file" accept=".csv,text/csv" required style="font-size:12px">
                <button type="submit" class="btn-kecil">Impor CSV</button>
            </form>
            <button type="button" class="btn-kecil btn-merah" onclick="hapus(false)">Hapus Terpilih</button>
            <button type="button" class="btn-kecil btn-merah" onclick="hapus(true)">Hapus Semua</button>
        </div>
    </div>
    <table>
        <tr>
            <th><input type="checkbox" id="pilih-semua" class="cek" {{ $siswaList->isEmpty() ? 'disabled' : '' }}></th>
            <th>No</th><th>NIS</th><th>Nama</th>
        </tr>
        @forelse ($siswaList as $i => $s)
        <tr>
            <td><input type="checkbox" class="cek pilih" form="frm-hapus" name="ids[]" value="{{ $s->id }}"></td>
            <td>{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
            <td><code style="font-family:Consolas,monospace">{{ $s->nis }}</code></td>
            <td style="font-weight:600;font-size:13.5px">{{ $s->nama }}</td>
        </tr>
        @empty
        <tr><td colspan="4" style="color:var(--abu)">Belum ada siswa di kelas ini.</td></tr>
        @endforelse
    </table>
</div>
<script>
    function hapus(semua) {
        var frm = document.getElementById('frm-hapus');
        var dipilih = frm.querySelectorAll('input[name="ids[]"]:checked');
        if (!semua && dipilih.length === 0) { alert('Centang siswa yang mau dihapus dulu.'); return; }
        var teks = semua || dipilih.length === 0
            ? 'Hapus SEMUA siswa di kelas {{ $kelasAktif->nama }}?'
            : 'Hapus ' + dipilih.length + ' siswa terpilih?';
        if (!confirm(teks + '\nData penitipan terkait ikut terhapus.')) return;
        if (!semua) { dipilih.forEach(function (c) { c.form = frm; }); }
        frm.submit();
    }
    var ps = document.getElementById('pilih-semua');
    if (ps) ps.addEventListener('change', function () {
        document.querySelectorAll('input.pilih').forEach(function (c) { c.checked = ps.checked; });
    });
</script>
@endif
@endsection
