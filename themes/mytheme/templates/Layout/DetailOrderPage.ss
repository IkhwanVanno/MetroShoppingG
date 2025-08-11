<main class="container my-5">
    <% if Order %>
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Detail Pesanan</h5>
        </div>
        <div class="card-body">
            <!-- Status, Invoice, Tanggal -->
            <div class="mb-4">
                <h6 class="fw-bold">Informasi Pesanan</h6>
                <p>Status: 
                    <span class="badge bg-<% if $Order.Status == 'Pending' %>warning<% else_if $Order.Status == 'Processing' %>info<% else_if $Order.Status == 'Shipped' %>primary<% else_if $Order.Status == 'Delivered' %>success<% else %>secondary<% end_if %>">
                        $Order.Status
                    </span>
                </p>
                <p>Kode Pesanan: <strong>$Order.OrderCode</strong></p>
                <p>Tanggal Pembelian: $Order.CreatedAt.Format('d F Y H:i')</p>
                <% if Order.UpdatedAt != Order.CreatedAt %>
                    <p>Terakhir Diupdate: $Order.UpdatedAt.Format('d F Y H:i')</p>
                <% end_if %>
            </div>

            <hr />

            <!-- Produk yang Dipesan -->
            <div class="mb-4">
                <h6 class="fw-bold">Produk yang Dipesan</h6>
                <% if OrderItems && OrderItems.Count > 0 %>
                    <% loop OrderItems %>
                    <div class="d-flex align-items-center border-bottom pb-3 mb-3">
                        <% if $Product.Image %>
                            <img src="$Product.Image.URL" class="me-3" alt="$Product.Name" width="80" />
                        <% else %>
                            <img src="https://picsum.photos/80?random=$Product.ID" class="me-3" alt="$Product.Name" width="80" />
                        <% end_if %>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">$Product.Name</h6>
                            <small class="text-muted">Berat: $Product.Weight gr</small>
                            <p class="mb-1">Rp $Price.number_format(0, '.', '.') × $Quantity</p>
                        </div>
                        <div class="fw-bold">Rp $SubTotal.number_format(0, '.', '.')</div>
                    </div>
                    <% end_loop %>
                <% end_if %>
            </div>

            <hr />

            <!-- Info Pengiriman -->
            <div class="mb-4">
                <h6 class="fw-bold">Info Pengiriman</h6>
                <% if ShippingAddress %>
                    <p><strong>Penerima:</strong> $ShippingAddress.ReceiverName</p>
                    <p><strong>Telepon:</strong> $ShippingAddress.PhoneNumber</p>
                    <p><strong>Alamat:</strong> $ShippingAddress.Address</p>
                    <p><strong>Kota:</strong> $ShippingAddress.CityID</p>
                    <p><strong>Provinsi:</strong> $ShippingAddress.ProvinceID</p>
                    <p><strong>Kode Pos:</strong> $ShippingAddress.PostalCode</p>
                <% else %>
                    <p class="text-muted">Alamat pengiriman tidak tersedia</p>
                <% end_if %>
                
                <% if Order.TrackingNumber %>
                    <p><strong>Nomor Resi:</strong> $Order.TrackingNumber</p>
                <% end_if %>
            </div>

            <hr />

            <!-- Rincian Pembayaran -->
            <div class="mb-4">
                <h6 class="fw-bold">Rincian Pembayaran</h6>
                <% if OrderItems && OrderItems.Count > 0 %>
                    <% loop OrderItems %>
                    <div class="row">
                        <div class="col-8">$Product.Name (× $Quantity)</div>
                        <div class="col-4 text-end">Rp $SubTotal.number_format(0, '.', '.')</div>
                    </div>
                    <% end_loop %>
                <% end_if %>
                
                <% if Order.ShippingGoal > 0 %>
                <div class="row">
                    <div class="col-8">Ongkos Kirim</div>
                    <div class="col-4 text-end">Rp $Order.ShippingGoal.number_format(0, '.', '.')</div>
                </div>
                <% end_if %>
                
                <div class="row fw-bold border-top mt-2 pt-2">
                    <div class="col-8">Total</div>
                    <div class="col-4 text-end">$Order.FormattedTotalAmount</div>
                </div>
            </div>

            <!-- Tombol -->
            <div class="d-flex justify-content-between">
                <a href="$BaseHref/order" class="btn btn-outline-secondary">Kembali</a>
                <div>
                    <% if Order.Status == 'Shipped' %>
                        <button class="btn btn-primary me-2" onclick="confirmReceived($Order.ID)">Pesanan Diterima</button>
                    <% end_if %>
                    <% if Order.Status == 'Delivered' %>
                        <button class="btn btn-success">Beri Review</button>
                    <% end_if %>
                </div>
            </div>
        </div>
    </div>
    <% else %>
    <div class="alert alert-danger">
        <h5>Pesanan tidak ditemukan</h5>
        <p>Pesanan yang Anda cari tidak ditemukan atau tidak tersedia.</p>
        <a href="$BaseHref/order" class="btn btn-primary">Kembali ke Riwayat Pesanan</a>
    </div>
    <% end_if %>
</main>

<script>
function confirmReceived(orderID) {
    if (confirm('Apakah Anda yakin pesanan sudah diterima?')) {
        // Ajax call to update order status
        fetch('$BaseHref/order/confirm-received', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'orderID=' + orderID + '&SecurityID=' + document.querySelector('input[name="SecurityID"]').value
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Terjadi kesalahan, silakan coba lagi');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan, silakan coba lagi');
        });
    }
}
</script>