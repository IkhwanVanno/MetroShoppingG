<?php

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
}