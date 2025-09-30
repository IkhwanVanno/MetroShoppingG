<?php

use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

class Order extends DataObject
{
    private static $table_name = "order";
    private static $db = [
        "OrderCode" => "Varchar(255)",
        "Status" => "Enum('pending,pending_payment,paid,processing,shipped,completed,cancelled', 'pending')",
        "TotalPrice" => "Double",
        "ShippingCost" => "Double",
        "PaymentFee" => "Double",
        "PaymentMethod" => "Varchar(255)",
        "PaymentStatus" => "Enum('unpaid,paid,failed,refunded', 'unpaid')",
        "ShippingCourier" => "Varchar(255)",
        "TrackingNumber" => "Varchar(255)",
        "CreateAt" => "Datetime",
        "UpdateAt" => "Datetime",
        "ExpiresAt" => "Datetime",
        "StockReduced" => "Boolean(0)",
    ];
    private static $has_one = [
        "Member" => Member::class,
        "ShippingAddress" => ShippingAddress::class,
    ];
    private static $has_many = [
        "OrderItem" => OrderItem::class,
        "PaymentTransaction" => PaymentTransaction::class,
    ];
    private static $summary_fields = [
        "Member.FirstName" => "Name",
        "OrderCode" => "Order Code",
        "Status" => "Status Order",
        "TotalPrice" => "Total Price",
        "ShippingCost" => "Shipping Cost",
        "PaymentFee" => "Payment Fee",
        "PaymentMethod" => "Payment Method",
        "PaymentStatus" => "Payment Status",
        "ShippingCourier" => "Shipping Courier",
        "TrackingNumber" => "Tracking Number",
        "CreateAt" => "Create At",
        "UpdateAt" => "Update At",
        "ExpiresAt" => "Expires At",
        "ShippingAddress.Address" => "Address",
    ];

    /**
     * Set default values and expiry time
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if (!$this->CreateAt) {
            $this->CreateAt = date('Y-m-d H:i:s');
        }

        $this->UpdateAt = date('Y-m-d H:i:s');

        if ($this->Status == 'pending' && !$this->ExpiresAt) {
            $this->ExpiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        }
    }

    /**
     * Menghitung total harga asli order (sebelum diskon)
     */
    public function getOriginalTotalPrice()
    {
        $orderItems = OrderItem::get()->filter('OrderID', $this->ID);

        $originalTotal = 0;
        foreach ($orderItems as $item) {
            $originalTotal += $item->getOriginalSubtotal();
        }

        return $originalTotal;
    }

    /**
     * Menghitung total diskon produk untuk order ini
     */
    public function getTotalProductDiscount()
    {
        $orderItems = OrderItem::get()->filter('OrderID', $this->ID);

        $totalDiscount = 0;
        foreach ($orderItems as $item) {
            $totalDiscount += $item->getProductDiscountTotal();
        }

        return $totalDiscount;
    }

    /**
     * Menghitung total diskon FlashSale untuk order ini
     */
    public function getTotalFlashSaleDiscount()
    {
        $orderItems = OrderItem::get()->filter('OrderID', $this->ID);

        $totalDiscount = 0;
        foreach ($orderItems as $item) {
            $totalDiscount += $item->getFlashSaleDiscountTotal();
        }

        return $totalDiscount;
    }

    /**
     * Format harga asli
     */
    public function getFormattedOriginalTotalPrice()
    {
        return 'Rp ' . number_format($this->getOriginalTotalPrice(), 0, '.', '.');
    }

    /**
     * Format total diskon produk
     */
    public function getFormattedTotalProductDiscount()
    {
        return 'Rp ' . number_format($this->getTotalProductDiscount(), 0, '.', '.');
    }

    /**
     * Format total diskon FlashSale
     */
    public function getFormattedTotalFlashSaleDiscount()
    {
        return 'Rp ' . number_format($this->getTotalFlashSaleDiscount(), 0, '.', '.');
    }

    /**
     * Get formatted total including shipping and payment fee
     */
    public function getGrandTotal()
    {
        return $this->TotalPrice + $this->ShippingCost + $this->PaymentFee;
    }

    /**
     * Get formatted grand total
     */
    public function getFormattedGrandTotal()
    {
        return 'Rp ' . number_format($this->getGrandTotal(), 0, '.', '.');
    }

    /**
     * Get formatted total price
     */
    public function getFormattedTotalPrice()
    {
        return 'Rp ' . number_format($this->TotalPrice, 0, '.', '.');
    }

    /**
     * Get formatted shipping cost
     */
    public function getFormattedShippingCost()
    {
        return 'Rp ' . number_format($this->ShippingCost, 0, '.', '.');
    }

    /**
     * Get formatted payment fee
     */
    public function getFormattedPaymentFee()
    {
        return 'Rp ' . number_format($this->PaymentFee, 0, '.', '.');
    }

    /**
     * Check if order can be paid
     */
    public function canBePaid()
    {
        return in_array($this->Status, ['pending', 'pending_payment']) &&
            $this->PaymentStatus == 'unpaid' &&
            !$this->isExpired();
    }

    /**
     * Check if order can be cancelled
     */
    public function canBeCancelled()
    {
        return in_array($this->Status, ['pending', 'pending_payment']) &&
            $this->PaymentStatus != 'paid';
    }

    /**
     * Check if order is expired
     */
    public function isExpired()
    {
        if (!$this->ExpiresAt) {
            return false;
        }
        return strtotime($this->ExpiresAt) < time();
    }

    /**
     * Cancel expired orders
     */
    public function checkAndCancelIfExpired()
    {
        if ($this->isExpired() && $this->canBeCancelled()) {
            $this->Status = 'cancelled';
            $this->PaymentStatus = 'failed';
            $this->write();
            return true;
        }
        return false;
    }

    /**
     * Cancel order manually
     */
    public function cancelOrder()
    {
        if ($this->canBeCancelled()) {
            $this->Status = 'cancelled';
            if ($this->PaymentStatus == 'unpaid') {
                $this->PaymentStatus = 'failed';
            }

            // Restore stock if it was already reduced
            if ($this->StockReduced) {
                $this->restoreStock();
            }

            $this->write();
            return true;
        }
        return false;
    }

    /**
     * Mark order as paid and reduce product stock
     */
    public function markAsPaid()
    {
        // Validasi stok sebelum menandai sebagai paid
        if (!$this->validateStock()) {
            error_log('Order::markAsPaid - Stock validation failed for order: ' . $this->ID);
            return false;
        }

        $this->Status = 'paid';
        $this->PaymentStatus = 'paid';

        // Kurangi stok produk jika belum dikurangi
        if (!$this->StockReduced) {
            $stockReduced = $this->reduceStock();
            if ($stockReduced) {
                $this->StockReduced = true;
                error_log('Order::markAsPaid - Stock reduced successfully for order: ' . $this->ID);
            } else {
                error_log('Order::markAsPaid - Failed to reduce stock for order: ' . $this->ID);
                return false;
            }
        }

        $this->write();
        return true;
    }

    /**
     * Validate if all products have sufficient stock
     */
    private function validateStock()
    {
        $orderItems = $this->OrderItem();

        foreach ($orderItems as $item) {
            $product = $item->Product();
            if (!$product) {
                error_log('Order::validateStock - Product not found for OrderItem: ' . $item->ID);
                return false;
            }

            if ($product->Stok < $item->Quantity) {
                error_log('Order::validateStock - Insufficient stock for product: ' . $product->ID .
                    ' (Available: ' . $product->Stok . ', Required: ' . $item->Quantity . ')');
                return false;
            }
        }

        return true;
    }

    /**
     * Reduce stock for all products in the order
     */
    private function reduceStock()
    {
        $orderItems = $this->OrderItem();
        $updatedProducts = [];

        try {
            foreach ($orderItems as $item) {
                $product = $item->Product();
                if (!$product) {
                    throw new Exception('Product not found for OrderItem: ' . $item->ID);
                }

                // Validasi stok sekali lagi sebelum pengurangan
                if ($product->Stok < $item->Quantity) {
                    throw new Exception('Insufficient stock for product: ' . $product->Name .
                        ' (Available: ' . $product->Stok . ', Required: ' . $item->Quantity . ')');
                }

                // Kurangi stok
                $oldStock = $product->Stok;
                $product->Stok = $product->Stok - $item->Quantity;
                $product->write();

                $updatedProducts[] = [
                    'product' => $product,
                    'oldStock' => $oldStock,
                    'quantity' => $item->Quantity
                ];

                error_log('Order::reduceStock - Product: ' . $product->Name .
                    ' stock reduced from ' . $oldStock . ' to ' . $product->Stok);
            }

            return true;

        } catch (Exception $e) {
            error_log('Order::reduceStock - Error: ' . $e->getMessage());

            // Rollback: kembalikan stok yang sudah dikurangi
            foreach ($updatedProducts as $productData) {
                $product = $productData['product'];
                $product->Stok = $productData['oldStock'];
                $product->write();
                error_log('Order::reduceStock - Rollback: Product ' . $product->Name .
                    ' stock restored to ' . $productData['oldStock']);
            }

            return false;
        }
    }

    /**
     * Restore stock when order is cancelled (only if stock was already reduced)
     */
    private function restoreStock()
    {
        $orderItems = $this->OrderItem();

        foreach ($orderItems as $item) {
            $product = $item->Product();
            if ($product) {
                $oldStock = $product->Stok;
                $product->Stok = $product->Stok + $item->Quantity;
                $product->write();

                error_log('Order::restoreStock - Product: ' . $product->Name .
                    ' stock restored from ' . $oldStock . ' to ' . $product->Stok);
            }
        }

        $this->StockReduced = false;
    }

    /**
     * Mark order as processing
     */
    public function markAsProcessing()
    {
        if ($this->Status == 'paid' && $this->PaymentStatus == 'paid') {
            $this->Status = 'processing';
            $this->write();
            return true;
        }
        return false;
    }

    /**
     * Mark order as shipped
     */
    public function markAsShipped($trackingNumber = null)
    {
        if (in_array($this->Status, ['paid', 'processing'])) {
            $this->Status = 'shipped';
            if ($trackingNumber) {
                $this->TrackingNumber = $trackingNumber;
            }
            $this->write();
            return true;
        }
        return false;
    }

    /**
     * Mark order as completed
     */
    public function markAsCompleted()
    {
        if ($this->Status == 'shipped') {
            $this->Status = 'completed';
            $this->write();
            return true;
        }
        // Update membership tier member setelah order completed
        if ($this->MemberID) {
            MembershipService::onOrderCompleted($this->ID);
        }
        return false;
    }

    /**
     * Get status label with color
     */
    public function getStatusLabel()
    {
        switch ($this->Status) {
            case 'pending':
                return '<span class="badge bg-secondary">Menunggu Konfirmasi</span>';
            case 'pending_payment':
                return '<span class="badge bg-warning">Menunggu Pembayaran</span>';
            case 'paid':
                return '<span class="badge bg-success">Dibayar</span>';
            case 'processing':
                return '<span class="badge bg-info">Diproses</span>';
            case 'shipped':
                return '<span class="badge bg-primary">Dikirim</span>';
            case 'completed':
                return '<span class="badge bg-success">Selesai</span>';
            case 'cancelled':
                return '<span class="badge bg-danger">Dibatalkan</span>';
            default:
                return '<span class="badge bg-secondary">Unknown</span>';
        }
    }

    /**
     * Check if order is completed and can be reviewed
     */
    public function canBeReviewed()
    {
        return $this->Status == 'completed';
    }

    /**
     * Get payment status label
     */
    public function getPaymentStatusLabel()
    {
        switch ($this->PaymentStatus) {
            case 'paid':
                return '<span class="badge bg-success">Lunas</span>';
            case 'failed':
                return '<span class="badge bg-danger">Gagal</span>';
            case 'refunded':
                return '<span class="badge bg-warning">Dikembalikan</span>';
            default:
                return '<span class="badge bg-secondary">Belum Bayar</span>';
        }
    }

    /**
     * Get range helper for templates
     */
    public function Range($start, $end)
    {
        $result = [];
        for ($i = $start; $i <= $end; $i++) {
            $result[] = ['Pos' => $i];
        }
        return new ArrayList($result);
    }
}