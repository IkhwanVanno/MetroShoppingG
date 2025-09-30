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
        'MembershipTier' => "Enum('bronze,silver,gold', '')",
        'TotalTransactions' => 'Double',
        'LastMembershipUpdate' => 'Datetime',
        'MembershipPeriodStart' => 'Datetime',
    ];

    /**
     * Update summary fields untuk admin
     */
    public function updateSummaryFields(&$fields)
    {
        $fields['MembershipTier'] = 'Membership Tier';
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