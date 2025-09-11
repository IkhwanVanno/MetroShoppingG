<?php

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

class CartItem extends DataObject
{
    private static $table_name = "CartItem";
    private static $db = [
        "Quantity" => "Int",
    ];
    private static $has_one = [
        "Product" => Product::class,
        "Member" => Member::class,
    ];

    // Medapatkan subtotal dikali dengan quantity
    public function getSubtotal()
    {
        return $this->Product()->getDisplayPriceValue() * $this->Quantity;
    }

    public function getFormattedSubtotal()
    {
        return 'Rp ' . number_format($this->getSubtotal(), 0, '.', '.');
    }

    // Mendapatkan subtotal dengan harga asli (sebelum diskon)
    public function getOriginalSubtotal()
    {
        return $this->Product()->Price * $this->Quantity;
    }

    public function getFormattedOriginalSubtotal()
    {
        return 'Rp ' . number_format($this->getOriginalSubtotal(), 0, '.', '.');
    }

    // Mendapatkan total diskon produk untuk item ini
    public function getProductDiscountTotal()
    {
        return $this->Product()->getProductDiscount() * $this->Quantity;
    }

    public function getFormattedProductDiscountTotal()
    {
        return 'Rp ' . number_format($this->getProductDiscountTotal(), 0, '.', '.');
    }

    // Mendapatkan total diskon FlashSale untuk item ini
    public function getFlashSaleDiscountTotal()
    {
        return $this->Product()->getFlashSaleDiscount() * $this->Quantity;
    }

    public function getFormattedFlashSaleDiscountTotal()
    {
        return 'Rp ' . number_format($this->getFlashSaleDiscountTotal(), 0, '.', '.');
    }
}