<?php

use SilverStripe\Admin\ModelAdmin;

class OrderAdmin extends ModelAdmin
{
    private static $menu_title = "Order";
    private static $url_segment = "order";
    private static $menu_icon_class = "font-icon-clipboard-pencil";

    private static $managed_models = [
        Order::class,
    ];
}