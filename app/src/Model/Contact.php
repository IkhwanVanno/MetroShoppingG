<?php

use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;

class Contact extends DataObject
{
    private static $table_name = "Contact";
    private static $db = [
        "UserName" => "Varchar(255)",
        "UserEmail" => "Varchar(255)",
        "Message" => "Text",
    ];
    private static $summary_fields = [
        'UserName' => 'Name',
        'UserEmail' => 'Email',
        'Message' => 'Message',
    ];
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldToTab('Root.Main', TextField::create('UserName', 'Name'));
        $fields->addFieldToTab('Root.Main', TextField::create('UserEmail', 'Email'));
        $fields->addFieldToTab('Root.Main', TextareaField::create('Message', 'Message'));
        return $fields;
    }
}