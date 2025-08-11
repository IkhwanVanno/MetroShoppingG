<main class="container py-5">
    <div class="row">
    <% if Product %>
    <!-- Product Image -->
    <div class="col-md-6 mb-4 text-center">
        <% if $Product.Image %>
        <img
            src="$Product.Image.URL"
            class="img-fluid rounded border"
            alt="$Product.Name"
        />
        <% else %>
        <img
            src="https://picsum.photos/500/400?random=$Product.ID"
            class="img-fluid rounded border"
            alt="$Product.Name"
        />
        <% end_if %>
    </div>
        
    <!-- Product Details -->
    <div class="col-md-6">
        <p class="text-muted mb-1">Category: <strong><% if $Product.Category %>$Product.Category.Name<% else %>General<% end_if %></strong></p>
        <h3 class="mb-2">$Product.Name</h3>
        
        <!-- Rating -->
        <div class="mb-2">
            <% if $Product.AverageRating %>
            <span class="text-warning">★ $Product.AverageRating</span>
            <span class="text-muted">($Product.Review.Count Reviews)</span>
            <% else %>
            <span class="text-warning">★ 0</span>
            <span class="text-muted">(0 Reviews)</span>
            <% end_if %>
        </div>

        <!-- Price -->
        <div class="mb-3">
            <% if $Product.hasDiscount %>
            <p class="mb-1">
                <del class="text-muted">$Product.OriginalPrice</del>
            </p>
            <% end_if %>
            <h4 class="text-danger">$Product.DisplayPrice</h4>
        </div>

        <!-- Stock -->
        <p class="mb-3">Available stock: <strong>$Product.Stok</strong> units</p>

        <!-- Action Buttons -->
        <div class="d-flex flex-wrap gap-2">
            <a href="$BaseHref/favorite/add/$Product.ID" class="btn btn-danger flex-fill">+ Favorite</a>
            <a href="$BaseHref/cart/add/$Product.ID" class="btn btn-primary flex-fill">+ Cart</a>
        </div>
    </div>
    <% end_if %>
    </div>

    <!-- Product Description -->
    <% if Product %>
    <div class="mt-5 border rounded p-4 bg-light">
        <h5 class="mb-3">Deskripsi Produk</h5>
        <% if $Product.Description %>
            <p class="mb-0" style="white-space: pre-line;">$Product.Description</p>
        <% else %>
            <p class="text-muted mb-0">Belum ada deskripsi produk.</p>
        <% end_if %>
    </div>
    <% end_if %>

    <hr class="my-5" />

    <!-- Reviews -->
    <div class="row">
        <div class="col-12">
            <h5 class="mb-3">Customer Reviews</h5>
            <% if Review && Review.Count > 0 %>
                <% loop Review %>
                <div class="mb-4 border rounded p-3">
                    <strong><% if $Users %>$Users.Username<% else %>Anonymous<% end_if %></strong> 
                    <span class="text-warning">★ $Rating</span>
                    <p class="mb-0">
                        <% if $Message %>$Message<% else %>No comment<% end_if %>
                    </p>
                </div>
                <% end_loop %>
            <% else %>
                <div class="alert alert-info">
                    <p class="mb-0">No reviews yet for this product.</p>
                </div>
            <% end_if %>
        </div>
    </div>
</main>
