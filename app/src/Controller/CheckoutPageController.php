<?php

use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Environment;
use SilverStripe\ORM\ArrayList;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\View\ArrayData;

class CheckoutPageController extends PageController
{
    private static $allowed_actions = [
        "index",
        "detailAlamat",
        "processOrder",
        "addAddress",
        "updateAddress",
        "getProvinces",
        "getCities",
        "getDistricts",
        "checkOngkir",
        "calculateTotal"
    ];

    private static $url_segment = "checkout";

    private static $url_handlers = [
        'detail-alamat' => 'detailAlamat',
        'process-order' => 'processOrder',
        'add-address' => 'addAddress',
        'update-address' => 'updateAddress',
        'api/provinces' => 'getProvinces',
        'api/cities/$ID' => 'getCities',
        'api/districts/$ID' => 'getDistricts',
        'api/check-ongkir' => 'checkOngkir',
        'api/calculate-total' => 'calculateTotal',
        '' => 'index'
    ];

    /**
     * Menampilkan halaman checkout utama
     */
    public function index(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            return $this->redirect(Director::absoluteBaseURL() . '/auth/login');
        }

        $user = $this->getCurrentUser();
        $cartItems = CartItem::get()->filter('MemberID', $user->ID);

        if (!$cartItems || $cartItems->count() == 0) {
            return $this->redirect(Director::absoluteBaseURL() . '/cart');
        }

        $shippingAddress = ShippingAddress::get()->filter('MemberID', $user->ID)->first();

        $data = array_merge($this->getCommonData(), [
            'CartItems' => $cartItems,
            'ShippingAddress' => $shippingAddress,
            'TotalItems' => $this->getTotalItems(),
            'TotalPrice' => $this->getTotalPrice(),
            'FormattedTotalPrice' => $this->getFormattedTotalPrice(),
            'OriginalTotalPrice' => $this->getOriginalTotalPrice(),
            'FormattedOriginalTotalPrice' => $this->getFormattedOriginalTotalPrice(),
            'TotalProductDiscount' => $this->getTotalProductDiscount(),
            'FormattedTotalProductDiscount' => $this->getFormattedTotalProductDiscount(),
            'TotalFlashSaleDiscount' => $this->getTotalFlashSaleDiscount(),
            'FormattedTotalFlashSaleDiscount' => $this->getFormattedTotalFlashSaleDiscount(),
            'TotalWeight' => $this->getTotalWeight(),
            'PaymentMethods' => $this->getPaymentMethod(),
        ]);

        return $this->customise($data)->renderWith(['CheckoutPage', 'Page']);
    }

    /**
     * Menampilkan halaman detail alamat pengiriman
     */
    public function detailAlamat(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            return $this->redirect(Director::absoluteBaseURL() . '/auth/login');
        }

        $user = $this->getCurrentUser();
        $shippingAddresses = ShippingAddress::get()->filter('MemberID', $user->ID);

        $data = array_merge($this->getCommonData(), [
            'ShippingAddresses' => $shippingAddresses
        ]);

        return $this->customise($data)->renderWith(['DetailAlamatPage', 'Page']);
    }

    /**
     * Mendapatkan daftar provinsi dari RajaOngkir API
     */
    public function getProvinces(HTTPRequest $request)
    {
        $rajaOngkir = new RajaOngkirService();
        $provinces = $rajaOngkir->getProvinces();

        return HTTPResponse::create(json_encode($provinces), 200)
            ->addHeader('Content-Type', 'application/json');
    }

    /**
     * Mendapatkan daftar kota berdasarkan province ID dari RajaOngkir API
     */
    public function getCities(HTTPRequest $request)
    {
        $provinceId = $request->param('ID');
        $rajaOngkir = new RajaOngkirService();
        $cities = $rajaOngkir->getCities($provinceId);

        return HTTPResponse::create(json_encode($cities), 200)
            ->addHeader('Content-Type', 'application/json');
    }

    /**
     * Mendapatkan daftar kecamatan berdasarkan city ID dari RajaOngkir API
     */
    public function getDistricts(HTTPRequest $request)
    {
        $cityId = $request->param('ID');
        $rajaOngkir = new RajaOngkirService();
        $districts = $rajaOngkir->getDistricts($cityId);

        return HTTPResponse::create(json_encode($districts), 200)
            ->addHeader('Content-Type', 'application/json');
    }

    /**
     * Mengecek ongkos kirim dari RajaOngkir API
     */
    public function checkOngkir(HTTPRequest $request)
    {
        if ($request->isPOST()) {
            $siteConfig = SiteConfig::current_site_config();
            $origin = $siteConfig->CompanyDistricID;

            $destination = $request->postVar('district_id');
            $weight = $request->postVar('weight');
            $courier = $request->postVar('courier');

            $rajaOngkir = new RajaOngkirService();
            $ongkir = $rajaOngkir->checkOngkir($origin, $destination, $weight, $courier);

            return HTTPResponse::create(json_encode($ongkir), 200)
                ->addHeader('Content-Type', 'application/json');
        }

        return HTTPResponse::create('{"error": "Method not allowed"}', 405)
            ->addHeader('Content-Type', 'application/json');
    }

    /**
     * Menghitung total keseluruhan termasuk ongkir
     */
    public function calculateTotal(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            return HTTPResponse::create('{"error": "Unauthorized"}', 401)
                ->addHeader('Content-Type', 'application/json');
        }

        if ($request->isPOST()) {
            $shippingCost = (int) $request->postVar('shipping_cost');
            $paymentFee = (int) $request->postVar('payment_fee');
            $subtotal = $this->getTotalPrice();
            $totalCost = $subtotal + $shippingCost + $paymentFee;

            $response = [
                'subtotal' => $subtotal,
                'formatted_subtotal' => $this->getFormattedTotalPrice(),
                'shipping_cost' => $shippingCost,
                'formatted_shipping_cost' => 'Rp ' . number_format($shippingCost, 0, '.', '.'),
                'payment_fee' => $paymentFee,
                'formatted_payment_fee' => 'Rp ' . number_format($paymentFee, 0, '.', '.'),
                'total_cost' => $totalCost,
                'formatted_total_cost' => 'Rp ' . number_format($totalCost, 0, '.', '.')
            ];

            return HTTPResponse::create(json_encode($response), 200)
                ->addHeader('Content-Type', 'application/json');
        }

        return HTTPResponse::create('{"error": "Method not allowed"}', 405)
            ->addHeader('Content-Type', 'application/json');
    }

    /**
     * Menambahkan alamat pengiriman baru
     */
    public function addAddress(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            return $this->redirect(Director::absoluteBaseURL() . '/auth/login');
        }

        if ($request->isPOST()) {
            $user = $this->getCurrentUser();

            $shippingAddress = ShippingAddress::create();
            $shippingAddress->MemberID = $user->ID;
            $shippingAddress->ReceiverName = $request->postVar('receiverName');
            $shippingAddress->PhoneNumber = $request->postVar('phoneNumber');
            $shippingAddress->Address = $request->postVar('address');
            $shippingAddress->ProvinceName = $request->postVar('provinceName');
            $shippingAddress->ProvinceID = $request->postVar('provinceID');
            $shippingAddress->CityName = $request->postVar('cityName');
            $shippingAddress->CityID = $request->postVar('cityID');
            $shippingAddress->DistrictName = $request->postVar('districtName');
            $shippingAddress->SubDistricID = $request->postVar('subDistricID');
            $shippingAddress->PostalCode = $request->postVar('postalCode');
            $shippingAddress->write();
        }

        return $this->redirect(Director::absoluteBaseURL() . '/checkout/detail-alamat');
    }

    /**
     * Mengupdate alamat pengiriman yang sudah ada
     */
    public function updateAddress(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            return $this->redirect(Director::absoluteBaseURL() . '/auth/login');
        }

        if ($request->isPOST()) {
            $user = $this->getCurrentUser();
            $addressID = $request->postVar('addressID');

            $shippingAddress = ShippingAddress::get()->filter([
                'ID' => $addressID,
                'MemberID' => $user->ID
            ])->first();

            if ($shippingAddress) {
                $shippingAddress->ReceiverName = $request->postVar('receiverName');
                $shippingAddress->PhoneNumber = $request->postVar('phoneNumber');
                $shippingAddress->Address = $request->postVar('address');
                $shippingAddress->ProvinceName = $request->postVar('provinceName');
                $shippingAddress->ProvinceID = $request->postVar('provinceID');
                $shippingAddress->CityName = $request->postVar('cityName');
                $shippingAddress->CityID = $request->postVar('cityID');
                $shippingAddress->DistrictName = $request->postVar('districtName');
                $shippingAddress->SubDistricID = $request->postVar('subDistricID');
                $shippingAddress->PostalCode = $request->postVar('postalCode');
                $shippingAddress->write();
            }
        }

        return $this->redirect(Director::absoluteBaseURL() . '/checkout/detail-alamat');
    }

    /**
     * Memproses pesanan dan membuat order baru
     */
    public function processOrder(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            return $this->redirect(Director::absoluteBaseURL() . '/auth/login');
        }

        if ($request->isPOST()) {
            $user = $this->getCurrentUser();
            $cartItems = CartItem::get()->filter('MemberID', $user->ID);

            if (!$cartItems || $cartItems->count() == 0) {
                return $this->redirect(Director::absoluteBaseURL() . '/cart');
            }

            // Check stock availability before processing order
            if (!$this->checkStockAvailability($cartItems)) {
                $this->getRequest()->getSession()->set('CheckoutError', 'Stok produk tidak mencukupi');
                return $this->redirectBack();
            }

            $shippingAddress = ShippingAddress::get()->filter('MemberID', $user->ID)->first();
            if (!$shippingAddress) {
                return $this->redirect(Director::absoluteBaseURL() . '/checkout/detail-alamat');
            }

            $paymentMethod = $request->postVar('paymentMethod');
            $shippingCost = (float) $request->postVar('shippingCost');
            $courierService = $request->postVar('courierService');

            if (!$paymentMethod) {
                $this->getRequest()->getSession()->set('CheckoutError', 'Pilih metode pembayaran');
                return $this->redirectBack();
            }

            if (!$shippingCost || !$courierService) {
                $this->getRequest()->getSession()->set('CheckoutError', 'Pilih layanan pengiriman');
                return $this->redirectBack();
            }

            // Get payment fee from selected payment method
            $paymentMethods = $this->getPaymentMethod();
            $paymentFee = 0;
            foreach ($paymentMethods as $method) {
                if ($method->paymentMethod == $paymentMethod) {
                    $paymentFee = (float) $method->totalFee;
                    break;
                }
            }

            // Hitung total
            $subtotal = $this->getTotalPrice();

            // Buat order
            $order = Order::create();
            $order->MemberID = $user->ID;
            $order->OrderCode = 'ORD-' . date('Y') . '-' . str_pad(Order::get()->count() + 1, 6, '0', STR_PAD_LEFT);
            $order->Status = 'pending';
            $order->TotalPrice = $subtotal;
            $order->ShippingCost = $shippingCost;
            $order->PaymentFee = $paymentFee; // Store payment fee
            $order->PaymentMethod = $paymentMethod;
            $order->ShippingCourier = $courierService;
            $order->PaymentStatus = 'unpaid';
            $order->CreateAt = date('Y-m-d H:i:s');
            $order->UpdateAt = date('Y-m-d H:i:s');
            $order->ShippingAddressID = $shippingAddress->ID;
            $order->write();

            // Buat order items
            foreach ($cartItems as $cartItem) {
                $orderItem = OrderItem::create();
                $orderItem->OrderID = $order->ID;
                $orderItem->ProductID = $cartItem->ProductID;
                $orderItem->Quantity = $cartItem->Quantity;
                $orderItem->Price = $cartItem->Product()->getDisplayPriceValue();
                $orderItem->Subtotal = $cartItem->getSubtotal();
                $orderItem->write();
            }

            // Clear cart
            foreach ($cartItems as $cartItem) {
                $cartItem->delete();
            }

            // Redirect to payment
            return $this->redirect(Director::absoluteBaseURL() . '/payment/initiate/' . $order->ID);
        }

        return $this->redirectBack();
    }

    /**
     * Menghitung total harga asli semua item di keranjang (tanpa diskon)
     */
    private function getOriginalTotalPrice()
    {
        if (!$this->isLoggedIn()) {
            return 0;
        }

        $user = $this->getCurrentUser();
        $cartItems = CartItem::get()->filter('MemberID', $user->ID);

        $totalPrice = 0;
        foreach ($cartItems as $item) {
            $totalPrice += $item->getOriginalSubtotal();
        }

        return $totalPrice;
    }

    /**
     * Menghitung total diskon produk
     */
    private function getTotalProductDiscount()
    {
        if (!$this->isLoggedIn()) {
            return 0;
        }

        $user = $this->getCurrentUser();
        $cartItems = CartItem::get()->filter('MemberID', $user->ID);

        $totalDiscount = 0;
        foreach ($cartItems as $item) {
            $totalDiscount += $item->getProductDiscountTotal();
        }

        return $totalDiscount;
    }

    /**
     * Menghitung total diskon FlashSale
     */
    private function getTotalFlashSaleDiscount()
    {
        if (!$this->isLoggedIn()) {
            return 0;
        }

        $user = $this->getCurrentUser();
        $cartItems = CartItem::get()->filter('MemberID', $user->ID);

        $totalDiscount = 0;
        foreach ($cartItems as $item) {
            $totalDiscount += $item->getFlashSaleDiscountTotal();
        }

        return $totalDiscount;
    }

    /**
     * Check stock availability for cart items
     */
    private function checkStockAvailability($cartItems)
    {
        foreach ($cartItems as $cartItem) {
            $product = Product::get()->byID($cartItem->ProductID);
            if (!$product || $product->Stok < $cartItem->Quantity) {
                return false;
            }
        }
        return true;
    }

    /**
     * Menghitung total jumlah item di keranjang
     */
    private function getTotalItems()
    {
        if (!$this->isLoggedIn()) {
            return 0;
        }

        $user = $this->getCurrentUser();
        $cartItems = CartItem::get()->filter('MemberID', $user->ID);

        $totalItems = 0;
        foreach ($cartItems as $item) {
            $totalItems += $item->Quantity;
        }

        return $totalItems;
    }

    /**
     * Menghitung total harga semua item di keranjang (tanpa ongkir)
     */
    private function getTotalPrice()
    {
        if (!$this->isLoggedIn()) {
            return 0;
        }

        $user = $this->getCurrentUser();
        $cartItems = CartItem::get()->filter('MemberID', $user->ID);

        $totalPrice = 0;
        foreach ($cartItems as $item) {
            $totalPrice += $item->getSubtotal();
        }

        return $totalPrice;
    }

    /**
     * Menghitung total berat semua item di keranjang
     */
    private function getTotalWeight()
    {
        if (!$this->isLoggedIn()) {
            return 0;
        }

        $user = $this->getCurrentUser();
        $cartItems = CartItem::get()->filter('MemberID', $user->ID);

        $totalWeight = 0;
        foreach ($cartItems as $item) {
            $totalWeight += $item->Product()->Weight * $item->Quantity;
        }

        return $totalWeight;
    }

    /**
     * Mendapatkan Payment method dari Duitku
     */
    private function getPaymentMethod()
    {
        $duitku = new DuitkuService();
        $paymentMethods = $duitku->getPaymentMethods($this->getTotalPrice());

        $methods = new ArrayList();
        foreach ($paymentMethods as $method) {
            $methods->push(new ArrayData([
                'paymentMethod' => $method['paymentMethod'],
                'paymentName' => $method['paymentName'],
                'totalFee' => $method['totalFee'],
                'formattedFee' => 'Rp ' . number_format($method['totalFee'], 0, '.', '.')
            ]));
        }

        return $methods;
    }

    /**
     * Format total harga asli
     */
    private function getFormattedOriginalTotalPrice()
    {
        return 'Rp ' . number_format($this->getOriginalTotalPrice(), 0, '.', '.');
    }

    /**
     * Format total diskon produk
     */
    private function getFormattedTotalProductDiscount()
    {
        return 'Rp ' . number_format($this->getTotalProductDiscount(), 0, '.', '.');
    }

    /**
     * Format total diskon FlashSale
     */
    private function getFormattedTotalFlashSaleDiscount()
    {
        return 'Rp ' . number_format($this->getTotalFlashSaleDiscount(), 0, '.', '.');
    }

    /**
     * Mendapatkan total harga dalam format rupiah
     */
    private function getFormattedTotalPrice()
    {
        return 'Rp ' . number_format($this->getTotalPrice(), 0, '.', '.');
    }


}