<?php

use SilverStripe\Control\HTTPRequest;

class CartPageController extends PageController
{
    private static $allowed_actions = [
        'add',
        'remove',
        'index'
    ];

    private static $url_segment = 'cart';

    private static $url_handlers = [
        'add/$ID' => 'add',
        'remove/$ID' => 'remove',
        '' => 'index'
    ];

    public function index(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            return $this->redirect('/auth-page/login');
        }

        $user = $this->getCurrentUser();
        $cartItems = CartItem::get()->filter('MemberID', $user->ID);

        $data = array_merge($this->getCommonData(), [
            'Title' => 'Shopping Cart',
            'CartItems' => $cartItems
        ]);

        return $this->customise($data)->renderWith(['CartProductPage', 'Page']);
    }

    public function add(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            return $this->redirect('/auth-page/login');
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
            return $this->redirect('/auth-page/login');
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
}