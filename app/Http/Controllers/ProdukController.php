<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Produk;
use App\Models\ProdukHarga;
use App\Models\Satuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

class ProdukController extends Controller
{
    public function index()
    {
        $kategori = Kategori::all()->pluck('nama_kategori', 'id_kategori');
        $satuan   = Satuan::orderBy('nama_satuan')->get();

        return view('produk.index', compact('kategori', 'satuan'));
    }

    public function data()
    {
        $produk = Produk::leftJoin('kategori', 'kategori.id_kategori', '=', 'produk.id_kategori')
            ->select('produk.*', 'kategori.nama_kategori')
            ->orderBy('kode_produk', 'asc')
            ->get();

        return datatables()
            ->of($produk)
            ->addIndexColumn()

            ->addColumn('select_all', function ($produk) {
                return '<input type="checkbox" name="id_produk[]" value="' . $produk->id_produk . '">';
            })

            ->addColumn('kode_produk', function ($produk) {
                return '<span class="label label-success">' . $produk->kode_produk . '</span>';
            })

            ->addColumn('harga_default', function ($produk) {
                $harga = ProdukHarga::where('produk_id', $produk->id_produk)
                    ->orderBy('konversi', 'asc')
                    ->first();

                return $harga ? format_uang($harga->harga_jual) : '-';
            })

            ->addColumn('harga_beli', function ($produk) {
                return format_uang($produk->harga_beli);
            })

            ->addColumn('stok', function ($produk) {
                return format_uang($produk->stok);
            })

            ->addColumn('aksi', function ($produk) {
                return '
                <div class="btn-group">
                    <button type="button" onclick="editForm(`' . route('produk.update', $produk->id_produk) . '`)" class="btn btn-xs btn-info btn-flat">
                        <i class="fa fa-pencil"></i>
                    </button>
                    <button type="button" onclick="deleteData(`' . route('produk.destroy', $produk->id_produk) . '`)" class="btn btn-xs btn-danger btn-flat">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>';
            })

            ->rawColumns(['aksi', 'kode_produk', 'select_all'])
            ->make(true);
    }

    public function show($id)
    {
        $produk = Produk::with('levelHarga')->find($id);
        return response()->json($produk);
    }

    // =============================
    // STORE (Tambah Produk)
    // =============================
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {

            $request->validate([
                'nama_produk' => 'required',
                'id_kategori' => 'required',
                'harga_beli'  => 'required|numeric',
                'stok'        => 'required|numeric',
                'satuan_id'   => 'required'
            ]);

            $produk = Produk::create([
                'nama_produk' => $request->nama_produk,
                'id_kategori' => $request->id_kategori,
                'merk'        => $request->merk,
                'harga_beli'  => $request->harga_beli,
                'stok'        => $request->stok,
            ]);

            // Upload gambar
            if ($request->hasFile('gambar')) {
                $file = $request->file('gambar');
                $namaFile = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads'), $namaFile);

                $produk->update([
                    'gambar' => 'uploads/' . $namaFile
                ]);
            }

            // Simpan level harga
            if ($request->level) {
                foreach ($request->level as $level) {
                    ProdukHarga::create([
                        'produk_id' => $produk->id_produk,
                        'nama_level' => $level['nama_level'],
                        'satuan_id' => $level['satuan_id'],
                        'konversi'  => $level['konversi'],
                        'harga_jual' => $level['harga_jual'],
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil ditambahkan'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }

    // =============================
    // EDIT (Ambil Data untuk Modal)
    // =============================
    public function edit($id)
    {
        $produk = Produk::with('levelHarga')->findOrFail($id);

        return response()->json([
            'id_produk'   => $produk->id_produk,
            'nama_produk' => $produk->nama_produk,
            'id_kategori' => $produk->id_kategori,
            'merk'        => $produk->merk,
            'harga_beli'  => $produk->harga_beli,
            'stok'        => $produk->stok,
            'satuan_id'   => $produk->satuan_id,
            'gambar'      => $produk->gambar,
            'level_harga' => $produk->levelHarga
        ]);
    }

    // =============================
    // UPDATE
    // =============================
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            $produk = Produk::findOrFail($id);

            $produk->update([
                'nama_produk' => $request->nama_produk,
                'id_kategori' => $request->id_kategori,
                'merk'        => $request->merk,
                'harga_beli'  => $request->harga_beli,
                'stok'        => $request->stok
            ]);

            // Update gambar jika ada
            if ($request->hasFile('gambar')) {
                $file = $request->file('gambar');
                $namaFile = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads'), $namaFile);

                $produk->update([
                    'gambar' => 'uploads/' . $namaFile
                ]);
            }

            // Hapus level lama
            ProdukHarga::where('produk_id', $produk->id_produk)->delete();

            // Insert ulang
            if ($request->level) {
                foreach ($request->level as $level) {
                    ProdukHarga::create([
                        'produk_id' => $produk->id_produk,
                        'nama_level' => $level['nama_level'],
                        'satuan_id' => $level['satuan_id'],
                        'konversi'  => $level['konversi'],
                        'harga_jual' => $level['harga_jual'],
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil diupdate'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            ProdukHarga::where('produk_id', $id)->delete();

            $produk = Produk::findOrFail($id);

            if ($produk->gambar && file_exists(public_path($produk->gambar))) {
                unlink(public_path($produk->gambar));
            }

            $produk->delete();

            DB::commit();
            return response(null, 204);
        } catch (\Exception $e) {

            DB::rollBack();
            return response(null, 500);
        }
    }

    public function deleteSelected(Request $request)
    {
        foreach ($request->id_produk as $id) {
            ProdukHarga::where('produk_id', $id)->delete();
            Produk::where('id_produk', $id)->delete();
        }

        return response(null, 204);
    }

    public function cetakBarcode(Request $request)
    {
        $dataproduk = [];

        foreach ($request->id_produk as $id) {
            $dataproduk[] = Produk::find($id);
        }

        $no = 1;
        $pdf = PDF::loadView('produk.barcode', compact('dataproduk', 'no'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('produk.pdf');
    }
}
