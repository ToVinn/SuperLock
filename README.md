<div align="center">
  <h1>NEPER — SuperLock</h1>
  <p>Sistem Penitipan & Peminjaman HP Sekolah Tingkat Lanjut</p>
  
  <p>
    <img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
    <img src="https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white" alt="Docker">
    <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
    <img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  </p>
</div>

---

**Neper PhoneLock** adalah platform manajemen penitipan gawai (HP) untuk lingkungan sekolah. Aplikasi ini merampingkan proses penitipan HP oleh siswa sebelum KBM, pencatatan pinjaman darurat, hingga pengambilan massal saat jam pulang dengan integrasi pemindai QR Code.

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Teknologi](#teknologi)
- [Instalasi & Konfigurasi](#instalasi--konfigurasi)
- [Alur Kerja (Workflow)](#alur-kerja-workflow)
- [Hak Akses (Role)](#hak-akses-role)
- [Referensi API](#referensi-api)
- [Struktur Proyek](#struktur-proyek)

---

## Fitur Utama

### 1. Dashboard & Monitoring Real-time
- **Analitik Harian**: Persentase penitipan, grafik tren (7/14/30 hari), dan ringkasan per kelas.
- **Log Aktivitas**: Tabel pantauan siswa yang sedang meminjam atau menitip HP secara real-time.
- **Ekspor Data**: Rincian log penitipan siap cetak (filter kelas & tanggal).

### 2. Manajemen Status Cepat (Guru)
- **Edit Massal**: Ubah status banyak siswa sekaligus (Pinjam / Kembali / Ambil).
- **Validasi Cerdas**: Tombol aksi beradaptasi dengan status siswa. Mencegah aksi ilegal.
- **Pencatatan Detail**: Otomatis menyimpan `guru_nama`, `keterangan`, dan `timestamp`.

### 3. Pengambilan via Pemindai QR
- **Pengambilan Kelas (KM)**: Pindai 1 QR Ketua Kelas untuk mengembalikan semua HP di kelas secara instan.
- **Pinjam/Ambil Individu**: Integrasi kamera untuk verifikasi instan.

### 4. Portal Khusus Ketua Kelas (KM)
- Antarmuka khusus untuk KM melakukan pengecekan pagi. Terkunci aman hanya untuk kelas masing-masing.

---

## Teknologi

- **Backend**: Laravel (PHP 8.4)
- **Database**: MySQL 8
- **Frontend**: Blade Templating, HTML5-QRCode, Chart.js
- **Environment**: Docker & Docker Compose

---

## Instalasi & Konfigurasi

### Prasyarat
- Docker & Docker Compose terinstal.

### Langkah-langkah

1. **Jalankan Container**
   ```bash
   docker compose up -d
   ```
2. **Migrasi Database**
   ```bash
   docker compose exec web php artisan migrate --force
   ```
3. **Data Awal (Seeder)**
   ```bash
   docker compose exec web php artisan db:seed
   ```

### Akses Layanan
- **Web App**: `http://localhost:8081`
- **phpMyAdmin**: `http://localhost:8082` (User: `root` / Pass: `root`)
- **MySQL Host**: Port `3308`

> **Catatan Developer**: Folder kode (`app`, `routes`, `resources`, `public`) ter-mount otomatis. Tidak perlu build ulang saat mengedit kode (kecuali `composer.json` atau `Dockerfile`).

### Akun Bawaan (Seeder)
| Nama | Username | Password | Peran |
|---|---|---|---|
| Administrator | `admin` | `admin123` | Admin |
| Raka Wibowo | `raka` | `admin123` | KM (X PPLG 1) |
| Dewi Lestari | `dewi` | `admin123` | KM (X PPLG 2) |
| Bu Sari | `sari` | `admin123` | Guru |

> Segera ubah kata sandi di lingkungan produksi.

---

## Alur Kerja (Workflow)

<details>
<summary><b>Klik untuk melihat urutan harian</b></summary>

1. **PAGI**: Ketua Kelas (KM) login, memvalidasi HP yang dikumpulkan, menandai status per siswa.
2. **SIANG (KBM)**: Guru scan QR siswa atau klik Edit Massal. Status menjadi Dipinjam.
3. **SIANG (Selesai KBM)**: Guru scan QR lagi. Status kembali Dikumpulkan.
4. **PULANG**: Guru scan QR khusus Ketua Kelas. Seluruh HP kelas ditandai Diambil.

</details>

---

## Hak Akses (Role)

| Modul | Admin | Guru | Ketua Kelas (KM) |
|---|:---:|:---:|:---:|
| Monitoring Dashboard | ✅ | ✅ | ❌ |
| Pengeditan Status | ✅ | ✅ | ❌ |
| Pengambilan (Scan QR) | ✅ | ✅ | ❌ |
| Pengecekan Pagi | ✅ (Pilih Kelas) | ❌ | ✅ (Kelas Sendiri) |
| Kelola Kartu QR | ✅ | ❌ | ❌ |
| Laporan & Rekap | ✅ | ❌ | ❌ |
| Data Siswa & Impor | ✅ | ❌ | ❌ |
| Manajemen User | ✅ | ❌ | ❌ |

---

## Referensi API

Endpoint API memvalidasi transisi state secara ketat (HTTP 409 Conflict jika ilegal):

- `GET /api?nis={nis}&tanggal={tanggal}` : Cek status terkini.
- `POST /api?nis={nis}&pinjam=1&guru={nama}` : Catat izin pinjam.
- `POST /api?nis={nis}&kembali=1&guru={nama}` : Catat pengembalian.
- `POST /api?nis={nis}&ambil=1` : Catat pengambilan.
- `POST /api?ambil_semua=1&km_id={km_id}` : Pengambilan massal via QR KM.

---

## Struktur Proyek

<details>
<summary><b>Lihat Struktur Direktori Utama</b></summary>

```text
├── app/Http/Controllers/
│   ├── MonitoringController.php   # Analitik & Tren
│   ├── EditController.php         # Manipulasi massal
│   ├── ApiController.php          # Endpoint QR Scanner
│   └── ... 
├── resources/views/
│   ├── layouts/app.blade.php      # Layout responsif per peran
│   ├── scan.blade.php             # Antarmuka kamera QR
│   ├── edit.blade.php             # Form aksi massal
│   └── ... 
├── routes/web.php                 # Ruting sistem & Middleware Role
└── public/css/style.css           # Styling
```
</details>

<br>
<div align="center">
  <i>Sistem manajemen penitipan perangkat digital modern dan akuntabel.</i>
</div>