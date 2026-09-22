<p align="center">
    <b>NEPER — SuperLock</b><br>
    Sistem Penitipan &amp; Peminjaman HP Sekolah
</p>

---

**Neper PhoneLock** adalah aplikasi web untuk mengelola penitipan HP siswa di sekolah. Siswa menitipkan HP sebelum belajar, guru piket & ketua kelas (KM) mencatat statusnya, dan pengambilan kembali dicatat saat pulang. Semua tercatat lengkap dengan jam dan petugas.

## Daftar Isi

- [Fitur](#fitur)
  - [Monitoring](#monitoring)
  - [Pengeditan](#pengeditan)
  - [Pengambilan (Scan QR)](#pengambilan-scan-qr)
  - [Pengecekan KM](#pengecekan-km)
  - [Kartu QR](#kartu-qr)
  - [Rekap](#rekap)
  - [Kelola Siswa](#kelola-siswa)
  - [Kelola User](#kelola-user)
  - [API](#api)
- [Peran & Hak Akses](#peran--hak-akses)
- [Alur Harian](#alur-harian)
- [Status Penitipan](#status-penitipan)
- [Struktur Kode](#struktur-kode)
- [Instalasi](#instalasi)
- [Akun Bawaan (Seeder)](#akun-bawaan-seeder)

## Fitur

### Monitoring

Dashboard utama untuk admin & guru (`/`). Menampilkan statistik hari ini:

- Persentase siswa yang menitipkan HP (dengan donut chart)
- Jumlah yang sudah diambil, sedang dipinjam, dan tidak mengumpulkan
- Jam kumpul & ambil pertama
- Grafik tren 7/14/30 hari
- Rincian per kelas
- Tabel 6 siswa terbaru yang sedang menitip/pinjam
- Detail siswa per kelas (jam kumpul, pinjam, kembali, ambil, petugas) untuk cetak
- Filter tanggal & kelas

### Pengeditan

Menu khusus guru (`/edit`) untuk memperbarui status HP secara massal — menggantikan fungsi pengembalian lama:

1. **Pilih kelas terlebih dahulu** — daftar siswa baru muncul setelah kelas dipilih (kartu kelas aktif ditandai border biru + badge "TERPILIH")
2. **Centang siswa** (bisa pilih semua sekaligus)
3. **Pilih aksi** — tombol aktif otomatis mengikuti status siswa tercentang:

   | Status Saat Ini | Meminjam | Mengembalikan | Mengambil |
   |---|:---:|:---:|:---:|
   | Dikumpulkan | ✅ | ❌ | ✅ |
   | Sedang Dipinjam | ❌ | ✅ | ❌ |
   | Diambil | ❌ | ❌ | — |

4. **Isi keterangan di popup** — nama & kelas terisi otomatis
5. Submit → status diperbarui + riwayat tercatat

Aturan status divalidasi **dua lapis**: tombol yang tidak sesuai dinonaktifkan di browser, dan server menolak aksi ilegal (siswa dilewati dengan pesan jumlah yang gagal). Data yang disimpan per aksi:

- **Meminjam** → `status = pinjam`, `jam_pinjam`, `guru_nama`, `keterangan`
- **Mengembalikan** → `status = kumpul`, `jam_kembali`, `guru_nama`, `keterangan`
- **Mengambil** → `jam_ambil` terisi (status tampil "Diambil"), `guru_nama`, `keterangan`

### Pengambilan (Scan QR)

Halaman guru (`/scan`) untuk jam pulang, dengan kamera QR (html5-qrcode):

1. **Scan QR Ketua Kelas** — satu scan menandai semua HP satu kelas yang "Sudah Mengumpulkan" jadi "Sudah Diambil"
2. **Izin ambil / kembalikan per siswa** — input NIS manual atau scan kartu QR siswa, dengan nama guru piket:
   - *Izin Ambil* → siswa berstatus "Sudah Mengumpulkan" boleh pinjam HP (izin dicatat, `jam_pinjam`)
   - *Kembalikan* → siswa yang "Sedang Dipinjam" mengembalikan HP (`jam_kembali`)
   - Scan HP siswa "Tidak Mengumpulkan" memunculkan peringatan

### Pengecekan KM

Halaman ketua kelas (`/km`): KM memeriksa siapa yang menitipkan HP pagi hari dan menandai **Sudah Mengumpulkan** / **Tidak Mengumpulkan** per siswa, lalu simpan. Admin bisa memilih kelas mana pun; KM hanya bisa kelasnya sendiri. Nama KM otomatis tercatat.

### Kartu QR

Admin membuat kartu QR (`/kartu`) per kelas:

- QR per siswa (berisi NIS)
- QR khusus ketua kelas (berisi `KM:id`) untuk ambil massal
- Siap cetak

### Rekap

Admin melihat rekap harian (`/rekap`): jumlah siswa per kelas per status, persentase kepatuhan, dan sudah diambil — dengan filter tanggal.

### Kelola Siswa

Admin mengelola data siswa (`/siswa`):

- Tambah siswa (NIS + nama + kelas)
- Pilih kelas dulu → daftar siswa tampil
- Hapus terpilih / hapus semua (data penitipan ikut terhapus)
- Impor CSV (`NIS;Nama`), ekspor CSV
- NIS unik; baris duplikat otomatis dilewati saat impor

### Kelola User

Admin mengelola akun (`/users`): buat user dengan peran admin/guru/km (KM terikat kelas), dan reset password.

### API

Endpoint JSON untuk scanner (`/api`, GET/POST), dipakai halaman scan:

- `GET /api?nis=...&tanggal=...` — status penitipan siswa
- `POST /api?nis=...&pinjam=1&guru=...` — izin ambil (pinjam)
- `POST /api?nis=...&kembali=1&guru=...` — kembalikan
- `POST /api?nis=...&ambil=1` — tandai sudah diambil
- `POST /api?ambil_semua=1&km_id=...` — ambil massal satu kelas via QR KM

Semua aksi memvalidasi status dan menolak transisi ilegal (HTTP 409).

## Peran & Hak Akses

| Menu | Admin | Guru | KM |
|---|:---:|:---:|:---:|
| Monitoring | ✅ | ✅ | ❌ |
| Pengeditan | ✅ | ✅ | ❌ |
| Pengambilan (scan) | ✅ | ✅ | ❌ |
| Pengecekan KM | pilih kelas | ❌ | ✅ (kelas sendiri) |
| Kartu QR | ✅ | ❌ | ❌ |
| Rekap | ✅ | ❌ | ❌ |
| Kelola Siswa | ✅ | ❌ | ❌ |
| Kelola User | ✅ | ❌ | ❌ |

Proteksi lewat middleware `role:...` di `routes/web.php`.

## Alur Harian

```
PAGI  : KM cek kelas  →  siswa ditandai Sudah/Tidak Mengumpulkan
SIANG : Guru perlu HP?  →  Pengeditan/Scan: Meminjam (isi keterangan)
       Selesai pakai?  →  Pengeditan/Scan: Mengembalikan
PULANG: Guru scan QR KM →  semua HP kelas ditandai Diambil
       Rapi manual?    →  Pengeditan: Mengambil per siswa
```

## Status Penitipan

| Status | Arti | Kolom terkait |
|---|---|---|
| `belum` | Belum Ditentukan (KM belum cek) | — |
| `kumpul` | Dikumpulkan | `jam_kumpul`, `jam_ambil` (jika sudah diambil) |
| `pinjam` | Sedang Dipinjam | `jam_pinjam`, `jam_kembali` |
| `tidak` | Tidak Mengumpulkan | — |

Satu baris `penitipan` per siswa per tanggal (`unique: siswa_id + tanggal`).

## Struktur Kode

```
app/Http/Controllers/
  MonitoringController.php   # dashboard statistik & tren
  EditController.php         # pengeditan status massal (baru)
  KmController.php           # pengecekan pagi oleh ketua kelas
  ApiController.php          # API JSON untuk scanner QR
  SiswaController.php        # CRUD siswa + impor/ekspor CSV
  UserController.php         # kelola akun & reset password
  KartuController.php        # kartu QR per kelas
  RekapController.php        # rekap harian

resources/views/
  layouts/app.blade.php      # layout + sidebar per peran
  monitoring.blade.php       # dashboard
  edit.blade.php             # pengeditan (baru)
  km.blade.php               # pengecekan KM
  scan.blade.php             # pengambilan via QR
  kartu.blade.php            # kartu QR
  rekap.blade.php            # rekap harian
  siswa.blade.php            # kelola siswa
  users.blade.php            # kelola user

routes/web.php               # semua rute + middleware role
public/css/style.css         # seluruh styling
```

## Instalasi

Prasyarat: Docker Desktop.

```bash
docker compose up -d          # jalankan web (PHP 8.4 + Apache) & MySQL
docker compose exec web php artisan migrate --force
```

Aplikasi berjalan di **http://localhost:8081**, phpMyAdmin di **http://localhost:8082** (root/root), MySQL di port host **3308**.

Perubahan folder `app`, `routes`, `resources/views`, `public`, dan `database` langsung ter-mount ke container — tidak perlu rebuild. Jika `composer.json`/`Dockerfile` berubah, jalankan `docker compose up -d --build`.
