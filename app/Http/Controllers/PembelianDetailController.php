<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Produk;
use App\Models\ProdukHarga;
use App\Models\StokBarang;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembelianDetailController extends Controller
{
    public function index()
    {
        $id_pembelian = session('id_pembelian');
        $produk = Produk::orderBy('id_produk')->get();
        $supplier = Supplier::find(session('id_supplier'));
        $diskon = Pembelian::find($id_pembelian)->diskon ?? 0;

        if (! $supplier) {
            abort(404);
        }

        return view('pembelian_detail.index', compact('id_pembelian', 'produk', 'supplier', 'diskon'));
    }

    public function data($id)
    {
        $detail = PembelianDetail::with('produk')
            ->where('id_pembelian', $id)
            ->get();

        $data = array();
        $total = 0;
        $total_item = 0;


        foreach ($detail as $item) {

            $satuan = ProdukHarga::with('satuan')
                ->where('produk_id', $item->id_produk)
                ->get();

            $satuanOptions = '';
            $usedSatuan = []; // 🔒 penanda nama satuan yang sudah muncul

            foreach ($satuan as $s) {

                $namaSatuan = $s->satuan->nama_satuan;

                // ❌ skip jika nama satuan sudah pernah ditampilkan
                if (in_array($namaSatuan, $usedSatuan)) {
                    continue;
                }

                $usedSatuan[] = $namaSatuan;

                $selected = ($item->produk_id ?? null) == $s->id ? 'selected' : '';

                $satuanOptions .= '
        <option 
            value="' . $s->satuan_id . '"
            data-konversi="' . $s->konversi . '"
            data-harga="' . $s->harga . '"
            ' . $selected . '>
            ' . $namaSatuan . '
        </option>
    ';
            }

            $row = array();

            $row['kode_produk'] =
                '<span class="label label-success kode-produk" ' .
                'data-id="' . $item->id_pembelian_detail . '">' .
                e($item->produk['nama_produk']) .
                '</span>';

            $row['nama_produk'] = $item->produk['nama_produk'];

            // ✅ Kolom harga beli (TERPISAH)
            $row['harga_beli'] = '
        <input type="text" 
            class="form-control input-sm harga" 
            data-id="' . $item->id_pembelian_detail . '" 
            value="' . 0 . '">
    ';

            // ✅ Kolom satuan (TERPISAH)
            $row['satuan'] = '
        <select class="form-control input-sm satuan" 
            data-id="' . $item->id_pembelian_detail . '">
            ' . $satuanOptions . '
        </select>
    ';

            $row['jumlah'] = '
        <input type="text" 
            class="form-control input-sm quantity" 
            data-id="' . $item->id_pembelian_detail . '" 
            value="' . $item->jumlah . '">
    ';

            $row['subtotal'] = 'Rp. ' . format_uang($item->subtotal);

            $row['aksi'] = '
        <div class="btn-group">
            <button onclick="deleteData(`'
                . route('pembelian_detail.destroy', $item->id_pembelian_detail) .
                '`)" class="btn btn-xs btn-danger btn-flat">
                <i class="fa fa-trash"></i>
            </button>
        </div>
    ';

            $data[] = $row;

            $total += $item->subtotal;
            $total_item += $item->jumlah;
        }
        $data[] = [
            'kode_produk' => '
                <div class="total hide">' . $total . '</div>
                <div class="total_item hide">' . $total_item . '</div>',
            'nama_produk' => '',
            'harga_beli'  => '',
            'satuan'      => '',
            'jumlah'      => '',
            'subtotal'    => '',
            'aksi'        => '',
        ];

        return datatables()
            ->of($data)
            ->addIndexColumn()
            ->rawColumns(['aksi', 'kode_produk', 'harga_beli', 'jumlah', 'satuan'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $produk = Produk::where('id_produk', $request->id_produk)->first();
        if (! $produk) {
            return response()->json('Data gagal disimpan', 400);
        }

        $hargabeli = StokBarang::where('id_produk', $request->id_produk)
            ->latest('id')
            ->first();

        $detail = new PembelianDetail();
        $detail->id_pembelian = $request->id_pembelian;
        $detail->id_produk = $produk->id_produk;
        $detail->harga_beli = $hargabeli->harga_beli ?? 0;
        $detail->jumlah = 1;
        $detail->subtotal = 0;
        $detail->save();

        return response()->json('Data berhasil disimpan', 200);
    }

    // public function store(Request $request)
    // {

    // }

    public function update(Request $request, $id)
    {
        $detail = PembelianDetail::findOrFail($id);

     
        // 🔥 Ambil satuan yang dipilih user
        $produkHarga = ProdukHarga::with('satuan')
            ->where('satuan_id', $request->satuan_id)
            ->where('produk_id', $detail->id_produk)
            ->first();

        $konversi = $produkHarga->konversi;

        // ✅ Simpan satuan aktif
        $detail->id_produk = $produkHarga->produk_id;

        // ✅ Update harga sesuai satuan
        $detail->harga_beli = $request->harga_beli;

        // ✅ Simpan jumlah dalam satuan terkecil
        $jumlahInput = $request->jumlah;
        $detail->jumlah = (int) ($jumlahInput * $konversi);
        // var_dump($jumlahInput);

        // ✅ Hitung subtotal berdasarkan qty tampilan
        $detail->subtotal = $request->harga_beli * $jumlahInput;

        $detail->save();

        return response()->json('ok');
    }

    public function destroy($id)
    {
        $detail = PembelianDetail::find($id);
        $detail->delete();

        return response(null, 204);
    }

    public function loadForm($diskon, $total)
    {
        $bayar = $total - ($diskon / 100 * $total);
        $data  = [
            'totalrp' => format_uang($total),
            'bayar' => $bayar,
            'bayarrp' => format_uang($bayar),
            'terbilang' => ucwords(terbilang($bayar) . ' Rupiah')
        ];

        return response()->json($data);
    }
}
