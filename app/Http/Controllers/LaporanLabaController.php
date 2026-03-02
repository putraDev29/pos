<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Pengeluaran;
use App\Models\Penjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

class LaporanLabaController extends Controller
{
    public function index(Request $request)
    {
        $tanggalAwal = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
        $tanggalAkhir = date('Y-m-d');

        if ($request->has('tanggal_awal') && $request->tanggal_awal != "" && $request->has('tanggal_akhir') && $request->tanggal_akhir) {
            $tanggalAwal = $request->tanggal_awal;
            $tanggalAkhir = $request->tanggal_akhir;
        }

        return view('laporanlaba.index', compact('tanggalAwal', 'tanggalAkhir'));
    }

    public function getData($awal, $akhir)
    {
        $no = 1;
        $data = [];
        $total_laba_semua = 0;

        $awal  = date('Y-m-d', strtotime($awal));
        $akhir = date('Y-m-d', strtotime($akhir));

        $results = DB::table('penjualan_detail as pd')
            ->join('penjualan as p', 'pd.id_penjualan', '=', 'p.id_penjualan')
            ->join('produk as pr', 'pd.id_produk', '=', 'pr.id_produk')
            ->join('stok_barang as sb', 'pd.id_produk', '=', 'sb.id_produk')
            ->selectRaw("
            DATE(p.created_at) as tanggal,
            pr.nama_produk,
            SUM(pd.jumlah) as total_terjual,
            SUM(pd.subtotal) as total_penjualan,
            SUM((pd.harga_jual_eceran - sb.harga_beli) * pd.jumlah) as laba
        ")
            ->whereBetween('p.created_at', [$awal . ' 00:00:00', $akhir . ' 23:59:59'])
            ->groupBy(DB::raw('DATE(p.created_at)'), 'pr.nama_produk')
            ->orderBy('tanggal', 'desc')
            ->get();

        foreach ($results as $row) {

            $total_laba_semua += $row->laba;

            $data[] = [
                'DT_RowIndex'     => $no++,
                'tanggal'         => tanggal_indonesia($row->tanggal, false),
                'produk'          => $row->nama_produk,
                'total_terjual'   => $row->total_terjual,
                'total_penjualan' => format_uang($row->total_penjualan),
                'laba'            => format_uang($row->laba),
            ];
        }

        // Total Laba
        $data[] = [
            'DT_RowIndex'     => '',
            'tanggal'         => '',
            'produk'          => '',
            'total_terjual'   => '',
            'total_penjualan' => 'Total Laba',
            'laba'            => format_uang($total_laba_semua),
        ];

        return $data;
    }
    
    public function data($awal, $akhir)
    {
        $data = $this->getData($awal, $akhir);

        return datatables()
            ->of($data)
            ->make(true);
    }

    public function exportPDF($awal, $akhir)
    {
        $data = $this->getData($awal, $akhir);
        $pdf  = PDF::loadView('laporan.pdf', compact('awal', 'akhir', 'data'));
        $pdf->setPaper('a4', 'potrait');

        return $pdf->stream('Laporan-laba-' . date('Y-m-d-his') . '.pdf');
    }
}
