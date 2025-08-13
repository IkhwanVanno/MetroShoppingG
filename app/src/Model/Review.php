<?php

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

class Review extends DataObject
{
    private static $table_name = "Review";
    private static $db = [
        "Rating" => "Int",
        "Message" => "Text",
        "CreatedAt" => "Datetime",
    ];
    private static $has_one = [
        "Product" => Product::class,
        "Member" => Member::class,
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
     * Get star display for rating
     */
    public function getStarDisplay()
    {
        $stars = '';
        for ($i = 1; $i <= 5; $i++) {
            $stars .= $i <= $this->Rating ? '★' : '☆';
        }
        return $stars;
    }

    /**
     * Get formatted created date
     */
    public function getFormattedDate()
    {
        return date('d F Y', strtotime($this->CreatedAt));
    }
}