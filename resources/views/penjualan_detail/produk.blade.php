<style>
    @media (max-width: 768px) {
        .modal-dialog {
            margin: 10px;
            width: auto;
        }

        .modal-title {
            font-size: 18px;
        }

        .produk-card {
            font-size: 12px;
        }
    }

    .produk-card img {
        display: block;
        margin: 0 auto 10px auto;
        max-height: 120px;
        object-fit: contain;
    }

    .produk-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        justify-content: flex-start;
    }

    .produk-card {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 10px;
        width: calc(33.33% - 10px);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        background: #fff;
    }

    .produk-card h5 {
        font-size: 14px;
        margin: 5px 0;
        font-weight: bold;
    }

    .produk-card .harga {
        font-size: 13px;
        margin-bottom: 5px;
    }

    .produk-card .stok {
        font-size: 12px;
        color: #888;
        margin-bottom: 8px;
    }

    .produk-card .btn {
        font-size: 12px;
        padding: 4px 8px;
    }

    @media (max-width: 768px) {
        .produk-card {
            width: calc(50% - 10px);
        }
    }

    #searchInput {
        margin-bottom: 15px;
    }
</style>

<div class="modal fade" id="modal-produk" tabindex="-1" role="dialog" aria-labelledby="modal-produk">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Pilih Produk</h4>
            </div>
            <div class="modal-body">
                <input type="text" id="searchInput" class="form-control" placeholder="Cari produk...">

                <div class="produk-grid" id="produkGrid">
                    @foreach ($produk as $item)
                    <div class="produk-card" data-nama="{{ strtolower($item->nama_produk) }}">

                        {{-- Gambar --}}
                        @if ($item->gambar)
                        <img src="{{ asset($item->gambar) }}" class="img-thumbnail">
                        @else
                        <img src="{{ asset('img/no_images.png') }}" class="img-thumbnail">
                        @endif

                        <h5 class="text-center">{{ $item->nama_produk }}</h5>
                        <div class="stok text-center">Stok: {{ $item->stok_total }}</div>

                        {{-- Loop Semua Level Harga --}}
                        @php
                        $colors = ['primary','success','warning','info','danger'];
                        @endphp

                        <div class="dropdown mb-2">
                            <button class="btn btn-secondary btn-xs btn-block dropdown-toggle"
                                type="button"
                                data-toggle="dropdown"
                                aria-haspopup="true"
                                aria-expanded="false">
                                Pilih Level Harga
                            </button>

                            <div class="dropdown-menu w-100">
                                @foreach($item->levelHarga as $index => $level)
                                <a href="javascript:void(0)"
                                    class="dropdown-item text-{{ $colors[$index % count($colors)] }}"
                                    onclick="pilihProduk(
                    '{{ $item->id_produk }}',
                    '{{ $item->kode_produk }}',
                    '{{ $level->nama_level }}',
                    '{{ $level->harga_jual }}'
               )">

                                    {{ ucfirst($level->nama_level) }}
                                    (Rp {{ number_format($level->harga_jual, 0, ',', '.') }})

                                </a>
                                @endforeach
                            </div>
                        </div>

                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {

        const searchInput = document.getElementById('searchInput');
        const produkCards = document.querySelectorAll('.produk-card');

        searchInput.addEventListener('input', function() {
            const keyword = this.value.toLowerCase();

            produkCards.forEach(card => {
                const namaProduk = card.getAttribute('data-nama');
                if (namaProduk.includes(keyword)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    function pilihProduk(id, kode, level, harga) {

        console.log("Produk:", id);
        console.log("Level:", level);
        console.log("Harga:", harga);

        // contoh isi input
        $('#produk_id').val(id);
        $('#harga').val(harga);
    }
</script>