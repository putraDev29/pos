<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Pengeluaran;
use App\Models\Penjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

class LaporanStokController extends Controller
{
    public function index(Request $request)
    {
        $tanggalAwal = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
        $tanggalAkhir = date('Y-m-d');

        if ($request->has('tanggal_awal') && $request->tanggal_awal != "" && $request->has('tanggal_akhir') && $request->tanggal_akhir) {
            $tanggalAwal = $request->tanggal_awal;
            $tanggalAkhir = $request->tanggal_akhir;
        }

        return view('laporanstok.index', compact('tanggalAwal', 'tanggalAkhir'));
    }

    public function getData($awal, $akhir)
    {
        $no = 1;
        $data = [];

        $awal  = date('Y-m-d', strtotime($awal));
        $akhir = date('Y-m-d', strtotime($akhir));

        $results = DB::table('produk as pr')

            // STOK AWAL (stok masuk sebelum periode)
            ->leftJoin('stok_barang as sb_awal', function ($join) use ($awal) {
                $join->on('pr.id_produk', '=', 'sb_awal.id_produk')
                    ->whereDate('sb_awal.created_at', '<', $awal);
            })

            // STOK MASUK (periode)
            ->leftJoin('stok_barang as sb_masuk', function ($join) use ($awal, $akhir) {
                $join->on('pr.id_produk', '=', 'sb_masuk.id_produk')
                    ->whereBetween('sb_masuk.created_at', [$awal . ' 00:00:00', $akhir . ' 23:59:59']);
            })

            // PENJUALAN (periode)
            ->leftJoin('penjualan_detail as pd', 'pr.id_produk', '=', 'pd.id_produk')
            ->leftJoin('penjualan as p', function ($join) use ($awal, $akhir) {
                $join->on('pd.id_penjualan', '=', 'p.id_penjualan')
                    ->whereBetween('p.created_at', [$awal . ' 00:00:00', $akhir . ' 23:59:59']);
            })

            ->selectRaw("
            pr.kode_produk,
            pr.nama_produk,
            COALESCE(SUM(DISTINCT sb_awal.stok_masuk),0) as stok_awal,
            COALESCE(SUM(DISTINCT sb_masuk.stok_masuk),0) as masuk,
            COALESCE(SUM(pd.jumlah),0) as keluar,
            COALESCE(SUM(DISTINCT sb_awal.stok_sisa),0) +
            COALESCE(SUM(DISTINCT sb_masuk.stok_sisa),0) as sisa_stok
        ")

            ->groupBy('pr.id_produk', 'pr.kode_produk', 'pr.nama_produk')
            ->orderBy('p.created_at', 'desc')
            ->get();

        foreach ($results as $row) {

            $data[] = [
                'DT_RowIndex' => $no++,
                'kode_produk' => $row->kode_produk,
                'nama_produk' => $row->nama_produk,
                'stok_awal'   => $row->stok_awal,
                'masuk'       => $row->masuk,
                'keluar'      => $row->keluar,
                'sisa_stok'   => $row->sisa_stok,
            ];
        }

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

        return $pdf->stream('Laporan-stok-' . date('Y-m-d-his') . '.pdf');
    }
}
