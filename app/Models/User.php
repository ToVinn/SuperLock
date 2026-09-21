<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['nama', 'role', 'kelas_id', 'username', 'password'];
    protected $hidden = ['password'];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }
}
