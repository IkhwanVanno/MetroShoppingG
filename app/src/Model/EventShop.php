<?php

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\DatetimeField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;

class EventShop extends DataObject
{
    private static $table_name = "EventShop";
    private static $db = [
        "Name" => "Varchar(255)",
        "Description" => "Text",
        "Link" => "Varchar(255)",
        "StartDate" => "Datetime",
        "EndDate" => "Datetime",
    ];
    private static $has_one = [
        "Image" => Image::class,
    ];
    private static $owns = [
        "Image",
    ];
    private static $has_many = [
        "Product" => Product::class,
    ];
    private static $summary_fields = [
        "Name" => "Name",
        "Description" => "Description",
        "Link" => "Link",
        "StartDate.Nice" => "Start Date",
        "EndDate.Nice" => "End Date",
        "Image.CMSThumbnail" => "Image",
    ];
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldToTab(
            "Root.Main",
            TextField::create("Name", "Shop Name")
        );
        $fields->addFieldToTab(
            "Root.Main",
            TextareaField::create("Description", "Shop Description")
        );
        $fields->addFieldToTab(
            "Root.Main",
            TextField::create("Link", "Shop Link URL")
        );
        $fields->addFieldToTab(
            "Root.Main",
            DatetimeField::create("StartDate", "Start Date")
        );
        $fields->addFieldToTab(
            "Root.Main",
            DateTimeField::create("EndDate", "End Date")
        );
        $fields->addFieldToTab(
            "Root.Main",
            UploadField::create("Image", "Shop Image")
        );
        $productGrid = GridField::create(
            'Product',
            'Products in this Event',
            $this->Product(),
            GridFieldConfig_RecordEditor::create()
        );
        $fields->addFieldToTab('Root.Products', $productGrid);

        return $fields;

    }
}