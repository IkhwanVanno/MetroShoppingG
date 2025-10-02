<?php

use SilverStripe\Dev\BuildTask;

/**
 * Task untuk reset membership yang sudah expired (lewat 1 bulan)
 * 
 * Jalankan via:
 * - Manual: /dev/tasks/MembershipResetTask
 * - Cron job: php vendor/silverstripe/framework/cli-script.php dev/tasks/MembershipResetTask
 * 
 * Setup cron (jalankan setiap hari jam 00:00):
 * 0 0 * * * cd /path/to/project && php vendor/silverstripe/framework/cli-script.php dev/tasks/MembershipResetTask
 */
class MembershipResetTask extends BuildTask
{
    private static $segment = 'MembershipResetTask';

    protected $title = 'Reset Expired Memberships';

    protected $description = 'Reset membership tier untuk member yang sudah lewat 1 bulan periode';

    public function run($request)
    {
        echo "Starting membership reset task...\n";
        echo "Current time: " . date('Y-m-d H:i:s') . "\n\n";

        $resetCount = MembershipService::resetExpiredMemberships();

        echo "\nTask completed!\n";
        echo "Total memberships reset: " . $resetCount . "\n";
        echo "Finished at: " . date('Y-m-d H:i:s') . "\n";
    }
}