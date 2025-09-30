<?php

use SilverStripe\Dev\BuildTask;
use SilverStripe\Security\Member;

/**
 * Task untuk migrasi data membership yang sudah ada ke database
 * Jalankan SEKALI setelah menambahkan field baru
 * 
 * Jalankan via: /dev/tasks/MigrateMembershipDataTask
 */
class MigrateMembershipDataTask extends BuildTask
{
    private static $segment = 'MigrateMembershipDataTask';

    protected $title = 'Migrate Membership Data';

    protected $description = 'Populate membership fields untuk semua member yang sudah ada';

    public function run($request)
    {
        echo "Starting membership data migration...\n";
        echo "Current time: " . date('Y-m-d H:i:s') . "\n\n";

        $members = Member::get();
        $totalMembers = $members->count();
        $updated = 0;

        echo "Found {$totalMembers} members to process\n\n";

        foreach ($members as $member) {
            echo "Processing member #{$member->ID} - {$member->Email}... ";

            try {
                // Set periode mulai jika belum ada
                if (!$member->MembershipPeriodStart) {
                    $member->MembershipPeriodStart = date('Y-m-d H:i:s');
                }

                // Update membership tier
                $success = MembershipService::updateMembershipTier($member->ID);

                if ($success) {
                    // Reload untuk melihat hasil
                    $member = Member::get()->byID($member->ID);
                    $tierName = MembershipService::getMembershipTierName($member->MembershipTier);
                    echo "✓ Done! Tier: {$tierName}, Total: Rp " . number_format($member->TotalTransactions, 0, '.', '.') . "\n";
                    $updated++;
                } else {
                    echo "✗ Failed\n";
                }

            } catch (Exception $e) {
                echo "✗ Error: " . $e->getMessage() . "\n";
            }
        }

        echo "\n========================================\n";
        echo "Migration completed!\n";
        echo "Total members: {$totalMembers}\n";
        echo "Successfully updated: {$updated}\n";
        echo "Failed: " . ($totalMembers - $updated) . "\n";
        echo "Finished at: " . date('Y-m-d H:i:s') . "\n";
        echo "========================================\n";
    }
}