<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Produk;
use App\Models\StokBarang;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class PembelianController extends Controller
{
    public function index()
    {
        $supplier = Supplier::orderBy('nama')->get();

        return view('pembelian.index', compact('supplier'));
    }

    public function data()
    {
        $pembelian = Pembelian::orderBy('id_pembelian', 'desc')->get();

        return datatables()
            ->of($pembelian)
            ->addIndexColumn()
            ->addColumn('total_item', function ($pembelian) {
                return format_uang($pembelian->total_item);
            })
            ->addColumn('total_harga', function ($pembelian) {
                return 'Rp. ' . format_uang($pembelian->total_harga);
            })
            ->addColumn('bayar', function ($pembelian) {
                return 'Rp. ' . format_uang($pembelian->bayar);
            })
            ->addColumn('tanggal', function ($pembelian) {
                return tanggal_indonesia($pembelian->created_at, false);
            })
            ->addColumn('supplier', function ($pembelian) {
                return $pembelian->supplier->nama;
            })
            ->editColumn('diskon', function ($pembelian) {
                return $pembelian->diskon . '%';
            })
            ->addColumn('aksi', function ($pembelian) {
                return '
                <div class="btn-group">
                    <button onclick="showDetail(`' . route('pembelian.show', $pembelian->id_pembelian) . '`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-eye"></i></button>
                    <button onclick="deleteData(`' . route('pembelian.destroy', $pembelian->id_pembelian) . '`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                </div>
                ';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function create($id)
    {
        $pembelian = new Pembelian();
        $pembelian->id_supplier = $id;
        $pembelian->total_item  = 0;
        $pembelian->total_harga = 0;
        $pembelian->diskon      = 0;
        $pembelian->bayar       = 0;
        $pembelian->save();

        session(['id_pembelian' => $pembelian->id_pembelian]);
        session(['id_supplier' => $pembelian->id_supplier]);

        return redirect()->route('pembelian_detail.index');
    }

    public function store(Request $request)
    {
        try {

            DB::transaction(function () use ($request) {

                $pembelian = Pembelian::findOrFail($request->id_pembelian);

                $pembelian->update([
                    'total_item' => $request->total_item,
                    'total_harga' => $request->total,
                    'diskon' => $request->diskon,
                    'bayar' => $request->bayar,
                ]);

                $produk = Produk::lockForUpdate()
                    ->findOrFail($request->id_produk_pembelian);

                $hargaBeli = (int) $pembelian->total_harga / $pembelian->total_item;
                $jumlah = (int) ($request->total_item ?? 1);

                $pembelian_detail = PembelianDetail::where('id_pembelian', $request->id_pembelian)->first();
                $pembelian_detail_update = PembelianDetail::where('id_pembelian', $request->id_pembelian);

                $pembelian_detail_update->update([
                    'id_produk' => $produk->id_produk,
                    'harga_beli' => $hargaBeli,
                    'jumlah' => $jumlah,
                    'subtotal' => $hargaBeli * $jumlah,
                ]);
                if ($pembelian_detail) {
                    StokBarang::create([
                        'id_produk' => $produk->id_produk,
                        'id_pembelian_detail' => $pembelian_detail->id_pembelian_detail,
                        'harga_beli' => $hargaBeli,
                        'stok_masuk' => $jumlah,
                        'stok_sisa' => $jumlah,
                    ]);

                    $stokLama = StokBarang::where('id_produk', $produk->id_produk)
                        ->sum('stok_sisa');
                }

                $avgLama  = $produk->harga_beli_avg ?? 0;

                $totalNilaiLama = $stokLama * $avgLama;
                $totalNilaiBaru = $jumlah * $hargaBeli;

                $totalStok = $stokLama;

                $avgBaru = $totalStok > 0
                    ? ($totalNilaiLama + $totalNilaiBaru) / $totalStok
                    : $hargaBeli;

                $produk->update([
                    'stok' => $totalStok,
                    'harga_beli_terakhir' => $hargaBeli,
                    'harga_beli_avg' => round($avgBaru, 2)
                ]);
            });

            return redirect()->route('pembelian.index');
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $detail = PembelianDetail::with('produk')->where('id_pembelian', $id)->get();

        return datatables()
            ->of($detail)
            ->addIndexColumn()
            ->addColumn('kode_produk', function ($detail) {
                return '<span class="label label-success">' . $detail->produk->kode_produk . '</span>';
            })
            ->addColumn('nama_produk', function ($detail) {
                return $detail->produk->nama_produk;
            })
            ->addColumn('harga_beli', function ($detail) {
                return 'Rp. ' . format_uang($detail->harga_beli);
            })
            ->addColumn('jumlah', function ($detail) {
                return format_uang($detail->jumlah);
            })
            ->addColumn('subtotal', function ($detail) {
                return 'Rp. ' . format_uang($detail->subtotal);
            })
            ->rawColumns(['kode_produk'])
            ->make(true);
    }

    public function destroy($id)
    {
        $pembelian = Pembelian::find($id);
        $detail    = PembelianDetail::where('id_pembelian', $pembelian->id_pembelian)->get();
        foreach ($detail as $item) {
            $produk = Produk::find($item->id_produk);
            if ($produk) {
                $produk->stok -= $item->jumlah;
                $produk->update();
            }
            $item->delete();
        }

        $pembelian->delete();

        return response(null, 204);
    }
}
