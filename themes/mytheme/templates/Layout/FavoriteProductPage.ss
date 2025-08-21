<main class="container py-5">
    <h3 class="mb-4">Favoritku</h3>

    <% if Favorites && Favorites.Count > 0 %>
        <% loop Favorites %>
        <div class="card mb-3">
            <div class="row g-0 align-items-center">
                <!-- Image -->
                <div class="col-md-3 text-center p-2">
                    <% if $Product.Image %>
                        <img src="$Product.Image.URL" class="img-fluid rounded" alt="$Product.Name" style="max-height: 150px;" />
                    <% else %>
                        <img src="https://picsum.photos/150?random=$Product.ID" class="img-fluid rounded" alt="$Product.Name" />
                    <% end_if %>
                </div>

                <!-- Product Info -->
                <div class="col-md-6">
                    <div class="card-body">
                        <p class="mb-1 text-muted">Kategori: <% if $Product.Category %>$Product.Category.Name<% else %>Kategori<% end_if %></p>
                        <h5 class="card-title mb-1">$Product.Name</h5>
                        <p class="mb-1 text-warning">
                            <% if $Product.AverageRating %>
                                ★ $Product.AverageRating <span class="text-muted">($Product.Review.Count Ulasan)</span>
                            <% else %>
                                ★ 0 <span class="text-muted">(0 Ulasan)</span>
                            <% end_if %>
                        </p>
                        <% if $Product.hasDiscount %>
                        <p class="mb-1">
                            <del class="text-muted">$Product.OriginalPrice</del>
                        </p>
                        <% end_if %>
                        <p class="mb-0 text-danger fw-bold fs-5">$Product.DisplayPrice</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="col-md-3 text-end pe-3">
                    <div class="d-flex flex-column gap-2">
                        <a href="$BaseHref/list-product/view/$Product.ID" class="btn btn-sm btn-primary w-100">Lihat Detail</a>
                        <a href="$BaseHref/favorite/remove/$ID" class="btn btn-sm btn-outline-danger w-100">Hapus</a>
                    </div>
                </div>
            </div>
        </div>
        <% end_loop %>
    <% else %>
        <div class="text-center py-5">
            <h5>Belum ada produk favorit.</h5>
            <p class="text-muted">Silakan tambahkan produk ke favorit terlebih dahulu.</p>
            <a href="$BaseHref" class="btn btn-primary">Belanja Sekarang</a>
        </div>
    <% end_if %>
</main>