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

    /**
     * Get formatted price
     */
    public function getFormattedPrice()
    {
        return number_format($this->Price, 0, '.', '.');
    }

    /**
     * Get formatted subtotal
     */
    public function getFormattedSubtotal()
    {
        return number_format($this->Subtotal, 0, '.', '.');
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