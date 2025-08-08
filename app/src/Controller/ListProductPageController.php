<?php

use SilverStripe\Control\HTTPRequest;
use SilverStripe\SiteConfig\SiteConfig;

class ListProductPageController extends PageController
{
    private static $allowed_actions = [
        'index',
        'view'
    ];

    private static $url_handlers = [
        'view/$ID!' => 'view'
    ];

    public function index(HTTPRequest $request)
    {
        $searchQuery = $request->getVar('search');
        $categoryFilter = $request->getVar('category');

        $filteredProducts = $this->getFilteredProducts($categoryFilter, $searchQuery);
        $categories = Category::get();

        $data = array_merge($this->getCommonData(), [
            'Title' => 'Product List',
            'FilteredProducts' => $filteredProducts,
            'Category' => $categories,
            'CategoryFilter' => $categoryFilter,
            'SearchQuery' => $searchQuery
        ]);

        return $this->customise($data)->renderWith(['ListProductPage', 'Page']);
    }

    public function view(HTTPRequest $request)
    {
        $id = $request->param('ID');
        $product = Product::get()->byID($id);

        if (!$product) {
            return $this->httpError(404);
        }

        $reviews = Review::get()->filter('ProductID', $id);
        
        // Check user status
        $isFavorite = false;
        $isInCart = false;
        
        if ($this->isLoggedIn()) {
            $user = $this->getCurrentUser();
            
            $existingFavorite = Favorite::get()->filter([
                'ProductID' => $id,
                'MemberID' => $user->ID
            ])->first();
            $isFavorite = (bool) $existingFavorite;
            
            $existingCartItem = CartItem::get()->filter([
                'ProductID' => $id,
                'MemberID' => $user->ID
            ])->first();
            $isInCart = (bool) $existingCartItem;
        }

        $data = array_merge($this->getCommonData(), [
            'Title' => $product->Name,
            'Product' => $product,
            'Review' => $reviews,
            'IsFavorite' => $isFavorite,
            'IsInCart' => $isInCart
        ]);

        return $this->customise($data)->renderWith(['DetailProductPage', 'Page']);
    }
}