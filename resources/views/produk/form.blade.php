<style>
    #overlay-loading {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(0, 0, 0, 0.5);
        display: none;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        z-index: 9999;
    }

    .level-box {
        border: 1px solid #ddd;
        padding: 15px;
        margin-bottom: 10px;
        border-radius: 6px;
        background: #f9f9f9;
    }
</style>

<div class="modal fade" id="modal-form">
    <div class="modal-dialog modal-lg">
        <form id="form-produk" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" id="method-field" value="POST">
            <input type="hidden" name="id_produk" id="id_produk">

            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Tambah Produk</h4>
                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <label>Nama Produk</label>
                        <input type="text" name="nama_produk" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="id_kategori" class="form-control" required>
                            <option value="">Pilih Kategori</option>
                            @foreach ($kategori as $key => $item)
                            <option value="{{ $key }}">{{ $item }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Merk</label>
                        <input type="text" name="merk" class="form-control">
                    </div>

                    <!-- <div class="form-group">
                        <label>Harga Beli</label>
                        <input type="number" name="harga_beli" class="form-control" required>
                    </div> -->

                    <!-- <div class="form-group">
                        <label>Stok Awal</label>
                        <input type="number" name="stok" class="form-control" min="0" value="0" required>
                    </div> -->

                    <hr>
                    <h4>Level Hargas</h4>

                    <div id="level-container"></div>

                    <button type="button" class="btn btn-success btn-sm" id="add-level">
                        <i class="fa fa-plus"></i> Tambah Level Harga
                    </button>

                    <hr>

                    <div class="form-group">
                        <label>Gambar</label>
                        <input type="file" name="gambar" id="gambar" class="form-control" accept="image/*">
                        <img id="preview-image" style="display:none; max-height:150px; margin-top:10px;">
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary btn-sm">
                        <i class="fa fa-save"></i> Simpan
                    </button>
                    <button type="button" class="btn btn-warning btn-sm" data-dismiss="modal">
                        Batal
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="overlay-loading">
    <i class="fa fa-spinner fa-spin fa-3x text-white"></i>
    <p class="text-white">Menyimpan data...</p>
</div>

<script>
    let levelIndex = 0;

    function createLevelRow(index, data = null) {
        return `
        <div class="level-box">
            <div class="row">

                <div class="col-md-3">
                    <label>Nama Level</label>
                    <input type="text" name="level[${index}][nama_level]"
                        class="form-control"
                        value="${data ? data.nama_level : ''}"
                        required>
                </div>

                <div class="col-md-3">
                    <label>Satuan</label>
                    <select name="level[${index}][satuan_id]" class="form-control" required>
                        <option value="">Pilih Satuan</option>
                        @foreach($satuan as $item)
                            <option value="{{ $item->id }}">
                                {{ $item->nama_satuan }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label>Konversi</label>
                    <input type="number" name="level[${index}][konversi]"
                        class="form-control"
                        value="${data ? data.konversi : 1}"
                        min="1" required>
                </div>

                <div class="col-md-3">
                    <label>Harga Jual</label>
                    <input type="number" name="level[${index}][harga_jual]"
                        class="form-control"
                        value="${data ? data.harga_jual : ''}"
                        required>
                </div>

                <div class="col-md-1" style="margin-top:25px;">
                    <button type="button" class="btn btn-danger btn-sm remove-level">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>

            </div>
        </div>
    `;
    }

    document.getElementById('add-level').addEventListener('click', function() {
        document.getElementById('level-container')
            .insertAdjacentHTML('beforeend', createLevelRow(levelIndex));
        levelIndex++;
    });

    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-level')) {
            e.target.closest('.level-box').remove();
        }
    });

    function resetForm() {
        document.getElementById('form-produk').reset();
        document.getElementById('level-container').innerHTML = '';
        document.getElementById('preview-image').style.display = 'none';
        levelIndex = 0;
    }

    // =====================
    // Preview Gambar
    // =====================
    $('#gambar').on('change', function(e) {
        const reader = new FileReader();
        reader.onload = function() {
            $('#preview-image').attr('src', reader.result).show();
        }
        reader.readAsDataURL(e.target.files[0]);
    });
</script>