<?php

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

class ShippingAddress extends DataObject
{
    private static $table_name = "shippingaddress";
    private static $db = [
        "ReceiverName" => "Varchar(255)",
        "PhoneNumber" => "Varchar(20)",
        "Address" => "Text",
        "CityID" => "Int",
        "ProvinceID" => "Int",
        "PostalCode" => "Varchar(10)",
        "IsDefault" => "Boolean",
    ];
    private static $has_one = [
        "Member" => Member::class,
    ];
}