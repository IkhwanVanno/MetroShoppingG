<?php

use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\DropdownField;

class ProductAdmin extends ModelAdmin
{
    private static $menu_title = "Products";
    private static $url_segment = "products";
    private static $menu_icon_class = "font-icon-p-shop";
    private static $managed_models = [
        Product::class,
    ];
}