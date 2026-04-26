<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destinasi extends Model
{
    use HasFactory;

    protected $table = 'destinasi';
    protected $primaryKey = 'id_destinasi';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nama',
        'deskripsi',
        'lokasi',
        'alamat_lengkap',
        'weekday',
        'weekend',
        'harga_tiket_weekday',
        'harga_tiket_weekend',
        'id_kategori',
        'foto',
        'created_by_id',
        'created_by_role'
    ];

    public function getRouteKeyName()
    {
        return 'id_destinasi';
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }
}