<?php

use SilverStripe\Admin\ModelAdmin;

class BrandAdmin extends ModelAdmin
{
    private static $menu_title = "Brands";
    private static $url_segment = "brands";
    private static $menu_icon_class = "font-icon-block-form";
    private static $managed_models = [
        Brand::class,
    ];

}