<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AdminWisata extends Authenticatable
{
    use HasFactory;

    protected $table = 'admin_wisata';

    protected $primaryKey = 'id_admin';

    // ✅ karena DB kamu int8 (auto increment)
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nama',
        'email',
        'password',
        'role'
    ];

    protected $hidden = [
        'password',
    ];
}