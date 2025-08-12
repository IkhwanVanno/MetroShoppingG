<?php

use SilverStripe\Control\HTTPRequest;

class FavoritePageController extends PageController
{
    private static $allowed_actions = [
        'add',
        'remove',
        'index'
    ];

    private static $url_segment = 'favorite';

    private static $url_handlers = [
        'add/$ID' => 'add',
        'remove/$ID' => 'remove',
        '' => 'index'
    ];

    public function index(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            return $this->redirect('$BaseHref/auth/login');
        }

        $user = $this->getCurrentUser();
        $favorites = Favorite::get()->filter('MemberID', $user->ID);

        $data = array_merge($this->getCommonData(), [
            'Title' => 'My Favorites',
            'Favorites' => $favorites
        ]);

        return $this->customise($data)->renderWith(['FavoriteProductPage', 'Page']);
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
        $existingFavorite = Favorite::get()->filter([
            'ProductID' => $productID,
            'MemberID' => $user->ID
        ])->first();

        if (!$existingFavorite) {
            $favorite = Favorite::create();
            $favorite->ProductID = $productID;
            $favorite->MemberID = $user->ID;
            $favorite->write();
        }

        return $this->redirectBack();
    }

    public function remove(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            return $this->redirect('$BaseHref/auth/login');
        }

        $favoriteID = $request->param('ID');
        $user = $this->getCurrentUser();

        $favorite = Favorite::get()->filter([
            'ID' => $favoriteID,
            'MemberID' => $user->ID
        ])->first();

        if ($favorite) {
            $favorite->delete();
        }

        return $this->redirectBack();
    }
}