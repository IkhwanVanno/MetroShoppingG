<?php

use SilverStripe\Control\Controller;
use SilverStripe\Control\Email\Email;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Environment;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\PaginatedList;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Security\IdentityStore;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\SiteConfig\SiteConfig;

class RestfullApiController extends Controller
{
    private static $url_segment = 'api';

    private static $allowed_actions = [
        'index',
        'login',
        'register',
        'logout',
        'googleAuth',
        'forgotpassword',
        'member',
        'updatePassword',
        'siteconfig',
        'carousel',
        'eventshops',
        'eventshop',
        'flashsales',
        'flashsale',
        'popupad',
        'category',
        'products',
        'product',
        // Cart
        'cart',
        'addToCart',
        'updateCartItem',
        'removeFromCart',
        'clearCart',
        // Favorites
        'favorites',
        'addToFavorites',
        'removeFromFavorites',
        'checkFavorite',
        // Address
        'addresses',
        'addAddress',
        'updateAddress',
        'deleteAddress',
        // Shipping
        'provinces',
        'cities',
        'districts',
        'checkOngkir',
        // Order
        'orders',
        'orderDetail',
        'createOrder',
        'cancelOrder',
        'markAsCompleted',
        // Payment
        'initiatePayment',
        'paymentCallback',
        'paymentMethods',
        // Review
        'submitReview',
        'productReviews',
        // Membership
        'membershipInfo',
        'membershipProgress',
        // Invoice
        'downloadInvoice',
        'sendInvoice'
    ];

    private static $url_handlers = [
        'login' => 'login',
        'register' => 'register',
        'logout' => 'logout',
        'google-auth' => 'googleAuth',
        'forgotpassword' => 'forgotpassword',
        'member/password' => 'updatePassword',
        'member' => 'member',
        'siteconfig' => 'siteconfig',
        'carousel' => 'carousel',
        'eventshops' => 'eventshops',
        'eventshop/$ID!' => 'eventshop',
        'flashsales' => 'flashsales',
        'flashsale/$ID!' => 'flashsale',
        'popupad' => 'popupad',
        'category' => 'category',
        'products' => 'products',
        'product/$ID!' => 'product',
        // Cart
        'cart/add' => 'addToCart',
        'cart/update/$ID!' => 'updateCartItem',
        'cart/remove/$ID!' => 'removeFromCart',
        'cart/clear' => 'clearCart',
        'cart' => 'cart',
        // Favorites
        'favorites/add' => 'addToFavorites',
        'favorites/remove/$ID!' => 'removeFromFavorites',
        'favorites/check/$ID!' => 'checkFavorite',
        'favorites' => 'favorites',
        // Address
        'addresses/add' => 'addAddress',
        'addresses/update/$ID!' => 'updateAddress',
        'addresses/delete/$ID!' => 'deleteAddress',
        'addresses' => 'addresses',
        // Shipping
        'shipping/provinces' => 'provinces',
        'shipping/cities/$ID!' => 'cities',
        'shipping/districts/$ID!' => 'districts',
        'shipping/check-ongkir' => 'checkOngkir',
        // Order
        'orders/complete/$ID!' => 'markAsCompleted',
        'orders/create' => 'createOrder',
        'orders/cancel/$ID!' => 'cancelOrder',
        'orders/$ID!' => 'orderDetail',
        'orders' => 'orders',
        // Payment
        'payment/initiate/$ID!' => 'initiatePayment',
        'payment/callback' => 'paymentCallback',
        'payment/methods' => 'paymentMethods',
        // Review
        'reviews/submit' => 'submitReview',
        'reviews/product/$ID!' => 'productReviews',
        // Membership
        'membership/info' => 'membershipInfo',
        'membership/progress' => 'membershipProgress',
        // Invoice
        'invoice/download/$ID!' => 'downloadInvoice',
        'invoice/send/$ID!' => 'sendInvoice',
        '' => 'index',
    ];

    private $authService;

    protected function init()
    {
        parent::init();
        $this->authService = new AuthServices();
    }

    public function index(HTTPRequest $request)
    {
        return $this->jsonResponse([
            'message' => 'SilverStripe E-Commerce API',
            'version' => '2.0',
            'status' => 'operational',
            'endpoints' => [
                'authentication' => [
                    'POST /api/google-auth' => 'Firebase Google Auth',
                    'POST /api/login' => 'Login user',
                    'POST /api/register' => 'Register new user',
                    'POST /api/logout' => 'Logout current user',
                    'POST /api/forgotpassword' => 'Forgot password'
                ],
                'member' => [
                    'GET /api/member' => 'Get current member profile',
                    'PUT /api/member' => 'Update member profile',
                    'PUT /api/member/password' => 'Update member password',
                ],
                'catalog' => [
                    'GET /api/siteconfig' => 'Get site configuration',
                    'GET /api/carousel' => 'Get carousel images',
                    'GET /api/eventshop' => 'Get event shop',
                    'GET /api/flashsales' => 'Get all flash sales',
                    'GET /api/flashsale/{id}' => 'Get flash sale detail',
                    'GET /api/popupad' => 'Get popup ads',
                    'GET /api/category' => 'Get categories',
                    'GET /api/products' => 'Get all products (with filters)',
                    'GET /api/product/{id}' => 'Get product detail',
                ],
                'cart' => [
                    'GET /api/cart' => 'Get cart items',
                    'POST /api/cart/add' => 'Add item to cart',
                    'PUT /api/cart/update/{id}' => 'Update cart item quantity',
                    'DELETE /api/cart/remove/{id}' => 'Remove item from cart',
                    'DELETE /api/cart/clear' => 'Clear all cart items',
                ],
                'favorites' => [
                    'GET /api/favorites' => 'Get favorite products',
                    'POST /api/favorites/add' => 'Add product to favorites',
                    'DELETE /api/favorites/remove/{id}' => 'Remove from favorites',
                    'GET /api/favorites/check/{product_id}' => 'Check if product is favorited',
                ],
                'address' => [
                    'GET /api/addresses' => 'Get all addresses',
                    'POST /api/addresses/add' => 'Add new address',
                    'PUT /api/addresses/update/{id}' => 'Update address',
                    'DELETE /api/addresses/delete/{id}' => 'Delete address',
                ],
                'shipping' => [
                    'GET /api/shipping/provinces' => 'Get provinces',
                    'GET /api/shipping/cities/{province_id}' => 'Get cities',
                    'GET /api/shipping/districts/{city_id}' => 'Get districts',
                    'POST /api/shipping/check-ongkir' => 'Check shipping cost',
                ],
                'order' => [
                    'GET /api/orders' => 'Get user orders',
                    'GET /api/orders/{id}' => 'Get order detail',
                    'POST /api/orders/create' => 'Create new order',
                    'POST /api/orders/cancel/{id}' => 'Cancel order',
                    'POST /api/orders/complete/{id}' => 'Mark As Complete order',
                ],
                'payment' => [
                    'GET /api/payment/methods' => 'Get payment methods',
                    'POST /api/payment/initiate/{order_id}' => 'Initiate payment',
                    'POST /api/payment/callback' => 'Payment callback (webhook)',
                ],
                'review' => [
                    'POST /api/reviews/submit' => 'Submit product review',
                    'GET /api/reviews/product/{product_id}' => 'Get product reviews',
                ],
                'membership' => [
                    'GET /api/membership/info' => 'Get membership information',
                    'GET /api/membership/progress' => 'Get progress to next tier',
                ],
                'invoice' => [
                    'GET /api/invoice/download/{order_id}' => 'Download invoice PDF',
                    'GET /api/invoice/send/{order_id}' => 'Send invoice Email',
                ],
            ],
        ]);
    }

    // ========== AUTHENTICATION ==========
    // * GOOGLE AUTH *
    // * Firebase *

    public function googleAuth(HTTPRequest $request)
    {
        if (!$request->isPOST()) {
            return $this->jsonResponse(['error' => 'Only POST method allowed'], 405);
        }

        $data = json_decode($request->getBody(), true);

        if (!isset($data['email'])) {
            return $this->jsonResponse(['error' => 'Email is required'], 400);
        }

        try {
            $email = $data['email'];
            $displayName = $data['display_name'] ?? '';
            $photoUrl = $data['photo_url'] ?? '';

            $nameParts = explode(' ', $displayName, 2);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';

            $member = Member::get()->filter('Email', $email)->first();

            if (!$member) {
                $member = Member::create();
                $member->FirstName = $firstName;
                $member->Surname = $lastName;
                $member->Email = $email;
                $member->IsVerified = true;
                $member->write();
                $member->addToGroupByCode('site-users');
                $member->changePassword(bin2hex(random_bytes(16)));
            } else {
                if (!$member->IsVerified) {
                    $member->IsVerified = true;
                    $member->write();
                }
            }

            Injector::inst()->get(IdentityStore::class)->logIn($member, false);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Google login successful',
                'user' => [
                    'id' => $member->ID,
                    'email' => $member->Email,
                    'first_name' => $member->FirstName,
                    'surname' => $member->Surname,
                ]
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse(['error' => 'Google authentication failed'], 500);
        }
    }

    // * MANUAL AUTH *
    public function login(HTTPRequest $request)
    {
        if (!$request->isPOST()) {
            return $this->jsonResponse(['error' => 'Gunakan metode POST'], 405);
        }

        $data = json_decode($request->getBody(), true);

        if (empty($data['email']) || empty($data['password'])) {
            return $this->jsonResponse(['error' => 'Email dan password diperlukan'], 400);
        }

        $member = Member::get()->filter('Email', $data['email'])->first();

        if (!$member || !password_verify($data['password'], $member->Password)) {
            return $this->jsonResponse(['error' => 'Email atau password salah'], 401);
        }

        if (!$member->IsVerified) {
            return $this->jsonResponse([
                'error' => 'Akun Anda belum diverifikasi. Silakan cek email Anda untuk melakukan verifikasi.'
            ], 403);
        }

        Injector::inst()->get(IdentityStore::class)->logIn($member, false);

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Login berhasil',
            'user' => [
                'id' => $member->ID,
                'email' => $member->Email,
                'first_name' => $member->FirstName,
                'surname' => $member->Surname,
            ]
        ]);
    }
    public function register(HTTPRequest $request)
    {
        if (!$request->isPOST()) {
            return $this->jsonResponse(['error' => 'Only POST method allowed'], 405);
        }

        $data = json_decode($request->getBody(), true);
        $baseURL = Environment::getEnv('SS_BASE_URL');
        $SiteConfig = SiteConfig::current_site_config();
        $emails = explode(',', $SiteConfig->Email);
        $CompanyEmail = trim($emails[0]);

        if (!isset($data['email'], $data['password'], $data['first_name'])) {
            return $this->jsonResponse(['error' => 'Email, password, and first name are required'], 400);
        }

        if (strlen($data['password']) < 8) {
            return $this->jsonResponse(['error' => 'Password must be at least 8 characters'], 400);
        }

        if (Member::get()->filter('Email', $data['email'])->exists()) {
            return $this->jsonResponse(['error' => 'Email already registered'], 400);
        }

        try {
            $member = Member::create();
            $member->FirstName = $data['first_name'];
            $member->Surname = $data['surname'] ?? '';
            $member->Email = $data['email'];
            $member->VerificationToken = sha1(uniqid());
            $member->IsVerified = false;
            $member->write();
            $member->addToGroupByCode('site-users');
            $member->changePassword($data['password']);

            $verifyLink = rtrim($baseURL) . '/verify?token=' . $member->VerificationToken;

            $emailObj = Email::create()
                ->setTo($data['email'] . $data['surname'])
                ->setFrom($CompanyEmail)
                ->setSubject('Verifikasi Email Anda')
                ->setHTMLTemplate('CustomEmail')
                ->setData([
                    'Name' => $data['first_name'],
                    'SenderEmail' => $data['email'],
                    'MessageContent' => "
                    Terima kasih telah mendaftar. Silakan salin link di bawah untuk memverifikasi akun Anda.
                    {$verifyLink}",
                    'SiteName' => $SiteConfig->Title,
                ]);

            $emailObj->send();

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Registration successful',
                'data' => [
                    'id' => $member->ID,
                    'email' => $member->Email,
                    'first_name' => $member->FirstName,
                    'surname' => $member->Surname,
                ]
            ], 201);
        } catch (ValidationException $e) {
            return $this->jsonResponse(['error' => 'Registration failed. Please try again.'], 500);
        }
    }
    public function logout(HTTPRequest $request)
    {
        if (!$request->isPOST()) {
            return $this->jsonResponse(['error' => 'Only POST method allowed'], 405);
        }

        $member = Security::getCurrentUser();

        if (!$member) {
            return $this->jsonResponse(['error' => 'Not logged in'], 401);
        }

        Injector::inst()->get(IdentityStore::class)->logOut($request);

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Logout successful'
        ]);
    }
    public function forgotpassword(HTTPRequest $request)
    {
        $data = json_decode($request->getBody(), true);
        $email = $data['email'] ?? '';

        if (empty($email)) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Email wajib diisi.'
            ], 422);
        }

        $validationResult = $this->authService->processForgotPassword($request, $email);
        if ($validationResult && $validationResult->isValid()) {
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Link atur ulang kata sandi telah dikirim ke email Anda.'
            ], 200);
        }

        $errorMessages = $validationResult ? $validationResult->getMessages() : [];
        $errorMessage = 'Terjadi kesalahan. Silakan coba lagi.';
        if (!empty($errorMessages)) {
            $errorMessage = $errorMessages[0]['message'] ?? $errorMessage;
        }

        return $this->jsonResponse([
            'success' => false,
            'message' => $errorMessage
        ], 400);
    }

    // ========== Data Model/Extension ==========
    // * MEMBER *
    public function member(HTTPRequest $request)
    {
        $member = $this->requireAuth();
        if ($member instanceof HTTPResponse)
            return $member;

        if ($request->isGET()) {
            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'id' => $member->ID,
                    'email' => $member->Email,
                    'first_name' => $member->FirstName,
                    'surname' => $member->Surname,
                ]
            ]);
        }

        if ($request->isPUT()) {
            $data = json_decode($request->getBody(), true);

            if (isset($data['first_name'])) {
                $member->FirstName = $data['first_name'];
            }
            if (isset($data['surname'])) {
                $member->Surname = $data['surname'];
            }
            if (isset($data['email'])) {
                $existingMember = Member::get()
                    ->filter('Email', $data['email'])
                    ->exclude('ID', $member->ID)
                    ->first();

                if ($existingMember) {
                    return $this->jsonResponse(['error' => 'Email already in use'], 400);
                }
                $member->Email = $data['email'];
            }

            try {
                $member->write();
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Profile updated successfully',
                    'data' => [
                        'id' => $member->ID,
                        'email' => $member->Email,
                        'first_name' => $member->FirstName,
                        'surname' => $member->Surname,
                    ]
                ]);
            } catch (ValidationException $e) {
                return $this->jsonResponse(['error' => 'Failed to update profile'], 500);
            }
        }

        return $this->jsonResponse(['error' => 'Method not allowed'], 405);
    }
    public function updatePassword(HTTPRequest $request)
    {
        if (!$request->isPUT()) {
            return $this->jsonResponse(['error' => 'Only PUT method allowed'], 405);
        }

        $member = $this->requireAuth();
        if ($member instanceof HTTPResponse)
            return $member;

        $data = json_decode($request->getBody(), true);

        if (!isset($data['new_password'])) {
            return $this->jsonResponse(['error' => 'New password is required'], 400);
        }

        if (strlen($data['new_password']) < 8) {
            return $this->jsonResponse(['error' => 'New password must be at least 8 characters'], 400);
        }

        try {
            $member->Password = $data['new_password'];
            $member->write();

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Password updated successfully'
            ]);
        } catch (ValidationException $e) {
            return $this->jsonResponse(['error' => 'Password update failed'], 400);
        }
    }

    // * SITECONFIG *
    public function siteconfig(HTTPRequest $request)
    {
        if (!$request->isGET()) {
            return $this->jsonResponse(['error' => 'Only Get method allowed'], status: 405);
        }
        $siteconfig = SiteConfig::current_site_config();
        if (!$siteconfig) {
            return $this->jsonResponse(['error' => 'SiteConfig not found'], status: 404);
        }

        return $this->jsonResponse([
            'success' => true,
            'data' => [
                "email" => $siteconfig->Email,
                "phone" => $siteconfig->Phone,
                "address" => $siteconfig->Address,
                "company_province_id" => $siteconfig->CompanyProvinceID,
                "company_province_name" => $siteconfig->CompanyProvinceName,
                "company_city_id" => $siteconfig->CompanyCityID,
                "company_city_name" => $siteconfig->CompanyCityName,
                "company_distric_id" => $siteconfig->CompanyDistricID,
                "company_distric_name" => $siteconfig->CompanyDistrictName,
                "company_postal_code" => $siteconfig->CompanyPostalCode,
                "credit" => $siteconfig->Credit,
                "about_title" => $siteconfig->AboutTitle,
                "about_description" => $siteconfig->AboutDescription,
                "sub_about1_title" => $siteconfig->SubAbout1Title,
                "sub_about1_description" => $siteconfig->SubAbout1Description,
                "sub_about2_title" => $siteconfig->SubAbout2Title,
                "sub_about2_description" => $siteconfig->SubAbout2Description,
                "sub_about3_title" => $siteconfig->SubAbout2Title,
                "sub_about3_description" => $siteconfig->SubAbout3Description,
                "sub_about4_title" => $siteconfig->SubAbout3Title,
                "sub_about4_description" => $siteconfig->SubAbout4Description,
                "favicon_url" => $siteconfig->favicon()->getAbsoluteURL(),
                "logo_url" => $siteconfig->logo()->getAbsoluteURL(),
            ]
        ]);
    }

    // * CAROUSEL *
    public function carousel(HTTPRequest $request)
    {
        if (!$request->isGET()) {
            return $this->jsonResponse(['error' => 'Only Get method allowed'], status: 405);
        }

        $carouselList = CarouselImage::get();
        if ($carouselList->count() == 0) {
            return $this->jsonResponse(['error' => 'Carousel not found'], 404);
        }

        $data = [];
        foreach ($carouselList as $carousel) {
            $data[] = [
                "id" => $carousel->ID,
                "name" => $carousel->Name,
                "link" => $carousel->Link,
                "image_url" => $carousel->Image()->exists() ? $carousel->Image()->getAbsoluteURL() : null,
            ];
        }

        return $this->jsonResponse([
            'success' => true,
            'data' => $data
        ]);
    }

    // * POPUP AD *
    public function popupad(HTTPRequest $request)
    {
        if (!$request->isGET()) {
            return $this->jsonResponse(['error' => 'Only Get method allowed'], 405);
        }

        $popUpAdList = PopupAd::get();
        if ($popUpAdList->count() == 0) {
            return $this->jsonResponse(['error' => 'PopUpAd not found'], 404);
        }
        $data = [];
        foreach ($popUpAdList as $popUpAd) {
            $data[] = [
                'id' => $popUpAd->ID,
                'title' => $popUpAd->Title,
                'link_url' => $popUpAd->Link,
                'active' => $popUpAd->Active,
                'sort_order' => $popUpAd->SortOrder,
                'image_url' => $popUpAd->Image() ? $popUpAd->Image()->getAbsoluteURL() : null,
            ];
        }

        return $this->jsonResponse([
            'success' => true,
            'data' => $data,
        ]);
    }

    // * EVENTSHOP *
    public function eventshops(HTTPRequest $request)
    {
        if (!$request->isGET()) {
            return $this->jsonResponse(['error' => 'Only Get method allowed'], 405);
        }

        $eventShopList = EventShop::get();
        if ($eventShopList->count() == 0) {
            return $this->jsonResponse(['error' => 'EventShop not found'], 404);
        }

        $data = [];
        foreach ($eventShopList as $eventShop) {
            $data[] = [
                'id' => $eventShop->ID,
                'name' => $eventShop->Name,
                'description' => $eventShop->Description,
                'link_url' => $eventShop->Link,
                'start_date' => $eventShop->StartDate,
                'end_date' => $eventShop->EndDate,
                'image_url' => $eventShop->Image() ? $eventShop->Image()->getAbsoluteURL() : null,
            ];
        }

        return $this->jsonResponse([
            'success' => true,
            'data' => $data,
        ]);
    }
    public function eventshop(HTTPRequest $request)
    {
        if (!$request->isGET()) {
            return $this->jsonResponse(['error' => 'Only GET method allowed'], 405);
        }

        $id = $request->param('ID');
        if (!$id || !is_numeric($id)) {
            return $this->jsonResponse(['error' => 'Invalid EventShop ID'], 400);
        }

        $eventShop = EventShop::get()->byID($id);
        if (!$eventShop) {
            return $this->jsonResponse(['error' => 'EventShop not found'], 404);
        }

        // Ambil produk dengan relasi yang benar
        $products = $eventShop->Product();
        $productList = [];

        foreach ($products as $product) {
            $productList[] = [
                'id' => $product->ID,
                'name' => $product->Name,
                'description' => $product->Description,
                'original_price' => (float) $product->Price,
                'price_after_product_discount' => (float) ($product->hasDiscount()
                    ? $product->Price - $product->DiscountPrice
                    : $product->Price),
                'price_after_all_discount' => (float) $product->getDisplayPriceValue(),
                'product_discount_percentage' => $product->hasDiscount()
                    ? round(($product->DiscountPrice / $product->Price) * 100, 2)
                    : 0,
                'flashsale_discount_percentage' => $product->getFlashSaleDiscountPercentage(),
                'has_flashsale' => $product->hasActiveFlashSale(),
                'flashsale_name' => $product->FlashSale()->exists() ? $product->FlashSale()->Name : null,
                'stock' => $product->Stok,
                'category' => $product->Category()->Name ?? null,
                'rating' => $product->getAverageRating(),
                'image_url' => $product->Image()->exists() ? $product->Image()->getAbsoluteURL() : null,
            ];
        }

        $data = [
            'id' => $eventShop->ID,
            'name' => $eventShop->Name,
            'description' => $eventShop->Description,
            'link_url' => $eventShop->Link,
            'start_date' => $eventShop->StartDate,
            'end_date' => $eventShop->EndDate,
            'image_url' => $eventShop->Image() ? $eventShop->Image()->getAbsoluteURL() : null,
            'products' => $productList,
        ];

        return $this->jsonResponse([
            'success' => true,
            'data' => $data,
        ]);
    }

    // * FLASHSALE *
    public function flashsales(HTTPRequest $request)
    {
        if (!$request->isGET()) {
            return $this->jsonResponse(['error' => 'Only GET method allowed'], 405);
        }

        $flashSaleList = FlashSale::get();
        if ($flashSaleList->count() == 0) {
            return $this->jsonResponse(['error' => 'FlashSale not found'], 404);
        }

        $data = [];
        foreach ($flashSaleList as $flashSale) {
            $data[] = [
                'id' => $flashSale->ID,
                'name' => $flashSale->Name,
                'description' => $flashSale->Description,
                'start_time' => $flashSale->Start_time,
                'end_time' => $flashSale->End_time,
                'discount_flash_sale' => (float) $flashSale->DiscountFlashSale,
                'status' => $flashSale->Status,
                'timer_status' => $flashSale->getTimerStatus(),
                'image_url' => $flashSale->Image() ? $flashSale->Image()->getAbsoluteURL() : null,
            ];
        }

        return $this->jsonResponse([
            'success' => true,
            'data' => $data,
        ]);
    }
    public function flashsale(HTTPRequest $request)
    {
        if (!$request->isGET()) {
            return $this->jsonResponse(['error' => 'Only GET method allowed'], 405);
        }

        $id = $request->param('ID');
        if (!$id || !is_numeric($id)) {
            return $this->jsonResponse(['error' => 'Invalid flashsale ID'], 400);
        }

        $flashSale = FlashSale::get()->byID($id);
        if (!$flashSale) {
            return $this->jsonResponse(['error' => 'FlashSale not found'], 404);
        }

        // Ambil produk dengan relasi yang benar
        $products = $flashSale->Product();
        $productList = [];

        foreach ($products as $product) {
            $productList[] = [
                'id' => $product->ID,
                'name' => $product->Name,
                'description' => $product->Description,
                'original_price' => (float) $product->Price,
                'price_after_product_discount' => (float) ($product->hasDiscount()
                    ? $product->Price - $product->DiscountPrice
                    : $product->Price),
                'price_after_all_discount' => (float) $product->getDisplayPriceValue(),
                'product_discount_percentage' => $product->hasDiscount()
                    ? round(($product->DiscountPrice / $product->Price) * 100, 2)
                    : 0,
                'flashsale_discount_percentage' => $product->getFlashSaleDiscountPercentage(),
                'has_flashsale' => $product->hasActiveFlashSale(),
                'flashsale_name' => $flashSale->Name,
                'stock' => $product->Stok,
                'category' => $product->Category()->Name ?? null,
                'rating' => $product->getAverageRating(),
                'image_url' => $product->Image()->exists() ? $product->Image()->getAbsoluteURL() : null,
            ];
        }

        $data = [
            'id' => $flashSale->ID,
            'name' => $flashSale->Name,
            'description' => $flashSale->Description,
            'start_time' => $flashSale->Start_time,
            'end_time' => $flashSale->End_time,
            'discount_flash_sale' => (float) $flashSale->DiscountFlashSale,
            'status' => $flashSale->Status,
            'timer_status' => $flashSale->getTimerStatus(),
            'image_url' => $flashSale->Image() ? $flashSale->Image()->getAbsoluteURL() : null,
            'products' => $productList,
        ];

        return $this->jsonResponse([
            'success' => true,
            'data' => $data,
        ]);
    }
    // *  CATEGORY * 
    public function category(HTTPRequest $request)
    {
        if (!$request->isGET()) {
            return $this->jsonResponse(['error' => 'Only GET method allowed'], 405);
        }

        $categoryList = Category::get();
        if ($categoryList->Count() == 0) {
            return $this->jsonResponse(['error' => 'Category not found'], 404);
        }
        $data = [];
        foreach ($categoryList as $category) {
            $data[] = [
                'id' => $category->ID,
                'name' => $category->Name,
            ];
        }
        return $this->jsonResponse([
            'success' => true,
            'data' => $data
        ]);
    }

    // * PRODUCT * 
    public function products(HTTPRequest $request)
    {
        if (!$request->isGET()) {
            return $this->jsonResponse(['error' => 'Only GET method allowed'], 405);
        }

        // Ambil parameter dari URL
        $search = $request->getVar('search');
        $category = $request->getVar('category');
        $minPrice = $request->getVar('min_price');
        $maxPrice = $request->getVar('max_price');
        $stock = $request->getVar('stock');
        $sort = $request->getVar('sort');
        $rating = $request->getVar('rating');
        $page = $request->getVar('page') ?? 1;
        $limit = $request->getVar('limit') ?? 10;

        // Ambil semua produk
        $products = Product::get();

        // === Filter berdasarkan kategori ===
        if ($category) {
            $products = $products->filter('CategoryID', $category);
        }

        // === Filter pencarian ===
        if ($search) {
            $products = $products->filterAny([
                'Name:PartialMatch' => $search,
                'Description:PartialMatch' => $search
            ]);
        }

        // === Filter stok ===
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

        // === Filter rating ===
        if ($rating && is_numeric($rating)) {
            $filteredIDs = [];
            foreach ($products as $p) {
                $avg = $p->getAverageRating();
                if ($avg !== null && $avg >= (float) $rating) {
                    $filteredIDs[] = $p->ID;
                }
            }
            $products = count($filteredIDs) > 0
                ? $products->filter('ID', $filteredIDs)
                : Product::get()->filter('ID', 0);
        }

        // === Filter harga ===
        $validProducts = [];
        foreach ($products as $p) {
            $price = $p->getDisplayPriceValue();
            if ($minPrice && $price < (float) $minPrice)
                continue;
            if ($maxPrice && $price > (float) $maxPrice)
                continue;
            $validProducts[] = $p;
        }
        if (count($validProducts) > 0) {
            $ids = array_map(fn($p) => $p->ID, $validProducts);
            $products = Product::get()->filter('ID', $ids);
        } else {
            $products = Product::get()->filter('ID', 0);
        }

        // === Sorting ===
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
            case 'rating_desc':
            case 'rating_asc':
                // Sorting manual berdasarkan rating
                $arr = [];
                foreach ($products as $p) {
                    $arr[] = [
                        'product' => $p,
                        'rating' => $p->getAverageRating() ?: 0
                    ];
                }
                usort(
                    $arr,
                    fn($a, $b) =>
                    $sort === 'rating_asc'
                    ? $a['rating'] <=> $b['rating']
                    : $b['rating'] <=> $a['rating']
                );
                $sorted = array_map(fn($x) => $x['product'], $arr);
                $products = new SilverStripe\ORM\ArrayList($sorted);
                break;
            default:
                $products = $products->sort('Created', 'DESC');
        }

        // === Pagination ===
        $paginated = new PaginatedList($products, $request);
        $paginated->setPageLength($limit);
        $paginated->setCurrentPage($page);

        // === Format response JSON ===
        $data = [];
        foreach ($paginated as $product) {
            $data[] = [
                'id' => $product->ID,
                'name' => $product->Name,
                'description' => $product->Description,
                'original_price' => (float) $product->Price,
                'price_after_product_discount' => (float) ($product->hasDiscount()
                    ? $product->Price - $product->DiscountPrice
                    : $product->Price),
                'price_after_all_discount' => (float) $product->getDisplayPriceValue(),
                'product_discount_percentage' => $product->hasDiscount()
                    ? round(($product->DiscountPrice / $product->Price) * 100, 2)
                    : 0,
                'flashsale_discount_percentage' => $product->getFlashSaleDiscountPercentage(),
                'has_flashsale' => $product->hasActiveFlashSale(),
                'flashsale_name' => $product->FlashSale()->exists() ? $product->FlashSale()->Name : null,
                'flashsale_status' => $product->FlashSale()->exists() ? $product->FlashSale()->getTimerStatus() : null,
                'stock' => $product->Stok,
                'category' => $product->Category()->Name ?? null,
                'rating' => $product->getAverageRating(),
                'created' => $product->Created,
                'updated' => $product->LastEdited,
                'image_url' => $product->Image()->exists() ? $product->Image()->getAbsoluteURL() : null,
            ];

        }

        return $this->jsonResponse([
            'success' => true,
            'meta' => [
                'total_items' => $paginated->getTotalItems(),
                'total_pages' => $paginated->TotalPages(),
                'current_page' => (int) $page,
                'limit' => (int) $limit,
            ],
            'data' => $data,
        ]);
    }
    public function product(HTTPRequest $request)
    {
        if (!$request->isGET()) {
            return $this->jsonResponse(['error' => 'Only GET method allowed'], 405);
        }

        $id = $request->param('ID');
        if (!$id || !is_numeric($id)) {
            return $this->jsonResponse(['error' => 'Invalid product ID'], 400);
        }

        $product = Product::get()->byID($id);

        if (!$product) {
            return $this->jsonResponse(['error' => 'Product not found'], 404);
        }

        // Ambil data dasar
        $data = [
            'id' => $product->ID,
            'name' => $product->Name,
            'description' => $product->Description,
            'original_price' => (float) $product->Price,
            'price_after_product_discount' => (float) ($product->hasDiscount()
                ? $product->Price - $product->DiscountPrice
                : $product->Price),
            'price_after_all_discount' => (float) $product->getDisplayPriceValue(),
            'product_discount_percentage' => $product->hasDiscount()
                ? round(($product->DiscountPrice / $product->Price) * 100, 2)
                : 0,
            'flashsale_discount_percentage' => $product->getFlashSaleDiscountPercentage(),
            'has_flashsale' => $product->hasActiveFlashSale(),
            'flashsale_name' => $product->FlashSale()->exists() ? $product->FlashSale()->Name : null,
            'flashsale_status' => $product->FlashSale()->exists() ? $product->FlashSale()->getTimerStatus() : null,
            'stock' => $product->Stok,
            'category' => $product->Category()->Name ?? null,
            'rating' => $product->getAverageRating(),
            'created' => $product->Created,
            'updated' => $product->LastEdited,
            'image_url' => $product->Image()->exists() ? $product->Image()->getAbsoluteURL() : null,
        ];


        if (class_exists(Review::class)) {
            $reviews = Review::get()->filter('ProductID', $product->ID);
            $data['reviews'] = [];

            foreach ($reviews as $review) {
                $data['reviews'][] = [
                    'id' => $review->ID,
                    'author' => $review->Member()->FirstName ?? 'Anonymous',
                    'rating' => $review->Rating,
                    'Message' => $review->Message,
                    'createdAt' => $review->CreatedAt,
                    'ShowName' => $reviews->ShowName,
                ];
            }
        }

        // Jika ada relasi Favorite
        if (class_exists(Favorite::class)) {
            $data['favorites_count'] = Favorite::get()->filter('ProductID', $product->ID)->count();
        }

        // Jika ada relasi CartItem (untuk status dalam keranjang)
        if (class_exists(CartItem::class)) {
            $data['in_cart'] = CartItem::get()->filter('ProductID', $product->ID)->exists();
        }

        return $this->jsonResponse([
            'success' => true,
            'data' => $data
        ]);
    }


    // ========== CART MANAGEMENT ==========
    /**
     * GET /api/cart - Get all cart items
     */
    public function cart(HTTPRequest $request)
    {
        if (!$request->isGET()) {
            return $this->jsonResponse(['error' => 'Only GET method allowed'], 405);
        }

        $member = $this->requireAuth();
        if ($member instanceof HTTPResponse)
            return $member;

        $cartItems = CartItem::get()->filter('MemberID', $member->ID);

        $items = [];
        $subtotal = 0;
        $totalItems = 0;
        $totalWeight = 0;
        $originalTotal = 0;
        $productDiscount = 0;
        $flashSaleDiscount = 0;

        foreach ($cartItems as $item) {
            $product = $item->Product();

            $items[] = [
                'id' => $item->ID,
                'product_id' => $product->ID,
                'product_name' => $product->Name,
                'quantity' => $item->Quantity,
                'price' => (float) $product->getDisplayPriceValue(),
                'original_price' => (float) $product->Price,
                'description' => $product->Description,
                'subtotal' => (float) $item->getSubtotal(),
                'original_subtotal' => (float) $item->getOriginalSubtotal(),
                'product_discount' => (float) $item->getProductDiscountTotal(),
                'flashsale_discount' => (float) $item->getFlashSaleDiscountTotal(),
                'weight' => $product->Weight,
                'total_weight' => $product->Weight * $item->Quantity,
                'stock' => $product->Stok,
                'rating' => $product->getAverageRating(),
                'image_url' => $product->Image()->exists() ? $product->Image()->getAbsoluteURL() : null,
            ];

            $subtotal += $item->getSubtotal();
            $originalTotal += $item->getOriginalSubtotal();
            $productDiscount += $item->getProductDiscountTotal();
            $flashSaleDiscount += $item->getFlashSaleDiscountTotal();
            $totalItems += $item->Quantity;
            $totalWeight += ($product->Weight * $item->Quantity);
        }

        return $this->jsonResponse([
            'success' => true,
            'data' => [
                'items' => $items,
                'summary' => [
                    'total_items' => $totalItems,
                    'total_weight' => $totalWeight,
                    'original_total' => $originalTotal,
                    'product_discount' => $productDiscount,
                    'flashsale_discount' => $flashSaleDiscount,
                    'subtotal' => $subtotal,
                    'formatted_original_total' => 'Rp ' . number_format($originalTotal, 0, '.', '.'),
                    'formatted_product_discount' => 'Rp ' . number_format($productDiscount, 0, '.', '.'),
                    'formatted_flashsale_discount' => 'Rp ' . number_format($flashSaleDiscount, 0, '.', '.'),
                    'formatted_subtotal' => 'Rp ' . number_format($subtotal, 0, '.', '.'),
                ]
            ]
        ]);
    }
    public function addToCart(HTTPRequest $request)
    {
        if (!$request->isPOST()) {
            return $this->jsonResponse(['error' => 'Only POST method allowed'], 405);
        }

        $member = $this->requireAuth();
        if ($member instanceof HTTPResponse)
            return $member;

        $data = json_decode($request->getBody(), true);

        if (!isset($data['product_id']) || !isset($data['quantity'])) {
            return $this->jsonResponse(['error' => 'product_id and quantity are required'], 400);
        }

        $productID = (int) $data['product_id'];
        $quantity = (int) $data['quantity'];

        if ($quantity <= 0) {
            return $this->jsonResponse(['error' => 'Quantity must be greater than 0'], 400);
        }

        $product = Product::get()->byID($productID);
        if (!$product) {
            return $this->jsonResponse(['error' => 'Product not found'], 404);
        }

        // Check stock
        if ($product->Stok < $quantity) {
            return $this->jsonResponse(['error' => 'Insufficient stock'], 400);
        }

        // Check if item already in cart
        $cartItem = CartItem::get()->filter([
            'MemberID' => $member->ID,
            'ProductID' => $productID
        ])->first();

        if ($cartItem) {
            // Update quantity
            $newQuantity = $cartItem->Quantity + $quantity;

            if ($product->Stok < $newQuantity) {
                return $this->jsonResponse(['error' => 'Insufficient stock'], 400);
            }

            $cartItem->Quantity = $newQuantity;
            $cartItem->write();
        } else {
            // Create new cart item
            $cartItem = CartItem::create();
            $cartItem->MemberID = $member->ID;
            $cartItem->ProductID = $productID;
            $cartItem->Quantity = $quantity;
            $cartItem->write();
        }

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Product added to cart',
            'data' => [
                'cart_item_id' => $cartItem->ID,
                'quantity' => $cartItem->Quantity,
            ]
        ], 201);
    }
    public function updateCartItem(HTTPRequest $request)
    {
        if (!$request->isPUT()) {
            return $this->jsonResponse(['error' => 'Only PUT method allowed'], 405);
        }

        $member = $this->requireAuth();
        if ($member instanceof HTTPResponse)
            return $member;

        $cartItemID = $request->param('ID');
        $data = json_decode($request->getBody(), true);

        if (!isset($data['quantity'])) {
            return $this->jsonResponse(['error' => 'quantity is required'], 400);
        }

        $quantity = (int) $data['quantity'];

        if ($quantity <= 0) {
            return $this->jsonResponse(['error' => 'Quantity must be greater than 0'], 400);
        }

        $cartItem = CartItem::get()->filter([
            'ID' => $cartItemID,
            'MemberID' => $member->ID
        ])->first();

        if (!$cartItem) {
            return $this->jsonResponse(['error' => 'Cart item not found'], 404);
        }

        $product = $cartItem->Product();

        if ($product->Stok < $quantity) {
            return $this->jsonResponse(['error' => 'Insufficient stock'], 400);
        }

        $cartItem->Quantity = $quantity;
        $cartItem->write();

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Cart item updated',
            'data' => [
                'cart_item_id' => $cartItem->ID,
                'quantity' => $cartItem->Quantity,
            ]
        ]);
    }
    public function removeFromCart(HTTPRequest $request)
    {
        if (!$request->isDELETE()) {
            return $this->jsonResponse(['error' => 'Only DELETE method allowed'], 405);
        }

        $member = $this->requireAuth();
        if ($member instanceof HTTPResponse)
            return $member;

        $cartItemID = $request->param('ID');

        $cartItem = CartItem::get()->filter([
            'ID' => $cartItemID,
            'MemberID' => $member->ID
        ])->first();

        if (!$cartItem) {
            return $this->jsonResponse(['error' => 'Cart item not found'], 404);
        }

        $cartItem->delete();

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Item removed from cart'
        ]);
    }
    public function clearCart(HTTPRequest $request)
    {
        if (!$request->isDELETE()) {
            return $this->jsonResponse(['error' => 'Only DELETE method allowed'], 405);
        }

        $member = $this->requireAuth();
        if ($member instanceof HTTPResponse)
            return $member;

        $cartItems = CartItem::get()->filter('MemberID', $member->ID);

        foreach ($cartItems as $item) {
            $item->delete();
        }

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Cart cleared'
        ]);
    }

    // ========== FAVORITES MANAGEMENT ==========
    public function favorites(HTTPRequest $request)
    {
        if (!$request->isGET()) {
            return $this->jsonResponse(['error' => 'Only GET method allowed'], 405);
        }

        $member = $this->requireAuth();
        if ($member instanceof HTTPResponse)
            return $member;

        $favorites = Favorite::get()->filter('MemberID', $member->ID);

        $items = [];
        foreach ($favorites as $fav) {
            $product = $fav->Product();

            $items[] = [
                'id' => $fav->ID,
                'product_id' => $product->ID,
                'product_name' => $product->Name,
                'price' => (float) $product->getDisplayPriceValue(),
                'original_price' => (float) $product->Price,
                'description' => $product->Description,
                'stock' => $product->Stok,
                'rating' => $product->getAverageRating(),
                'has_flashsale' => $product->hasActiveFlashSale(),
                'image_url' => $product->Image()->exists() ? $product->Image()->getAbsoluteURL() : null,
            ];
        }

        return $this->jsonResponse([
            'success' => true,
            'data' => $items,
            'total' => count($items)
        ]);
    }
    public function addToFavorites(HTTPRequest $request)
    {
        if (!$request->isPOST()) {
            return $this->jsonResponse(['error' => 'Only POST method allowed'], 405);
        }

        $member = $this->requireAuth();
        if ($member instanceof HTTPResponse)
            return $member;

        $data = json_decode($request->getBody(), true);

        if (!isset($data['product_id'])) {
            return $this->jsonResponse(['error' => 'product_id is required'], 400);
        }

        $productID = (int) $data['product_id'];

        $product = Product::get()->byID($productID);
        if (!$product) {
            return $this->jsonResponse(['error' => 'Product not found'], 404);
        }

        // Check if already favorited
        $existing = Favorite::get()->filter([
            'MemberID' => $member->ID,
            'ProductID' => $productID
        ])->first();

        if ($existing) {
            return $this->jsonResponse(['error' => 'Product already in favorites'], 400);
        }

        $favorite = Favorite::create();
        $favorite->MemberID = $member->ID;
        $favorite->ProductID = $productID;
        $favorite->write();

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Product added to favorites',
            'data' => [
                'favorite_id' => $favorite->ID
            ]
        ], 201);
    }
    public function removeFromFavorites(HTTPRequest $request)
    {
        if (!$request->isDELETE()) {
            return $this->jsonResponse(['error' => 'Only DELETE method allowed'], 405);
        }

        $member = $this->requireAuth();
        if ($member instanceof HTTPResponse)
            return $member;

        $favoriteID = $request->param('ID');

        $favorite = Favorite::get()->filter([
            'ID' => $favoriteID,
            'MemberID' => $member->ID
        ])->first();

        if (!$favorite) {
            return $this->jsonResponse(['error' => 'Favorite not found'], 404);
        }

        $favorite->delete();

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Removed from favorites'
        ]);
    }
    public function checkFavorite(HTTPRequest $request)
    {
        if (!$request->isGET()) {
            return $this->jsonResponse(['error' => 'Only GET method allowed'], 405);
        }

        $member = $this->requireAuth();
        if ($member instanceof HTTPResponse)
            return $member;

        $productID = $request->param('ID');

        $favorite = Favorite::get()->filter([
            'MemberID' => $member->ID,
            'ProductID' => $productID
        ])->first();

        return $this->jsonResponse([
            'success' => true,
            'is_favorited' => $favorite ? true : false,
            'favorite_id' => $favorite ? $favorite->ID : null
        ]);
    }

    // ========== ADDRESS MANAGEMENT ==========
    public function addresses(HTTPRequest $request)
    {
        if (!$request->isGET()) {
            return $this->jsonResponse(['error' => 'Only GET method allowed'], 405);
        }

        $member = $this->requireAuth();
        if ($member instanceof HTTPResponse)
            return $member;

        $addresses = ShippingAddress::get()->filter('MemberID', $member->ID);

        $items = [];
        foreach ($addresses as $addr) {
            $items[] = [
                'id' => $addr->ID,
                'receiver_name' => $addr->ReceiverName,
                'phone_number' => $addr->PhoneNumber,
                'address' => $addr->Address,
                'province_id' => $addr->ProvinceID,
                'province_name' => $addr->ProvinceName,
                'city_id' => $addr->CityID,
                'city_name' => $addr->CityName,
                'district_id' => $addr->SubDistricID,
                'district_name' => $addr->DistrictName,
                'postal_code' => $addr->PostalCode,
                'is_default' => $addr->IsDefault,
            ];
        }

        return $this->jsonResponse([
            'success' => true,
            'data' => $items
        ]);
    }
    public function addAddress(HTTPRequest $request)
    {
        if (!$request->isPOST()) {
            return $this->jsonResponse(['error' => 'Only POST method allowed'], 405);
        }

        $member = $this->requireAuth();
        if ($member instanceof HTTPResponse)
            return $member;

        $data = json_decode($request->getBody(), true);

        $required = [
            'receiver_name',
            'phone_number',
            'address',
            'province_id',
            'province_name',
            'city_id',
            'city_name',
            'district_id',
            'district_name',
            'postal_code'
        ];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                return $this->jsonResponse(['error' => "$field is required"], 400);
            }
        }

        $address = ShippingAddress::create();
        $address->MemberID = $member->ID;
        $address->ReceiverName = $data['receiver_name'];
        $address->PhoneNumber = $data['phone_number'];
        $address->Address = $data['address'];
        $address->ProvinceID = $data['province_id'];
        $address->ProvinceName = $data['province_name'];
        $address->CityID = $data['city_id'];
        $address->CityName = $data['city_name'];
        $address->SubDistricID = $data['district_id'];
        $address->DistrictName = $data['district_name'];
        $address->PostalCode = $data['postal_code'];
        $address->IsDefault = $data['is_default'] ?? false;
        $address->write();

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Address added successfully',
            'data' => ['address_id' => $address->ID]
        ], 201);
    }
    public function updateAddress(HTTPRequest $request)
    {
        if (!$request->isPUT()) {
            return $this->jsonResponse(['error' => 'Only PUT method allowed'], 405);
        }

        $member = $this->requireAuth();
        if ($member instanceof HTTPResponse)
            return $member;

        $addressID = $request->param('ID');
        $data = json_decode($request->getBody(), true);

        $address = ShippingAddress::get()->filter([
            'ID' => $addressID,
            'MemberID' => $member->ID
        ])->first();

        if (!$address) {
            return $this->jsonResponse(['error' => 'Address not found'], 404);
        }

        if (isset($data['receiver_name']))
            $address->ReceiverName = $data['receiver_name'];
        if (isset($data['phone_number']))
            $address->PhoneNumber = $data['phone_number'];
        if (isset($data['address']))
            $address->Address = $data['address'];
        if (isset($data['province_id']))
            $address->ProvinceID = $data['province_id'];
        if (isset($data['province_name']))
            $address->ProvinceName = $data['province_name'];
        if (isset($data['city_id']))
            $address->CityID = $data['city_id'];
        if (isset($data['city_name']))
            $address->CityName = $data['city_name'];
        if (isset($data['district_id']))
            $address->SubDistricID = $data['district_id'];
        if (isset($data['district_name']))
            $address->DistrictName = $data['district_name'];
        if (isset($data['postal_code']))
            $address->PostalCode = $data['postal_code'];
        if (isset($data['is_default']))
            $address->IsDefault = $data['is_default'];

        $address->write();

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Address updated successfully'
        ]);
    }
    public function deleteAddress(HTTPRequest $request)
    {
        if (!$request->isDELETE()) {
            return $this->jsonResponse(['error' => 'Only DELETE method allowed'], 405);
        }

        $member = $this->requireAuth();
        if ($member instanceof HTTPResponse)
            return $member;

        $addressID = $request->param('ID');

        $address = ShippingAddress::get()->filter([
            'ID' => $addressID,
            'MemberID' => $member->ID
        ])->first();

        if (!$address) {
            return $this->jsonResponse(['error' => 'Address not found'], 404);
        }

        $address->delete();

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Address deleted successfully'
        ]);
    }

    // ========== SHIPPING (RajaOngkir Integration) ==========
    public function provinces(HTTPRequest $request)
    {
        if (!$request->isGET()) {
            return $this->jsonResponse(['error' => 'Only GET method allowed'], 405);
        }

        $rajaOngkir = new RajaOngkirService();
        $provinces = $rajaOngkir->getProvinces();

        return $this->jsonResponse([
            'success' => true,
            'data' => $provinces
        ]);
    }
    public function cities(HTTPRequest $request)
    {
        if (!$request->isGET()) {
            return $this->jsonResponse(['error' => 'Only GET method allowed'], 405);
        }

        $provinceID = $request->param('ID');

        if (!$provinceID) {
            return $this->jsonResponse(['error' => 'province_id is required'], 400);
        }

        $rajaOngkir = new RajaOngkirService();
        $cities = $rajaOngkir->getCities($provinceID);

        return $this->jsonResponse([
            'success' => true,
            'data' => $cities
        ]);
    }
    public function districts(HTTPRequest $request)
    {
        if (!$request->isGET()) {
            return $this->jsonResponse(['error' => 'Only GET method allowed'], 405);
        }

        $cityID = $request->param('ID');

        if (!$cityID) {
            return $this->jsonResponse(['error' => 'city_id is required'], 400);
        }

        $rajaOngkir = new RajaOngkirService();
        $districts = $rajaOngkir->getDistricts($cityID);

        return $this->jsonResponse([
            'success' => true,
            'data' => $districts
        ]);
    }
    public function checkOngkir(HTTPRequest $request)
    {
        if (!$request->isPOST()) {
            return $this->jsonResponse(['error' => 'Only POST method allowed'], 405);
        }

        $data = json_decode($request->getBody(), true);

        if (!isset($data['destination']) || !isset($data['weight']) || !isset($data['courier'])) {
            return $this->jsonResponse(['error' => 'destination, weight, and courier are required'], 400);
        }

        $siteConfig = SiteConfig::current_site_config();
        $origin = $siteConfig->CompanyDistricID;

        $rajaOngkir = new RajaOngkirService();
        $ongkir = $rajaOngkir->checkOngkir(
            $origin,
            $data['destination'],
            $data['weight'],
            $data['courier']
        );

        return $this->jsonResponse([
            'success' => true,
            'data' => $ongkir
        ]);
    }

    // ========== ORDER MANAGEMENT ==========
    public function orders(HTTPRequest $request)
    {
        if (!$request->isGET()) {
            return $this->jsonResponse(['error' => 'Only GET method allowed'], 405);
        }

        $member = $this->requireAuth();
        if ($member instanceof HTTPResponse)
            return $member;

        $status = $request->getVar('status');
        $page = $request->getVar('page') ?? 1;
        $limit = $request->getVar('limit') ?? 10;

        $filter = ['MemberID' => $member->ID];
        if ($status) {
            $filter['Status'] = $status;
        }

        $orders = Order::get()->filter($filter)->sort('CreateAt', 'DESC');

        $paginated = new PaginatedList($orders, $request);
        $paginated->setPageLength($limit);
        $paginated->setCurrentPage($page);

        $items = [];
        foreach ($paginated as $order) {
            $items[] = [
                'id' => $order->ID,
                'order_code' => $order->OrderCode,
                'status' => $order->Status,
                'payment_status' => $order->PaymentStatus,
                'total_price' => (float) $order->TotalPrice,
                'shipping_cost' => (float) $order->ShippingCost,
                'payment_fee' => (float) $order->PaymentFee,
                'grand_total' => (float) $order->getGrandTotal(),
                'payment_method' => $order->PaymentMethod,
                'shipping_courier' => $order->ShippingCourier,
                'tracking_number' => $order->TrackingNumber,
                'created_at' => $order->CreateAt,
                'expires_at' => $order->ExpiresAt,
                'is_expired' => $order->isExpired(),
                'can_be_paid' => $order->canBePaid(),
                'can_be_cancelled' => $order->canBeCancelled(),
            ];
        }

        return $this->jsonResponse([
            'success' => true,
            'meta' => [
                'total_items' => $paginated->getTotalItems(),
                'total_pages' => $paginated->TotalPages(),
                'current_page' => (int) $page,
                'limit' => (int) $limit,
            ],
            'data' => $items
        ]);
    }
    public function orderDetail(HTTPRequest $request)
    {
        if (!$request->isGET()) {
            return $this->jsonResponse(['error' => 'Only GET method allowed'], 405);
        }

        $member = $this->requireAuth();
        if ($member instanceof HTTPResponse)
            return $member;

        $orderID = $request->param('ID');

        $order = Order::get()->filter([
            'ID' => $orderID,
            'MemberID' => $member->ID
        ])->first();

        if (!$order) {
            return $this->jsonResponse(['error' => 'Order not found'], 404);
        }

        $orderItems = [];
        foreach ($order->OrderItem() as $item) {
            $product = $item->Product();
            $orderItems[] = [
                'id' => $item->ID,
                'product_id' => $product->ID,
                'product_name' => $product->Name,
                'quantity' => $item->Quantity,
                'price' => (float) $item->Price,
                'subtotal' => (float) $item->Subtotal,
                'original_subtotal' => (float) $item->getOriginalSubtotal(),
                'product_discount' => (float) $item->getProductDiscountTotal(),
                'flashsale_discount' => (float) $item->getFlashSaleDiscountTotal(),
                'image_url' => $product->Image()->exists() ? $product->Image()->getAbsoluteURL() : null,
                'can_be_reviewed' => $item->canBeReviewed(),
                'has_review' => $item->hasReview(),
            ];
        }

        $shippingAddress = $order->ShippingAddress();

        return $this->jsonResponse([
            'success' => true,
            'data' => [
                'id' => $order->ID,
                'order_code' => $order->OrderCode,
                'status' => $order->Status,
                'payment_status' => $order->PaymentStatus,
                'total_price' => (float) $order->TotalPrice,
                'original_total_price' => (float) $order->getOriginalTotalPrice(),
                'total_product_discount' => (float) $order->getTotalProductDiscount(),
                'total_flashsale_discount' => (float) $order->getTotalFlashSaleDiscount(),
                'shipping_cost' => (float) $order->ShippingCost,
                'payment_fee' => (float) $order->PaymentFee,
                'grand_total' => (float) $order->getGrandTotal(),
                'payment_method' => $order->PaymentMethod,
                'shipping_courier' => $order->ShippingCourier,
                'tracking_number' => $order->TrackingNumber,
                'created_at' => $order->CreateAt,
                'updated_at' => $order->UpdateAt,
                'expires_at' => $order->ExpiresAt,
                'is_expired' => $order->isExpired(),
                'can_be_paid' => $order->canBePaid(),
                'can_be_cancelled' => $order->canBeCancelled(),
                'can_be_reviewed' => $order->canBeReviewed(),
                'items' => $orderItems,
                'shipping_address' => $shippingAddress ? [
                    'receiver_name' => $shippingAddress->ReceiverName,
                    'phone_number' => $shippingAddress->PhoneNumber,
                    'address' => $shippingAddress->Address,
                    'city_name' => $shippingAddress->CityName,
                    'district_name' => $shippingAddress->DistrictName,
                    'province_name' => $shippingAddress->ProvinceName,
                    'postal_code' => $shippingAddress->PostalCode,
                ] : null
            ]
        ]);
    }
    public function createOrder(HTTPRequest $request)
    {
        if (!$request->isPOST()) {
            return $this->jsonResponse(['error' => 'Only POST method allowed'], 405);
        }

        $member = $this->requireAuth();
        if ($member instanceof HTTPResponse)
            return $member;

        $data = json_decode($request->getBody(), true);

        if (
            !isset($data['address_id']) || !isset($data['payment_method']) ||
            !isset($data['shipping_cost']) || !isset($data['courier_service'])
        ) {
            return $this->jsonResponse([
                'error' => 'address_id, payment_method, shipping_cost, and courier_service are required'
            ], 400);
        }

        // Get cart items
        $cartItems = CartItem::get()->filter('MemberID', $member->ID);

        if (!$cartItems || $cartItems->count() == 0) {
            return $this->jsonResponse(['error' => 'Cart is empty'], 400);
        }

        // Check stock availability
        foreach ($cartItems as $cartItem) {
            $product = $cartItem->Product();
            if (!$product || $product->Stok < $cartItem->Quantity) {
                return $this->jsonResponse([
                    'error' => 'Insufficient stock for ' . ($product ? $product->Name : 'product')
                ], 400);
            }
        }

        // Get shipping address
        $shippingAddress = ShippingAddress::get()->filter([
            'ID' => $data['address_id'],
            'MemberID' => $member->ID
        ])->first();

        if (!$shippingAddress) {
            return $this->jsonResponse(['error' => 'Shipping address not found'], 404);
        }

        // Get payment fee
        $duitku = new DuitkuService();
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += $item->getSubtotal();
        }

        $paymentMethods = $duitku->getPaymentMethods($subtotal);
        $paymentFee = 0;
        foreach ($paymentMethods as $method) {
            if ($method['paymentMethod'] == $data['payment_method']) {
                $paymentFee = (float) $method['totalFee'];
                break;
            }
        }

        // Create order
        $order = Order::create();
        $order->MemberID = $member->ID;
        $order->OrderCode = 'ORD-' . date('Y') . '-' . str_pad(Order::get()->count() + 1, 6, '0', STR_PAD_LEFT);
        $order->Status = 'pending';
        $order->TotalPrice = $subtotal;
        $order->ShippingCost = (float) $data['shipping_cost'];
        $order->PaymentFee = $paymentFee;
        $order->PaymentMethod = $data['payment_method'];
        $order->ShippingCourier = $data['courier_service'];
        $order->PaymentStatus = 'unpaid';
        $order->ShippingAddressID = $shippingAddress->ID;
        $order->write();

        // Create order items
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

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Order created successfully',
            'data' => [
                'order_id' => $order->ID,
                'order_code' => $order->OrderCode,
            ]
        ], 201);
    }
    public function cancelOrder(HTTPRequest $request)
    {
        if (!$request->isPOST()) {
            return $this->jsonResponse(['error' => 'Only POST method allowed'], 405);
        }

        $member = $this->requireAuth();
        if ($member instanceof HTTPResponse)
            return $member;

        $orderID = $request->param('ID');

        $order = Order::get()->filter([
            'ID' => $orderID,
            'MemberID' => $member->ID
        ])->first();

        if (!$order) {
            return $this->jsonResponse(['error' => 'Order not found'], 404);
        }

        if (!$order->canBeCancelled()) {
            return $this->jsonResponse(['error' => 'Order cannot be cancelled'], 400);
        }

        $order->cancelOrder();

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Order cancelled successfully'
        ]);
    }
    public function markAsCompleted(HTTPRequest $request)
    {
        if (!$request->isPOST()) {
            return $this->jsonResponse(['error' => 'Only POST method allowed'], 405);
        }

        $member = $this->requireAuth();
        if ($member instanceof HTTPResponse)
            return $member;

        $orderID = $request->param('ID');

        $order = Order::get()->filter([
            'ID' => $orderID,
            'MemberID' => $member->ID
        ])->first();

        if (!$order) {
            return $this->jsonResponse(['error' => 'Order not found'], 404);
        }

        if ($order->markAsCompleted()) {
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Order marked as completed successfully'
            ]);
        }

        return $this->jsonResponse([
            'error' => 'Order cannot be marked as completed'
        ], 400);
    }

    // ========== PAYMENT MANAGEMENT ==========
    public function paymentMethods(HTTPRequest $request)
    {
        if (!$request->isGET()) {
            return $this->jsonResponse(['error' => 'Only GET method allowed'], 405);
        }

        $amount = $request->getVar('amount') ?? 10000;

        $duitku = new DuitkuService();
        $methods = $duitku->getPaymentMethods($amount);

        $formatted = [];
        foreach ($methods as $method) {
            $formatted[] = [
                'payment_method' => $method['paymentMethod'],
                'payment_name' => $method['paymentName'],
                'payment_image' => $method['paymentImage'] ?? null,
                'total_fee' => (float) $method['totalFee'],
                'formatted_fee' => 'Rp ' . number_format($method['totalFee'], 0, '.', '.')
            ];
        }

        return $this->jsonResponse([
            'success' => true,
            'data' => $formatted
        ]);
    }
    public function initiatePayment(HTTPRequest $request)
    {
        if (!$request->isPOST()) {
            return $this->jsonResponse(['error' => 'Only POST method allowed'], 405);
        }

        $member = $this->requireAuth();
        if ($member instanceof HTTPResponse)
            return $member;

        $orderID = $request->param('ID');

        $order = Order::get()->filter([
            'ID' => $orderID,
            'MemberID' => $member->ID
        ])->first();

        if (!$order) {
            return $this->jsonResponse(['error' => 'Order not found'], 404);
        }

        if ($order->isExpired()) {
            $order->cancelOrder();
            return $this->jsonResponse(['error' => 'Order has expired'], 400);
        }

        if (!$order->canBePaid()) {
            return $this->jsonResponse(['error' => 'Order cannot be paid'], 400);
        }

        try {
            $duitku = new DuitkuService();
            $response = $duitku->createTransaction($order);

            if ($response && $response['success']) {
                $transaction = PaymentTransaction::create();
                $transaction->OrderID = $order->ID;
                $transaction->PaymentGateway = 'duitku';
                $transaction->TransactionID = $response['merchantOrderId'] ?? $order->OrderCode;
                $transaction->Amount = $order->getGrandTotal();
                $transaction->Status = 'pending';
                $transaction->write();

                $order->Status = 'pending_payment';
                $order->write();

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Payment initiated successfully',
                    'data' => [
                        'payment_url' => $response['paymentUrl'] ?? null,
                        'va_number' => $response['vaNumber'] ?? null,
                        'qr_string' => $response['qrString'] ?? null,
                        'transaction_id' => $transaction->ID,
                    ]
                ]);
            }

            return $this->jsonResponse([
                'error' => $response['error'] ?? 'Failed to initiate payment'
            ], 400);

        } catch (Exception $e) {
            error_log('API::initiatePayment - Exception: ' . $e->getMessage());
            return $this->jsonResponse(['error' => 'Payment initiation failed'], 500);
        }
    }
    public function paymentCallback(HTTPRequest $request)
    {
        if (!$request->isPOST() && !$request->isGET()) {
            return $this->jsonResponse(['error' => 'Invalid method'], 405);
        }

        try {
            if ($request->isPOST()) {
                $rawBody = $request->getBody();
                $data = json_decode($rawBody, true);
                if (!$data) {
                    $data = $request->postVars();
                }
            } else {
                $data = $request->getVars();
            }

            if (!$data || empty($data)) {
                error_log('API::paymentCallback - No data received');
                return new HTTPResponse('No data received', 400);
            }

            $duitku = new DuitkuService();

            if (!$duitku->verifyCallback($data)) {
                error_log('API::paymentCallback - Invalid signature');
                return new HTTPResponse('Invalid signature', 400);
            }

            $merchantOrderId = $data['merchantOrderId'] ?? '';
            $resultCode = $data['resultCode'] ?? '';

            $transaction = PaymentTransaction::get()->filter('TransactionID', $merchantOrderId)->first();

            if (!$transaction) {
                error_log('API::paymentCallback - Transaction not found: ' . $merchantOrderId);
                return new HTTPResponse('Transaction not found', 404);
            }

            $order = $transaction->Order();
            if (!$order) {
                error_log('API::paymentCallback - Order not found for transaction: ' . $merchantOrderId);
                return new HTTPResponse('Order not found', 404);
            }

            if ($transaction->Status === 'success' || $transaction->Status === 'failed') {
                return new HTTPResponse('OK', 200);
            }

            $transaction->ResponseData = json_encode($data);

            if ($resultCode == '00') {
                $transaction->Status = 'success';
                $order->markAsPaid();
                InvoiceController::sendInvoiceAfterPayment($order);
            } else {
                $transaction->Status = 'failed';
                $order->Status = 'cancelled';
                $order->PaymentStatus = 'failed';
                $order->write();
            }

            $transaction->write();

            return new HTTPResponse('OK', 200);

        } catch (Exception $e) {
            error_log('API::paymentCallback - Exception: ' . $e->getMessage());
            return new HTTPResponse('Internal server error', 500);
        }
    }

    // ========== REVIEW MANAGEMENT ==========
    public function submitReview(HTTPRequest $request)
    {
        if (!$request->isPOST()) {
            return $this->jsonResponse(['error' => 'Only POST method allowed'], 405);
        }

        $member = $this->requireAuth();
        if ($member instanceof HTTPResponse)
            return $member;

        $data = json_decode($request->getBody(), true);

        // Validasi input
        if (
            !isset($data['order_id']) || !isset($data['order_item_id']) ||
            !isset($data['rating']) || !isset($data['message'])
        ) {
            return $this->jsonResponse([
                'error' => 'order_id, order_item_id, rating, and message are required'
            ], 400);
        }

        $orderID = (int) $data['order_id'];
        $orderItemID = (int) $data['order_item_id'];
        $rating = (int) $data['rating'];
        $message = trim($data['message']);
        $showName = $data['show_name'] ?? true;

        // Validasi rating
        if ($rating < 1 || $rating > 5) {
            return $this->jsonResponse(['error' => 'Rating must be between 1 and 5'], 400);
        }

        // Validasi message
        if (strlen($message) < 5) {
            return $this->jsonResponse(['error' => 'Review message must be at least 5 characters'], 400);
        }

        // Cek order ownership dan status
        $order = Order::get()->filter([
            'ID' => $orderID,
            'MemberID' => $member->ID
        ])->first();

        if (!$order) {
            return $this->jsonResponse(['error' => 'Order not found'], 404);
        }

        if ($order->Status != 'completed') {
            return $this->jsonResponse(['error' => 'Order must be completed before reviewing'], 400);
        }

        // Cek order item
        $orderItem = OrderItem::get()->filter([
            'ID' => $orderItemID,
            'OrderID' => $orderID
        ])->first();

        if (!$orderItem) {
            return $this->jsonResponse(['error' => 'Order item not found'], 404);
        }

        // Cek apakah sudah direview
        if ($orderItem->hasReview()) {
            return $this->jsonResponse(['error' => 'This item has already been reviewed'], 400);
        }

        // Cek apakah bisa direview
        if (!$orderItem->canBeReviewed()) {
            return $this->jsonResponse(['error' => 'This item cannot be reviewed'], 400);
        }

        // Buat review
        $review = Review::create();
        $review->ProductID = $orderItem->ProductID;
        $review->MemberID = $member->ID;
        $review->OrderItemID = $orderItem->ID;
        $review->Rating = $rating;
        $review->Message = $message;
        $review->ShowName = $showName;
        $review->write();

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Review submitted successfully',
            'data' => [
                'review_id' => $review->ID,
                'product_id' => $review->ProductID,
                'rating' => $review->Rating
            ]
        ], 201);
    }
    public function productReviews(HTTPRequest $request)
    {
        if (!$request->isGET()) {
            return $this->jsonResponse(['error' => 'Only GET method allowed'], 405);
        }

        $productID = $request->param('ID');
        $page = $request->getVar('page') ?? 1;
        $limit = $request->getVar('limit') ?? 10;

        $reviews = Review::get()->filter('ProductID', $productID)->sort('CreatedAt', 'DESC');

        $paginated = new PaginatedList($reviews, $request);
        $paginated->setPageLength($limit);
        $paginated->setCurrentPage($page);

        $items = [];
        foreach ($paginated as $review) {
            $member = $review->Member();
            $items[] = [
                'id' => $review->ID,
                'author_name' => $review->ShowName ? $member->FirstName : 'Anonymous',
                'rating' => $review->Rating,
                'message' => $review->Message,
                'created_at' => $review->CreatedAt,
                'formatted_date' => $review->getFormattedDate(),
            ];
        }

        return $this->jsonResponse([
            'success' => true,
            'meta' => [
                'total_items' => $paginated->getTotalItems(),
                'total_pages' => $paginated->TotalPages(),
                'current_page' => (int) $page,
                'limit' => (int) $limit,
            ],
            'data' => $items
        ]);
    }

    // ========== MEMBERSHIP ==========
    public function membershipInfo(HTTPRequest $request)
    {
        if (!$request->isGET()) {
            return $this->jsonResponse(['error' => 'Only GET method allowed'], 405);
        }

        $member = $this->requireAuth();
        if ($member instanceof HTTPResponse)
            return $member;

        $tierID = MembershipService::getMembershipTier($member->ID);
        $tierName = MembershipService::getMembershipTierName($tierID);
        $totalTransactions = MembershipService::getMemberTotalTransactions($member->ID);
        $tierObject = MembershipService::getMembershipTierObject($tierID);

        $data = [
            'tier_id' => $tierID,
            'tier_name' => $tierName,
            'total_transactions' => $totalTransactions,
            'formatted_total' => 'Rp ' . number_format($totalTransactions, 0, '.', '.'),
        ];

        if ($tierObject) {
            $data['tier_image'] = $tierObject->Image()->exists() ? $tierObject->Image()->getAbsoluteURL() : null;
            $data['tier_limit'] = $tierObject->Limit;
        }

        return $this->jsonResponse([
            'success' => true,
            'data' => $data
        ]);
    }
    public function membershipProgress(HTTPRequest $request)
    {
        if (!$request->isGET()) {
            return $this->jsonResponse(['error' => 'Only GET method allowed'], 405);
        }

        $member = $this->requireAuth();
        if ($member instanceof HTTPResponse)
            return $member;

        $progress = MembershipService::getProgressToNextTier($member->ID);

        return $this->jsonResponse([
            'success' => true,
            'data' => $progress
        ]);
    }

    // ========== INVOICE ==========
    public function downloadInvoice(HTTPRequest $request)
    {
        if (!$request->isGET()) {
            return $this->jsonResponse(['error' => 'Only GET method allowed'], 405);
        }

        $member = $this->requireAuth();
        if ($member instanceof HTTPResponse)
            return $member;

        $orderID = $request->param('ID');
        if (!$orderID) {
            return $this->jsonResponse(['error' => 'Order ID required'], 400);
        }

        $order = Order::get()->filter([
            'ID' => $orderID,
            'MemberID' => $member->ID
        ])->first();

        if (!$order) {
            return $this->jsonResponse(['error' => 'Order not found'], 404);
        }

        $invoiceController = new InvoiceController();
        $pdfBinary = $invoiceController->generatePDFContent($order);

        $pdfBase64 = base64_encode($pdfBinary);

        return $this->jsonResponse([
            'success' => true,
            'data' => [
                'order_code' => $order->OrderCode,
                'file_name' => 'Invoice-' . $order->OrderCode . '.pdf',
                'pdf_base64' => $pdfBase64,
            ]
        ]);
    }
    public function sendInvoice(HTTPRequest $request)
    {
        if (!$request->isPOST()) {
            return $this->jsonResponse(['error' => 'Only POST method allowed'], 405);
        }

        $member = $this->requireAuth();
        if ($member instanceof HTTPResponse)
            return $member;

        $orderID = $request->param('ID');
        if (!$orderID) {
            return $this->jsonResponse(['error' => 'Order ID required'], 400);
        }

        $order = Order::get()->filter([
            'ID' => $orderID,
            'MemberID' => $member->ID
        ])->first();

        if (!$order) {
            return $this->jsonResponse(['error' => 'Order not found'], 404);
        }

        $invoiceController = new InvoiceController();
        $isSent = $invoiceController->sendInvoiceToMember($order);

        if ($isSent) {
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Invoice berhasil dikirim ke email Anda.'
            ]);
        }

        return $this->jsonResponse([
            'success' => false,
            'message' => 'Gagal mengirim invoice ke email.'
        ], 500);
    }

    // ========== HELPER METHODS ==========
    private function requireAuth()
    {
        $member = Security::getCurrentUser();

        if (!$member) {
            return $this->jsonResponse(['error' => 'Authentication required'], 401);
        }

        return $member;
    }

    private function jsonResponse($data, $status = 200)
    {
        $response = new HTTPResponse(json_encode($data), $status);
        $response->addHeader('Content-Type', 'application/json');
        $response->addHeader('Access-Control-Allow-Origin', '*');
        $response->addHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->addHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $response->addHeader('Access-Control-Allow-Credentials', 'true');

        return $response;
    }
}