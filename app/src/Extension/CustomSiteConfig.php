<?php

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;

class CustomSiteConfig extends DataExtension
{
    private static $table_name = 'CompanyConfig';
    private static $db = [
        "Email" => "Varchar(255)",
        "Phone" => "Varchar(20)",
        "Address" => "Text",
        "CompanyProvinceID" =>"Int",
        "CompanyCityID"=> "Int",
        "CompanyDistricID"=> "Int",
        "CompanyPostalCode"=> "Int",
        "Credit"=> "Varchar(255)",
        "AboutTitle"=> "Varchar(255)",
        "AboutDescription"=> "Text",
        "SubAbout1Title"=> "Varchar(255)",
        "SubAbout1Description"=>"Text",
        "SubAbout2Title"=> "Varchar(255)",
        "SubAbout2Description"=>"Text",
        "SubAbout3Title"=> "Varchar(255)",
        "SubAbout3Description"=>"Text",
        "SubAbout4Title"=> "Varchar(255)",
        "SubAbout4Description"=>"Text",
    ];
    private static $has_one = [
        "favicon" => Image::class,
        "logo" => Image::class,
    ];
    private static $owns = [
        "favicon",
        "logo",
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab('Root.Main', [
            TextField::create('Email', 'Email Company,'),
            TextField::create('Phone', 'Phone Company'),
            TextField::create('Address', 'Address Company'),
            TextField::create('CompanyProvinceID','Provinsi Company'),
            TextField::create('CompanyCityID','City Company'),
            TextField::create('CompanyDistricID','Kecamatan Company'),
            TextField::create('CompanyPostalCode','Kode Pos Company'),
            TextField::create('Credit', 'Credit Company'),
            UploadField::create('favicon', 'favicon'),
            UploadField::create('logo', 'Logo'),
            TextField::create('AboutTitle','Title About'),
            TextareaField::create('AboutDescription','Description About'),
            TextField::create('SubAbout1Title','Sub Title About'),
            TextareaField::create('SubAbout1Description','Sub Description About'),
            TextField::create('SubAbout2Title','Sub Title About'),
            TextareaField::create('SubAbout2Description','Sub Description About'),
            TextField::create('SubAbout3Title','Sub Title About'),
            TextareaField::create('SubAbout3Description','Sub Description About'),
            TextField::create('SubAbout4Title','Sub Title About'),
            TextareaField::create('SubAbout4Description','Sub Description About'),
        ]);
    }
}