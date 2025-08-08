<?php 

use SilverStripe\Admin\ModelAdmin;

class EventShopAdmin extends ModelAdmin
{
    private static $menu_title = "Event Shops";
    private static $url_segment = "event-shops";
    private static $menu_icon_class = "font-icon-safari";

    private static $managed_models = [
        EventShop::class,
    ];
}