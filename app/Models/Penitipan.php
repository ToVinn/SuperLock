<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penitipan extends Model
{
    protected $table = 'penitipan';
    protected $fillable = [
        'siswa_id', 'tanggal', 'status',
        'jam_kumpul', 'jam_ambil', 'jam_pinjam', 'jam_kembali',
        'km_nama', 'guru_nama', 'keterangan',
    ];
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'tanggal' => 'date:Y-m-d',
            'jam_kumpul' => 'datetime:H:i',
            'jam_ambil' => 'datetime:H:i',
            'jam_pinjam' => 'datetime:H:i',
            'jam_kembali' => 'datetime:H:i',
        ];
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
