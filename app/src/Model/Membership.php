<?php

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\NumericField;
use SilverStripe\ORM\DataObject;

class Membership extends DataObject
{
    private static $table_name = "Membership";

    private static $db = [
        "Name" => "Varchar(50)",
        "Limit" => "Double",
        "Sort" => "Int"
    ];

    private static $has_one = [
        "Image" => Image::class,
    ];

    private static $owns = [
        "Image"
    ];

    private static $default_sort = "Sort ASC";

    private static $summary_fields = [
        'Name' => 'Tier Name',
        'Limit' => 'Transaction Limit',
        'Image.CMSThumbnail' => 'Image'
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName(['Sort']);

        $fields->addFieldToTab('Root.Main', TextField::create("Name", "Tier Name")
            ->setDescription("Contoh: Bronze Member, Silver Member, Gold Member"));

        $fields->addFieldToTab('Root.Main', NumericField::create("Limit", "Transaction Limit")
            ->setDescription("Total transaksi yang dibutuhkan untuk mencapai tier ini (dalam Rupiah)"));

        $fields->addFieldToTab('Root.Main', UploadField::create("Image", "Tier Image")
            ->setDescription("Upload icon/badge untuk tier ini"));

        $fields->addFieldToTab('Root.Main', NumericField::create("Sort", "Sort Order")
            ->setDescription("Urutan tier (angka kecil = tier rendah)"));

        return $fields;
    }

    public function getFormattedLimit()
    {
        return 'Rp ' . number_format($this->Limit, 0, '.', '.');
    }

    /**
     * Recalculate all member tiers after membership config changes
     */
    public function onAfterWrite()
    {
        parent::onAfterWrite();

        // Hanya jalankan jika ada perubahan pada Limit atau Sort
        if ($this->isChanged('Limit') || $this->isChanged('Sort')) {
            MembershipService::recalculateAllMemberTiers();
        }
    }

    /**
     * Recalculate all member tiers after membership is deleted
     */
    public function onAfterDelete()
    {
        parent::onAfterDelete();
        MembershipService::recalculateAllMemberTiers();
    }
}