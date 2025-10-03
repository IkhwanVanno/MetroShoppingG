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
        'GoogleID' => 'Varchar(255)',
        'TotalTransactions' => 'Double',
        'MembershipTier' => 'Int',
        'MembershipTierName' => 'Varchar(100)',
        'MembershipPeriodStart' => 'Datetime',
        'LastMembershipUpdate' => 'Datetime',
        'PopupViewCount' => 'Int',
        'LastPopupDate' => 'Date'
    ];

    private static $indexes = [
        'GoogleID' => true,
        'VerificationToken' => true,
        'ResetPasswordToken' => true
    ];

    public function updateSummaryFields(&$fields)
    {
        $fields['GoogleID'] = 'GoogleID';
        $fields['IsVerified'] = 'Terverifikasi';
        $fields['PopupViewCount'] = 'Pop-Up Showcount';
        $fields['LastPopupDate'] = 'Terakhir PopUp Update';
        $fields['MembershipTierName'] = 'Membership Tier';
        $fields['FormattedTotalTransactions'] = 'Total Transaksi';
        $fields['MembershipPeriodStart'] = 'Periode Mulai';
        $fields['LastMembershipUpdate'] = 'Terakhir Update';
    }

    /**
     * Format total transaksi
     */
    public function getFormattedTotalTransactions()
    {
        return 'Rp ' . number_format($this->owner->TotalTransactions, 0, '.', '.');
    }

    /**
     * Mendapatkan nama tier yang user-friendly
     */
    public function getMembershipTierName()
    {
        return MembershipService::getMembershipTierName($this->owner->MembershipTier);
    }

    /**
     * Set default values saat member baru dibuat
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if (!$this->owner->MembershipPeriodStart) {
            $this->owner->MembershipPeriodStart = date('Y-m-d H:i:s');
        }
    }
}