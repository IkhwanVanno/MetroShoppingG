<?php

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;

class SocialMedia extends DataObject
{
    private static $table_name = "SocialMedia";
    private static $db = [
        "Name" => "Varchar(50)",
        "Link" => "Varchar(255)",
    ];
    private static $has_one = [
        "Image" => Image::class,
    ];
    private static $owns = [
        "Image",
    ];
    private static $summary_fields = [
        'Name' => 'Name',
        'Link' => 'Link',
        'Image.CMSThumbnail' => 'Image',
    ];
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldToTab('Root.Main', TextField::create('Name', 'Social Media Name'));
        $fields->addFieldToTab('Root.Main', TextField::create('Link', 'Social Media Link'));
        $fields->addFieldToTab('Root.Main', UploadField::create('Image', 'Image'));
        return $fields;
    }
}