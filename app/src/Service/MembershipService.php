<?php

use SilverStripe\Security\Member;
use SilverStripe\ORM\DataObject;

class MembershipService
{
    /**
     * Update membership tier berdasarkan konfigurasi di CMS
     */
    public static function updateMembershipTier($memberID, $forceRecalculate = false)
    {
        $member = Member::get()->byID($memberID);
        if (!$member) {
            return false;
        }

        $startTime = $member->MembershipPeriodStart ?? null;
        $totalTransactions = self::calculateMemberTotalTransactions($memberID, $startTime);
        $tierData = self::calculateTierFromConfig($totalTransactions);

        $member->TotalTransactions = $totalTransactions;
        $member->MembershipTier = $tierData['tier_id'];
        $member->MembershipTierName = $tierData['tier_name'];
        $member->LastMembershipUpdate = date('Y-m-d H:i:s');
        $member->write();

        return true;
    }

    /**
     * Recalculate semua member tier (dipanggil saat config berubah)
     */
    public static function recalculateAllMemberTiers()
    {
        $members = Member::get()->filterAny([
            'TotalTransactions:GreaterThan' => 0,
            'MembershipTier:not' => null
        ]);

        $updateCount = 0;
        foreach ($members as $member) {
            $tierData = self::calculateTierFromConfig($member->TotalTransactions);
            if (
                $member->MembershipTier != $tierData['tier_id'] ||
                $member->MembershipTierName != $tierData['tier_name']
            ) {

                $member->MembershipTier = $tierData['tier_id'];
                $member->MembershipTierName = $tierData['tier_name'];
                $member->LastMembershipUpdate = date('Y-m-d H:i:s');
                $member->write();

                $updateCount++;
            }
        }

        error_log("MembershipService::recalculateAllMemberTiers - Updated $updateCount members");
        return $updateCount;
    }

    /**
     * Hitung total transaksi member
     */
    private static function calculateMemberTotalTransactions($memberID, $startTime = null)
    {
        $filter = [
            'MemberID' => $memberID,
            'Status' => 'completed',
            'PaymentStatus' => 'paid'
        ];

        if ($startTime) {
            $filter['CreateAt:GreaterThanOrEqual'] = $startTime;
        }

        $orders = Order::get()->filter($filter);

        $total = 0;
        foreach ($orders as $order) {
            $total += $order->getGrandTotal();
        }

        return $total;
    }

    /**
     * Hitung tier berdasarkan konfigurasi dari CMS
     */
    private static function calculateTierFromConfig($totalTransactions)
    {
        $memberships = Membership::get()->sort('Limit DESC');

        foreach ($memberships as $membership) {
            if ($totalTransactions >= $membership->Limit) {
                return [
                    'tier_id' => $membership->ID,
                    'tier_name' => $membership->Name
                ];
            }
        }
        return [
            'tier_id' => null,
            'tier_name' => 'Member'
        ];
    }

    /**
     * Get membership tier
     */
    public static function getMembershipTier($memberID)
    {
        $member = Member::get()->byID($memberID);
        if (!$member) {
            return null;
        }

        if (self::needsMembershipUpdate($member)) {
            self::updateMembershipTier($memberID);
            $member = Member::get()->byID($memberID);
        }

        return $member->MembershipTier;
    }

    /**
     * Get membership tier name
     */
    public static function getMembershipTierName($tierID)
    {
        if (!$tierID) {
            return 'Member';
        }

        $membership = Membership::get()->byID($tierID);
        return $membership ? $membership->Name : 'Member';
    }

    /**
     * Get membership tier object
     */
    public static function getMembershipTierObject($tierID)
    {
        if (!$tierID) {
            return null;
        }

        return Membership::get()->byID($tierID);
    }

    /**
     * Get member total transactions
     */
    public static function getMemberTotalTransactions($memberID)
    {
        $member = Member::get()->byID($memberID);
        if (!$member) {
            return 0;
        }

        if (self::needsMembershipUpdate($member)) {
            self::updateMembershipTier($memberID);
            $member = Member::get()->byID($memberID);
        }

        return $member->TotalTransactions;
    }

    /**
     * Cek apakah membership perlu diupdate
     */
    private static function needsMembershipUpdate($member)
    {
        if (!$member->LastMembershipUpdate) {
            return true;
        }

        $lastOrder = Order::get()
            ->filter([
                'MemberID' => $member->ID,
                'Status' => 'completed',
                'PaymentStatus' => 'paid'
            ])
            ->sort('CreateAt', 'DESC')
            ->first();

        if (!$lastOrder) {
            return false;
        }

        $lastOrderTime = strtotime($lastOrder->CreateAt);
        $now = time();
        $inactivityThreshold = strtotime('+5 minutes', $lastOrderTime);

        if ($now >= $inactivityThreshold) {
            return true;
        }

        return false;
    }

    /**
     * Reset membership period
     */
    public static function resetMembershipPeriod($memberID)
    {
        $member = Member::get()->byID($memberID);
        if (!$member) {
            return false;
        }

        $member->MembershipPeriodStart = date('Y-m-d H:i:s');
        $member->TotalTransactions = 0;
        $member->MembershipTier = null;
        $member->MembershipTierName = 'Member';
        $member->LastMembershipUpdate = date('Y-m-d H:i:s');
        $member->write();

        self::updateMembershipTier($memberID);

        return true;
    }

    /**
     * Reset expired memberships
     */
    public static function resetExpiredMemberships()
    {
        $members = Member::get()->filter('MembershipTier:not', null);

        $resetCount = 0;
        foreach ($members as $member) {
            $lastOrder = Order::get()
                ->filter([
                    'MemberID' => $member->ID,
                    'Status' => 'completed',
                    'PaymentStatus' => 'paid'
                ])
                ->sort('CreateAt', 'DESC')
                ->first();

            if ($lastOrder) {
                $lastOrderTime = strtotime($lastOrder->CreateAt);
                $now = time();
                $inactivityThreshold = strtotime('+5 minutes', $lastOrderTime);

                if ($now >= $inactivityThreshold) {
                    if (self::resetMembershipPeriod($member->ID)) {
                        $resetCount++;
                    }
                }
            }
        }

        return $resetCount;
    }

    /**
     * Callback saat order completed
     */
    public static function onOrderCompleted($orderID)
    {
        $order = Order::get()->byID($orderID);
        if (!$order || !$order->MemberID) {
            return false;
        }

        return self::updateMembershipTier($order->MemberID);
    }

    /**
     * Get progress to next tier
     */
    public static function getProgressToNextTier($memberID)
    {
        $member = Member::get()->byID($memberID);
        if (!$member) {
            return null;
        }

        if (self::needsMembershipUpdate($member)) {
            self::updateMembershipTier($memberID);
            $member = Member::get()->byID($memberID);
        }

        $totalTransactions = $member->TotalTransactions;
        $currentTierID = $member->MembershipTier;

        $lastOrder = Order::get()
            ->filter([
                'MemberID' => $memberID,
                'Status' => 'completed',
                'PaymentStatus' => 'paid'
            ])
            ->sort('CreateAt', 'DESC')
            ->first();

        $periodEnd = null;
        if ($lastOrder) {
            $periodEnd = date('Y-m-d H:i:s', strtotime('+5 minutes', strtotime($lastOrder->CreateAt)));
        }

        $currentTierName = $member->MembershipTierName ?: 'Member';
        $allTiers = Membership::get()->sort('Limit ASC');
        $nextTier = null;

        foreach ($allTiers as $tier) {
            if ($tier->Limit > $totalTransactions) {
                $nextTier = $tier;
                break;
            }
        }

        $result = [
            'current_total' => number_format($totalTransactions, 0, '.', '.'),
            'current_tier' => $currentTierName,
            'current_tier_id' => $currentTierID,
            'next_tier' => null,
            'next_threshold' => null,
            'remaining_amount' => 0,
            'progress_percentage' => '100%',
            'period_start' => $member->MembershipPeriodStart,
            'period_end' => $periodEnd,
        ];

        if ($nextTier) {
            $previousThreshold = 0;
            if ($currentTierID) {
                $currentTierObj = Membership::get()->byID($currentTierID);
                if ($currentTierObj) {
                    $previousThreshold = $currentTierObj->Limit;
                }
            }

            $result['next_tier'] = $nextTier->Name;
            $result['next_threshold'] = $nextTier->Limit;
            $result['remaining_amount'] = number_format($nextTier->Limit - $totalTransactions, 0, '.', '.');

            // Hitung persentase progress
            $range = $nextTier->Limit - $previousThreshold;
            $progress = $totalTransactions - $previousThreshold;
            $result['progress_percentage'] = round(($progress / $range) * 100, 1) . '%';
        }

        return $result;
    }

    /**
     * Check if member has specific tier or higher
     */
    public static function hasTier($memberID, $tierID)
    {
        if (!$tierID) {
            return false;
        }

        $currentTierID = self::getMembershipTier($memberID);

        if (!$currentTierID) {
            return false;
        }

        $currentTier = Membership::get()->byID($currentTierID);
        $requiredTier = Membership::get()->byID($tierID);

        if (!$currentTier || !$requiredTier) {
            return false;
        }

        return $currentTier->Limit >= $requiredTier->Limit;
    }
}