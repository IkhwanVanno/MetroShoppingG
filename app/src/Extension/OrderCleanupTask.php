<?php

use SilverStripe\Dev\BuildTask;

class OrderCleanupTask extends BuildTask
{
    protected $title = 'Order Cleanup Task';
    protected $description = 'Automatically cancel expired orders';

    public function run($request)
    {
        $expiredOrders = Order::get()->filter([
            'Status' => ['pending', 'pending_payment'],
            'PaymentStatus' => 'unpaid',
            'ExpiresAt:LessThan' => date('Y-m-d H:i:s')
        ]);

        $cancelledCount = 0;
        foreach ($expiredOrders as $order) {
            if ($order->cancelOrder()) {
                $cancelledCount++;
                echo "Cancelled order: " . $order->OrderCode . "\n";
            }
        }

        echo "Total cancelled orders: " . $cancelledCount . "\n";
    }
}