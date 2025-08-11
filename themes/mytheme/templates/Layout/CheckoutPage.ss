<main class="container my-4">
    <h3 class="mb-4">Checkout</h3>
    
    <% if CartItems && CartItems.Count > 0 %>
    <form method="post" action="$BaseHref/checkout/process-order">
        <!-- Alamat Pengiriman -->
        <div class="card mb-3">
            <div class="card-header fw-bold">Alamat Pengiriman</div>
            <div class="card-body">
                <% if ShippingAddress %>
                    <p class="mb-1"><strong>$ShippingAddress.ReceiverName</strong></p>
                    <p class="mb-1">$ShippingAddress.Address</p>
                    <p class="mb-0">$ShippingAddress.DistrictName, $ShippingAddress.CityName, $ShippingAddress.ProvinceName $ShippingAddress.PostalCode</p>
                    <p class="mb-0">$ShippingAddress.PhoneNumber</p>
                    <a href="$BaseHref/checkout/detail-alamat" class="btn btn-link p-0 mt-2">Ubah Alamat</a>
                <% else %>
                    <p class="mb-0 text-muted">Belum ada alamat pengiriman</p>
                    <a href="$BaseHref/checkout/detail-alamat" class="btn btn-primary mt-2">Tambah Alamat</a>
                <% end_if %>
            </div>
        </div>

        <!-- Produk di Keranjang -->
        <div class="card mb-3">
            <div class="card-header fw-bold">Produk Dipesan</div>
            <div class="card-body">
                <% loop CartItems %>
                <div class="d-flex align-items-center border-bottom pb-3 mb-3">
                    <% if $Product.Image %>
                        <img src="$Product.Image.URL" class="me-3" alt="$Product.Name" width="80" />
                    <% else %>
                        <img src="https://picsum.photos/80?random=$Product.ID" class="me-3" alt="$Product.Name" width="80" />
                    <% end_if %>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">$Product.Name</h6>
                        <small class="text-muted">Berat: $Product.Weight gr</small>
                        <p class="mb-1">$Product.DisplayPrice × $Quantity</p>
                    </div>
                    <div class="fw-bold">$FormattedSubtotal</div>
                </div>
                <% end_loop %>
            </div>
        </div>

        <!-- Pilih Kurir -->
        <div class="card mb-3">
            <div class="card-header fw-bold">Pilih Kurir</div>
            <div class="card-body">
                <% if ShippingAddress %>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Pilih Kurir</label>
                        <select id="courierSelect" class="form-select">
                            <option value="">Pilih kurir</option>
                            <option value="jne">JNE</option>
                            <option value="pos">POS Indonesia</option>
                            <option value="tiki">TIKI</option>
                            <option value="jnt">J&T</option>
                            <option value="sicepat">SiCepat</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Berat Total (gram)</label>
                        <input type="text" id="totalWeight" class="form-control" value="<% loop CartItems %>$Product.Weight<% if not $Last %> + <% end_if %><% end_loop %>" readonly>
                    </div>
                    <div class="col-md-4">
                        <button type="button" id="checkOngkirBtn" class="btn btn-primary mt-4">
                            <span class="btn-text">Cek Ongkir</span>
                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        </button>
                    </div>
                </div>
                <div id="ongkirResults" class="d-none">
                    <h6>Pilih Layanan Pengiriman:</h6>
                    <div id="ongkirOptions"></div>
                </div>
                <% else %>
                <p class="text-muted">Tambahkan alamat terlebih dahulu untuk melihat ongkir</p>
                <% end_if %>
            </div>
        </div>

        <!-- Metode Pembayaran -->
        <div class="card mb-3">
            <div class="card-header fw-bold">Metode Pembayaran</div>
            <div class="card-body">
            <select class="form-select">
                <option value="">Pilih metode pembayaran</option>
                <option value="va">Virtual Account</option>
                <option value="ewallet">E-Wallet</option>
                <option value="cc">Kartu Kredit/Debit</option>
            </select>
            </div>
        </div>

        <!-- Ringkasan Pembayaran -->
        <div class="card mb-3">
            <div class="card-header fw-bold">Ringkasan Pembayaran</div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal ($TotalItems items)</span>
                    <span>$FormattedTotalPrice</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Ongkir</span>
                    <span id="shippingCost">Rp 0</span>
                </div>
                <div class="d-flex justify-content-between fw-bold border-top pt-2">
                    <span>Total</span>
                    <span id="totalCost">$FormattedTotalPrice</span>
                </div>
                <% if ShippingAddress %>
                    <input type="hidden" name="SecurityID" value="$SecurityID">
                    <input type="hidden" name="shippingCost" id="hiddenShippingCost" value="0">
                    <input type="hidden" name="courierService" id="hiddenCourierService" value="">
                    <button type="submit" class="btn btn-success w-100 mt-3">Buat Pesanan</button>
                <% else %>
                    <button type="button" class="btn btn-secondary w-100 mt-3" disabled>Tambahkan Alamat Terlebih Dahulu</button>
                <% end_if %>
            </div>
        </div>
    </form>
    <% else %>
    <div class="text-center py-5">
        <h5>Keranjang kosong</h5>
        <p class="text-muted">Tambahkan produk ke keranjang terlebih dahulu</p>
        <a href="$BaseHref" class="btn btn-primary">Belanja Sekarang</a>
    </div>
    <% end_if %>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    const basePrice = parseInt('$TotalPrice'); // Get base price from template
    
    // Calculate total weight
    let totalWeight = 0;
    <% loop CartItems %>
    totalWeight += $Product.Weight * $Quantity;
    <% end_loop %>
    
    $('#totalWeight').val(totalWeight);

    $('#checkOngkirBtn').click(function() {
        const courier = $('#courierSelect').val();
        const districtId = '$ShippingAddress.SubDistricID';
        
        if (!courier) {
            alert('Pilih kurir terlebih dahulu');
            return;
        }
        
        if (!districtId) {
            alert('Alamat pengiriman tidak valid');
            return;
        }
        
        // Show loading state
        $(this).prop('disabled', true);
        $(this).find('.btn-text').text('Loading...');
        $(this).find('.spinner-border').removeClass('d-none');
        
        $.ajax({
            url: '$BaseHref/checkout/api/check-ongkir',
            method: 'POST',
            data: {
                district_id: districtId,
                courier: courier,
                weight: totalWeight
            },
            success: function(response) {
                displayOngkirResults(response);
            },
            error: function() {
                alert('Gagal mengecek ongkir. Silakan coba lagi.');
            },
            complete: function() {
                // Reset button state
                $('#checkOngkirBtn').prop('disabled', false);
                $('#checkOngkirBtn').find('.btn-text').text('Cek Ongkir');
                $('#checkOngkirBtn').find('.spinner-border').addClass('d-none');
            }
        });
    });
    
    function displayOngkirResults(data) {
        const resultsDiv = $('#ongkirResults');
        const optionsDiv = $('#ongkirOptions');
        
        optionsDiv.empty();
        
        if (data && data.length > 0) {
            data.forEach(function(service) {
                const serviceHtml = `
                    <div class="form-check mb-2">
                        <input class="form-check-input shipping-option" type="radio" 
                               name="shippingOption" value="${service.cost}" 
                               data-service="${service.service}" 
                               data-description="${service.description}"
                               data-etd="${service.etd}">
                        <label class="form-check-label d-flex justify-content-between w-100">
                            <span>${service.service} - ${service.description} (${service.etd})</span>
                            <strong>Rp ${formatNumber(service.cost)}</strong>
                        </label>
                    </div>
                `;
                optionsDiv.append(serviceHtml);
            });
            
            resultsDiv.removeClass('d-none');
        } else {
            optionsDiv.html('<p class="text-muted">Tidak ada layanan pengiriman tersedia</p>');
            resultsDiv.removeClass('d-none');
        }
    }
    
    // Handle shipping option selection
    $(document).on('change', '.shipping-option', function() {
        const shippingCost = parseInt($(this).val());
        const service = $(this).data('service');
        const description = $(this).data('description');
        const etd = $(this).data('etd');
        
        // Update shipping cost display
        $('#shippingCost').text('Rp ' + formatNumber(shippingCost));
        
        // Update total cost
        const totalCost = basePrice + shippingCost;
        $('#totalCost').text('Rp ' + formatNumber(totalCost));
        
        // Update hidden fields
        $('#hiddenShippingCost').val(shippingCost);
        $('#hiddenCourierService').val(`${service} - ${description} (${etd})`);
    });
    
    function formatNumber(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }
});
</script>