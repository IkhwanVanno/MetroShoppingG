<?php

use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;

class Category extends DataObject
{
    private static $table_name = "Category";
    private static $db = [
        "Name" => "Varchar(50)",
    ];
    private static $has_many = [
        "Product" => Product::class,
    ];
    private static $summary_fields = [
        'Name' => 'Name',
    ];
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldToTab('Root.Main', TextField::create('Name', 'Category Name'));
        return $fields;
    }
}