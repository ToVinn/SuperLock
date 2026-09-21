@extends('layouts.app')

@section('title', 'Kelola User')

@push('head')
<style>
.user-grid { display: grid; gap: 20px; grid-template-columns: 5fr 7fr; align-items: start; }
@media (max-width: 1100px) { .user-grid { grid-template-columns: 1fr; } }

.form-user { display: grid; gap: 12px; }
.form-user label { display: flex; flex-direction: column; gap: 5px; font-size: 12.5px; color: var(--abu); font-weight: 600; }
.form-user .kiri { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
@media (max-width: 560px) { .form-user .kiri { grid-template-columns: 1fr; } }
.form-user select, .form-user input { width: 100%; }

.avatar-user {
  width: 36px; height: 36px; border-radius: 999px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-weight: 700; font-size: 13px;
}
.badge-role { padding: 4px 12px; border-radius: 999px; font-size: 11.5px; font-weight: 600; white-space: nowrap; text-transform: uppercase; letter-spacing: .4px; }
.btn-kecil { padding: 7px 14px; font-size: 12.5px; border-radius: 8px; }
.frm-reset { display: none; align-items: center; gap: 8px; }
.frm-reset.buka { display: flex; }
.frm-reset input { padding: 7px 12px; font-size: 13px; }
</style>
@endpush

@section('content')
@php
  $warnaRole = [
    'admin' => ['#dbeafe', '#1d4ed8', '#1d4ed8'],
    'km'    => ['#ede9fe', '#6d28d9', '#6d28d9'],
    'guru'  => ['#fef3c7', '#b45309', '#b45309'],
  ];
@endphp

<div class="kartu-head" style="margin-bottom:16px">
  <div>
    <h1 style="margin:0 0 4px">Kelola User</h1>
    <p style="font-size:13px;color:var(--abu)">Tambah akun baru &amp; lihat daftar semua pengguna ({{ $users->count() }} akun).</p>
  </div>
</div>

@if (session('pesan'))<div class="msg">{{ session('pesan') }}</div>@endif

<div class="user-grid">

  {{-- ===== FORM TAMBAH ===== --}}
  <form method="post" action="{{ route('users') }}" class="card form-user">
    @csrf
    <h2 style="margin:0">Tambah User Baru</h2>
    <div class="kiri">
      <label>Nama Lengkap<input type="text" name="nama" required placeholder="cth: Budi Santoso"></label>
      <label>Username<input type="text" name="username" required placeholder="cth: budi"></label>
    </div>
    <div class="kiri">
      <label>Password<input type="text" name="password" required minlength="6" placeholder="min. 6 karakter"></label>
      <label>Role
        <select name="role" required onchange="this.form.kelas_id.disabled = this.value !== 'km'">
          <option value="km">Ketua Kelas (KM)</option>
          <option value="guru">Guru</option>
          <option value="admin">Admin</option>
        </select>
      </label>
    </div>
    <label>Kelas <small style="font-weight:400">(khusus role KM)</small>
      <select name="kelas_id">
        <option value="">-- Tidak ada --</option>
        @foreach ($kelasList as $k)
          <option value="{{ $k->id }}">{{ $k->nama }}</option>
        @endforeach
      </select>
    </label>
    <button type="submit">+ Tambah User</button>
  </form>

  <script>
    var frm = document.querySelector('.form-user');
    if (frm) frm.kelas_id.disabled = frm.role.value !== 'km';
  </script>

  {{-- ===== DAFTAR USER ===== --}}
  <div class="card">
    <h2 style="margin:0 0 12px">Daftar User</h2>
    <table>
      <tr><th>Nama</th><th>Role</th><th>Kelas</th><th>Aksi</th></tr>
      @foreach ($users as $u)
        @php [$bg, $fg] = $warnaRole[$u->role]; @endphp
      <tr>
        <td>
          <div style="display:flex;align-items:center;gap:10px">
            <span class="avatar-user" style="background:{{ $fg }}">{{ strtoupper(substr($u->nama, 0, 1)) }}</span>
            <div style="min-width:0">
              <div style="font-weight:600;font-size:13.5px">{{ $u->nama }}</div>
              <div style="font-size:11.5px;color:var(--abu)">{{ '@'.$u->username }}</div>
            </div>
          </div>
        </td>
        <td><span class="badge-role" style="background:{{ $bg }};color:{{ $fg }};border:1px solid {{ $fg }}33">{{ $u->role }}</span></td>
        <td style="font-size:12.5px;color:var(--abu)">{{ $u->kelas?->nama ?? '—' }}</td>
        <td>
          <button type="button" class="btn-kecil" onclick="var f=this.nextElementSibling;f.classList.toggle('buka');this.textContent=f.classList.contains('buka')?'Batal':'Reset Password'">Reset Password</button>
          <form method="post" action="{{ route('users.reset', $u) }}" class="frm-reset" style="margin-top:8px">
            @csrf
            <input type="password" name="password" required minlength="6" placeholder="password baru">
            <button type="submit" class="btn-kecil">Simpan</button>
          </form>
        </td>
      </tr>
      @endforeach
    </table>
  </div>

</div>
@endsection
