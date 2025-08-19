<?php

use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

class Review extends DataObject
{
    private static $table_name = "Review";
    private static $db = [
        "Rating" => "Int",
        "Message" => "Text",
        "CreatedAt" => "Datetime",
        "ShowName" => "Boolean",
    ];
    private static $defaults = [
        "ShowName" => true,
    ];
    private static $has_one = [
        "Product" => Product::class,
        "Member" => Member::class,
        "OrderItem" => OrderItem::class,
    ];

    /**
     * Set created date on write
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if (!$this->CreatedAt) {
            $this->CreatedAt = date('Y-m-d H:i:s');
        }
    }

    /**
     * Get formatted created date
     */
    public function getFormattedDate()
    {
        return date('d F Y', strtotime($this->CreatedAt));
    }
    /**
     * Get range helper for templates (untuk star rating)
     */
    public function Range($start, $end)
    {
        $result = [];
        for ($i = $start; $i <= $end; $i++) {
            $result[] = ['Pos' => $i];
        }
        return new ArrayList($result);
    }
}