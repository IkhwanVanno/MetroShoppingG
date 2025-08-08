<?php

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;

class Product extends DataObject
{
    private static $table_name = "Product";
    private static $db = [
        "Name" => "Varchar(50)",
        "Stok" => "Int",
        "Weight" => "Int",
        "Price" => "Double",
        "DiscountPrice" => "Double",
        "Description" => "Varchar(255)",
    ];
    private static $has_one = [
        "Image" => Image::class,
        "Category" => Category::class,
        "EventShop" => EventShop::class,
    ];
    private static $owns = [
        "Image",
    ];
    private static $has_many = [
        "Review" => Review::class,
        "Favorite" => Favorite::class,
        "CartItem" => CartItem::class,
        "OrderItem" => OrderItem::class,
    ];
    private static $summary_fields = [
        'Name' => 'Name',
        'Category.Name' => 'Category',
        'EventShop.Name' => 'Event',
        'Stok' => 'Stok',
        'Weight' => 'Weight',
        'numberFormat' => 'Price',
        'discountNumberFormat' => 'Discount Price',
        'DisplayPrice' => 'After Discount',
        'Image.CMSThumbnail' => 'Image',
    ];

    public function numberFormat()
    {
        return 'Rp ' . number_format($this->Price, 0, '.', '.');
    }
    public function discountNumberFormat()
    {
        if ($this->DiscountPrice && $this->DiscountPrice > 0) {
            return 'Rp ' . number_format($this->DiscountPrice, 0, '.', '.');
        }
        return null;
    }
    public function hasDiscount()
    {
        return $this->DiscountPrice && $this->DiscountPrice > 0 && $this->DiscountPrice < $this->Price;
    }
    public function getDiscountPercentage()
    {
        if ($this->hasDiscount()) {
            $discount = ($this->DiscountPrice / $this->Price) * 100;
            return number_format($discount, 2) . '%';

        }
        return null;
    }

    public function getDisplayPrice()
    {
        if ($this->hasDiscount()) {
            $finalPrice = $this->Price - $this->DiscountPrice;
        } else {
            $finalPrice = $this->Price;
        }

        return 'Rp ' . number_format($finalPrice, 0, '.', '.');
    }
    public function getDisplayPriceValue()
    {
        if ($this->hasDiscount()) {
            return $this->Price - $this->DiscountPrice;
        } else {
            return $this->Price;
        }
    }

    public function getOriginalPrice()
    {
        if ($this->hasDiscount()) {
            return 'Rp ' . number_format($this->Price, 0, '.', '.');
        }
        return null;
    }

    public function getAverageRating()
    {
        $reviews = $this->Review();
        if ($reviews->count() == 0) {
            return null;
        }

        $totalRating = 0;
        foreach ($reviews as $review) {
            $totalRating += $review->Rating;
        }

        $average = $totalRating / $reviews->count();
        return number_format($average, 1);
    }
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldToTab('Root.Main', TextField::create('Name', 'Product Name'));
        $fields->addFieldToTab('Root.Main', TextField::create('Stok', 'Stock'));
        $fields->addFieldToTab('Root.Main', TextField::create('Weight', 'Weight'));
        $fields->addFieldToTab('Root.Main', TextField::create('Price', 'Price'));
        $fields->addFieldToTab('Root.Main', TextField::create('DiscountPrice', 'Discount Price'));
        $fields->addFieldToTab('Root.Main', UploadField::create('Image', 'Image'));
        $fields->addFieldToTab('Root.Main', DropdownField::create('CategoryID', 'Category')
            ->setSource(Category::get()->map('ID', 'Name'))
            ->setEmptyString('Select a Category'));
        $fields->addFieldToTab('Root.Main', TextareaField::create('Description', 'Description'));
        return $fields;
    }
}