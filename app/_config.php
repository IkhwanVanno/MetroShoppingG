<?php

use SilverStripe\SiteConfig\SiteConfig;

if (class_exists('CustomSiteConfig')) {
      SiteConfig::add_extension(CustomSiteConfig::class);
}