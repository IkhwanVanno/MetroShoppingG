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
                    <ul class="list-group mb-3">
                        <% loop $Order.OrderItem %>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <img src="$Product.Image.URL" alt="$Product.Name" class="me-2 rounded" style="width:50px; height:50px; object-fit:cover;">
                                    <div>
                                        <strong>$Product.Name</strong><br>
                                        <small>Rp $FormattedPrice x $Quantity</small>
                                    </div>
                                </div>
                                <span>Rp $FormattedSubtotal</span>
                            </li>
                        <% end_loop %>
                    </ul>
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
                        <a href="$BaseHref/payment/initiate/$Order.ID" class="btn btn-success w-100 mt-2">Bayar Sekarang</a>
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
