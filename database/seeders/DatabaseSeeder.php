<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $kelas = collect([
            Kelas::create(['nama' => 'X PPLG 1']),
            Kelas::create(['nama' => 'X PPLG 2']),
        ]);

        $akun = [
            ['Administrator', 'admin', 'admin', null, 'admin123'],
            ['Raka Wibowo', 'raka', 'km', $kelas[0]->id, 'admin123'],
            ['Dewi Lestari', 'dewi', 'km', $kelas[1]->id, 'admin123'],
            ['Bu Sari', 'sari', 'guru', null, 'admin123'],
        ];
        foreach ($akun as [$nama, $username, $role, $kelasId, $pass]) {
            User::create([
                'nama' => $nama, 'role' => $role, 'kelas_id' => $kelasId,
                'username' => $username, 'password' => Hash::make($pass),
            ]);
        }

        $siswa = [
            ['2401', 'Kevin Ardiansyah'], ['2402', 'Jibril Ramadhan'], ['2403', 'Akbar Maulana'],
            ['2404', 'Kanza Aulia'], ['2405', 'Rizky Pratama'], ['2406', 'Andi Saputra'],
            ['2407', 'Bunga Rahma'], ['2408', 'Citra Dewi'], ['2409', 'Dimas Aditya'],
            ['2410', 'Eka Putri'], ['2411', 'Fajar Nugroho'], ['2412', 'Gita Safira'],
            ['2413', 'Hendra Wijaya'], ['2414', 'Indah Permata'], ['2415', 'Joko Santoso'],
            ['2416', 'Kirana Ayu'], ['2417', 'Lukman Hakim'], ['2418', 'Maya Anggraini'],
            ['2419', 'Naufal Hidayat'], ['2420', 'Oktaviani'], ['2421', 'Putra Wijaya'],
            ['2422', 'Qonita Salsabila'], ['2423', 'Reza Fahlevi'], ['2424', 'Sinta Bella'],
            ['2425', 'Taufik Rahman'], ['2426', 'Umi Kalsum'], ['2427', 'Vina Amelia'],
            ['2428', 'Wahyu Setiawan'], ['2429', 'Xena Maharani'], ['2430', 'Yoga Pratama'],
            ['2431', 'Zahra Nur'], ['2432', 'Bagas Saputra'], ['2433', 'Farhan Akbar'],
            ['2434', 'Nadia Zahira'], ['2435', 'Ilham Ramadhan'], ['2436', 'Salma Nabila'],
        ];
        foreach ($siswa as [$nis, $nama]) {
            Siswa::create(['nis' => $nis, 'nama' => $nama, 'kelas_id' => $kelas[0]->id]);
        }
    }
}
