<?php

namespace App\Http\Controllers;

use App\Models\LevelHarga;
use Illuminate\Http\Request;

class LevelhargaController extends Controller
{
    public function index()
    {
        return view('levelharga.index');
    }

    public function data()
    {
        $levelharga = LevelHarga::orderBy('id', 'desc')->get();

        return datatables()
            ->of($levelharga)
            ->addIndexColumn()
            ->addColumn('aksi', function ($levelharga) {
                return '
                <div class="btn-group">
                    <button type="button" onclick="editForm(`'. route('levelharga.update', $levelharga->id) .'`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-pencil"></i></button>
                    <button type="button" onclick="deleteData(`'. route('levelharga.destroy', $levelharga->id) .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                </div>
                ';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $levelharga = LevelHarga::create($request->all());

        return response()->json('Data berhasil disimpan', 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $levelharga = LevelHarga::find($id);

        return response()->json($levelharga);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $levelharga = LevelHarga::find($id)->update($request->all());

        return response()->json('Data berhasil disimpan', 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $levelharga = LevelHarga::find($id)->delete();

        return response(null, 204);
    }
}
