<?php

use SilverStripe\ORM\DataExtension;
use SilverStripe\View\ArrayData;

class MemberExtension extends DataExtension
{
    private static $table_name = "member_extension";
    private static $db = [
        'VerificationToken' => 'Varchar(255)',
        'IsVerified' => 'Boolean',
        'ResetPasswordToken' => 'Varchar(255)',
        'ResetPasswordExpiry' => 'Datetime',
    ];
    
    /**
     * Get membership tier for this member
     */
    public function getMembershipTier()
    {
        return MembershipService::getMembershipTier($this->owner->ID);
    }

    /**
     * Get membership tier name for this member
     */
    public function getMembershipTierName()
    {
        $tier = $this->getMembershipTier();
        return MembershipService::getMembershipTierName($tier);
    }

    /**
     * Get progress to next tier
     */
    public function getMembershipProgress()
    {
        $progress = MembershipService::getProgressToNextTier($this->owner->ID);
        return ArrayData::create($progress);
    }
}