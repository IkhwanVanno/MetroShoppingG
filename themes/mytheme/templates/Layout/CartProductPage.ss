<main class="container py-5">
    <h3 class="mb-4">Shopping Cart</h3>

    <% if CartItems && CartItems.Count > 0 %>
    <section class="row">
        <!-- Product List -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <!-- Desktop Header -->
                    <div class="row fw-bold d-none d-md-flex mb-2">
                        <div class="col-md-5">Product</div>
                        <div class="col-md-2 text-center">Price</div>
                        <div class="col-md-2 text-center">Quantity</div>
                        <div class="col-md-2 text-end">Subtotal</div>
                    </div>

                    <!-- Product Loop -->
                    <% loop CartItems %>
                    <div class="border rounded p-3 mb-3">
                        <div class="row align-items-center">
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
                                <form method="post" action="$BaseHref/cart/update-quantity" class="d-inline">
                                    <input type="hidden" name="cartItemID" value="$ID" />
                                    <div class="input-group input-group-sm" style="width: 100px; margin: 0 auto;">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="decreaseQuantity(this)">-</button>
                                        <input type="number" name="quantity" value="$Quantity" min="1" max="$Product.Stok" 
                                            class="form-control text-center" onchange="this.form.submit()" />
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="increaseQuantity(this)">+</button>
                                    </div>
                                    <small class="text-muted">Max: $Product.Stok</small>
                                </form>
                            </div>
                            <div class="col-12 col-md-2 text-end mt-2 mt-md-0">
                                <small class="d-md-none">Subtotal:</small><br />
                                <span class="fw-bold">$FormattedSubtotal</span>
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
                        <span>$TotalItems items</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Total Price</span>
                        <span class="fw-bold text-danger">$FormattedTotalPrice</span>
                    </div>
                    <button class="btn btn-success w-100">Checkout</button>
                </div>
            </div>
        </div>
    </section>
    <% else %>
    <div class="text-center py-5">   
        <h5>Your cart is empty</h5>
        <p class="text-muted">Please add products to cart first</p>
        <a href="$BaseHref" class="btn btn-primary">Shop Now</a>
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