<?php

use SilverStripe\Control\HTTPRequest;
use SilverStripe\ORM\PaginatedList;

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

        $paginatedProducts = PaginatedList::create($filteredProducts, $request);
        $paginatedProducts->setPageLength(10);

        $categories = Category::get();

        $data = array_merge($this->getCommonData(), [
            'Title' => 'Product List',
            'FilteredProducts' => $paginatedProducts,
            'Category' => $categories,
            'CategoryFilter' => $categoryFilter,
            'SearchQuery' => $searchQuery,
            // Add filter values for maintaining state
            'MinPriceFilter' => $request->getVar('min_price'),
            'MaxPriceFilter' => $request->getVar('max_price'),
            'SortFilter' => $request->getVar('sort'),
            'StockFilter' => $request->getVar('stock')
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

    public function getFilteredProducts($category = null, $search = null)
    {
        $request = $this->getRequest();

        $minPrice = $request->getVar('min_price');
        $maxPrice = $request->getVar('max_price');
        $stock = $request->getVar('stock');
        $sort = $request->getVar('sort');

        $products = Product::get();

        // Filter by category
        if ($category) {
            $products = $products->filter('CategoryID', $category);
        }

        // Search by name or description
        if ($search) {
            $products = $products->filterAny([
                'Name:PartialMatch' => $search,
                'Description:PartialMatch' => $search
            ]);
        }

        // Filter by stock
        if ($stock) {
            switch ($stock) {
                case 'available':
                    $products = $products->filter('Stok:GreaterThan', 0);
                    break;
                case 'low':
                    $products = $products->filter('Stok:LessThanOrEqual', 10);
                    break;
            }
        }

        // Filter by price range
        if ($minPrice && is_numeric($minPrice) && $minPrice > 0) {
            $products = $products->filter('Price:GreaterThanOrEqual', (float) $minPrice);
        }
        if ($maxPrice && is_numeric($maxPrice) && $maxPrice > 0) {
            $products = $products->filter('Price:LessThanOrEqual', (float) $maxPrice);
        }

        // Sort by option
        switch ($sort) {
            case 'price_asc':
                $products = $products->sort('Price', 'ASC');
                break;
            case 'price_desc':
                $products = $products->sort('Price', 'DESC');
                break;
            case 'name_asc':
                $products = $products->sort('Name', 'ASC');
                break;
            case 'name_desc':
                $products = $products->sort('Name', 'DESC');
                break;
            default:
                $products = $products->sort('Created', 'DESC'); // default sorting
        }

        return $products;
    }
}