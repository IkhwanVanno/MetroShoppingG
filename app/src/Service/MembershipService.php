<?php

use SilverStripe\Security\Member;

class MembershipService
{
    const BRONZE_THRESHOLD = 1000000;
    const SILVER_THRESHOLD = 5000000;
    const GOLD_THRESHOLD = 15000000;

    /**
     * Update membership tier untuk member tertentu
     * Menghitung ulang total transaksi dan menyimpan ke database
     */
    public static function updateMembershipTier($memberID)
    {
        $member = Member::get()->byID($memberID);
        if (!$member) {
            return false;
        }

        $startTime = $member->MembershipPeriodStart ?? null;

        // Hitung total transaksi hanya sejak MembershipPeriodStart
        $totalTransactions = self::calculateMemberTotalTransactions($memberID, $startTime);

        $tier = self::calculateTier($totalTransactions);

        $member->TotalTransactions = $totalTransactions;
        $member->MembershipTier = $tier;
        $member->LastMembershipUpdate = date('Y-m-d H:i:s');
        $member->write();

        return true;
    }

    /**
     * Hitung total transaksi member dari database
     * Method private untuk perhitungan internal
     */
    private static function calculateMemberTotalTransactions($memberID, $startTime = null)
    {
        $filter = [
            'MemberID' => $memberID,
            'Status' => 'completed',
            'PaymentStatus' => 'paid'
        ];

        if ($startTime) {
            $filter['Created:GreaterThanOrEqual'] = $startTime;
        }

        $orders = Order::get()->filter($filter);

        $total = 0;
        foreach ($orders as $order) {
            $total += $order->getGrandTotal();
        }

        return $total;
    }

    /**
     * Tentukan tier berdasarkan total transaksi
     * Method private untuk perhitungan internal
     */
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

    /**
     * Get membership tier dari database (cached)
     * Tidak melakukan perhitungan ulang
     */
    public static function getMembershipTier($memberID)
    {
        $member = Member::get()->byID($memberID);
        if (!$member) {
            return null;
        }

        // Cek apakah perlu update (jika belum pernah diupdate atau sudah lewat 1 bulan)
        if (self::needsMembershipUpdate($member)) {
            self::updateMembershipTier($memberID);
            $member = Member::get()->byID($memberID); // Reload
        }

        return $member->MembershipTier;
    }

    /**
     * Get total transaksi dari database (cached)
     */
    public static function getMemberTotalTransactions($memberID)
    {
        $member = Member::get()->byID($memberID);
        if (!$member) {
            return 0;
        }

        // Update jika perlu
        if (self::needsMembershipUpdate($member)) {
            self::updateMembershipTier($memberID);
            $member = Member::get()->byID($memberID); // Reload
        }

        return $member->TotalTransactions;
    }

    /**
     * Cek apakah membership perlu diupdate
     */
    private static function needsMembershipUpdate($member)
    {
        // Jika belum pernah diupdate
        if (!$member->LastMembershipUpdate) {
            return true;
        }

        // Cek apakah sudah lewat 1 bulan dari periode mulai
        $periodStart = strtotime($member->MembershipPeriodStart);
        $now = time();
        $oneMonthLater = strtotime('+5 minutes', $periodStart);

        // Jika sudah lewat 1 bulan, perlu reset
        if ($now >= $oneMonthLater) {
            return true;
        }

        return false;
    }

    /**
     * Reset membership period untuk member
     * Dipanggil otomatis atau manual
     */
    public static function resetMembershipPeriod($memberID)
    {
        $member = Member::get()->byID($memberID);
        if (!$member) {
            return false;
        }

        // Reset periode mulai ke sekarang
        $member->MembershipPeriodStart = date('Y-m-d H:i:s');
        $member->TotalTransactions = 0;
        $member->MembershipTier = null;
        $member->LastMembershipUpdate = date('Y-m-d H:i:s');
        $member->write();

        // Hitung ulang membership berdasarkan transaksi bulan baru
        self::updateMembershipTier($memberID);

        return true;
    }

    /**
     * Reset semua membership yang sudah lewat 1 bulan
     * Untuk dijalankan via cron job
     */
    public static function resetExpiredMemberships()
    {
        $members = Member::get()->filter('MembershipPeriodStart:LessThan', date('Y-m-d H:i:s', strtotime('-5 minutes')));

        $resetCount = 0;
        foreach ($members as $member) {
            if (self::resetMembershipPeriod($member->ID)) {
                $resetCount++;
            }
        }

        return $resetCount;
    }

    /**
     * Force update membership ketika order completed
     * Dipanggil dari Order::markAsCompleted()
     */
    public static function onOrderCompleted($orderID)
    {
        $order = Order::get()->byID($orderID);
        if (!$order || !$order->MemberID) {
            return false;
        }

        // Update membership tier member tersebut
        return self::updateMembershipTier($order->MemberID);
    }

    /**
     * Dapatkan nama tier yang user-friendly
     */
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

    /**
     * Progress ke tier berikutnya
     */
    public static function getProgressToNextTier($memberID)
    {
        $member = Member::get()->byID($memberID);
        if (!$member) {
            return null;
        }

        // Update jika perlu
        if (self::needsMembershipUpdate($member)) {
            self::updateMembershipTier($memberID);
            $member = Member::get()->byID($memberID); // Reload
        }

        $totalTransactions = $member->TotalTransactions;
        $currentTier = $member->MembershipTier;

        $result = [
            'current_total' => number_format($totalTransactions, 0, '.', '.'),
            'current_tier' => $currentTier,
            'next_tier' => null,
            'next_threshold' => null,
            'remaining_amount' => 0,
            'progress_percentage' => '100%',
            'period_start' => $member->MembershipPeriodStart,
            'period_end' => $member->MembershipPeriodStart
                ? date('Y-m-d H:i:s', strtotime('+1 month', strtotime($member->MembershipPeriodStart)))
                : null,
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

    /**
     * Check apakah member memiliki tier tertentu
     */
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