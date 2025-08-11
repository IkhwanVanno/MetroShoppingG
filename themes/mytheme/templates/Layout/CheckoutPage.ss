<main class="container my-4">
    <h3 class="mb-4">Checkout</h3>
    
    <% if CartItems && CartItems.Count > 0 %>
    <form method="post" action="$BaseHref/checkout/process-order">
        <!-- Alamat Pengiriman -->
        <div class="card mb-3">
            <div class="card-header fw-bold">Alamat Pengiriman</div>
            <div class="card-body">
                <% if ShippingAddress %>
                    <p class="mb-1"><strong>$ShippingAddress.ReceiverName</strong></p>
                    <p class="mb-1">$ShippingAddress.Address</p>
                    <p class="mb-0">$ShippingAddress.PostalCode - $ShippingAddress.CityID</p>
                    <p class="mb-0">$ShippingAddress.PhoneNumber</p>
                    <a href="$BaseHref/checkout/detail-alamat" class="btn btn-link p-0 mt-2">Ubah Alamat</a>
                <% else %>
                    <p class="mb-0 text-muted">Belum ada alamat pengiriman</p>
                    <a href="$BaseHref/checkout/detail-alamat" class="btn btn-primary mt-2">Tambah Alamat</a>
                <% end_if %>
            </div>
        </div>

        <!-- Produk di Keranjang -->
        <div class="card mb-3">
            <div class="card-header fw-bold">Produk Dipesan</div>
            <div class="card-body">
                <% loop CartItems %>
                <div class="d-flex align-items-center border-bottom pb-3 mb-3">
                    <% if $Product.Image %>
                        <img src="$Product.Image.URL" class="me-3" alt="$Product.Name" width="80" />
                    <% else %>
                        <img src="https://picsum.photos/80?random=$Product.ID" class="me-3" alt="$Product.Name" width="80" />
                    <% end_if %>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">$Product.Name</h6>
                        <small class="text-muted">Berat: $Product.Weight gr</small>
                        <p class="mb-1">$Product.DisplayPrice × $Quantity</p>
                    </div>
                    <div class="fw-bold">$FormattedSubtotal</div>
                </div>
                <% end_loop %>
            </div>
        </div>

        <!-- Pilih Kurir -->
        <div class="card mb-3">
            <div class="card-header fw-bold">Pilih Kurir</div>
            <div class="card-body">
            <select class="form-select">
                <option value="">Pilih kurir</option>
                <option value="jne">JNE - Rp20.000</option>
                <option value="pos">POS Indonesia - Rp18.000</option>
                <option value="tiki">TIKI - Rp22.000</option>
            </select>
            </div>
        </div>

        <!-- Metode Pembayaran -->
        <div class="card mb-3">
            <div class="card-header fw-bold">Metode Pembayaran</div>
            <div class="card-body">
            <select class="form-select">
                <option value="">Pilih metode pembayaran</option>
                <option value="va">Virtual Account</option>
                <option value="ewallet">E-Wallet</option>
                <option value="cc">Kartu Kredit/Debit</option>
            </select>
            </div>
        </div>

        <!-- Ringkasan Pembayaran -->
        <div class="card mb-3">
            <div class="card-header fw-bold">Ringkasan Pembayaran</div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal ($TotalItems items)</span>
                    <span>$FormattedTotalPrice</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Ongkir</span>
                    <span>-</span>
                </div>
                <div class="d-flex justify-content-between fw-bold border-top pt-2">
                    <span>Total</span>
                    <span>$FormattedTotalPrice</span>
                </div>
                <% if ShippingAddress %>
                    <input type="hidden" name="SecurityID" value="$SecurityID">
                    <button type="submit" class="btn btn-success w-100 mt-3">Buat Pesanan</button>
                <% else %>
                    <button type="button" class="btn btn-secondary w-100 mt-3" disabled>Tambahkan Alamat Terlebih Dahulu</button>
                <% end_if %>
            </div>
        </div>
    </form>
    <% else %>
    <div class="text-center py-5">
        <h5>Keranjang kosong</h5>
        <p class="text-muted">Tambahkan produk ke keranjang terlebih dahulu</p>
        <a href="$BaseHref" class="btn btn-primary">Belanja Sekarang</a>
    </div>
    <% end_if %>
</main>