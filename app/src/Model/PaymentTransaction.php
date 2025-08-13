<?php

use SilverStripe\ORM\DataObject;

class PaymentTransaction extends DataObject
{
    private static $table_name = "paymenttransaction";
    private static $db = [
        "PaymentGateway" => "Varchar(255)",
        "TransactionID" => "Varchar(255)",
        "Amount" => "Double",
        "Status" => "Enum('pending,success,failed','pending')",
        "ResponseData" => "Text",
        "CreateAt" => "Datetime",
    ];
    private static $has_one = [
        "Order" => Order::class,
    ];
    private static $summary_fields = [
        "Order.OrderCode"=> "Order Code",
        "PaymentGateway" => "Payment Gateway",
        "TransactionID" => "Transaction ID",
        "Amount" => "Amount",
        "Status" => "Status",
        "ResponseData" => "Response Data",
        "CreateAt" => "Create At",
    ];

    /** 
     * set default values
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if (!$this->CreateAt) {
            $this->CreateAt = date('Y-m-d H:i:s');
        }
    }
}