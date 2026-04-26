<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class SuperAdmin extends Authenticatable
{
    use HasFactory;

    protected $table = 'super_admin';
    protected $primaryKey = 'id_superadmin';
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