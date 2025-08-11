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
                        $Address, $CityID, $ProvinceID, $PostalCode
                    </p>
                    <p class="mb-0 small text-muted">$PhoneNumber</p>
                    <button class="btn btn-link p-0 small" data-bs-toggle="collapse" data-bs-target="#editAddress$ID">
                        Ubah
                    </button>
                </div>
            </label>

            <!-- Form edit alamat -->
            <div id="editAddress$ID" class="collapse p-3 border rounded bg-white">
                <form method="post" action="$BaseHref/checkout/update-address">
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
                        <input type="text" name="provinceID" class="form-control form-control-sm" value="$ProvinceID" required />
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Kota</label>
                        <input type="text" name="cityID" class="form-control form-control-sm" value="$CityID" required />
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Kecamatan</label>
                        <input type="text" name="subDistricID" class="form-control form-control-sm" value="$SubDistricID" required />
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
        <form method="post" action="$BaseHref/checkout/add-address">
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
                <label class="form-label small">Kota</label>
                <input type="text" name="cityID" class="form-control form-control-sm" placeholder="Kota" required />
            </div>
            <div class="mb-2">
                <label class="form-label small">Provinsi</label>
                <input type="text" name="provinceID" class="form-control form-control-sm" placeholder="Provinsi" required />
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