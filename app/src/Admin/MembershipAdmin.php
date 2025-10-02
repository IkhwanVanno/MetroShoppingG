<?php

use SilverStripe\Admin\ModelAdmin;

class MembershipAdmin extends ModelAdmin
{
    private static $menu_title = "Membership";
    private static $url_segment = "Membership";
    private static $menu_icon_class = "font-icon-circle-star";
    private static $managed_models = [
        Membership::class,
    ];
}