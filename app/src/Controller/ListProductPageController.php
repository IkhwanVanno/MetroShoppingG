<?php

use SilverStripe\Control\HTTPRequest;
use SilverStripe\ORM\PaginatedList;
use SilverStripe\ORM\ArrayList;

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
            'MinPriceFilter' => $request->getVar('min_price'),
            'MaxPriceFilter' => $request->getVar('max_price'),
            'SortFilter' => $request->getVar('sort'),
            'StockFilter' => $request->getVar('stock'),
            'RatingFilter' => $request->getVar('rating')
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
        $rating = $request->getVar('rating');

        $products = Product::get();

        if ($category) {
            $products = $products->filter('CategoryID', $category);
        }

        if ($search) {
            $products = $products->filterAny([
                'Name:PartialMatch' => $search,
                'Description:PartialMatch' => $search
            ]);
        }

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

        if ($rating && is_numeric($rating) && $rating >= 1 && $rating <= 5) {
            $productIds = $this->getProductIdsByMinRating((float) $rating);
            if (!empty($productIds)) {
                $products = $products->filter('ID', $productIds);
            } else {
                $products = $products->filter('ID', 0);
            }
        }

        $productsArray = [];
        foreach ($products as $product) {
            $finalPrice = $product->getDisplayPriceValue();
            
            $includeProduct = true;
            
            if ($minPrice && is_numeric($minPrice) && $minPrice > 0) {
                if ($finalPrice < (float) $minPrice) {
                    $includeProduct = false;
                }
            }
            
            if ($maxPrice && is_numeric($maxPrice) && $maxPrice > 0) {
                if ($finalPrice > (float) $maxPrice) {
                    $includeProduct = false;
                }
            }
            
            if ($includeProduct) {
                $productsArray[] = $product;
            }
        }

        if (!empty($productsArray)) {
            $productIds = array_map(function($product) {
                return $product->ID;
            }, $productsArray);
            $products = Product::get()->filter('ID', $productIds);
        } else {
            $products = Product::get()->filter('ID', 0);
        }

        switch ($sort) {
            case 'price_asc':
                $products = $this->sortProductsByPrice($products, 'ASC');
                break;
            case 'price_desc':
                $products = $this->sortProductsByPrice($products, 'DESC');
                break;
            case 'name_asc':
                $products = $products->sort('Name', 'ASC');
                break;
            case 'name_desc':
                $products = $products->sort('Name', 'DESC');
                break;
            case 'rating_desc':
                $products = $this->sortProductsByRating($products, 'DESC');
                break;
            case 'rating_asc':
                $products = $this->sortProductsByRating($products, 'ASC');
                break;
            default:
                $products = $products->sort('Created', 'DESC');
        }

        return $products;
    }

    /**
     * Get product IDs that have minimum rating
     */
    private function getProductIdsByMinRating($minRating)
    {
        $productIds = [];
        $allProducts = Product::get();
        
        foreach ($allProducts as $product) {
            $averageRating = $product->getAverageRating();
            if ($averageRating !== null && (float) $averageRating >= $minRating) {
                $productIds[] = $product->ID;
            }
        }
        
        return $productIds;
    }

    /**
     * Sort products by final price (considering discounts)
     */
    private function sortProductsByPrice($products, $direction = 'ASC')
    {
        $productsArray = [];
        foreach ($products as $product) {
            $productsArray[] = [
                'product' => $product,
                'final_price' => $product->getDisplayPriceValue()
            ];
        }

        usort($productsArray, function($a, $b) use ($direction) {
            if ($direction === 'ASC') {
                return $a['final_price'] <=> $b['final_price'];
            } else {
                return $b['final_price'] <=> $a['final_price'];
            }
        });

        $sortedProducts = [];
        foreach ($productsArray as $item) {
            $sortedProducts[] = $item['product'];
        }

        return new ArrayList($sortedProducts);
    }

    /**
     * Sort products by rating
     */
    private function sortProductsByRating($products, $direction = 'DESC')
    {
        $productsArray = [];
        foreach ($products as $product) {
            $averageRating = $product->getAverageRating();
            $productsArray[] = [
                'product' => $product,
                'rating' => $averageRating !== null ? (float) $averageRating : 0
            ];
        }

        usort($productsArray, function($a, $b) use ($direction) {
            if ($direction === 'ASC') {
                return $a['rating'] <=> $b['rating'];
            } else {
                return $b['rating'] <=> $a['rating'];
            }
        });

        $sortedProducts = [];
        foreach ($productsArray as $item) {
            $sortedProducts[] = $item['product'];
        }

        return new ArrayList($sortedProducts);
    }
}