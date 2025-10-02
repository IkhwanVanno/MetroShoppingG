<?php

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;

class PopupAd extends DataObject
{
    private static $table_name = 'PopupAd';

    private static $db = [
        'Title' => 'Varchar(255)',
        'Link' => 'Varchar',
        'Active' => 'Boolean',
        'SortOrder' => 'Int', // Tambahan untuk urutan
    ];

    private static $has_one = [
        'Image' => Image::class,
    ];

    private static $owns = [
        'Image'
    ];

    private static $default_sort = 'SortOrder ASC, ID ASC'; // Default sorting

    private static $summary_fields = [
        'SortOrder' => 'Urutan',
        'Title' => 'Title Advertisement',
        'Link' => 'Direct Link',
        'Active.Nice' => 'Active Status',
        'Image.CMSThumbnail' => 'Image Advertisement',
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->addFieldToTab(
            'Root.Main',
            NumericField::create("SortOrder", "Urutan Tampil (1 = pertama)")
                ->setDescription('Semakin kecil angka, semakin dulu ditampilkan')
        );
        $fields->addFieldToTab('Root.Main', TextField::create("Title", "Title Advertisement"));
        $fields->addFieldToTab(
            'Root.Main',
            TextField::create("Link", "Direct Link (Optional)")
                ->setDescription('Kosongkan jika tidak ingin gambar bisa diklik')
        );
        $fields->addFieldToTab('Root.Main', DropdownField::create("Active", "Active Status", [
            1 => 'Active',
            0 => 'Inactive',
        ]));

        $uploadField = UploadField::create("Image", "Image Advertisement");
        $uploadField->setFolderName('popup-ads');
        $uploadField->getValidator()->setAllowedExtensions(['jpg', 'jpeg', 'png', 'gif']);
        $fields->addFieldToTab('Root.Main', $uploadField);

        return $fields;
    }

    /**
     * Set default sort order saat create baru
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        // Set default sort order jika belum diisi
        if (!$this->SortOrder) {
            $maxSort = PopupAd::get()->max('SortOrder');
            $this->SortOrder = $maxSort ? $maxSort + 1 : 1;
        }
    }

    /**
     * Publish image otomatis saat popup disave
     */
    public function onAfterWrite()
    {
        parent::onAfterWrite();

        // Publish image jika ada
        if ($this->Image() && $this->Image()->exists()) {
            $this->Image()->publishSingle();
        }
    }
}