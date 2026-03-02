@extends('layouts.master')

@section('title')
Transaksi Pembelian
@endsection

@push('css')
<style>
    .tampil-bayar {
        font-size: 5em;
        text-align: center;
        height: 100px;
    }

    .tampil-terbilang {
        padding: 10px;
        background: #f0f0f0;
    }

    .table-pembelian tbody tr:last-child {
        display: none;
    }

    @media(max-width: 768px) {
        .tampil-bayar {
            font-size: 3em;
            height: 70px;
            padding-top: 5px;
        }
    }
</style>
@endpush

@section('breadcrumb')
@parent
<li class="active">Transaksi Pembelian</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <table>
                    <tr>
                        <td>Supplier</td>
                        <td>: {{ $supplier->nama }}</td>
                    </tr>
                    <tr>
                        <td>Telepon</td>
                        <td>: {{ $supplier->telepon }}</td>
                    </tr>
                    <tr>
                        <td>Alamat</td>
                        <td>: {{ $supplier->alamat }}</td>
                    </tr>
                </table>
            </div>
            <div class="box-body">

                <form class="form-produk">
                    @csrf
                    <div class="form-group row">
                        <label for="kode_produk" class="col-lg-2">Kode Produk</label>
                        <div class="col-lg-5">
                            <div class="input-group">
                                <input type="hidden" name="id_pembelian" id="id_pembelian" value="{{ $id_pembelian }}">
                                <input type="hidden" name="id_produk" id="id_produk">
                                <input type="text" class="form-control" name="kode_produk" id="kode_produk">
                                <span class="input-group-btn">
                                    <button onclick="tampilProduk()" style="background-color:#FFB703; border-color:#FFB703; color:#000;" class="btn btn-flat" type="button"><i class="fa fa-arrow-right"></i></button>
                                </span>
                            </div>
                        </div>
                    </div>
                </form>

                <table class="table table-stiped table-bordered table-pembelian">
                    <thead>
                        <th width="5%">No</th>
                        <th>Nama</th>
                        <th>Harga</th>
                        <th width="15%">Satuan</th>
                        <th width="15%">Jumlah</th>
                        <th>Subtotal</th>
                        <th width="15%"><i class="fa fa-cog"></i></th>
                    </thead>
                </table>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="tampil-bayar" style="background-color: #FF8C00;"></div>
                        <div class="tampil-terbilang"></div>
                    </div>
                    <div class="col-lg-4">
                        <form action="{{ route('pembelian.store') }}" class="form-pembelian" method="post">
                            @csrf
                            <input type="hidden" name="id_pembelian" value="{{ $id_pembelian }}">
                            <input type="hidden" name="id_produk_pembelian" id="id_produk_pembelian">
                            <input type="hidden" name="total" id="total">
                            <input type="hidden" name="total_item" id="total_item">
                            <input type="hidden" name="bayar" id="bayar">

                            <div class="form-group row">
                                <label for="totalrp" class="col-lg-2 control-label">Total</label>
                                <div class="col-lg-8">
                                    <input type="text" id="totalrp" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="diskon" class="col-lg-2 control-label">Diskon</label>
                                <div class="col-lg-8">
                                    <input type="number" name="diskon" id="diskon" class="form-control" value="{{ $diskon }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="bayar" class="col-lg-2 control-label">Bayar</label>
                                <div class="col-lg-8">
                                    <input type="text" id="bayarrp" class="form-control">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="box-footer">
                <button type="submit" style="background-color:#FFB703; border-color:#FFB703; color:#000;" class="btn btn-sm btn-flat pull-right btn-simpan"><i class="fa fa-floppy-o"></i> Simpan Transaksi</button>
            </div>
        </div>
    </div>
</div>

@includeIf('pembelian_detail.produk')
@endsection

@push('scripts')
<script>
    let table, table2;
    let selectedSatuan = {};

    $(function() {
        $('body').addClass('sidebar-collapse');

        table = $('.table-pembelian').DataTable({
                processing: true,
                autoWidth: false,
                ajax: {
                    url: '{{ route('pembelian_detail.data', $id_pembelian) }}',
                },
                columns: [{
                        data: 'DT_RowIndex',
                        searchable: false,
                        sortable: false
                    },
                    {
                        data: 'kode_produk'
                    },
                    // {
                    //     data: 'nama_produk'
                    // },
                    {
                        data: 'harga_beli'
                    },
                    {
                        data: 'satuan'
                    },
                    {
                        data: 'jumlah'
                    },
                    {
                        data: 'subtotal',
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
            })
            .on('draw.dt', function() {
                loadForm($('#diskon').val());
                for (let id in selectedSatuan) {

                    let select = $('.satuan[data-id="' + id + '"]');

                    if (select.length) {
                        select.val(selectedSatuan[id]).trigger('change');
                    }
                }
            });
        table2 = $('.table-produk').DataTable();

        $('.btn-simpan').on('click', function() {
            $('.form-pembelian').submit();
        });
    })

    // ==========================================
    // GLOBAL STATE
    // ==========================================
    let rowCache = {};
    let lastFocused = null;

    // ==========================================
    // HELPER
    // ==========================================
    function formatRupiah(angka) {
        if (!angka) return '';
        return 'Rp. ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function parseRupiah(rupiah) {
        if (!rupiah) return 0;
        return parseFloat(rupiah.toString().replace(/[^\d]/g, '')) || 0;
    }


    // ==========================================
    // SIMPAN FIELD & CURSOR SAAT FOKUS
    // ==========================================
    $(document).on('focus', '.quantity, .harga', function() {

        lastFocused = {
            id: $(this).data('id'),
            field: $(this).hasClass('harga') ? 'harga' : 'quantity',
            cursor: this.selectionStart
        };

    });


    // ==========================================
    // UPDATE POSISI CURSOR SAAT MENGETIK
    // ==========================================
    $(document).on('input', '.quantity, .harga', function() {

        if (lastFocused && lastFocused.id == $(this).data('id')) {
            lastFocused.cursor = this.selectionStart;
        }

    });


    // ==========================================
    // FORMAT HARGA (TIDAK DIUBAH)
    // ==========================================
    $(document).on('input', '.harga', function() {

        let id = $(this).data('id');

        let cursorPos = this.selectionStart;
        let oldLength = $(this).val().length;

        let angka = parseRupiah($(this).val());
        let formatted = formatRupiah(angka);

        $(this).val(formatted);

        let newLength = formatted.length;
        cursorPos = cursorPos + (newLength - oldLength);

        this.setSelectionRange(cursorPos, cursorPos);

        if (lastFocused && lastFocused.id == id) {
            lastFocused.cursor = cursorPos;
        }

        updateRow(id);
    });


    // ==========================================
    // UPDATE QTY (Cursor selalu di akhir)
    // ==========================================
    $(document).on('input', '.quantity', function() {

        let id = $(this).data('id');

        let value = $(this).val().replace(/[^\d]/g, '');
        $(this).val(value);

        // Cursor paksa di belakang saat mengetik
        this.setSelectionRange(value.length, value.length);

        lastFocused = {
            id: id,
            field: 'quantity',
            cursor: value.length
        };

        updateRow(id);
    });


    // ==========================================
    // UPDATE SATUAN
    // ==========================================
    $(document).on('change', '.satuan', function() {
        updateRow($(this).data('id'));
    });


    // ==========================================
    // FUNGSI UPDATE ROW
    // ==========================================
    function updateRow(id) {

        let qtyInput = $('.quantity[data-id="' + id + '"]');
        let satuanSelect = $('.satuan[data-id="' + id + '"]');
        let selected = satuanSelect.find(':selected');
        let hargaInput = $('.harga[data-id="' + id + '"]');
        let subtotalEl = $('.subtotal[data-id="' + id + '"]');

        let qty = parseFloat(qtyInput.val()) || 0;
        let harga = parseRupiah(hargaInput.val());
        let satuan = selected.val();
        let konversi = parseFloat(selected.data('konversi')) || 1;
         console.log('selected', satuan)

        if (qty < 1 || qty > 100000 || harga < 0) return;

        let subtotal = Math.round(harga * qty * konversi);
        subtotalEl.text(formatRupiah(subtotal));

        if (
            rowCache[id] &&
            rowCache[id].qty === qty &&
            rowCache[id].harga === harga &&
            rowCache[id].satuan === satuan
        ) {
            return;
        }

        rowCache[id] = {
            qty,
            harga,
            satuan
        };

        $.ajax({
            url: `/pembelian_detail/${id}`,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                _method: 'PUT',
                harga_beli: harga,
                jumlah: qty,
                satuan_id: satuan
            },
            success: function() {

                let state = {};

                $('.quantity').each(function() {
                    let rowId = $(this).data('id');

                    state[rowId] = {
                        qty: $('.quantity[data-id="' + rowId + '"]').val(),
                        harga: $('.harga[data-id="' + rowId + '"]').val(),
                        satuan: $('.satuan[data-id="' + rowId + '"]').val(),
                        kode_produk: $('.kode-produk[data-id="' + rowId + '"]').text(),
                        nama_produk: $('.nama-produk[data-id="' + rowId + '"]').text()
                    };
                });

                table.ajax.reload(function() {

                    loadForm($('#diskon').val());

                    for (let rowId in state) {

                        $('.quantity[data-id="' + rowId + '"]').val(state[rowId].qty);
                        $('.harga[data-id="' + rowId + '"]').val(state[rowId].harga);
                        $('.satuan[data-id="' + rowId + '"]').val(state[rowId].satuan);
                        $('.kode-produk[data-id="' + rowId + '"]').text(state[rowId].kode_produk);
                        $('.nama-produk[data-id="' + rowId + '"]').text(state[rowId].nama_produk);

                        let selected = $('.satuan[data-id="' + rowId + '"]').find(':selected');
                        let konversi = parseFloat(selected.data('konversi')) || 1;

                        let subtotal = Math.round(
                            parseRupiah(state[rowId].harga) *
                            parseFloat(state[rowId].qty) *
                            konversi
                        );

                        $('.subtotal[data-id="' + rowId + '"]').text(formatRupiah(subtotal));

                        rowCache[rowId] = {
                            qty: parseFloat(state[rowId].qty),
                            harga: parseRupiah(state[rowId].harga),
                            satuan: state[rowId].satuan
                        };
                    }

                    // =============================
                    // RESTORE FOKUS & CURSOR
                    // =============================
                    if (lastFocused) {

                        let selector = '.' + lastFocused.field + '[data-id="' + lastFocused.id + '"]';
                        let el = $(selector);

                        if (el.length) {

                            el.focus();

                            if (el.is('input')) {

                                if (lastFocused.field === 'quantity') {

                                    // 🔥 PAKSA CURSOR DI AKHIR
                                    let len = el.val().length;
                                    el[0].setSelectionRange(len, len);

                                } else {

                                    // Harga tetap seperti sebelumnya
                                    let valLength = el.val().length;
                                    let cursorPos = lastFocused.cursor;

                                    if (cursorPos > valLength) {
                                        cursorPos = valLength;
                                    }

                                    el[0].setSelectionRange(cursorPos, cursorPos);
                                }
                            }
                        }
                    }

                }, false);

            },
            error: function() {
                alert('Tidak dapat update data');
            }
        });
    }

    function tampilProduk() {
        $('#modal-produk').modal('show');
    }

    function hideProduk() {
        $('#modal-produk').modal('hide');
    }

    function pilihProduk(id, kode) {
        $('#id_produk').val(id);
        $('#kode_produk').val(kode);
        $('#id_produk_pembelian').val(id);
        hideProduk();
        tambahProduk();
    }

    function tambahProduk() {
        $.post('{{ route('pembelian_detail.store') }}', $('.form-produk').serialize())
            .done(response => {
                $('#kode_produk').focus();
                table.ajax.reload(() => loadForm($('#diskon').val()));
            })
            .fail(errors => {
                alert('Tidak dapat menyimpan data');
                return;
            });
    }

    function deleteData(url) {
        if (confirm('Yakin ingin menghapus data terpilih?')) {
            $.post(url, {
                    '_token': $('[name=csrf-token]').attr('content'),
                    '_method': 'delete'
                })
                .done((response) => {
                    table.ajax.reload(() => loadForm($('#diskon').val()));
                })
                .fail((errors) => {
                    alert('Tidak dapat menghapus data');
                    return;
                });
        }
    }

    function loadForm(diskon = 0) {
        $('#total').val($('.total').text());
        $('#total_item').val($('.total_item').text());

        $.get(`{{ url('/pembelian_detail/loadform') }}/${diskon}/${$('.total').text()}`)
            .done(response => {
                $('#totalrp').val('Rp. ' + response.totalrp);
                $('#bayarrp').val('Rp. ' + response.bayarrp);
                $('#bayar').val(response.bayar);
                $('.tampil-bayar').text('Rp. ' + response.bayarrp);
                $('.tampil-terbilang').text(response.terbilang);
            })
            .fail(errors => {
                alert('Tidak dapat menampilkan data');
                return;
            })
    }
</script>
@endpush