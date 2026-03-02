<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Pengeluaran;
use App\Models\Penjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

class LaporanPembelianController extends Controller
{
    public function index(Request $request)
    {
        $tanggalAwal = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
        $tanggalAkhir = date('Y-m-d');

        if ($request->has('tanggal_awal') && $request->tanggal_awal != "" && $request->has('tanggal_akhir') && $request->tanggal_akhir) {
            $tanggalAwal = $request->tanggal_awal;
            $tanggalAkhir = $request->tanggal_akhir;
        }

        return view('laporanpembelian.index', compact('tanggalAwal', 'tanggalAkhir'));
    }

    public function getData($awal, $akhir)
    {

        $no = 1;
        $data = [];
        $total_pembelian_semua = 0;

        $awal  = date('Y-m-d', strtotime($awal));
        $akhir = date('Y-m-d', strtotime($akhir));

        $results = DB::table('pembelian as p')
            ->join('pembelian_detail as pd', 'p.id_pembelian', '=', 'pd.id_pembelian')
            ->join('produk as pr', 'pd.id_produk', '=', 'pr.id_produk')
            ->whereBetween('p.created_at', [
                $awal . ' 00:00:00',
                $akhir . ' 23:59:59'
            ])
            ->selectRaw("
            DATE(p.created_at) as tanggal,
            pr.nama_produk,
            SUM(pd.jumlah) as total_beli,
            SUM(pd.jumlah * pd.harga_beli) as total_pembelian
        ")
            ->groupBy(
                DB::raw('DATE(p.created_at)'),
                'pr.id_produk',
                'pr.nama_produk'
            )
            ->orderBy('tanggal', 'desc')
            ->get();

        foreach ($results as $row) {

            $total_pembelian_semua += $row->total_pembelian;

            $data[] = [
                'DT_RowIndex'      => $no++,
                'tanggal'          => date('d M Y', strtotime($row->tanggal)),
                'produk'           => $row->nama_produk,
                'total_terjual'    => $row->total_beli, // kolom ini dipakai ulang di tabel
                'total_penjualan'  => format_uang($row->total_pembelian),
            ];
        }

        // === BARIS TOTAL PEMBELIAN KESELURUHAN ===
        $data[] = [
            'DT_RowIndex'      => '',
            'tanggal'          => '',
            'produk'           => '',
            'total_terjual'    => 'Total Pembelian',
            'total_penjualan'  => format_uang($total_pembelian_semua),
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

        return $pdf->stream('Laporan-pendapatan-' . date('Y-m-d-his') . '.pdf');
    }
}
