<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProdukSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('app/produk.csv');

        if (!file_exists($path)) {
            $this->command->error("File CSV tidak ditemukan: $path");
            return;
        }

        $csv = array_map('str_getcsv', file($path));
        $header = array_map('trim', explode(',', file($path)[0]));

        array_shift($csv); // Hapus baris header

        foreach ($csv as $row) {
            $data = array_combine($header, $row);

            // Cek apakah nama_produk sudah ada
            $exists = DB::table('produk')->where('nama_produk', $data['nama_produk'])->exists();

            if ($exists) {
                $this->command->warn("Lewati: {$data['nama_produk']} (sudah ada)");
                continue;
            }

            DB::table('produk')->insert([
                'id_kategori'         => (int) $data['id_kategori'],
                'kode_produk'         => $data['kode_produk'],
                'nama_produk'         => $data['nama_produk'],
                'merk'                => $data['merk'],
                'harga_beli'          => (int) $data['harga_beli'],
                'diskon'              => (int) $data['diskon'],
                'harga_jual_eceran'   => (int) $data['harga_jual_eceran'],
                'harga_jual_grosir'   => (int) $data['harga_jual_grosir'],
                'stok'                => (int) $data['stok'],
                'gambar'              => $data['gambar'],
                'created_at'          => $data['created_at'],
                'updated_at'          => $data['updated_at'],
            ]);
        }

        $this->command->info("Import selesai. Data duplikat berdasarkan nama_produk dilewati.");
    }
}
