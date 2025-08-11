<?php

use SilverStripe\Control\HTTPRequest;

class CheckoutPageController extends PageController
{
    private static $allowed_actions = [
        "index",
        "detailAlamat",
        "processOrder",
        "addAddress",
        "updateAddress"
    ];

    private static $url_segment = "checkout";

    private static $url_handlers = [
        'detail-alamat' => 'detailAlamat',
        'process-order' => 'processOrder',
        'add-address' => 'addAddress',
        'update-address' => 'updateAddress',
        '' => 'index'
    ];

    public function index(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            return $this->redirect('$BaseHref/auth-page/login');
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
            return $this->redirect('$BaseHref/auth-page/login');
        }

        $user = $this->getCurrentUser();
        $shippingAddresses = ShippingAddress::get()->filter('MemberID', $user->ID);

        $data = array_merge($this->getCommonData(), [
            'ShippingAddresses' => $shippingAddresses
        ]);

        return $this->customise($data)->renderWith(['DetailAlamatPage', 'Page']);
    }

    public function addAddress(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            return $this->redirect('$BaseHref/auth-page/login');
        }

        if ($request->isPOST()) {
            $user = $this->getCurrentUser();

            $shippingAddress = ShippingAddress::create();
            $shippingAddress->MemberID = $user->ID;
            $shippingAddress->ReceiverName = $request->postVar('receiverName');
            $shippingAddress->PhoneNumber = $request->postVar('phoneNumber');
            $shippingAddress->Address = $request->postVar('address');
            $shippingAddress->ProvinceID = $request->postVar('provinceID');
            $shippingAddress->CityID = $request->postVar('cityID');
            $shippingAddress->SubDistricID = $request->postVar('subDistricID');
            $shippingAddress->PostalCode = $request->postVar('postalCode');
            $shippingAddress->write();
        }

        return $this->redirect('$BaseHref/checkout/detail-alamat');
    }

    public function updateAddress(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            return $this->redirect('$BaseHref/auth-page/login');
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
                $shippingAddress->ProvinceID = $request->postVar('provinceID');
                $shippingAddress->CityID = $request->postVar('cityID');
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
            return $this->redirect('$BaseHref/auth-page/login');
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