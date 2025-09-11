<?php

use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;

class OrderItem extends DataObject
{
    private static $table_name = "orderitem";
    private static $db = [
        "Quantity" => "Int",
        "Price" => "Double",
        "Subtotal" => "Double",
    ];
    private static $has_one = [
        "Order" => Order::class,
        "Product" => Product::class,
    ];
    private static $has_many = [
        "Review" => Review::class,
    ];

    /**
     * Mendapatkan subtotal dengan harga asli (sebelum diskon)
     * Menggunakan harga produk saat ini untuk perhitungan retrospektif
     */
    public function getOriginalSubtotal()
    {
        if ($this->Product() && $this->Product()->exists()) {
            return $this->Product()->Price * $this->Quantity;
        }

        // Fallback jika produk sudah tidak ada
        // Estimasi harga asli berdasarkan harga yang tersimpan
        return $this->Price * $this->Quantity;
    }

    public function getFormattedOriginalSubtotal()
    {
        return 'Rp ' . number_format($this->getOriginalSubtotal(), 0, '.', '.');
    }

    /**
     * Mendapatkan total diskon produk untuk item ini
     * Berdasarkan kondisi produk saat order dibuat
     */
    public function getProductDiscountTotal()
    {
        if ($this->Product() && $this->Product()->exists()) {
            return $this->Product()->getProductDiscount() * $this->Quantity;
        }

        // Fallback calculation jika produk sudah tidak ada
        $originalPrice = $this->getOriginalSubtotal() / $this->Quantity;
        $discountAmount = max(0, $originalPrice - $this->Price);
        return $discountAmount * $this->Quantity;
    }

    public function getFormattedProductDiscountTotal()
    {
        return 'Rp ' . number_format($this->getProductDiscountTotal(), 0, '.', '.');
    }

    /**
     * Mendapatkan total diskon FlashSale untuk item ini
     * Berdasarkan kondisi FlashSale saat order dibuat
     */
    public function getFlashSaleDiscountTotal()
    {
        if ($this->Product() && $this->Product()->exists()) {
            return $this->Product()->getFlashSaleDiscount() * $this->Quantity;
        }

        // Untuk order lama, kita tidak bisa menghitung FlashSale retrospektif
        // Karena FlashSale bersifat temporal
        return 0;
    }

    public function getFormattedFlashSaleDiscountTotal()
    {
        return 'Rp ' . number_format($this->getFlashSaleDiscountTotal(), 0, '.', '.');
    }

    /**
     * Format price yang tersimpan
     */
    public function getFormattedPrice()
    {
        return 'Rp ' . number_format($this->Price, 0, '.', '.');
    }

    /**
     * Format subtotal yang tersimpan
     */
    public function getFormattedSubtotal()
    {
        return 'Rp ' . number_format($this->Subtotal, 0, '.', '.');
    }

    /**
     * Check if this order item has been reviewed
     */
    public function hasReview()
    {
        return $this->Review()->exists();
    }

    /**
     * Get the review for this order item
     */
    public function getReview()
    {
        return $this->Review()->first();
    }

    /**
     * Check if this order item can be reviewed
     */
    public function canBeReviewed()
    {
        return $this->Order()->Status == 'completed' && !$this->hasReview();
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