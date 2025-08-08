<?php

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

class Favorite extends DataObject
{
    private static $table_name = "Favorite";
    private static $has_one = [
        "Product" => Product::class,
        "Member" => Member::class,
    ];
}