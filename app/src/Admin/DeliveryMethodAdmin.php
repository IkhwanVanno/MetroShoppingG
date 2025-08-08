<?php

use SilverStripe\Admin\ModelAdmin;

class DeliveryMethodAdmin extends ModelAdmin
{
    private static $menu_title = "Delivery Methods";
    private static $url_segment = "delivery-methods";
    private static $menu_icon_class = "font-icon-p-package";
    private static $managed_models = [
        DeliveryMethod::class,
    ];
}