<main class="container py-5">
    <h3 class="mb-4">Keranjangku</h3>

    <% if CartItems && CartItems.Count > 0 %>
    <section class="row">
        <!-- Product List -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <!-- Desktop Header -->
                    <div class="row fw-bold d-none d-md-flex mb-2">
                        <div class="col-md-5">Produk</div>
                        <div class="col-md-2 text-center">Harga</div>
                        <div class="col-md-2 text-center">Jumlah</div>
                        <div class="col-md-2 text-end">Total Biaya</div>
                    </div>

                    <!-- Product Loop -->
                    <% loop CartItems %>
                    <div class="border rounded p-3 mb-3">
                        <div class="row align-items-center">
                            <div class="col-12 col-md-5 d-flex gap-3 align-items-start">
                                <a href="$BaseHref/list-product/view/$Product.ID" class="text-decoration-none text-black">
                                <% if $Product.Image %>
                                    <img src="$Product.Image.URL" class="img-thumbnail" width="80" alt="$Product.Name" />
                                <% else %>
                                    <img src="https://picsum.photos/80?random=$Product.ID" class="img-thumbnail" width="80" alt="$Product.Name" />
                                <% end_if %>
                                </a>
                                <div>
                                    <p class="mb-1 fw-bold">$Product.Name</p>
                                    <small class="text-muted">Kategori: <% if $Product.Category %>$Product.Category.Name<% else %>Kategori<% end_if %></small><br />
                                    <% if $Product.AverageRating %>
                                        <span class="text-warning">★ $Product.AverageRating</span>
                                    <% else %>
                                        <span class="text-warning">★ 0</span>
                                    <% end_if %>
                                </div>
                            </div>
                            <div class="col-6 col-md-2 text-center mt-2 mt-md-0">
                                <small class="d-md-none">Harga:</small><br />
                                <% if $Product.hasActiveFlashSale %>
                                    <%-- Flash Sale Active: Show original price crossed, discount price crossed (if any), flash sale price --%>
                                    <del class="text-muted small">$Product.numberFormat</del><br />
                                    <% if $Product.hasDiscount %>
                                        <del class="text-muted small">$Product.DisplayPrice</del><br />
                                    <% end_if %>
                                    <span class="text-danger fw-bold">$Product.FlashSalePrice</span>
                                <% else_if $Product.hasDiscount %>
                                    <%-- Only Regular Discount: Show original price crossed, discount price --%>
                                    <del class="text-muted small">$Product.numberFormat</del><br />
                                    <span class="text-danger fw-bold">$Product.DisplayPrice</span>
                                <% else %>
                                    <%-- No Discount: Show normal price --%>
                                    <span class="text-dark fw-bold">$Product.numberFormat</span>
                                <% end_if %>
                            </div>
                            <div class="col-6 col-md-2 text-center mt-2 mt-md-0">
                                <small class="d-md-none">Jumlah:</small><br />
                                <form method="post" action="$BaseHref/cart/update-quantity" class="d-inline">
                                    <input type="hidden" name="cartItemID" value="$ID" />
                                    <div class="input-group input-group-sm" style="width: 100px; margin: 0 auto;">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="decreaseQuantity(this)">-</button>
                                        <input type="number" name="quantity" value="$Quantity" min="1" max="$Product.Stok" 
                                            class="form-control text-center" onchange="this.form.submit()" />
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="increaseQuantity(this)">+</button>
                                    </div>
                                    <small class="text-muted">Maksimal: $Product.Stok</small>
                                </form>
                            </div>
                            <div class="col-12 col-md-2 text-end mt-2 mt-md-0">
                                <small class="d-md-none">Total Biaya:</small><br />
                                <span class="fw-bold">$FormattedSubtotal</span>
                                <br />
                                <a href="$BaseHref/cart/remove/$ID" class="btn btn-sm btn-outline-danger mt-1">Hapus</a>
                            </div>
                        </div>
                    </div>
                    <% end_loop %>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3">Ringkasan Belanja</h5>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Barang</span>
                        <span>$TotalItems item</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Total Harga</span>
                        <span class="fw-bold text-danger">$FormattedTotalPrice</span>
                    </div>
                    <a href="$BaseHref/checkout" class="btn btn-success w-100">Beli</a>
                </div>
            </div>
        </div>
    </section>
    <% else %>
    <div class="text-center py-5">   
        <h5>Keranjang anda kosong</h5>
        <p class="text-muted">Silahkan Tambah Produk Dahulu</p>
        <a href="$BaseHref" class="btn btn-primary">Belanja Sekarang</a>
    </div>
    <% end_if %>
</main>
<script>
function increaseQuantity(btn) {
    const input = btn.parentNode.querySelector('input[name="quantity"]');
    const max = parseInt(input.getAttribute('max'));
    const current = parseInt(input.value);
    
    if (current < max) {
        input.value = current + 1;
        input.form.submit();
    }
}

function decreaseQuantity(btn) {
    const input = btn.parentNode.querySelector('input[name="quantity"]');
    const current = parseInt(input.value);
    
    if (current > 1) {
        input.value = current - 1;
        input.form.submit();
    }
}
</script>