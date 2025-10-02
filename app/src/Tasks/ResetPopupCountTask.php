<?php

use SilverStripe\Dev\BuildTask;
use SilverStripe\Security\Member;
/**
 * Task untuk reset membership yang sudah expired (lewat 1 bulan)
 * 
 * Jalankan via:
 * - Manual: /dev/tasks/reset-popup-count
 * - Cron job: php vendor/silverstripe/framework/cli-script.php dev/tasks/reset-popup-count
 * 
 * Setup cron (jalankan setiap hari jam 00:00):
 * 0 0 * * * cd /path/to/project && php vendor/silverstripe/framework/cli-script.php dev/tasks/reset-popup-count
 */
class ResetPopupCountTask extends BuildTask
{
    private static $segment = 'reset-popup-count';

    protected $title = 'Reset Popup View Count';

    protected $description = 'Reset popup view count untuk semua member setiap hari';

    public function run($request)
    {
        $members = Member::get();
        $resetCount = 0;

        foreach ($members as $member) {
            $member->PopupViewCount = 0;
            $member->LastPopupDate = date('Y-m-d');
            $member->write();
            $resetCount++;
        }

        echo "Reset berhasil untuk {$resetCount} member.\n";
    }
}