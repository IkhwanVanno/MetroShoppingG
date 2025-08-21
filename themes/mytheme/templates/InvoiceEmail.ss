<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Terima Kasih - $Order.OrderCode</title>
  <style>
    body {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 14px;
      line-height: 1.6;
      margin: 0;
      padding: 0;
      background-color: #f4f4f4;
    }

    .container {
      max-width: 600px;
      margin: 20px auto;
      background-color: #ffffff;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .header {
      text-align: center;
      margin-bottom: 30px;
      padding-bottom: 20px;
      border-bottom: 2px solid #e9ecef;
    }

    .company-logo {
      max-height: 60px;
      margin-bottom: 15px;
    }

    .company-name {
      font-size: 24px;
      font-weight: bold;
      color: #2c3e50;
      margin-bottom: 5px;
    }

    .thank-you-message {
      text-align: center;
      margin-bottom: 30px;
    }

    .thank-you-title {
      font-size: 28px;
      color: #27ae60;
      font-weight: bold;
      margin-bottom: 10px;
    }

    .thank-you-text {
      font-size: 16px;
      color: #555555;
    }

    .order-info {
      background-color: #f8f9fa;
      padding: 20px;
      border-radius: 6px;
      margin-bottom: 25px;
    }

    .order-info h3 {
      color: #2c3e50;
      margin-top: 0;
      margin-bottom: 15px;
      font-size: 18px;
    }

    .info-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 8px;
      padding: 5px 0;
    }

    .info-label {
      font-weight: bold;
      color: #555555;
    }

    .info-value {
      color: #2c3e50;
    }

    .order-code {
      font-size: 15px;
      font-weight: bold;
      color: #e74c3c;
    }

    .total-amount {
      font-size: 15px;
      font-weight: bold;
      color: #27ae60;
    }

    .message-section {
      margin-bottom: 25px;
      padding: 15px;
      background-color: #fff3cd;
      border-left: 4px solid #ffc107;
      border-radius: 4px;
    }

    .message-section p {
      margin: 5px 0;
      color: #856404;
    }

    .footer {
      text-align: center;
      margin-top: 30px;
      padding-top: 20px;
      border-top: 1px solid #e9ecef;
      font-size: 12px;
      color: #6c757d;
    }

    .contact-info {
      margin-top: 15px;
    }

    .contact-info p {
      margin: 3px 0;
    }

    .attachment-note {
      background-color: #d1ecf1;
      border: 1px solid #bee5eb;
      border-radius: 4px;
      padding: 15px;
      margin-top: 20px;
      text-align: center;
    }

    .attachment-note p {
      margin: 0;
      color: #0c5460;
      font-weight: bold;
    }

    @media (max-width: 600px) {
      .container {
        margin: 10px;
        padding: 20px;
      }
      
      .info-row {
        flex-direction: column;
      }
      
      .info-label {
        margin-bottom: 3px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    
    <!-- Header -->
    <div class="header">
      <% if $LogoCID %>
        <img src="$LogoCID" alt="$SiteConfig.Title" class="company-logo" style="max-height: 60px; display: block; margin: 0 auto 15px auto;">
      <% end_if %>
      <div class="company-name">$SiteConfig.Title</div>
    </div>

    <!-- Thank You Message -->
    <div class="thank-you-message">
      <div class="thank-you-title">Terima Kasih!</div>
      <div class="thank-you-text">
        Pembayaran Anda telah berhasil diproses.<br>
        Kami akan segera memproses pesanan Anda.
      </div>
    </div>

    <!-- Order Information -->
    <div class="order-info">
      <h3>Informasi Pesanan</h3>
      
      <div class="info-row">
        <span class="info-label">Kode Pesanan:</span>
        <span class="info-value order-code">$Order.OrderCode</span>
      </div>
      
      <div class="info-row">
        <span class="info-label">Tanggal Pesanan:</span>
        <span class="info-value">$Order.CreateAt.Format('Y-m-d')</span>
      </div>
      
      <div class="info-row">
        <span class="info-label">Status:</span>
        <span class="info-value">$Order.Status</span>
      </div>
      
      <div class="info-row">
        <span class="info-label">Metode Pembayaran:</span>
        <span class="info-value">$Order.PaymentMethod</span>
      </div>
      
      <div class="info-row">
        <span class="info-label">Total Pembayaran:</span>
        <span class="info-value total-amount">$FormattedGrandTotalWithFee</span>
      </div>
      
      <% if $Order.ShippingCourier %>
      <div class="info-row">
        <span class="info-label">Kurir:</span>
        <span class="info-value">$Order.ShippingCourier</span>
      </div>
      <% end_if %>
    </div>

    <!-- Message Section -->
    <div class="message-section">
      <p><strong>Informasi Penting:</strong></p>
      <p>Pesanan Anda sedang diproses dan akan segera dikirim</p>
      <p>Anda akan menerima notifikasi ketika pesanan telah dikirim</p>
      <p>Invoice detail terlampir sebagai file PDF</p>
      <% if $SiteConfig.Phone %>
      <p>Hubungi customer service di $SiteConfig.Phone jika ada pertanyaan</p>
      <% end_if %>
    </div>

    <!-- Attachment Note -->
    <div class="attachment-note">
      <p>Invoice detail terlampir dalam email ini sebagai file PDF</p>
    </div>

    <!-- Footer -->
    <div class="footer">
      <div><strong>$SiteConfig.Title</strong></div>
      
      <div class="contact-info">
        <% if $SiteConfig.Address %>
          <p>$SiteConfig.Address</p>
        <% end_if %>
        <% if $SiteConfig.Phone %>
          <p>Telp: $SiteConfig.Phone</p>
        <% end_if %>
        <% if $SiteConfig.Email %>
          <p>Email: $SiteConfig.Email</p>
        <% end_if %>
      </div>
      
      <div style="margin-top: 15px;">
        <p>Email ini dikirim otomatis pada $InvoiceDate</p>
        <% if $SiteConfig.Credit %>
          <p>$SiteConfig.Credit</p>
        <% end_if %>
      </div>
    </div>
    
  </div>
</body>
</html>