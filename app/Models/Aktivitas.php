<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Aktivitas extends Model
{
    protected $table = 'aktivitas';
    protected $fillable = ['user_id', 'aksi', 'deskripsi'];
    public $timestamps = false; 

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function catat($aksi, $deskripsi)
    {
        self::create([
            'user_id' => Auth::id(),
            'aksi' => $aksi,
            'deskripsi' => $deskripsi,
        ]);
    }
}
