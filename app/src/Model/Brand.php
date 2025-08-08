<?php

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\ORM\DataObject;

class Brand extends DataObject
{
    private static $table_name = "Brand";
    private static $db = [
        "Name" => "Varchar(50)",
        "Description" => "Text",
    ];
    private static $has_one = [
        "Image" => Image::class,
    ];
    private static $owns = [
        "Image",
    ];
    private static $summary_fields = [
        'Name' => 'Name',
        'Description' => 'Description',
        'Image.CMSThumbnail' => 'Image',
    ];
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldToTab('Root.Main', UploadField::create('Image', 'Brand Image'));
        return $fields;
    }
}