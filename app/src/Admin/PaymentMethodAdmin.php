<?php

use SilverStripe\Admin\ModelAdmin;

class PaymentMethodAdmin extends ModelAdmin
{
    private static $menu_title = "Payment Methods";
    private static $url_segment = "payment-methods";
    private static $menu_icon_class = "font-icon-credit-card";
    private static $managed_models = [
        PaymentMethod::class,
    ];
}