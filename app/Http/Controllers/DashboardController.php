<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Member;
use App\Models\Pembelian;
use App\Models\Pengeluaran;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $kategori = Kategori::count();
        $produk   = Produk::count();
        $supplier = Supplier::count();
        $member   = Member::count();

        $tanggal_awal  = date('Y-m-01');
        $tanggal_akhir = date('Y-m-d');

        $data_tanggal    = [];
        $data_pendapatan = [];
        $total_laba_semua = 0;

        $results = DB::table('penjualan_detail as pd')
            ->join('penjualan as p', 'pd.id_penjualan', '=', 'p.id_penjualan')
            ->leftJoin('stok_barang as sb', 'pd.id_produk', '=', 'sb.id_produk')
            ->selectRaw("
        DATE(p.created_at) as tanggal,
        SUM((pd.harga_jual_eceran - COALESCE(sb.harga_beli,0)) * pd.jumlah) as laba
    ")
            ->whereBetween('p.created_at', [
                $tanggal_awal . ' 00:00:00',
                $tanggal_akhir . ' 23:59:59'
            ])
            ->groupBy(DB::raw('DATE(p.created_at)'))
            ->orderBy('tanggal', 'asc')
            ->get();

        foreach ($results as $row) {

            $data_tanggal[]    = date('d', strtotime($row->tanggal));
            $data_pendapatan[] = $row->laba;
        }

        if (auth()->user()->level == 1) {
            return view('admin.dashboard', compact(
                'kategori',
                'produk',
                'supplier',
                'member',
                'tanggal_awal',
                'tanggal_akhir',
                'data_tanggal',
                'data_pendapatan'
            ));
        } else {
            return view('kasir.dashboard');
        }
    }
}
