<?php

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\SiteConfig\SiteConfig;

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
        "checkOngkir"
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
        '' => 'index'
    ];

    public function index(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            return $this->redirect('$BaseHref/auth/login');
        }

        $user = $this->getCurrentUser();
        $cartItems = CartItem::get()->filter('MemberID', $user->ID);

        if (!$cartItems || $cartItems->count() == 0) {
            return $this->redirect('$BaseHref/cart');
        }

        $shippingAddress = ShippingAddress::get()->filter('MemberID', $user->ID)->first();

        $data = array_merge($this->getCommonData(), [
            'CartItems' => $cartItems,
            'ShippingAddress' => $shippingAddress,
            'TotalItems' => $this->getTotalItems(),
            'TotalPrice' => $this->getTotalPrice(),
            'FormattedTotalPrice' => $this->getFormattedTotalPrice()
        ]);

        return $this->customise($data)->renderWith(['CheckoutPage', 'Page']);
    }

    public function detailAlamat(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            return $this->redirect('$BaseHref/auth/login');
        }

        $user = $this->getCurrentUser();
        $shippingAddresses = ShippingAddress::get()->filter('MemberID', $user->ID);

        $data = array_merge($this->getCommonData(), [
            'ShippingAddresses' => $shippingAddresses
        ]);

        return $this->customise($data)->renderWith(['DetailAlamatPage', 'Page']);
    }

    public function getProvinces(HTTPRequest $request)
    {
        $rajaOngkir = new RajaOngkirService();
        $provinces = $rajaOngkir->getProvinces();
        
        return HTTPResponse::create(json_encode($provinces), 200)
            ->addHeader('Content-Type', 'application/json');
    }

    public function getCities(HTTPRequest $request)
    {
        $provinceId = $request->param('ID');
        $rajaOngkir = new RajaOngkirService();
        $cities = $rajaOngkir->getCities($provinceId);
        
        return HTTPResponse::create(json_encode($cities), 200)
            ->addHeader('Content-Type', 'application/json');
    }

    public function getDistricts(HTTPRequest $request)
    {
        $cityId = $request->param('ID');
        $rajaOngkir = new RajaOngkirService();
        $districts = $rajaOngkir->getDistricts($cityId);
        
        return HTTPResponse::create(json_encode($districts), 200)
            ->addHeader('Content-Type', 'application/json');
    }

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

    public function addAddress(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            return $this->redirect('$BaseHref/auth/login');
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

        return $this->redirect('$BaseHref/checkout/detail-alamat');
    }

    public function updateAddress(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            return $this->redirect('$BaseHref/auth/login');
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

        return $this->redirect('$BaseHref/checkout/detail-alamat');
    }

    public function processOrder(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            return $this->redirect('$BaseHref/auth/login');
        }

        if ($request->isPOST()) {
            $user = $this->getCurrentUser();
            $cartItems = CartItem::get()->filter('MemberID', $user->ID);

            if (!$cartItems || $cartItems->count() == 0) {
                return $this->redirect('$BaseHref/cart');
            }

            $shippingAddress = ShippingAddress::get()->filter('MemberID', $user->ID)->first();
            if (!$shippingAddress) {
                return $this->redirect('$BaseHref/checkout/detail-alamat');
            }

            $order = Order::create();
            $order->MemberID = $user->ID;
            $order->OrderCode = 'ORD-' . date('Y') . '-' . str_pad(Order::get()->count() + 1, 6, '0', STR_PAD_LEFT);
            $order->Status = 'pending';
            $order->ShippingGoal = 0; // Will be updated when shipping is selected
            $order->CreatedAt = date('Y-m-d H:i:s');
            $order->UpdatedAt = date('Y-m-d H:i:s');
            $order->write();

            foreach ($cartItems as $cartItem) {
                $orderItem = OrderItem::create();
                $orderItem->OrderID = $order->ID;
                $orderItem->ProductID = $cartItem->ProductID;
                $orderItem->Quantity = $cartItem->Quantity;
                $orderItem->Price = $cartItem->Product()->Price;
                $orderItem->SubTotal = $cartItem->getSubtotal();
                $orderItem->write();
            }

            // Clear cart
            foreach ($cartItems as $cartItem) {
                $cartItem->delete();
            }

            return $this->redirect('$BaseHref/order/detail/' . $order->ID);
        }

        return $this->redirectBack();
    }

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

    private function getFormattedTotalPrice()
    {
        return 'Rp ' . number_format($this->getTotalPrice(), 0, '.', '.');
    }
}