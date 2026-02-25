@extends('layouts.master')

@section('title')
Transaksi Penjualan
@endsection

@push('css')
<style>
    .tampil-bayar {
        font-size: 2em;
        text-align: center;
        height: 100px;
    }

    .tampil-terbilang {
        padding: 10px;
        background: #f0f0f0;
    }

    .table-penjualan tbody tr:last-child {
        display: none;
    }

    .btn-checkout-mobile {
        display: none;
    }

    @media(max-width: 1024px) {
        .tampil-bayar {
            font-size: 2em;
            height: 70px;
            padding-top: 16px;
        }

        .table-penjualan thead,
        .table-penjualan th:nth-child(1),
        .table-penjualan th:nth-child(2),
        .table-penjualan th:nth-child(4),
        .table-penjualan th:nth-child(6),
        .table-penjualan th:nth-child(7),
        .table-penjualan td:nth-child(1),
        .table-penjualan td:nth-child(2),
        .table-penjualan td:nth-child(4),
        .table-penjualan td:nth-child(6),
        .table-penjualan td:nth-child(7) {
            display: none;
        }

        #form-bayar-container {
            display: none;
            background: #f9f9f9;
            padding: 15px;
            border-top: 2px solid #ddd;
            margin-top: 20px;
        }

        .btn-checkout-mobile {
            display: block;
            width: 100%;
            margin-top: 15px;
        }
    }
</style>
@endpush

@section('breadcrumb')
@parent
<li class="active">Transaksi Penjaualn</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-body">

                <form class="form-produk" id="form-produks">
                    @csrf
                    <div class="form-group row">
                        <label for="kode_produk" class="col-lg-2">Kode Produk</label>
                        <div class="col-lg-5">
                            <div class="input-group">
                                <input type="hidden" name="id_penjualan" id="id_penjualan" value="{{ $id_penjualan }}">
                                <input type="hidden" name="id_produk_level_harga" id="id_produk_level_harga">
                                <input type="hidden" name="id_produk" id="id_produk">
                                <input type="hidden" name="harga_type" id="harga_type">
                                <input type="hidden" name="harga_jual" id="harga_jual">
                                <input type="text" class="form-control" name="kode_produk" id="kode_produk">
                                <span class="input-group-btn">
                                    <button onclick="tampilProduk()" class="btn btn-info btn-flat" type="button"><i class="fa fa-arrow-right"></i></button>
                                </span>
                            </div>
                        </div>
                    </div>
                </form>

                <table class="table table-stiped table-bordered table-penjualan" id="tabel-produk">
                    <thead>
                        <th width="5%">No</th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Harga</th>
                        <th width="15%">Jumlah</th>
                        <th>Diskon</th>
                        <th>Subtotal</th>
                        <th width="15%"><i class="fa fa-cog"></i></th>
                    </thead>
                </table>

                <button class="btn btn-success btn-checkout-mobile d-md-none" id="btn-toggle-checkout" onclick="toggleCheckout()">Checkout</button>

                <div id="form-bayar-container" class="row">
                    <div class="col-lg-6">
                        <div class="tampil-bayar bg-primary"></div>
                        <div class="tampil-terbilang"></div>
                    </div>
                    <div class="col-lg-6">
                        <form action="{{ route('transaksi.simpan') }}" class="form-penjualan" method="post">
                            @csrf
                            <input type="hidden" name="id_penjualan" value="{{ $id_penjualan }}">
                            <input type="hidden" name="id_pembelian_detail" id="id_pembelian_detail">
                            <input type="hidden" name="total" id="total">
                            <input type="hidden" name="total_item" id="total_item">
                            <input type="hidden" name="bayar" id="bayar">
                            <input type="hidden" name="harga_type" id="harga_type">
                            <input type="hidden" name="id_member" id="id_member" value="{{ $memberSelected->id_member }}">

                            <div class="form-group row">
                                <label for="totalrp" class="col-lg-2 control-label">Total</label>
                                <div class="col-lg-8">
                                    <input type="text" id="totalrp" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="form-group row" style="display: none;">
                                <label for="kode_member" class="col-lg-2 control-label">Member</label>
                                <div class="col-lg-8">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="kode_member" value="{{ $memberSelected->kode_member }}">
                                        <span class="input-group-btn">
                                            <button onclick="tampilMember()" class="btn btn-info btn-flat" type="button"><i class="fa fa-arrow-right"></i></button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="diskon" class="col-lg-2 control-label">Diskon %</label>
                                <div class="col-lg-8">
                                    <input type="number" name="diskon" id="diskon" class="form-control" value="0">
                                </div>
                            </div>
                            <div class="form-group row" style="display: none;">
                                <label for="bayar" class="col-lg-2 control-label">Bayar</label>
                                <div class="col-lg-8">
                                    <input type="text" id="bayarrp" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-lg-2 control-label">Metode Pembayaran</label>
                                <div class="col-lg-8">
                                    <select name="metode_pembayaran" id="metode_pembayaran" class="form-control">
                                        <option value="cash" selected>Cash</option>
                                        <option value="qris">QRIS</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row d-none" id="field-qris">
                                <label class="col-lg-2 control-label">QRIS</label>
                                <div class="col-lg-8">
                                    <img src="{{ asset('img/qris.png') }}" class="img-fluid" style="max-width:300px;">
                                </div>
                            </div>

                            <div id="field-cash">

                                <div class="form-group row">
                                    <label for="diterima_display" class="col-lg-2 control-label">Diterima</label>
                                    <div class="col-lg-8">
                                        <input type="text" id="diterima_display" class="form-control" value="0">
                                        <input type="hidden" id="diterima" name="diterima" value="0">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="kembali" class="col-lg-2 control-label">Kembali</label>
                                    <div class="col-lg-8">
                                        <input type="text" id="kembali" name="kembali" class="form-control" value="0" readonly>
                                    </div>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="box-footer" id="box-footer">
                <button type="submit" class="btn btn-primary btn-sm btn-flat pull-right btn-simpan"><i class="fa fa-floppy-o"></i> Simpan Transaksi</button>
            </div>
        </div>
    </div>
</div>

@includeIf('penjualan_detail.produk')
@includeIf('penjualan_detail.member')
@endsection

@push('scripts')
<script>
    let table, table2;
    $(function() {
        $('body').addClass('sidebar-collapse');

        table = $('.table-penjualan').DataTable({
            processing: true,
            autoWidth: false,
            ajax: '{{ route("transaksi.data", $id_penjualan) }}',
            columns: [{
                    data: 'DT_RowIndex',
                    searchable: false,
                    sortable: false
                },
                {
                    data: 'kode_produk'
                },
                {
                    data: 'nama_produk'
                },
                {
                    data: 'harga_jual_eceran'
                },
                {
                    data: 'jumlah'
                },
                {
                    data: 'diskon'
                },
                {
                    data: 'subtotal'
                },
                {
                    data: 'aksi',
                    searchable: false,
                    sortable: false
                },
            ],
            dom: 'Brt',
            bSort: false,
            paginate: false
        }).on('draw.dt', function() {
            loadForm($('#diskon').val());
            setTimeout(() => $('#diterima_display').trigger('input'), 300);
        });

        table2 = $('.table-produk').DataTable();

        $(document).on('input', '.quantity', function() {
            let id = $(this).data('id');
            let jumlah = parseInt($(this).val());
            if (jumlah < 1 || jumlah > 10000) {
                alert('Jumlah tidak valid');
                return $(this).val(1);
            }

            $.post(`{{ url('/transaksi') }}/${id}`, {
                '_token': $('[name=csrf-token]').attr('content'),
                '_method': 'put',
                'jumlah': jumlah
            }).done(() => {
                table.ajax.reload(() => loadForm($('#diskon').val()));
            }).fail(() => alert('Tidak dapat menyimpan data'));
        });

        $('#diskon').on('input', function() {
            if ($(this).val() == "") $(this).val(0).select();
            loadForm($(this).val());
        });

        $('#diterima_display').on('input', function() {
            let val = $(this).val().replace(/[^0-9]/g, '');
            $('#diterima').val(val);
            $(this).val(formatRupiah(val));
            if ($('#diterima').val(val))
                loadForm($('#diskon').val(), val || 0);
        }).focus(function() {
            $(this).select();
        });

        $('.btn-simpan').on('click', function() {
            $('.form-penjualan').submit();
        });
    });

    function tampilProduk() {
        $('#modal-produk').modal('show');
    }

    function hideProduk() {
        $('#modal-produk').modal('hide');
    }

    function tampilMember() {
        $('#modal-member').modal('show');
    }

    function hideMember() {
        $('#modal-member').modal('hide');
    }

    function toggleCheckout() {
        const $produkForm = $('#form-produks');
        const $produkTable = $('#tabel-produk');
        const $formBayar = $('#form-bayar-container');
        const $button = $('#btn-toggle-checkout');

        if ($produkTable.is(':hidden')) {
            $produkForm.show();
            $produkTable.show();
            $formBayar.hide();
            $button.text('Checkout')
                .removeClass('btn-danger')
                .addClass('btn-success');
            $('#box-footer').hide();
        } else {
            $('#box-footer').show();
            $produkForm.hide();
            $produkTable.hide();
            $formBayar.show();
            $button.text('Kembali ke Produk')
                .removeClass('btn-success')
                .addClass('btn-danger');

            // $('#diterima_display').focus();
        }
    }

    function pilihProduk(id, kode, type, harga_jual, id_produk_level_harga, idpembeliandetail) {
        $('#id_produk').val(id);
        if (!$('#harga_type').length) {
            $('#form-produks').append(`<input type="hidden" name="harga_type" id="harga_type">`);
        }
        $('#harga_type').val(type);
        $('#harga_jual').val(harga_jual);
        $('#id_produk_level_harga').val(id_produk_level_harga);
        $('#id_pembelian_detail').val(idpembeliandetail);
        hideProduk();
        tambahProduk();
        if ($(window).width() <= 768) $('#form-bayar-container').slideUp();
    }

    function tambahProduk() {
        $.post('{{ route('transaksi.store') }}', $('.form-produk').serialize())
            .done(() => {
                table.ajax.reload(() => loadForm($('#diskon').val()));
            })
            .fail(() => alert('Tidak dapat menyimpan data'));
    }

    function pilihMember(id, kode) {
        $('#id_member').val(id);
        $('#kode_member').val(kode);
        $('#diskon').val('{{ $diskon }}');
        loadForm($('#diskon').val());
        $('#diterima_display').val('0').focus().select();
        hideMember();
    }

    function deleteData(url) {
        if (confirm('Yakin ingin menghapus data terpilih?')) {
            $.post(url, {
                '_token': $('[name=csrf-token]').attr('content'),
                '_method': 'delete'
            }).done(() => {
                table.ajax.reload(() => loadForm($('#diskon').val()));
            }).fail(() => alert('Tidak dapat menghapus data'));
        }
    }

    function loadForm(diskon = 0, diterima = 0) {
        $('#total').val($('.total').text());
        $('#total_item').val($('.total_item').text());

        $.get(`{{ url('/transaksi/loadform') }}/${diskon}/${$('.total').text()}/${diterima}`)
            .done(response => {
                $('#totalrp').val('Rp. ' + response.totalrp);
                $('#bayarrp').val('Rp. ' + response.bayarrp);
                $('#bayar').val(response.bayar);
                $('.tampil-bayar').text('Bayar: Rp. ' + response.bayarrp);
                $('.tampil-terbilang').text(response.terbilang);
                $('#kembali').val('Rp.' + response.kembalirp);
            }).fail(() => alert('Tidak dapat menampilkan data'));
    }

    function formatRupiah(angka, prefix = 'Rp. ') {
        let number_string = angka.replace(/[^,\d]/g, '').toString(),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return rupiah;
    }

    function toggleMetodePembayaran() {
        let metode = $('#metode_pembayaran').val();

        if (metode === 'qris') {
            $('#field-cash').hide();
            $('#field-qris').show();

            // otomatis bayar pas
            let total = $('#bayar').val() || 0;
            $('#diterima').val(total);
            loadForm($('#diskon').val(), total);

        } else {
            $('#field-cash').show();
            $('#field-qris').hide();
        }
    }

    $('#metode_pembayaran').on('change', function() {
        toggleMetodePembayaran();
    });

    toggleMetodePembayaran(); // jalankan saat load
</script>
@if(session('success'))
<script>
    alert("{{ session('success') }}");
</script>
@endif

@if(session('error'))
<script>
    alert("{{ session('error') }}");
</script>
@endif
@endpush