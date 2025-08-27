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
                    <p><strong>Resi:</strong> <span class="text-primary">$Order.TrackingNumber</p>
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
                                            <a href="$BaseHref/list-product/view/$Item.Product.ID" class="text-decoration-none text-black">
                                            <% if $Item.Product.Image %>
                                                <img src="$Item.Product.Image.URL" alt="$Item.Product.Name" class="me-2 rounded" style="width:50px; height:50px; object-fit:cover;">
                                            <% else %>
                                                <div class="me-2 bg-light rounded d-flex align-items-center justify-content-center" style="width:50px; height:50px;">
                                                    <small class="text-muted">Gambar Tidak Ada</small>
                                                </div>
                                            <% end_if %>
                                            </a>
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
                                            <h6 class="mb-2">Ulasan Anda:</h6>
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
                                            <small class="text-muted">Diulas pada: $Review.FormattedDate</small>
                                        </div>
                                    <% else_if $CanReview %>
                                        <!-- Form Review -->
                                        <form action="$BaseHref/order/review/submit/$Top.Order.ID/$Item.ID" method="post" class="mt-3 p-3 bg-light rounded">
                                            <h6 class="mb-3">Berikan Ulasan:</h6>
                                            <div class="mb-2">
                                                <label class="form-label d-block">Rating *</label>
                                                <div class="star-rating" data-itemid="$Item.ID">
                                                    <% loop $Item.RatingRange %>
                                                        <span class="star" data-value="$Value">&#9733;</span>
                                                    <% end_loop %>
                                                    <input type="hidden" name="rating" id="rating-$Item.ID" required />
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="message-$Item.ID" class="form-label">Pesan *</label>
                                                <textarea class="form-control" id="message-$Item.ID" name="message" rows="3" placeholder="Tulis ulasan Anda..." required minlength="5"></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Tampilkan Nama Anda?</label>
                                                <div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="showname" id="showname-yes-$Item.ID" value="1" checked>
                                                        <label class="form-check-label" for="showname-yes-$Item.ID">Ya</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="showname" id="showname-no-$Item.ID" value="0">
                                                        <label class="form-check-label" for="showname-no-$Item.ID">Tidak</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-sm">Kirim Ulasan</button>
                                        </form>
                                    <% else %>
                                        <div class="mt-3 p-2 bg-secondary bg-opacity-10 rounded">
                                            <small class="text-muted">Ulasan hanya tersedia setelah pesanan selesai</small>
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
                    <p class="d-flex justify-content-between"><span>Biaya Pembayaran</span><span>$Order.FormattedPaymentFee</span></p>
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
            <% if $Order.PaymentStatus == 'paid' %>
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0">Tagihan & Dokumen</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">Unduh atau kirim ulang Tagihan untuk pesanan ini</p>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="$BaseHref/invoice/download/$Order.ID" class="btn btn-primary">
                                <i class="fas fa-download me-1"></i> Download Tagihan
                            </a>
                            <a href="$BaseHref/invoice/send/$Order.ID" class="btn btn-outline-primary">
                                <i class="fas fa-envelope me-1"></i> Kirim ke Email
                            </a>
                            <a href="$BaseHref/invoice/generate/$Order.ID" target="_blank" class="btn btn-outline-secondary">
                                <i class="fas fa-eye me-1"></i> Lihat Tagihan
                            </a>
                        </div>
                    </div>
                </div>
            <% end_if %>
        </div>
    </div>
</div>
<style>
    .star-rating {
        display: flex;
        flex-direction: row-reverse;
        justify-content: flex-end;
    }

    .star {
        font-size: 1.6rem;
        color: #ccc;
        cursor: pointer;
        transition: color 0.2s;
        padding: 0 3px;
    }

    .star.selected,
    .star.hovered {
        color: #f1c40f;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.star-rating').forEach(function (container) {
            const stars = container.querySelectorAll('.star');
            const input = container.querySelector('input[name="rating"]');

            stars.forEach(function (star) {
                star.addEventListener('click', function () {
                    const rating = this.getAttribute('data-value');
                    input.value = rating;

                    stars.forEach(function (s) {
                        s.classList.toggle('selected', s.getAttribute('data-value') <= rating);
                    });
                });

                // Optional: hover effect
                star.addEventListener('mouseover', function () {
                    const hoverValue = this.getAttribute('data-value');
                    stars.forEach(function (s) {
                        s.classList.toggle('hovered', s.getAttribute('data-value') <= hoverValue);
                    });
                });

                star.addEventListener('mouseout', function () {
                    stars.forEach(function (s) {
                        s.classList.remove('hovered');
                    });
                });
            });
        });
    });
</script>