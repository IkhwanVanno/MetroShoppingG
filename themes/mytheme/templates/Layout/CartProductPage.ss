<main class="container py-4">
    <h3 class="mb-4">Shopping Cart</h3>

    <% if CartItems && CartItems.Count > 0 %>
    <section class="row">
        <!-- Product List -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <!-- Desktop Header -->
                    <div class="row fw-bold d-none d-md-flex mb-2">
                        <div class="col-md-1">Select</div>
                        <div class="col-md-5">Product</div>
                        <div class="col-md-2 text-center">Price</div>
                        <div class="col-md-2 text-center">Quantity</div>
                        <div class="col-md-2 text-end">Subtotal</div>
                    </div>

                    <!-- Product Loop -->
                    <% loop CartItems %>
                    <div class="border rounded p-3 mb-3">
                        <div class="row align-items-center">
                            <div class="col-12 col-md-1 mb-2 mb-md-0 text-center">
                                <input type="checkbox" class="form-check-input" />
                            </div>
                            <div class="col-12 col-md-5 d-flex gap-3 align-items-start">
                                <a href="$BaseHref/list-product-page/view/$Product.ID" class="text-decoration-none text-black">
                                <% if $Product.Image %>
                                    <img src="$Product.Image.URL" class="img-thumbnail" width="80" alt="$Product.Name" />
                                <% else %>
                                    <img src="https://picsum.photos/80?random=$Product.ID" class="img-thumbnail" width="80" alt="$Product.Name" />
                                <% end_if %>
                                </a>
                                <div>
                                    <p class="mb-1 fw-bold">$Product.Name</p>
                                    <small class="text-muted">Category: <% if $Product.Category %>$Product.Category.Name<% else %>General<% end_if %></small><br />
                                    <% if $Product.AverageRating %>
                                        <span class="text-warning">★ $Product.AverageRating</span>
                                    <% else %>
                                        <span class="text-warning">★ 0</span>
                                    <% end_if %>
                                </div>
                            </div>
                            <div class="col-6 col-md-2 text-center mt-2 mt-md-0">
                                <small class="d-md-none">Price:</small><br />
                                <% if $Product.hasDiscount %>
                                    <del class="text-muted">$Product.OriginalPrice</del><br />
                                    <span class="text-danger fw-bold">$Product.DisplayPrice</span>
                                <% else %>
                                    <span class="text-danger fw-bold">$Product.DisplayPrice</span>
                                <% end_if %>
                            </div>
                            <div class="col-6 col-md-2 text-center mt-2 mt-md-0">
                                <small class="d-md-none">Quantity:</small><br />
                                <span class="fw-bold">$Quantity</span>
                            </div>
                            <div class="col-12 col-md-2 text-end mt-2 mt-md-0">
                                <small class="d-md-none">Subtotal:</small><br />
                                <span class="fw-bold">
                                    <% if $Product.hasDiscount %>
                                        Rp{$Product.getDisplayPriceValue.multiply($Quantity).number_format}
                                    <% else %>
                                        Rp{$Product.Price.multiply($Quantity).number_format}
                                    <% end_if %>
                                </span>
                                <br />
                                <a href="$BaseHref/cart/remove/$ID" class="btn btn-sm btn-outline-danger mt-1">Remove</a>
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
                    <h5 class="mb-3">Shopping Summary</h5>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Items</span>
                        <span>$CartItems.Count items</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Total Price</span>
                        <span class="fw-bold text-danger">
                            <% if CartItems.getTotalPrice %>
                                Rp{$CartItems.getTotalPrice.number_format}
                            <% else %>
                                Rp0
                            <% end_if %>
                        </span>
                    </div>
                    <button class="btn btn-success w-100" disabled>Checkout</button>
                </div>
            </div>
        </div>
    </section>
    <% else %>
    <div class="text-center py-5">
        <h5>Your cart is empty</h5>
        <p class="text-muted">Please add products to cart first</p>
        <a href="/" class="btn btn-primary">Shop Now</a>
    </div>
    <% end_if %>
</main>