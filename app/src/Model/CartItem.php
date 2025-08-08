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
}