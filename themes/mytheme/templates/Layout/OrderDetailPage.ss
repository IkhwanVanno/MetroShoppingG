<div class="container py-4">

    <!-- Alerts -->
    <% if $Session.PaymentSuccess %>
        <div class="alert alert-success alert-dismissible fade show">
            $Session.PaymentSuccess
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <% end_if %>
    <% if $Session.PaymentError %>
        <div class="alert alert-danger alert-dismissible fade show">
            $Session.PaymentError
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <% end_if %>
    <% if $Session.ReviewSuccess %>
        <div class="alert alert-success alert-dismissible fade show">
            $Session.ReviewSuccess
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <% end_if %>
    <% if $Session.ReviewError %>
        <div class="alert alert-danger alert-dismissible fade show">
            $Session.ReviewError
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <% end_if %>

    <div class="row">
        <!-- Order Details -->
        <div class="col-md-8 mb-3">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">$Title</h5>
                    <div>
                        <% if $Order.canBeCancelled %>
                            <a href="$BaseHref/order/cancel/$Order.ID" class="btn btn-danger btn-sm">Batalkan</a>
                        <% end_if %>
                        <% if $Order.Status == 'shipped' %>
                            <a href="$BaseHref/order/complete/$Order.ID" class="btn btn-success btn-sm">Terima</a>
                        <% end_if %>
                    </div>
                </div>
                <div class="card-body">
                    <p><strong>Kode:</strong> $Order.OrderCode</p>
                    <p><strong>Status:</strong> $Order.StatusLabel.RAW</p>
                    <p><strong>Pembayaran:</strong> $Order.PaymentStatusLabel.RAW</p>
                    <p><strong>Tanggal:</strong> $Order.CreateAt.Nice</p>

                    <% if $Order.ExpiresAt && $Order.canBePaid %>
                        <p class="text-danger"><strong>Batas:</strong> $Order.ExpiresAt.Nice</p>
                    <% end_if %>

                    <hr>
                    <h6>Produk</h6>

                    <% if $OrderItemsWithReview %>
                        <ul class="list-group mb-3">
                            <% loop $OrderItemsWithReview %>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <% if $Item.Product.Image %>
                                                <img src="$Item.Product.Image.URL" alt="$Item.Product.Name" class="me-2 rounded" style="width:50px; height:50px; object-fit:cover;">
                                            <% else %>
                                                <div class="me-2 bg-light rounded d-flex align-items-center justify-content-center" style="width:50px; height:50px;">
                                                    <small class="text-muted">No Image</small>
                                                </div>
                                            <% end_if %>
                                            <div>
                                                <strong>$Item.Product.Name</strong><br>
                                                <small>Rp $Item.FormattedPrice x $Item.Quantity</small>
                                            </div>
                                        </div>
                                        <span>Rp $Item.FormattedSubtotal</span>
                                    </div>

                                    <% if $HasReview %>
                                        <!-- Review yang sudah ada -->
                                        <div class="mt-3 p-3 bg-light rounded">
                                            <h6 class="mb-2">Review Anda:</h6>
                                            <div class="mb-2">
                                                <strong>Rating:</strong>
                                                <% if $Review.Rating >= 1 %><span class="text-warning">★</span><% end_if %>
                                                <% if $Review.Rating >= 2 %><span class="text-warning">★</span><% end_if %>
                                                <% if $Review.Rating >= 3 %><span class="text-warning">★</span><% end_if %>
                                                <% if $Review.Rating >= 4 %><span class="text-warning">★</span><% end_if %>
                                                <% if $Review.Rating >= 5 %><span class="text-warning">★</span><% end_if %>
                                                <% if $Review.Rating < 5 %><span class="text-muted">☆</span><% end_if %>
                                                <% if $Review.Rating < 4 %><span class="text-muted">☆</span><% end_if %>
                                                <% if $Review.Rating < 3 %><span class="text-muted">☆</span><% end_if %>
                                                <% if $Review.Rating < 2 %><span class="text-muted">☆</span><% end_if %>
                                                <% if $Review.Rating < 1 %><span class="text-muted">☆</span><% end_if %>
                                                ($Review.Rating/5)
                                            </div>
                                            <div class="mb-2">
                                                <strong>Pesan:</strong><br>
                                                $Review.Message
                                            </div>
                                            <small class="text-muted">Direview pada: $Review.FormattedDate</small>
                                        </div>
                                    <% else_if $CanReview %>
                                        <!-- Form Review -->
                                        <form action="$BaseHref/order/review/submit/$Top.Order.ID/$Item.ID" method="post" class="mt-3 p-3 bg-light rounded">
                                            <h6 class="mb-3">Berikan Review:</h6>

                                            <div class="mb-2">
                                                <label class="form-label">Rating *</label>
                                                <div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="rating" id="rating-$Item.ID-1" value="1" required>
                                                        <label class="form-check-label" for="rating-$Item.ID-1">1</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="rating" id="rating-$Item.ID-2" value="2">
                                                        <label class="form-check-label" for="rating-$Item.ID-2">2</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="rating" id="rating-$Item.ID-3" value="3">
                                                        <label class="form-check-label" for="rating-$Item.ID-3">3</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="rating" id="rating-$Item.ID-4" value="4">
                                                        <label class="form-check-label" for="rating-$Item.ID-4">4</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="rating" id="rating-$Item.ID-5" value="5">
                                                        <label class="form-check-label" for="rating-$Item.ID-5">5</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="message-$Item.ID" class="form-label">Pesan *</label>
                                                <textarea class="form-control" id="message-$Item.ID" name="message" rows="3" placeholder="Tulis ulasan Anda..." required minlength="5"></textarea>
                                            </div>

                                            <button type="submit" class="btn btn-primary btn-sm">Kirim Review</button>
                                        </form>
                                    <% else %>
                                        <div class="mt-3 p-2 bg-secondary bg-opacity-10 rounded">
                                            <small class="text-muted">Review hanya tersedia setelah pesanan selesai</small>
                                        </div>
                                    <% end_if %>
                                </li>
                            <% end_loop %>
                        </ul>
                    <% else %>
                        <div class="alert alert-warning">
                            Tidak ada item dalam pesanan ini.
                        </div>
                    <% end_if %>

                </div>
            </div>
        </div>

        <!-- Payment Summary -->
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-header">Ringkasan</div>
                <div class="card-body">
                    <p class="d-flex justify-content-between"><span>Subtotal</span><span>$Order.FormattedTotalPrice</span></p>
                    <p class="d-flex justify-content-between"><span>Ongkir</span><span>$Order.FormattedShippingCost</span></p>
                    <hr>
                    <p class="d-flex justify-content-between fw-bold"><span>Total</span><span>$Order.FormattedGrandTotal</span></p>

                    <% if $Order.canBePaid %>
                        <a href="$BaseHref/payment/initiate/$Order.ID" target="_blank" class="btn btn-success w-100 mt-2">Bayar Sekarang</a>
                    <% end_if %>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">Alamat</div>
                <div class="card-body small">
                    <p class="mb-1"><strong>$Order.ShippingAddress.ReceiverName</strong></p>
                    <p class="mb-1">$Order.ShippingAddress.PhoneNumber</p>
                    <p class="mb-1">$Order.ShippingAddress.Address</p>
                    <p class="mb-0">$Order.ShippingAddress.CityName, $Order.ShippingAddress.ProvinceName $Order.ShippingAddress.PostalCode</p>
                </div>
            </div>
        </div>
    </div>
</div>
