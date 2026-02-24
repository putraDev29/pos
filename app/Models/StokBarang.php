<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokBarang extends Model
{
    protected $table = 'stok_barang';

    protected $fillable = [
        'id_produk',
        'id_pembelian_detail',
        'harga_beli',
        'stok_masuk',
        'stok_sisa',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk');
    }

    public function pembelianDetail()
    {
        return $this->belongsTo(PembelianDetail::class, 'id_pembelian_detail');
    }
}
