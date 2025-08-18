<main class="container py-5">
    <% if EventShop %>
    <!-- Banner Event -->
    <div class="container-fluid p-0">
    <img
        src="$EventShop.Image.URL"
        class="img-fluid w-100"
        alt="Banner Event"
    />
    </div>

    <!-- Info Event -->
    <div class="container py-5">
    <h2 class="mb-3">$EventShop.Name</h2>
    <p class="text-muted">Start: $EventShop.StartDate, End: $EventShop.EndDate </p>
    <p>$EventShop.Description</p>
    </div>

    <!-- Produk Terkait Event -->
    <div class="container pb-5">
    <h4 class="mb-4">Product Event</h4>
    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 g-4">
        <% loop $EventShop.Product %>
            <!-- Product Card -->
            <div class="col">
                <a href="{$BaseHref}list-product/view/{$ID}" class="text-decoration-none" >
                <div class="card h-100 position-relative" style="flex: 1 1 150px; max-width: 100%">
                    <% if $hasDiscount %>
                    <span class="badge bg-danger position-absolute" style="top: 5px; left: 5px; z-index: 1; font-size: 0.6rem;">
                        $DiscountPercentage
                    </span>
                    <% end_if %>
                    
                    <% if $Image %>
                    <img src="$Image.URL" class="card-img-top" alt="$Name" style="height: 150px; object-fit: cover" />
                    <% else %>
                    <img src="https://picsum.photos/200/150?random=$ID" class="card-img-top" alt="$Name" style="height: 150px; object-fit: cover" />
                    <% end_if %>
                    
                    <div class="card-body d-flex flex-column p-2">
                        <div class="d-flex flex-column flex-grow-1">
                            <h6 class="card-subtitle mb-1 text-muted" style="font-size: 0.7rem">
                                <% if $Category %>$Category.Name<% else %>General<% end_if %>
                            </h6>
                            <h6 class="card-title mb-2" style="font-size: 0.8rem; line-height: 1.2" title="$Name">
                            <% if $Name.Length > 20 %>$Name.LimitCharacters(20)...<% else %>$Name<% end_if %>
                            </h6>
                        </div>
                        <div class="mt-auto">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <% if $AverageRating %>
                            <span class="badge bg-warning text-dark me-1" style="font-size: 0.6rem">★ $AverageRating</span>
                            <% else %>
                            <span class="badge bg-secondary text-light me-1" style="font-size: 0.6rem">★ 0</span>
                            <% end_if %>
                            <small class="text-muted" style="font-size: 0.6rem">$Stok available</small>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <!-- Display Price -->
                            <p class="card-text fw-bold text-primary mb-0" style="font-size: 0.9rem">
                            $DisplayPrice
                            </p>
                            <!-- Original Price (crossed out if discount exists) -->
                            <% if $hasDiscount && $OriginalPrice %>
                            <small class="text-muted text-decoration-line-through" style="font-size: 0.7rem">
                            $OriginalPrice
                            </small>
                            <% end_if %>
                        </div>
                        </div>
                    </div>
                </div>
                </a>
            </div>
        <% end_loop %>
    </div>
    </div>
    <% end_if %>
</main>