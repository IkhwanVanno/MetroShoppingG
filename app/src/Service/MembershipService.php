<?php

use SilverStripe\Security\Member;

class MembershipService
{
    const BRONZE_THRESHOLD = 1000000;
    const SILVER_THRESHOLD = 5000000;
    const GOLD_THRESHOLD = 15000000;

    public static function updateMembershipTier($memberID)
    {
        $member = Member::get()->byID($memberID);
        if (!$member) {
            return false;
        }

        $startTime = $member->MembershipPeriodStart ?? null;

        $totalTransactions = self::calculateMemberTotalTransactions($memberID, $startTime);

        $tier = self::calculateTier($totalTransactions);

        $member->TotalTransactions = $totalTransactions;
        $member->MembershipTier = $tier;
        $member->LastMembershipUpdate = date('Y-m-d H:i:s');
        $member->write();

        return true;
    }

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

    private static function calculateTier($totalTransactions)
    {
        if ($totalTransactions >= self::GOLD_THRESHOLD) {
            return 'gold';
        } elseif ($totalTransactions >= self::SILVER_THRESHOLD) {
            return 'silver';
        } elseif ($totalTransactions >= self::BRONZE_THRESHOLD) {
            return 'bronze';
        }

        return null;
    }

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

    public static function resetMembershipPeriod($memberID)
    {
        $member = Member::get()->byID($memberID);
        if (!$member) {
            return false;
        }

        $member->MembershipPeriodStart = date('Y-m-d H:i:s');
        $member->TotalTransactions = 0;
        $member->MembershipTier = null;
        $member->LastMembershipUpdate = date('Y-m-d H:i:s');
        $member->write();

        self::updateMembershipTier($memberID);

        return true;
    }

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

    public static function onOrderCompleted($orderID)
    {
        $order = Order::get()->byID($orderID);
        if (!$order || !$order->MemberID) {
            return false;
        }

        return self::updateMembershipTier($order->MemberID);
    }

    public static function getMembershipTierName($tier)
    {
        switch ($tier) {
            case 'gold':
                return 'Gold Member';
            case 'silver':
                return 'Silver Member';
            case 'bronze':
                return 'Bronze Member';
            default:
                return 'Member';
        }
    }

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
        $currentTier = $member->MembershipTier;

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

        $result = [
            'current_total' => number_format($totalTransactions, 0, '.', '.'),
            'current_tier' => $currentTier,
            'next_tier' => null,
            'next_threshold' => null,
            'remaining_amount' => 0,
            'progress_percentage' => '100%',
            'period_start' => $member->MembershipPeriodStart,
            'period_end' => $periodEnd,
        ];

        switch ($currentTier) {
            case null:
                $result['next_tier'] = 'Bronze Member';
                $result['next_threshold'] = self::BRONZE_THRESHOLD;
                $result['remaining_amount'] = number_format(self::BRONZE_THRESHOLD - $totalTransactions, 0, '.', '.');
                $result['progress_percentage'] = round(($totalTransactions / self::BRONZE_THRESHOLD) * 100, 1) . '%';
                break;

            case 'bronze':
                $result['next_tier'] = 'Silver Member';
                $result['next_threshold'] = self::SILVER_THRESHOLD;
                $result['remaining_amount'] = number_format(self::SILVER_THRESHOLD - $totalTransactions, 0, '.', '.');
                $result['progress_percentage'] = round((($totalTransactions - self::BRONZE_THRESHOLD) / (self::SILVER_THRESHOLD - self::BRONZE_THRESHOLD)) * 100, 1) . '%';
                break;

            case 'silver':
                $result['next_tier'] = 'Gold Member';
                $result['next_threshold'] = self::GOLD_THRESHOLD;
                $result['remaining_amount'] = number_format(self::GOLD_THRESHOLD - $totalTransactions, 0, '.', '.');
                $result['progress_percentage'] = round((($totalTransactions - self::SILVER_THRESHOLD) / (self::GOLD_THRESHOLD - self::SILVER_THRESHOLD)) * 100, 1) . '%';
                break;
        }

        return $result;
    }

    public static function hasTier($memberID, $tier)
    {
        $currentTier = self::getMembershipTier($memberID);

        $tierHierarchy = ['bronze' => 1, 'silver' => 2, 'gold' => 3];

        if (!isset($tierHierarchy[$tier]) || !$currentTier) {
            return false;
        }

        return $tierHierarchy[$currentTier] >= $tierHierarchy[$tier];
    }
}