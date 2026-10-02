@extends('layouts.app')

@section('title', 'Pengeditan — Neper PhoneLock')

@push('head')
<style>
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
.card-kelas .tanda {
    display: none; align-self: flex-start;
    background: var(--biru); color: #fff; font-size: 10px; font-weight: 700;
    padding: 2px 8px; border-radius: 999px; letter-spacing: .5px;
}
.card-kelas.aktif .tanda { display: inline-block; }

.aksi-baris { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; margin: 0 0 12px; }
.aksi-kiri { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.btn-kecil { padding: 7px 14px; font-size: 12.5px; border-radius: 8px; }
.btn-kecil:disabled { opacity: .4; cursor: not-allowed; }
.cek { width: 16px; height: 16px; accent-color: var(--biru); cursor: pointer; }
.cek:disabled { cursor: not-allowed; opacity: .4; }
#pilih-semua:checked { accent-color: var(--biru); }

.modal-overlay {
    display: none; position: fixed; inset: 0; z-index: 100;
    background: rgba(13, 71, 161, .35); align-items: center; justify-content: center; padding: 16px;
}
.modal-overlay.buka { display: flex; }
.modal {
    background: #fff; border: 2px solid var(--biru); border-radius: 12px;
    padding: 22px; width: 100%; max-width: 380px;
    box-shadow: 0 8px 30px rgba(13,71,161,.25);
}
.modal h3 { margin: 0 0 14px; font-size: 16px; color: var(--biru-tua); text-align: center; }
.modal label { display: flex; flex-direction: column; gap: 5px; font-size: 12.5px; color: var(--abu); font-weight: 600; margin-bottom: 10px; }
.modal input, .modal textarea {
    width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 8px;
    font-size: 14px; font-family: inherit; resize: vertical; font-weight: 400;
}
.modal input[readonly] { background: var(--biru-muda); color: var(--teks); }
.modal .modal-btn { display: flex; justify-content: flex-end; gap: 10px; margin-top: 6px; }
.btn-batal { background: #e2e8f0; color: var(--teks); }
.btn-batal:hover { background: #cbd5e1; }
</style>
@endpush

@php
    $statusMap = [
        'belum' => ['Belum Ditentukan', '#94a3b8'],
        'kumpul' => ['Dikumpulkan', '#10b981'],
        'pinjam' => ['Sedang Dipinjam', '#f59e0b'],
        'tidak' => ['Tidak Bawa', '#ef4444'],
    ];
    // 'diambil' = penitipan hari ini dengan jam_ambil terisi
    $labelAksi = ['meminjam' => 'Meminjam', 'mengembalikan' => 'Mengembalikan', 'mengambil' => 'Mengambil'];
@endphp

@section('content')
<div class="kartu-head" style="margin-bottom:16px">
    <div>
        <h1 style="margin:0 0 4px">Pengeditan</h1>
        <p style="font-size:13px;color:var(--abu)">Perbarui status HP siswa: meminjam, mengembalikan &amp; mengambil.</p>
    </div>
</div>

@if (session('pesan'))<div class="msg">{{ session('pesan') }}</div>@endif

<div class="card">
    <h2 style="margin:0 0 4px">{{ $namaKelas ? 'Pilih Kelas' : 'Pilih Kelas Terlebih Dahulu' }}</h2>
    <p style="margin:0 0 14px;font-size:12.5px;color:var(--abu)">{{ $namaKelas ? 'Pindah kelas lain untuk melihat daftar siswanya.' : 'Daftar siswa hanya tampil setelah kelas dipilih.' }}</p>
    <div class="grid-kelas">
        @forelse ($kelasList as $k)
        <a href="{{ route('edit', ['kelas' => $k->id]) }}" class="card-kelas {{ $k->id === $kelasId ? 'aktif' : '' }}">
            <span class="tanda">✓ TERPILIH</span>
            <span class="nama">{{ $k->nama }}</span>
            <span class="jumlah">{{ $siswaCount[$k->id] ?? 0 }} Siswa</span>
        </a>
        @empty
        <span style="color:var(--abu);font-size:13px">Belum ada kelas.</span>
        @endforelse
    </div>
</div>

@if ($namaKelas && $rows->count())
<div class="card" style="margin-top:4px">
    <form method="post" action="{{ route('edit') }}" id="frm-edit">
        @csrf
        <input type="hidden" name="kelas" value="{{ $kelasId }}">
        <input type="hidden" name="aksi" id="in-aksi">
        <input type="hidden" name="keterangan" id="in-ket">
        <div class="aksi-baris">
            <h2 style="margin:0">Daftar Siswa — {{ $namaKelas }} ({{ $rows->count() }})</h2>
            <div class="aksi-kiri">
                @foreach ($labelAksi as $val => $label)
                <button type="button" class="btn btn-kecil" data-aksi="{{ $val }}" disabled>{{ $label }}</button>
                @endforeach
            </div>
        </div>
        <table>
            <tr>
                <th><input type="checkbox" id="pilih-semua" class="cek"></th>
                <th>No</th><th>NIS</th><th>Nama</th><th>Status</th>
            </tr>
            @foreach ($rows as $i => $r)
            @php
                $p = $r->penitipan->first();
                $st = $p->status ?? 'belum';
                $diambil = $p && $p->jam_ambil !== null;
                $statusLabel = $diambil ? 'Diambil' : $statusMap[$st][0];
                $warna = $diambil ? '#6b7280' : $statusMap[$st][1];
                // aksi yang boleh: kumpul -> meminjam/mengambil, pinjam -> mengembalikan
                $boleh = $diambil ? [] : match ($st) {
                    'kumpul' => ['meminjam', 'mengambil'],
                    'pinjam' => ['mengembalikan'],
                    default => [],
                };
            @endphp
            <tr data-boleh="{{ implode(',', $boleh) }}">
                <td><input type="checkbox" class="cek pilih" name="siswa[]" value="{{ $r->id }}" data-nama="{{ $r->nama }}" @if(!$boleh) disabled @endif></td>
                <td>{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                <td><code style="font-family:Consolas,monospace">{{ $r->nis }}</code></td>
                <td style="font-weight:600;font-size:13.5px">{{ $r->nama }}</td>
                <td><span class="badge" style="background:{{ $warna }}">{{ $statusLabel }}</span></td>
            </tr>
            @endforeach
        </table>
    </form>
</div>
@elseif ($namaKelas)
<div class="card" style="margin-top:4px"><p style="margin:0;color:var(--abu);font-size:13px">Belum ada siswa di kelas ini.</p></div>
@endif

{{-- popup keterangan --}}
<div class="modal-overlay" id="modal">
    <div class="modal">
        <h3 id="md-judul">—</h3>
        <label>Nama
            <input type="text" id="md-nama" readonly>
        </label>
        <label>Kelas
            <input type="text" id="md-kelas" value="{{ $namaKelas }}" readonly>
        </label>
        <label>Keterangan
            <textarea id="md-ket" rows="3" maxlength="255" placeholder="Contoh: Digunakan untuk keperluan orang tua."></textarea>
        </label>
        <div class="modal-btn">
            <button type="button" class="btn btn-batal" onclick="tutupModal()">Batal</button>
            <button type="button" class="btn" id="md-submit">Submit</button>
        </div>
    </div>
</div>

<script>
(function () {
    var modal = document.getElementById('modal');
    var inAksi = document.getElementById('in-aksi');
    var namaEl = document.getElementById('md-nama');
    var ketEl = document.getElementById('md-ket');
    var aksiDipilih = '';
    var judul = { meminjam: 'Meminjam HP', mengembalikan: 'Mengembalikan HP', mengambil: 'Mengambil HP' };

    function tercentang() {
        return Array.prototype.slice.call(document.querySelectorAll('.pilih:checked'));
    }

    function segarkan() {
        var cek = tercentang();
        document.querySelectorAll('.btn-kecil[data-aksi]').forEach(function (b) {
            var aksi = b.dataset.aksi;
            // tombol aktif hanya jika semua siswa tercentang mengizinkan aksi ini
            var boleh = cek.length > 0 && cek.every(function (c) {
                return c.closest('tr').dataset.boleh.split(',').indexOf(aksi) !== -1;
            });
            b.disabled = !boleh;
        });
    }

    document.querySelectorAll('.pilih').forEach(function (c) {
        c.addEventListener('change', segarkan);
    });

    var ps = document.getElementById('pilih-semua');
    if (ps) ps.addEventListener('change', function () {
        document.querySelectorAll('input.pilih:not(:disabled)').forEach(function (c) { c.checked = ps.checked; });
        segarkan();
    });

    document.querySelectorAll('.btn-kecil[data-aksi]').forEach(function (b) {
        b.addEventListener('click', function () {
            if (b.disabled) return;
            aksiDipilih = b.dataset.aksi;
            var cek = tercentang();
            inAksi.value = aksiDipilih;
            document.getElementById('md-judul').textContent = judul[aksiDipilih];
            namaEl.value = cek.map(function (c) { return c.dataset.nama; }).join(', ');
            ketEl.value = '';
            modal.classList.add('buka');
        });
    });

    document.getElementById('md-submit').addEventListener('click', function () {
        document.getElementById('in-ket').value = ketEl.value;
        document.getElementById('frm-edit').submit();
    });

    window.tutupModal = function () {
        modal.classList.remove('buka');
        inAksi.value = '';
    };

    modal.addEventListener('click', function (e) {
        if (e.target === modal) tutupModal();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') tutupModal();
    });

    segarkan();
})();
</script>
@endsection
