<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Satuan extends Model
{
    use HasFactory;

    protected $table = 'satuan';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function produkLevelHarga()
    {
        return $this->hasMany(
            ProdukHarga::class,
            'satuan_id',
            'id'
        );
    }
}
