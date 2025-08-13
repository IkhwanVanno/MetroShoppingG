<?php

use SilverStripe\Admin\ModelAdmin;

class PaymentTransactionAdmin extends ModelAdmin
{
    private static $menu_title = "Payment Transaction";
    private static $url_segment = "payment-transaction";
    private static $menu_icon_class = "font-icon-credit-card";

    private static $managed_models = [
        PaymentTransaction::class,
    ];
}