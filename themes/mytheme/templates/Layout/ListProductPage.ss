<main class="container py-5">
    <div class="row">
        <div class="col-md-3 mb-4">
            <% include SidebarFilter %>
        </div>        
        <div class="col-md-9">
            <!-- Product Grid -->
            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 g-4">
                <% loop $FilteredProducts %>
                    <!-- Product Card -->
                    <div class="col">
                        <a href="{$Up.Link}/view/{$ID}" class="text-decoration-none" >
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

            <!-- Pagination -->
            <% if $FilteredProducts.MoreThanOnePage %>
            <div class="row mt-4">
                <div class="col-12">
                    <% include Pagination Items=$FilteredProducts %>
                </div>
            </div>
            <% end_if %>
            
            <!-- No Products Message -->
            <% if not $FilteredProducts %>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <h5>No products found</h5>
                        <p class="mb-0">No products available in this category or products are out of stock.</p>
                    </div>
                </div>
            </div>
            <% end_if %>
        </div>
    </div>
</main>