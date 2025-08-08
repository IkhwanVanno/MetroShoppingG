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
    public function getSubtotal()
    {
        if ($this->Product()->hasDiscount()) {
            return $this->Product()->getDisplayPriceValue() * $this->Quantity;
        } else {
            return $this->Product()->Price * $this->Quantity;
        }
    }
    public function getFormattedSubtotal()
    {
        return 'Rp ' . number_format($this->getSubtotal(), 0, '.', '.');
    }
}