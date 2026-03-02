<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdukHarga extends Model
{
    use HasFactory;

    protected $table = 'produk_level_harga';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'produk_id');
    }

    // Relasi ke Detail Penjualan
    public function penjualanDetails()
    {
        return $this->hasMany(PenjualanDetail::class, 'id_produk_level_harga');
    }

    public function satuan()
    {
        return $this->belongsTo(
            Satuan::class,
            'satuan_id', // FK di tabel ini
            'id'         // PK di satuan
        );
    }

    public function penjualanDetail()
    {
        return $this->hasMany(
            PenjualanDetail::class,
            'id_produk_level_harga',
            'satuan_id'
        );
    }

}
