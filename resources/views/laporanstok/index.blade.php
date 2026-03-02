@extends('layouts.master')

@section('title')
Laporan Stok {{ tanggal_indonesia($tanggalAwal, false) }} s/d {{ tanggal_indonesia($tanggalAkhir, false) }}
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('/AdminLTE-2/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
@endpush

@section('breadcrumb')
@parent
<li class="active">Laporan</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <button onclick="updatePeriode()" class="btn btn-info btn-xs btn-flat"><i class="fa fa-plus-circle"></i> Ubah Periode</button>
                <a href="{{ route('laporanstok.export_pdf', [$tanggalAwal, $tanggalAkhir]) }}" target="_blank" class="btn btn-success btn-xs btn-flat"><i class="fa fa-file-excel-o"></i> Export PDF</a>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-stiped table-bordered">
                    <thead>
                        <th width="5%">No</th>
                        <th>Kode Produk</th>
                        <th>Nama Produk</th>
                        <th>Stok Awal</th>
                        <th>Masuk</th>
                        <th>Keluar</th>
                        <th>Sisa Stok</th>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

@includeIf('laporanstok.form')
@endsection

@push('scripts')
<script src="{{ asset('/AdminLTE-2/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
<script>
    let table;

    $(function() {
        table = $('.table').DataTable({
            processing: true,
            autoWidth: false,
            ajax: {
                url: '{{ route('laporanstok.data', [$tanggalAwal, $tanggalAkhir]) }}',
            },
            columns: [{
                    data: 'DT_RowIndex',
                    searchable: true,
                    sortable: true
                },
                {
                    data: 'kode_produk'
                },
                {
                    data: 'nama_produk'
                },
                {
                    data: 'stok_awal'
                },
                {
                    data: 'masuk'
                },
                {
                    data: 'keluar'
                },
                {
                    data: 'sisa_stok'
                }
            ],
            bSort: false,
            bPaginate: true,
        });

        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });
    });

    function updatePeriode() {
        $('#modal-form').modal('show');
    }
</script>
@endpush