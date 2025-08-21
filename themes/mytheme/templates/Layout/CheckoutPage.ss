<main class="container my-4">
    <h3 class="mb-4">Proses Pembayaran</h3>
    
    <% if $Session.CheckoutError %>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        $Session.CheckoutError
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <% end_if %>
    
    <% if CartItems && CartItems.Count > 0 %>
    <form method="post" action="$BaseHref/checkout/process-order" target="_blank" id="checkoutForm">
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
                    <a href="$BaseHref/list-product/view/$Product.ID" class="text-decoration-none text-black">
                    <% if $Product.Image %>
                        <img src="$Product.Image.URL" class="me-3" alt="$Product.Name" width="80" />
                    <% else %>
                        <img src="https://picsum.photos/80?random=$Product.ID" class="me-3" alt="$Product.Name" width="80" />
                    <% end_if %>
                    </a>
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
                        <input type="text" id="totalWeight" class="form-control" value="$TotalWeight" readonly>
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
                <% if PaymentMethods && PaymentMethods.Count > 0 %>
                <div class="row">
                    <% loop PaymentMethods %>
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input payment-method" type="radio" 
                                   name="paymentMethod" value="$paymentMethod" 
                                   data-fee="$totalFee" id="payment_$paymentMethod">
                            <label class="form-check-label w-100" for="payment_$paymentMethod">
                                <div class="d-flex justify-content-between">
                                    <span>$paymentName</span>
                                    <small class="text-muted">+ $formattedFee</small>
                                </div>
                            </label>
                        </div>
                    </div>
                    <% end_loop %>
                </div>
                <% else %>
                <p class="text-muted">Metode pembayaran tidak tersedia</p>
                <% end_if %>
            </div>
        </div>

        <!-- Ringkasan Pembayaran -->
        <div class="card mb-3">
            <div class="card-header fw-bold">Ringkasan Pembayaran</div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal ($TotalItems items)</span>
                    <span id="subtotalAmount">$FormattedTotalPrice</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Ongkir</span>
                    <span id="shippingCost">Rp 0</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Biaya Pembayaran</span>
                    <span id="paymentFee">Rp 0</span>
                </div>
                <div class="d-flex justify-content-between fw-bold border-top pt-2">
                    <span>Total</span>
                    <span id="totalCost">$FormattedTotalPrice</span>
                </div>
                
                <% if ShippingAddress %>
                    <input type="hidden" name="SecurityID" value="$SecurityID">
                    <input type="hidden" name="shippingCost" id="hiddenShippingCost" value="0">
                    <input type="hidden" name="courierService" id="hiddenCourierService" value="">
                    <button type="submit" id="submitOrderBtn" class="btn btn-success w-100 mt-3" disabled>
                        Lanjutkan ke Pembayaran
                    </button>
                <% else %>
                    <button type="button" class="btn btn-secondary w-100 mt-3" disabled>
                        Tambahkan Alamat Terlebih Dahulu
                    </button>
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
    let selectedShippingCost = 0;
    let selectedPaymentFee = 0;

    // Event handler untuk tombol cek ongkir
    $('#checkOngkirBtn').click(function() {
        const courier = $('#courierSelect').val();
        const districtId = '$ShippingAddress.SubDistricID';
        const totalWeight = $('#totalWeight').val();
        
        if (!courier) {
            alert('Pilih kurir terlebih dahulu');
            return;
        }
        
        if (!districtId) {
            alert('Alamat pengiriman tidak valid');
            return;
        }
        
        // Tampilkan loading state
        $(this).prop('disabled', true);
        $(this).find('.btn-text').text('Loading...');
        $(this).find('.spinner-border').removeClass('d-none');
        
        // Request ke server untuk cek ongkir
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
    
    // Fungsi untuk menampilkan hasil ongkir
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
    
    // Event handler ketika memilih layanan pengiriman
    $(document).on('change', '.shipping-option', function() {
        selectedShippingCost = parseInt($(this).val());
        const service = $(this).data('service');
        const description = $(this).data('description');
        const etd = $(this).data('etd');
        
        // Update hidden fields
        $('#hiddenShippingCost').val(selectedShippingCost);
        $('#hiddenCourierService').val(`${service} - ${description} (${etd})`);
        
        updateTotal();
        checkFormValidity();
    });

    // Event handler untuk payment method
    $(document).on('change', '.payment-method', function() {
        selectedPaymentFee = parseInt($(this).data('fee')) || 0;
        updateTotal();
        checkFormValidity();
    });

    // Update total calculation
    function updateTotal() {
        $.ajax({
            url: '$BaseHref/checkout/api/calculate-total',
            method: 'POST',
            data: {
                shipping_cost: selectedShippingCost,
                payment_fee: selectedPaymentFee
            },
            success: function(response) {
                $('#shippingCost').text(response.formatted_shipping_cost);
                $('#paymentFee').text(response.formatted_payment_fee);
                $('#totalCost').text(response.formatted_total_cost);
            },
            error: function() {
                console.error('Gagal menghitung total');
            }
        });
    }

    // Check if form is valid to enable submit button
    function checkFormValidity() {
        const hasShipping = selectedShippingCost > 0;
        const hasPayment = $('input[name="paymentMethod"]:checked').length > 0;
        
        if (hasShipping && hasPayment) {
            $('#submitOrderBtn').prop('disabled', false);
        } else {
            $('#submitOrderBtn').prop('disabled', true);
        }
    }

    // Form validation before submit
    $('#checkoutForm').submit(function(e) {
        if (!$('input[name="paymentMethod"]:checked').length) {
            e.preventDefault();
            alert('Pilih metode pembayaran');
            return false;
        }

        if (selectedShippingCost <= 0) {
            e.preventDefault();
            alert('Pilih layanan pengiriman');
            return false;
        }

        // Show loading state on submit
        $('#submitOrderBtn').prop('disabled', true).text('Memproses...');

        setTimeout(function() {
            location.reload();
        }, 2000);
    });
    
    // Fungsi helper untuk format number
    function formatNumber(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }
});
</script>