<?php

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

class Order extends DataObject
{
    private static $table_name = "order";
    private static $db = [
        "OrderCode" => "Varchar(255)",
        "Status" => "Enum('pending,paid,shipped,completed,cancelled', 'pending')",
        "TotalPrice" => "Double",
        "ShippingCost" => "Double",
        "PaymentMethod" => "Varchar(255)",
        "PaymentStatus" => "Enum('unpaid,paid,failed', 'unpaid')",
        "ShippingCourier" => "Varchar(255)",
        "TrackingNumber" => "Varchar(255)",
        "CreateAt" => "Datetime",
        "UpdateAt" => "Datetime",
    ];
    private static $has_one = [
        "Member" => Member::class,
        "ShippingAddress" => ShippingAddress::class,
    ];
    private static $has_many = [
        "OrderItem" => OrderItem::class,
        "PaymentTransaction" => PaymentTransaction::class,
    ];
}