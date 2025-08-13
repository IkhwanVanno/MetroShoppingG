<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2>$Title</h2>
            <% if $Orders %>
                <div class="row">
                    <% loop $Orders %>
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between">
                                    <strong>$OrderCode</strong>
                                    <div>
                                        $StatusLabel.RAW
                                        $PaymentStatusLabel.RAW
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="mb-1"><strong>Tanggal:</strong> $CreateAt.Nice</p>
                                    <p class="mb-1"><strong>Total:</strong> $FormattedGrandTotal</p>
                                    <p class="mb-2"><strong>Kurir:</strong> $ShippingCourier</p>

                                    <% if $ExpiresAt && $canBePaid %>
                                        <p class="text-danger small"><strong>Batas Bayar:</strong> $ExpiresAt.Nice</p>
                                    <% end_if %>

                                    <% if $TrackingNumber %>
                                        <p><strong>Resi:</strong> <span class="text-primary">$TrackingNumber</span></p>
                                    <% end_if %>

                                    <div class="mt-3 d-flex flex-wrap gap-2">
                                        <a href="$BaseHref/order/detail/$ID" class="btn btn-primary btn-sm">Lihat Detail</a>

                                        <% if $canBePaid %>
                                            <a href="$BaseHref/payment/initiate/$ID" class="btn btn-success btn-sm">Bayar Sekarang</a>
                                        <% end_if %>

                                        <% if $canBeCancelled %>
                                            <a href="$BaseHref/order/cancel/$ID" class="btn btn-outline-danger btn-sm"
                                               onclick="return confirm('Yakin batalkan pesanan ini?')">
                                                Batalkan
                                            </a>
                                        <% end_if %>

                                        <% if $Status == 'shipped' %>
                                            <a href="$BaseHref/order/complete/$ID" class="btn btn-outline-success btn-sm"
                                               onclick="return confirm('Pesanan sudah diterima?')">
                                                Diterima
                                            </a>
                                        <% end_if %>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <% end_loop %>
                </div>
            <% else %>
                <div class="alert alert-info">
                    <h4>Belum Ada Pesanan</h4>
                    <p>Anda belum memiliki pesanan. <a href="$BaseHref">Mulai berbelanja sekarang</a>!</p>
                </div>
            <% end_if %>
        </div>
    </div>
</div>
