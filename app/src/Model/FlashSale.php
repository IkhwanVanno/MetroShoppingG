<?php

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\DatetimeField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDatetime;

class FlashSale extends DataObject
{
    private static $table_name = "FlashSale";
    private static $db = [
        "Name" => "Varchar(255)",
        "Description" => "Text",
        "Start_time" => "Datetime",
        "End_time" => "Datetime",
        "DiscountFlashSale" => "Int",
        "Status" => "Enum('active, inactive', 'inactive')",
    ];
    private static $has_one = [
        "Image" => Image::class,
    ];
    private static $owns = [
        "Image"
    ];
    private static $has_many = [
        "Product" => Product::class,
    ];
    private static $summary_fields = [
        "Name" => "Name FlashSale",
        "Start_time" => "FlashSale Start",
        "End_time" => "FlashSale End",
        "DiscountFlashSale" => "Discount",
        "Status" => "Status",
        "getTimerStatus" => "Timer Status",
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldToTab("Root.Main", TextField::create("Name", "FlashSale Name"));
        $fields->addFieldToTab("Root.Main", TextareaField::create("Description", "FlashSale Description"));
        $fields->addFieldToTab("Root.Main", UploadField::create("Image", "FlashSale Image"));
        $fields->addFieldToTab("Root.Main", TextField::create("DiscountFlashSale", "Discount FlashSale %"));
        $fields->addFieldToTab("Root.Main", DatetimeField::create("Start_time", "Start Time"));
        $fields->addFieldToTab("Root.Main", DatetimeField::create("End_time", "End Time"));
        $fields->addFieldToTab("Root.Main", DropdownField::create("Status", "FlashSale Status", [
            "active" => "Active",
            "inactive" => "Inactive",
        ]));
        return $fields;
    }

    /**
     * Get the current timer status of the flash sale
     * @return string 'coming_soon', 'active', 'expired', or 'inactive'
     */
    public function getTimerStatus()
    {
        if ($this->Status !== 'active') {
            return 'inactive';
        }

        $now = DBDatetime::now()->getValue();
        $startTime = $this->Start_time;
        $endTime = $this->End_time;

        if ($now < $startTime) {
            return 'coming_soon';
        } elseif ($now >= $startTime && $now <= $endTime) {
            return 'active';
        } else {
            return 'expired';
        }
    }

    /**
     * Check if flash sale is currently running
     * @return bool
     */
    public function isActive()
    {
        return $this->getTimerStatus() === 'active';
    }

    /**
     * Check if flash sale is coming soon
     * @return bool
     */
    public function isComingSoon()
    {
        return $this->getTimerStatus() === 'coming_soon';
    }

    /**
     * Check if flash sale has expired
     * @return bool
     */
    public function isExpired()
    {
        return $this->getTimerStatus() === 'expired';
    }

    /**
     * Check if flash sale is inactive (disabled)
     * @return bool
     */
    public function isInactive()
    {
        return $this->getTimerStatus() === 'inactive';
    }

    /**
     * Get status badge class for display
     * @return string
     */
    public function getStatusBadgeClass()
    {
        switch ($this->getTimerStatus()) {
            case 'active':
                return 'bg-success';
            case 'coming_soon':
                return 'bg-warning';
            case 'expired':
                return 'bg-danger';
            case 'inactive':
                return 'bg-secondary';
            default:
                return 'bg-secondary';
        }
    }

    /**
     * Get status text for display
     * @return string
     */
    public function getStatusText()
    {
        switch ($this->getTimerStatus()) {
            case 'active':
                return 'Flash Sale Aktif';
            case 'coming_soon':
                return 'Segera Dimulai';
            case 'expired':
                return 'Flash Sale Berakhir';
            case 'inactive':
                return 'Tidak Aktif';
            default:
                return 'Tidak Aktif';
        }
    }

    /**
     * Get time remaining until start (for coming soon status)
     * @return int seconds until start
     */
    public function getTimeUntilStart()
    {
        if ($this->isComingSoon()) {
            $now = strtotime(DBDatetime::now()->getValue());
            $start = strtotime($this->Start_time);
            return max(0, $start - $now);
        }
        return 0;
    }

    /**
     * Get time remaining until end (for active status)
     * @return int seconds until end
     */
    public function getTimeUntilEnd()
    {
        if ($this->isActive()) {
            $now = strtotime(DBDatetime::now()->getValue());
            $end = strtotime($this->End_time);
            return max(0, $end - $now);
        }
        return 0;
    }

    /**
     * Get formatted start time for JavaScript
     * @return string ISO format datetime
     */
    public function getStartTimeISO()
    {
        return date('c', strtotime($this->Start_time));
    }

    /**
     * Get formatted end time for JavaScript
     * @return string ISO format datetime
     */
    public function getEndTimeISO()
    {
        return date('c', strtotime($this->End_time));
    }
}