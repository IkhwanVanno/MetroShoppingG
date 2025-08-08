<?php

use SilverStripe\Admin\ModelAdmin;

class SocialMediaAdmin extends ModelAdmin
{
    private static $menu_title = "Social Media";
    private static $url_segment = "social-media";
    private static $menu_icon_class = "font-icon-p-post";
    private static $managed_models = [
        SocialMedia::class,
    ];
}