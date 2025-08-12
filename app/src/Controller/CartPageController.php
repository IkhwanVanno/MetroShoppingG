<?php

use SilverStripe\Control\HTTPRequest;

class CartPageController extends PageController
{
    private static $allowed_actions = [
        'add',
        'remove',
        'index',
        'updateQuantity',
    ];

    private static $url_segment = 'cart';

    private static $url_handlers = [
        'add/$ID' => 'add',
        'remove/$ID' => 'remove',
        'update-quantity' => 'updateQuantity',
        '' => 'index'
    ];

    public function index(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            return $this->redirect('$BaseHref/auth/login');
        }

        $user = $this->getCurrentUser();
        $cartItems = CartItem::get()->filter('MemberID', $user->ID);

        $data = array_merge($this->getCommonData(), [
            'Title' => 'Shopping Cart',
            'CartItems' => $cartItems,
            'TotalItems' => $this->getTotalItems(),
            'TotalPrice' => $this->getTotalPrice(),
            'FormattedTotalPrice' => $this->getFormattedTotalPrice()
        ]);

        return $this->customise($data)->renderWith(['CartProductPage', 'Page']);
    }

    public function add(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            return $this->redirect('$BaseHref/auth/login');
        }

        $productID = $request->param('ID');
        $product = Product::get()->byID($productID);

        if (!$product) {
            return $this->httpError(404);
        }

        $user = $this->getCurrentUser();
        $existingCartItem = CartItem::get()->filter([
            'ProductID' => $productID,
            'MemberID' => $user->ID
        ])->first();

        if ($existingCartItem) {
            $existingCartItem->Quantity += 1;
            $existingCartItem->write();
        } else {
            $cartItem = CartItem::create();
            $cartItem->ProductID = $productID;
            $cartItem->MemberID = $user->ID;
            $cartItem->Quantity = 1;
            $cartItem->write();
        }

        return $this->redirectBack();
    }

    public function remove(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            return $this->redirect('$BaseHref/auth/login');
        }

        $cartItemID = $request->param('ID');
        $user = $this->getCurrentUser();

        $cartItem = CartItem::get()->filter([
            'ID' => $cartItemID,
            'MemberID' => $user->ID
        ])->first();

        if ($cartItem) {
            $cartItem->delete();
        }

        return $this->redirectBack();
    }

    public function updateQuantity(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            return $this->redirect('$BaseHref/auth/login');
        }

        if ($request->isPOST()) {
            $cartItemID = $request->postVar('cartItemID');
            $newQuantity = (int) $request->postVar('quantity');
            $user = $this->getCurrentUser();

            $cartItem = CartItem::get()->filter([
                'ID' => $cartItemID,
                'MemberID' => $user->ID
            ])->first();

            if ($cartItem && $newQuantity > 0) {
                // Cek apakah quantity tidak melebihi stok
                if ($newQuantity <= $cartItem->Product()->Stok) {
                    $cartItem->Quantity = $newQuantity;
                    $cartItem->write();
                }
            }
        }

        return $this->redirectBack();
    }

    public function getTotalItems()
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

    public function getTotalPrice()
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

    public function getFormattedTotalPrice()
    {
        return 'Rp ' . number_format($this->getTotalPrice(), 0, '.', '.');
    }


}