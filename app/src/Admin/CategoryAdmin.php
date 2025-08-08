<?php

use SilverStripe\Admin\ModelAdmin;

class CategoryAdmin extends ModelAdmin
{
    private static $menu_title = "Categories";
    private static $url_segment = "categories";
    private static $menu_icon_class = "font-icon-block-file-list";

    private static $managed_models = [
        Category::class,
    ];
}