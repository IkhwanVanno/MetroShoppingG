<?php

class MembershipService
{
    const BRONZE_THRESHOLD = 1000000;
    const SILVER_THRESHOLD = 5000000;
    const GOLD_THRESHOLD = 15000000;

    /**
     * Hitung total transaksi member yang sudah completed
     * Menggunakan SQL aggregate untuk performa lebih baik
     */
    public static function getMemberTotalTransactions($memberID)
    {
        // Gunakan SQL aggregate untuk performa lebih baik
        $orders = Order::get()->filter([
            'MemberID' => $memberID,
            'Status' => 'completed',
            'PaymentStatus' => 'paid'
        ]);

        $total = 0;
        foreach ($orders as $order) {
            $total += $order->getGrandTotal();
        }

        return $total;
    }

    /**
     * Tentukan membership tier berdasarkan total transaksi
     */
    public static function getMembershipTier($memberID)
    {
        $totalTransactions = self::getMemberTotalTransactions($memberID);

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
        $totalTransactions = self::getMemberTotalTransactions($memberID);
        $currentTier = self::getMembershipTier($memberID);

        $result = [
            'current_total' => number_format($totalTransactions, 0, '.', '.'),
            'current_tier' => $currentTier,
            'next_tier' => null,
            'next_threshold' => null,
            'remaining_amount' => 0,
            'progress_percentage' => '100%'
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