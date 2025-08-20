<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Invoice - $Order.OrderCode</title>
  <style>
    @page {
      size: A4;
      margin: 1cm;
    }

    body {
      font-family: DejaVu Sans, Arial, sans-serif;
      font-size: 10px;
      line-height: 1.4;
      margin: 0;
      padding: 0;
    }

    .container {
      width: 100%;
      max-width: 800px;
      margin: 0 auto;
      padding: 10px;
    }

    .row {
      display: flex;
      flex-wrap: wrap;
      margin: 0 -5px;
    }

    .col-6 {
      flex: 0 0 50%;
      padding: 0 5px;
      box-sizing: border-box;
    }

    .col-8 {
      flex: 0 0 66.6667%;
      padding: 0 5px;
      box-sizing: border-box;
    }

    .col-4 {
      flex: 0 0 33.3333%;
      padding: 0 5px;
      box-sizing: border-box;
    }

    .text-end {
      text-align: right;
    }

    .text-center {
      text-align: center;
    }

    .company-logo {
      max-height: 40px;
    }

    .invoice-title {
      font-size: 20px;
      font-weight: bold;
      color: #343a40;
      margin-bottom: 5px;
    }

    .invoice-number {
      font-size: 11px;
      color: #6c757d;
    }

    h5, h6 {
      font-size: 11px;
      margin-bottom: 6px;
    }

    p, td, th {
      margin: 0;
      padding: 2px 4px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    table th {
      background-color: #f8f9fa;
      border: 1px solid #dee2e6;
      font-size: 10px;
      text-align: left;
    }

    table td {
      border: 1px solid #dee2e6;
      font-size: 10px;
    }

    .table-sm td, .table-sm th {
      padding: 4px;
    }

    .total-section {
      background-color: #f8f9fa;
      padding: 8px;
      border-radius: 4px;
    }

    .footer {
      margin-top: 20px;
      border-top: 1px solid #dee2e6;
      font-size: 9px;
      color: #6c757d;
      padding-top: 10px;
      text-align: center;
    }

    .invoice-header {
      margin-bottom: 20px;
      border-bottom: 1px solid #dee2e6;
      padding-bottom: 10px;
    }

    .invoice-details {
      margin-bottom: 20px;
    }

    .invoice-table {
      margin-bottom: 15px;
    }

    .mt-4 {
      margin-top: 12px !important;
    }

    .border {
      border: 1px solid #dee2e6;
    }

    .p-2 {
      padding: 8px;
    }

    ul.list-unstyled {
      margin: 0;
      padding-left: 18px;
      list-style: disc;
    }

    ul.list-unstyled li {
      margin-bottom: 3px;
    }

  </style>
</head>
<body>
  <div class="container">

    <!-- Header -->
    <div class="invoice-header row">
      <div class="col-8">
        <% if $SiteConfig.logo %>
          <img src="$SiteConfig.logo.URL" alt="Company Logo" class="company-logo mb-1">
        <% end_if %>
        <div>
          <strong>$SiteConfig.Title</strong><br>
          <% if $SiteConfig.Address %>$SiteConfig.Address<br><% end_if %>
          <% if $SiteConfig.Phone %>Telp: $SiteConfig.Phone<br><% end_if %>
          <% if $SiteConfig.Email %>Email: $SiteConfig.Email<% end_if %>
        </div>
      </div>
      <div class="col-4 text-end">
        <div class="invoice-title">INVOICE</div>
        <div class="invoice-number">No: $InvoiceNumber</div>
        <div>Tanggal: $InvoiceDate</div>
      </div>
    </div>

    <!-- Customer & Order Details -->
    <div class="invoice-details row">
      <div class="col-6">
        <h6>Tagihan Untuk:</h6>
        <div class="border p-2">
          <strong>$Member.FirstName $Member.Surname</strong><br>
          <% if $ShippingAddress %>
            $ShippingAddress.ReceiverName<br>
            $ShippingAddress.Address<br>
            $ShippingAddress.DistrictName, $ShippingAddress.CityName<br>
            $ShippingAddress.ProvinceName $ShippingAddress.PostalCode<br>
            Telp: $ShippingAddress.PhoneNumber
          <% end_if %>
        </div>
      </div>
      <div class="col-6">
        <h6>Detail Pesanan:</h6>
        <table class="table-sm">
          <tr><td>Kode Pesanan:</td><td>$Order.OrderCode</td></tr>
          <tr><td>Tanggal Pesanan:</td><td>$Order.CreateAt.Format('Y-m-d')</td></tr>
          <tr><td>Status:</td><td>$Order.Status</td></tr>
          <tr><td>Metode Pembayaran:</td><td>$Order.PaymentMethod</td></tr>
          <tr><td>Kurir:</td><td>$Order.ShippingCourier</td></tr>
        </table>
      </div>
    </div>

    <!-- Order Items Table -->
    <div class="invoice-table">
      <table>
        <thead>
          <tr>
            <th width="5%" class="text-center">No</th>
            <th width="40%">Nama Produk</th>
            <th width="10%" class="text-center">Qty</th>
            <th width="15%" class="text-end">Harga Satuan</th>
            <th width="20%" class="text-end">Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <% loop $OrderItems %>
            <tr>
              <td class="text-center">$Pos</td>
              <td>$Product.Name</td>
              <td class="text-center">$Quantity</td>
              <td class="text-end">Rp $FormattedPrice</td>
              <td class="text-end"><strong>Rp $FormattedSubtotal</strong></td>
            </tr>
          <% end_loop %>
        </tbody>
      </table>
    </div>

    <!-- Total Section -->
    <div class="row">
      <div class="col-8"></div>
      <div class="col-4">
        <div class="total-section">
          <table class="table-sm">
            <tr><td>Subtotal Produk:</td><td class="text-end">$Order.FormattedTotalPrice</td></tr>
            <tr><td>Ongkos Kirim:</td><td class="text-end">$Order.FormattedShippingCost</td></tr>
            <% if $PaymentFee %>
              <tr><td>Biaya Pembayaran:</td><td class="text-end">$FormattedPaymentFee</td></tr>
            <% end_if %>
            <tr>
              <td><strong>TOTAL:</strong></td>
              <td class="text-end"><strong>Rp $FormattedGrandTotalWithFee</strong></td>
            </tr>
          </table>
        </div>
      </div>
    </div>

    <!-- Notes -->
    <div class="mt-4">
      <h6>Catatan:</h6>
      <ul class="list-unstyled">
        <li>Invoice ini merupakan bukti pembayaran yang sah</li>
        <li>Barang akan dikirim sesuai alamat pengiriman</li>
        <% if $SiteConfig.Phone %>
          <li>Hubungi CS: $SiteConfig.Phone</li>
        <% end_if %>
      </ul>
    </div>

    <!-- Footer -->
    <div class="footer">
      <div><strong>$SiteConfig.Title</strong></div>
      <div>Generated otomatis pada $InvoiceDate</div>
      <% if $SiteConfig.Credit %>
        <div>$SiteConfig.Credit</div>
      <% end_if %>
    </div>
  </div>
</body>
</html>
