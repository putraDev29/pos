<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LevelHarga extends Model
{
    use HasFactory;

    protected $table = 'level_harga';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function satuan()
    {
        return $this->belongsTo(
            Satuan::class,
            'satuan_id', // FK di tabel ini
            'id'         // PK di satuan
        );
    }
}
