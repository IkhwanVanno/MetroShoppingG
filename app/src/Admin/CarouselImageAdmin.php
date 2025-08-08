<?php

use SilverStripe\Admin\ModelAdmin;

class CarouselImageAdmin extends ModelAdmin
{
    private static $menu_title = "Carousel Images";
    private static $url_segment = "carousel-images";
    private static $menu_icon_class = "font-icon-picture";
    private static $managed_models = [
        CarouselImage::class,
    ];
}