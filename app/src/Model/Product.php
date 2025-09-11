<?php

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\FieldType\DBDatetime;

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
        "FlashSale" => FlashSale::class,
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
        'FlashSale.Name' => 'FlashSale',
        'Stok' => 'Stok',
        'Weight' => 'Weight',
        'numberFormat' => 'Price',
        'discountNumberFormat' => 'Discount Price',
        'DisplayPrice' => 'After Discount',
        'Image.CMSThumbnail' => 'Image',
    ];
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

    // Cek diskon
    public function hasDiscount()
    {
        return $this->DiscountPrice && $this->DiscountPrice > 0 && $this->DiscountPrice < $this->Price;
    }

    // Menghitung Presentase diskon
    public function getDiscountPercentage()
    {
        if ($this->hasDiscount()) {
            $discount = ($this->DiscountPrice / $this->Price) * 100;
            return number_format($discount, 2) . '%';
        }
        return null;
    }

    // Harga Produk Original
    public function getOriginalPrice()
    {
        if ($this->hasDiscount()) {
            return 'Rp ' . number_format($this->Price, 0, '.', '.');
        }
        return null;
    }

    // DiscountPrice ->Produk
    public function getDisplayPrice()
    {
        if ($this->hasDiscount()) {
            $finalPrice = $this->Price - $this->DiscountPrice;
        } else {
            $finalPrice = $this->Price;
        }

        return 'Rp ' . number_format($finalPrice, 0, '.', '.');
    }

    // FlashSale Diskon
    public function getFlashSalePrice()
    {
        $basePrice = $this->Price;

        // Step 1: Diskon produk
        if ($this->hasDiscount()) {
            $basePrice -= $this->DiscountPrice;
        }

        if (
            $this->FlashSale()->exists() &&
            $this->FlashSale()->Status === 'active' &&
            DBDatetime::now()->getValue() >= $this->FlashSale()->Start_time &&
            DBDatetime::now()->getValue() <= $this->FlashSale()->End_time
        ) {
            $discountFlashSale = $this->FlashSale()->DiscountFlashSale;
            $flashSaleAmount = $basePrice * ($discountFlashSale / 100);
            $basePrice -= $flashSaleAmount;
        }
        $finalPrice = max($basePrice, 0);
        return 'Rp ' . number_format($finalPrice, 0, ',', '.');

    }

    // Untuk logika pembayaran
    public function getDisplayPriceValue()
    {
        $price = $this->Price;

        // Step 1: Diskon Produk (potongan nominal)
        if ($this->hasDiscount()) {
            $price -= $this->DiscountPrice;
        }

        // Step 2: Flash Sale (potongan persen)
        $flashSale = $this->FlashSale();

        $isFlashSaleActive = $flashSale->exists()
            && $flashSale->Status === 'active'
            && DBDatetime::now()->getValue() >= $flashSale->Start_time
            && DBDatetime::now()->getValue() <= $flashSale->End_time;

        if ($isFlashSaleActive) {
            $discountPercent = $flashSale->DiscountFlashSale;
            $discountAmount = $price * ($discountPercent / 100);
            $price -= $discountAmount;
        }

        // Harga tidak boleh negatif
        return max($price, 0);
    }

    // Mendapatkan total diskon produk (nominal)
    public function getProductDiscount()
    {
        if ($this->hasDiscount()) {
            return $this->DiscountPrice;
        }
        return 0;
    }

    // Mendapatkan total diskon FlashSale (nominal)
    public function getFlashSaleDiscount()
    {
        $basePrice = $this->Price;

        // Step 1: Kurangi diskon produk terlebih dahulu
        if ($this->hasDiscount()) {
            $basePrice -= $this->DiscountPrice;
        }

        // Step 2: Hitung diskon FlashSale
        $flashSale = $this->FlashSale();
        $isFlashSaleActive = $flashSale->exists()
            && $flashSale->Status === 'active'
            && DBDatetime::now()->getValue() >= $flashSale->Start_time
            && DBDatetime::now()->getValue() <= $flashSale->End_time;

        if ($isFlashSaleActive) {
            $discountPercent = $flashSale->DiscountFlashSale;
            return $basePrice * ($discountPercent / 100);
        }

        return 0;
    }

    // Mendapatkan persentase diskon FlashSale
    public function getFlashSaleDiscountPercentage()
    {
        $flashSale = $this->FlashSale();
        $isFlashSaleActive = $flashSale->exists()
            && $flashSale->Status === 'active'
            && DBDatetime::now()->getValue() >= $flashSale->Start_time
            && DBDatetime::now()->getValue() <= $flashSale->End_time;

        if ($isFlashSaleActive) {
            return $flashSale->DiscountFlashSale;
        }

        return 0;
    }

    // Check apakah ada FlashSale aktif
    public function hasActiveFlashSale()
    {
        $flashSale = $this->FlashSale();
        return $flashSale->exists()
            && $flashSale->Status === 'active'
            && DBDatetime::now()->getValue() >= $flashSale->Start_time
            && DBDatetime::now()->getValue() <= $flashSale->End_time;
    }

    // Menghitung rata rata rating
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

    // Format Harga
    public function numberFormat()
    {
        return 'Rp ' . number_format($this->Price, 0, '.', '.');
    }

    // Format harga diskon
    public function discountNumberFormat()
    {
        if ($this->DiscountPrice && $this->DiscountPrice > 0) {
            return 'Rp ' . number_format($this->DiscountPrice, 0, '.', '.');
        }
        return null;
    }

    // Format diskon produk
    public function getFormattedProductDiscount()
    {
        return 'Rp ' . number_format($this->getProductDiscount(), 0, '.', '.');
    }

    // Format diskon FlashSale
    public function getFormattedFlashSaleDiscount()
    {
        return 'Rp ' . number_format($this->getFlashSaleDiscount(), 0, '.', '.');
    }
}