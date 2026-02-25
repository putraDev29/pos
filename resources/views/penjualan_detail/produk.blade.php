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

    .product-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 12px;
        margin-bottom: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }

    .product-title {
        font-weight: 600;
        margin-bottom: 10px;
        font-size: 14px;
    }

    .price-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }

    .price-btn {
        background: #f5f7fa;
        border: none;
        border-radius: 10px;
        padding: 2px;
        cursor: pointer;
        text-align: center;
        transition: 0.2s ease;
    }

    .price-btn:hover {
        background: #2563eb;
        color: white;
        transform: translateY(-2px);
    }

    .price-level {
        display: block;
        font-size: 12px;
        font-weight: 600;
    }

    .price-value {
        display: block;
        font-size: 13px;
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

                        <div>
                            <div class="price-grid">
                                @foreach($item->levelHarga as $level)
                                <button class="price-btn"
                                    onclick="pilihProduk(
                    '{{ $item->id_produk }}',
                    '{{ $item->kode_produk }}',
                    '{{ $level->nama_level }}',
                    '{{ $level->harga_jual }}',
                    '{{ $level->satuan_id }}',
                )">

                                    <span class="price-level">
                                        {{ ucfirst($level->nama_level) }}
                                    </span>

                                    <span class="price-value">
                                        Rp {{ number_format($level->harga_jual,0,',','.') }}
                                    </span>
                                </button>
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
</script>