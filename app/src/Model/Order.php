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
        "PaymentMethod" => "Varchar(255)",
        "PaymentStatus" => "Enum('unpaid,paid,failed,refunded', 'unpaid')",
        "ShippingCourier" => "Varchar(255)",
        "TrackingNumber" => "Varchar(255)",
        "CreateAt" => "Datetime",
        "UpdateAt" => "Datetime",
        "ExpiresAt" => "Datetime",
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
     * Get formatted total including shipping
     */
    public function getGrandTotal()
    {
        return $this->TotalPrice + $this->ShippingCost;
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
        $this->Status = 'paid';
        $this->PaymentStatus = 'paid';
        $this->reduceProductStock();

        $this->write();
    }

    /**
     * Reduce product stock based on order items
     */
    private function reduceProductStock()
    {
        $orderItems = OrderItem::get()->filter('OrderID', $this->ID);

        foreach ($orderItems as $orderItem) {
            $product = Product::get()->byID($orderItem->ProductID);
            if ($product && $product->Stok >= $orderItem->Quantity) {
                $product->Stok = $product->Stok - $orderItem->Quantity;
                $product->write();
            }
        }
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