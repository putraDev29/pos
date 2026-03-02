<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenjualanDetail extends Model
{
    use HasFactory;

    protected $table = 'penjualan_detail';
    protected $primaryKey = 'id_penjualan_detail';
    protected $guarded = [];

    public function produk()
    {
        return $this->hasOne(Produk::class, 'id_produk', 'id_produk');
    }

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'id_penjualan');
    }


    public function produkLevelHarga()
    {
        return $this->belongsTo(
            ProdukHarga::class,
            'id_produk_level_harga', // FK di tabel ini
            'id'                     // PK di produk_level_harga
        );
    }
}
