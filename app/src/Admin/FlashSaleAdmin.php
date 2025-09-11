<?php

use SilverStripe\Admin\ModelAdmin;

class FlashSaleAdmin extends ModelAdmin
{
    private static $menu_title = "FlashSale";
    private static $url_segment = "flashsale";
    private static $menu_icon_class = "font-icon-safari";

    private static $managed_models = [
        FlashSale::class,
    ];
}