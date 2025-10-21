<?php

use SilverStripe\Control\Controller;
use SilverStripe\Control\Email\Email;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Environment;
use SilverStripe\Core\Injector\Injector;
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
        'eventshop',
        'flashsale',
        'popupad',
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
        'eventshop' => 'eventshop',
        'flashsale' => 'flashsale',
        'popupad' => 'popupad',
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
            'message' => 'SilverStripe Self-Order API',
            'status' => 'operational',
            'endpoints' => [
                'authentication' => [
                    'POST /api/google-auth' => 'Firebase Google Auth',
                    'POST /api/login' => 'Login user',
                    'POST /api/register' => 'Register new user',
                    'POST /api/logout' => 'Logout current user',
                ],
                'member' => [
                    'GET /api/member' => 'Get current member profile',
                    'PUT /api/member' => 'Update member profile',
                    'PUT /api/member/password' => 'Update member password',
                ],
                'siteconfig' => [
                    'GET /api/siteconfig' => 'Get SiteConfig Data',
                ],
                'carousel' => [
                    'GET /api/carousel' => 'Get Carousel Data',
                ],
                'eventshop' => [
                    'GET /api/eventshop' => 'Get EventShop Data',
                ],
                'flashsale' => [
                    'GET /api/flashsale' => 'Get FlashSale Data',
                ],
                'popupad' => [
                    'GET /api/popupad' => 'Get PopUpAd Data'
                ],
                'forgotpassword' => [
                    'POST /api/forgotpassword' => 'Forgot password'
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

    // * EVENTSHOP *
    public function eventshop(HTTPRequest $request)
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

    // * FLASHSALE *
    public function flashsale(HTTPRequest $request)
    {
        if (!$request->isGET()) {
            return $this->jsonResponse(['error' => 'Only Get method allowed'], 405);
        }

        $flashSaleList = FlashSale::get();
        if ($flashSaleList->count() == 0) {
            return $this->jsonResponse(['error' => 'FlashSale not found'], 404);
        }
        $data = [];
        foreach ($flashSaleList as $flashSale) {
            $data[] = [
                'name' => $flashSale->Name,
                'description' => $flashSale->Description,
                'start_time' => $flashSale->Start_time,
                'end_time' => $flashSale->End_time,
                'discount_flash_sale' => $flashSale->DiscountFlashSale,
                'status' => $flashSale->Status,
            ];
        }

        return $this->jsonResponse([
            'success' => true,
            'data' => $data,
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

    // ========== Feature/Methods Etc ==========
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