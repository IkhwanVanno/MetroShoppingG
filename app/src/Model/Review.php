<?php

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

class Review extends DataObject
{
    private static $table_name = "Review";
    private static $db = [
        "Rating" => "Int",
        "Message" => "Text",
    ];
    private static $has_one = [
        "Product" => Product::class,
        "Member" => Member::class,
    ];
}