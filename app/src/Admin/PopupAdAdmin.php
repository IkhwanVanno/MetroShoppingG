<?php

use SilverStripe\Admin\ModelAdmin;

class PopupAdAdmin extends ModelAdmin
{
    private static $menu_title = "Pop-Up Advertisement";
    private static $url_segment = "popupad";
    private static $menu_icon_class = "font-icon-export";
    private static $managed_models = [
        PopupAd::class,
    ];
}