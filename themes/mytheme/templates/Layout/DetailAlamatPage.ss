<main class="container my-4">
    <!-- Judul -->
    <div class="mb-4">
        <h4 class="fw-bold">Pilih Alamat Pengiriman</h4>
        <p class="text-muted">Pilih atau ubah alamat untuk pesananmu</p>
    </div>

    <!-- Daftar Alamat -->
    <div class="list-group mb-4">
        <% if ShippingAddresses && ShippingAddresses.Count > 0 %>
            <% loop ShippingAddresses %>
            <label class="list-group-item d-flex align-items-start gap-3">
                <input class="form-check-input mt-1" type="radio" name="selectedAddress" value="$ID" <% if $First %>checked<% end_if %> />
                <div>
                    <h6 class="mb-1">
                        $ReceiverName
                    </h6>
                    <p class="mb-1 small">
                        $Address, $DistrictName, $CityName, $ProvinceName, $PostalCode
                    </p>
                    <p class="mb-0 small text-muted">$PhoneNumber</p>
                    <button class="btn btn-link p-0 small" data-bs-toggle="collapse" data-bs-target="#editAddress$ID">
                        Ubah
                    </button>
                </div>
            </label>

            <!-- Form edit alamat -->
            <div id="editAddress$ID" class="collapse p-3 border rounded bg-white">
                <form method="post" action="$BaseHref/checkout/update-address" class="address-form">
                    <input type="hidden" name="addressID" value="$ID">
                    <input type="hidden" name="SecurityID" value="$SecurityID">
                    
                    <div class="mb-2">
                        <label class="form-label small">Nama Penerima</label>
                        <input type="text" name="receiverName" class="form-control form-control-sm" value="$ReceiverName" required />
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Nomor Telepon</label>
                        <input type="text" name="phoneNumber" class="form-control form-control-sm" value="$PhoneNumber" required />
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Alamat Lengkap</label>
                        <textarea name="address" class="form-control form-control-sm" rows="2" required>$Address</textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Provinsi</label>
                        <select name="provinceID" class="form-control form-control-sm province-select" data-current="$ProvinceID" data-current-name="$ProvinceName" required>
                            <option value="">Pilih Provinsi</option>
                        </select>
                        <input type="hidden" name="provinceName" value="$ProvinceName">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Kota</label>
                        <select name="cityID" class="form-control form-control-sm city-select" data-current="$CityID" data-current-name="$CityName" required>
                            <option value="">Pilih Kota</option>
                        </select>
                        <input type="hidden" name="cityName" value="$CityName">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Kecamatan</label>
                        <select name="subDistricID" class="form-control form-control-sm district-select" data-current="$SubDistricID" data-current-name="$DistrictName" required>
                            <option value="">Pilih Kecamatan</option>
                        </select>
                        <input type="hidden" name="districtName" value="$DistrictName">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Kode Pos</label>
                        <input type="text" name="postalCode" class="form-control form-control-sm" value="$PostalCode" required />
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="collapse" data-bs-target="#editAddress$ID">
                        Batal
                    </button>
                </form>
            </div>
            <% end_loop %>
        <% else %>
            <div class="alert alert-info">
                <p class="mb-0">Belum ada alamat pengiriman. Tambahkan alamat baru di bawah ini.</p>
            </div>
        <% end_if %>
    </div>

    <!-- Tombol tambah alamat baru -->
    <button class="btn btn-outline-primary w-100" data-bs-toggle="collapse" data-bs-target="#addAddressForm">
        + Tambah Alamat Baru
    </button>

    <!-- Form tambah alamat baru -->
    <div id="addAddressForm" class="collapse mt-3 p-3 border rounded bg-white">
        <form method="post" action="$BaseHref/checkout/add-address" class="address-form">
            <input type="hidden" name="SecurityID" value="$SecurityID">
            
            <div class="mb-2">
                <label class="form-label small">Nama Penerima</label>
                <input type="text" name="receiverName" class="form-control form-control-sm" placeholder="Nama penerima" required />
            </div>
            <div class="mb-2">
                <label class="form-label small">Nomor Telepon</label>
                <input type="text" name="phoneNumber" class="form-control form-control-sm" placeholder="+62 ..." required />
            </div>
            <div class="mb-2">
                <label class="form-label small">Alamat Lengkap</label>
                <textarea name="address" class="form-control form-control-sm" rows="2" placeholder="Nama jalan, no rumah, RT/RW, dsb." required></textarea>
            </div>
            <div class="mb-2">
                <label class="form-label small">Provinsi</label>
                <select name="provinceID" class="form-control form-control-sm province-select" required>
                    <option value="">Pilih Provinsi</option>
                </select>
                <input type="hidden" name="provinceName">
            </div>
            <div class="mb-2">
                <label class="form-label small">Kota</label>
                <select name="cityID" class="form-control form-control-sm city-select" required>
                    <option value="">Pilih Kota</option>
                </select>
                <input type="hidden" name="cityName">
            </div>
            <div class="mb-2">
                <label class="form-label small">Kecamatan</label>
                <select name="subDistricID" class="form-control form-control-sm district-select" required>
                    <option value="">Pilih Kecamatan</option>
                </select>
                <input type="hidden" name="districtName">
            </div>
            <div class="mb-2">
                <label class="form-label small">Kode Pos</label>
                <input type="text" name="postalCode" class="form-control form-control-sm" placeholder="Kode pos" required />
            </div>
            <button type="submit" class="btn btn-success btn-sm">
                Simpan Alamat
            </button>
        </form>
    </div>

    <!-- Tombol kembali -->
    <div class="mt-3">
        <a href="$BaseHref/checkout" class="btn btn-secondary">Kembali ke Checkout</a>
    </div>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Load provinces on page load
    loadProvinces();

    // Province change handler
    $(document).on('change', '.province-select', function() {
        const provinceId = $(this).val();
        const provinceText = $(this).find('option:selected').text();
        const form = $(this).closest('.address-form');
        const citySelect = form.find('.city-select');
        const districtSelect = form.find('.district-select');
        
        // Update hidden field
        form.find('input[name="provinceName"]').val(provinceText);
        
        // Clear dependent dropdowns
        citySelect.html('<option value="">Pilih Kota</option>');
        districtSelect.html('<option value="">Pilih Kecamatan</option>');
        
        if (provinceId) {
            loadCities(provinceId, citySelect);
        }
    });

    // City change handler
    $(document).on('change', '.city-select', function() {
        const cityId = $(this).val();
        const cityText = $(this).find('option:selected').text();
        const form = $(this).closest('.address-form');
        const districtSelect = form.find('.district-select');
        
        // Update hidden field
        form.find('input[name="cityName"]').val(cityText);
        
        // Clear district dropdown
        districtSelect.html('<option value="">Pilih Kecamatan</option>');
        
        if (cityId) {
            loadDistricts(cityId, districtSelect);
        }
    });

    // District change handler
    $(document).on('change', '.district-select', function() {
        const districtText = $(this).find('option:selected').text();
        const form = $(this).closest('.address-form');
        
        // Update hidden field
        form.find('input[name="districtName"]').val(districtText);
    });

    function loadProvinces() {
        $.get('$BaseHref/checkout/api/provinces')
            .done(function(data) {
                $('.province-select').each(function() {
                    const select = $(this);
                    const currentId = select.data('current');
                    const currentName = select.data('current-name');
                    
                    select.html('<option value="">Pilih Provinsi</option>');
                    
                    $.each(data, function(index, province) {
                        const selected = (currentId && province.id == currentId) ? 'selected' : '';
                        select.append(`<option value="${province.id}" ${selected}>${province.name}</option>`);
                    });
                    
                    // Load cities if province is selected
                    if (currentId) {
                        const form = select.closest('.address-form');
                        const citySelect = form.find('.city-select');
                        loadCities(currentId, citySelect);
                    }
                });
            })
            .fail(function() {
                console.error('Failed to load provinces');
            });
    }

    function loadCities(provinceId, citySelect) {
        $.get(`$BaseHref/checkout/api/cities/${provinceId}`)
            .done(function(data) {
                const currentCityId = citySelect.data('current');
                
                citySelect.html('<option value="">Pilih Kota</option>');
                
                $.each(data, function(index, city) {
                    const selected = (currentCityId && city.id == currentCityId) ? 'selected' : '';
                    citySelect.append(`<option value="${city.id}" ${selected}>${city.name}</option>`);
                });
                
                // Load districts if city is selected
                if (currentCityId) {
                    const form = citySelect.closest('.address-form');
                    const districtSelect = form.find('.district-select');
                    loadDistricts(currentCityId, districtSelect);
                }
            })
            .fail(function() {
                console.error('Failed to load cities');
            });
    }

    function loadDistricts(cityId, districtSelect) {
        $.get(`$BaseHref/checkout/api/districts/${cityId}`)
            .done(function(data) {
                const currentDistrictId = districtSelect.data('current');
                
                districtSelect.html('<option value="">Pilih Kecamatan</option>');
                
                $.each(data, function(index, district) {
                    const selected = (currentDistrictId && district.id == currentDistrictId) ? 'selected' : '';
                    districtSelect.append(`<option value="${district.id}" ${selected}>${district.name}</option>`);
                });
            })
            .fail(function() {
                console.error('Failed to load districts');
            });
    }
});
</script>