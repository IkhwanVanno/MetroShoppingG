<?php

use SilverStripe\ORM\DataObject;

class PaymentTransaction extends DataObject
{
    private static $table_name = "paymenttransaction";
    private static $db = [
        "PaymentGateway" => "Varchar(255)",
        "TransactionID" => "varchar(255)",
        "Amount" => "Double",
        "Status" => "Enum('pending,success,failed','pending')",
        "ResponseData" => "Text",
        "CreateAt" => "Datetime",
    ];
    private static $has_one = [
        "Order" => Order::class,
    ];
}