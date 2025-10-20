<?php

use Embed\HttpApiTrait;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Environment;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\Debug;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\ValidationException;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\IdentityStore;
use SilverStripe\Security\Member;
use SilverStripe\Security\MemberAuthenticator\LoginHandler;
use SilverStripe\Security\MemberAuthenticator\MemberAuthenticator;
use SilverStripe\Security\Security;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\View\ArrayData;

class AuthPageController extends PageController
{
    private static $allowed_actions = [
        'login',
        'logout',
        'register',
        'forgotPassword',
        'resetPassword',
        'init',
        'googleLogin',
        'googleCallback'
    ];

    private static $url_handlers = [
        'login' => 'login',
        'logout' => 'logout',
        'register' => 'register',
        'forgot-password' => 'forgotPassword',
        'reset-password' => 'resetPassword',
        'google-login' => 'googleLogin',
        'google-callback' => 'googleCallback'
    ];

    private $AuthService;

    protected function init()
    {
        parent::init();
        $this->AuthService = new AuthServices();
    }

    // Google Auth
    private function getGoogleConfig()
    {
        return [
            'client_id' => Environment::getEnv('GOOGLE_CLIENT_ID'),
            'client_secret' => Environment::getEnv('GOOGLE_CLIENT_SECRET'),
            'redirect_uri' => Director::absoluteBaseURL() . '/auth/google-callback',
            'auth_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
            'token_url' => 'https://oauth2.googleapis.com/token',
            'userinfo_url' => 'https://www.googleapis.com/oauth2/v2/userinfo'
        ];
    }

    public function googleLogin(HTTPRequest $request)
    {
        $config = $this->getGoogleConfig();

        $params = [
            'client_id' => $config['client_id'],
            'redirect_uri' => $config['redirect_uri'],
            'response_type' => 'code',
            'scope' => 'email profile',
            'access_type' => 'online'
        ];

        $authUrl = $config['auth_url'] . '?' . http_build_query($params);

        return $this->redirect($authUrl);
    }

    public function googleCallback(HTTPRequest $request)
    {
        $code = $request->getVar('code');
        if (!$code) {
            $request->getSession()->set('FlashMessage', [
                'Message' => 'Login dengan Google dibatalkan.',
                'Type' => 'warning'
            ]);
            return $this->redirect(Director::absoluteBaseURL() . '/auth/login');
        }

        try {
            $config = $this->getGoogleConfig();
            $tokenData = $this->AuthService->getGoogleAccessToken($code, $config);

            if (!isset($tokenData['access_token'])) {
                throw new \Exception('Failed to get access token');
            }

            $userInfo = $this->AuthService->getGoogleUserInfo($tokenData['access_token'], $config);

            if (!isset($userInfo['email'])) {
                throw new \Exception('Failed to get user email');
            }

            $email = $userInfo['email'];
            $firstName = $userInfo['given_name'] ?? '';
            $lastName = $userInfo['family_name'] ?? '';
            $googleId = $userInfo['id'];

            $member = Member::get()->filter('Email', $email)->first();

            if (!$member) {
                $member = Member::create();
                $member->FirstName = $firstName;
                $member->Surname = $lastName;
                $member->Email = $email;
                $member->GoogleID = $googleId;
                $member->IsVerified = true;
                $member->write();
                $member->addToGroupByCode('site-users');

                $member->changePassword(bin2hex(random_bytes(16)));

                $request->getSession()->set('FlashMessage', [
                    'Message' => 'Akun berhasil dibuat dengan Google. Selamat datang!',
                    'Type' => 'success'
                ]);
            } else {
                if (!$member->GoogleID) {
                    $member->GoogleID = $googleId;
                    $member->IsVerified = true;
                    $member->write();
                }

                $request->getSession()->set('FlashMessage', [
                    'Message' => 'Masuk berhasil! Selamat datang.',
                    'Type' => 'primary'
                ]);
            }

            Injector::inst()->get(IdentityStore::class)->logIn($member, false, $request);
            return $this->redirect(Director::absoluteBaseURL());

        } catch (\Exception $e) {
            $request->getSession()->set('FlashMessage', [
                'Message' => 'Terjadi kesalahan saat login dengan Google: ' . $e->getMessage(),
                'Type' => 'danger'
            ]);
            return $this->redirect(Director::absoluteBaseURL() . '/auth/login');
        }
    }

    // Manual Auth
    public function login(HTTPRequest $request)
    {
        $flash = $request->getSession()->get('FlashMessage');
        if ($flash) {
            $this->flashMessages = ArrayData::create($flash);
            $request->getSession()->clear('FlashMessage');
        }

        $validationResult = null;

        if ($request->isPOST()) {
            $validationResult = $this->AuthService->processLogin($request);

            if ($validationResult->isValid()) {
                $request->getSession()->set('FlashMessage', [
                    'Message' => 'Masuk berhasil! Selamat datang.',
                    'Type' => 'primary'
                ]);
                return $this->redirect(Director::absoluteBaseURL());
            }
        }

        if ($validationResult && !$validationResult->isValid()) {
            $this->flashMessages = ArrayData::create([
                'Message' => 'Masuk gagal. Periksa email dan password Anda.',
                'Type' => 'danger'
            ]);
        }

        $data = array_merge($this->getCommonData(), [
            'Title' => 'Login',
            'ValidationResult' => $validationResult,
            'FlashMessages' => $this->flashMessages ?? null
        ]);

        return $this->customise($data)->renderWith(['LoginPage', 'Page']);
    }

    public function logout(HTTPRequest $request)
    {
        Injector::inst()->get(IdentityStore::class)->logOut($request);
        $request->getSession()->set('FlashMessage', [
            'Message' => 'Anda berhasil logout.',
            'Type' => 'info'
        ]);

        return $this->redirect(Director::absoluteBaseURL() . '/auth/login');
    }

    public function register(HTTPRequest $request)
    {
        $validationResult = null;

        if ($request->isPOST()) {
            $validationResult = $this->AuthService->processRegister($request);

            if ($validationResult->isValid()) {
                $this->getRequest()->getSession()->set('FlashMessage', [
                    'Message' => 'Pendaftaran berhasil! Silakan cek email untuk verifikasi akun.',
                    'Type' => 'primary'
                ]);
                return $this->redirect(Director::absoluteBaseURL());
            }
        }

        if ($validationResult && !$validationResult->isValid()) {
            $this->flashMessages = ArrayData::create([
                'Message' => 'Pendaftaran gagal',
                'Type' => 'danger'
            ]);
        }

        $data = array_merge($this->getCommonData(), [
            'Title' => 'Register',
            'ValidationResult' => $validationResult
        ]);

        return $this->customise($data)->renderWith(['RegisterPage', 'Page']);
    }

    public function forgotPassword(HTTPRequest $request)
    {
        $validationResult = null;

        if ($request->isPOST()) {
            $validationResult = $this->AuthService->processForgotPassword($request);

            if ($validationResult->isValid()) {
                $this->flashMessages = ArrayData::create([
                    'Message' => 'Link reset password telah dikirim ke email Anda.',
                    'Type' => 'primary'
                ]);
            }
        }

        if ($validationResult && !$validationResult->isValid()) {
            $this->flashMessages = ArrayData::create([
                'Message' => 'Email tidak ditemukan atau terjadi kesalahan.',
                'Type' => 'danger'
            ]);
        }

        $data = array_merge($this->getCommonData(), [
            'Title' => 'Lupa Sandi',
            'ValidationResult' => $validationResult
        ]);

        return $this->customise($data)->renderWith(['ForgotPasswordPage', 'Page']);
    }

    public function resetPassword(HTTPRequest $request)
    {
        $token = $request->getVar('token');
        $validationResult = null;

        if (!$token) {
            return $this->redirect(Director::absoluteBaseURL() . '/auth/forgot-password');
        }

        $member = Member::get()->filter('ResetPasswordToken', $token)->first();
        if (!$member || !$member->ResetPasswordExpiry || strtotime($member->ResetPasswordExpiry) < time()) {
            $this->flashMessages = ArrayData::create([
                'Message' => 'Link reset password tidak valid atau sudah kadaluarsa.',
                'Type' => 'danger'
            ]);
            return $this->redirect(Director::absoluteBaseURL() . '/auth/forgot-password');
        }

        if ($request->isPOST()) {
            $validationResult = $this->AuthService->processResetPassword($request, $member);

            if ($validationResult->isValid()) {
                $this->getRequest()->getSession()->set('FlashMessage', [
                    'Message' => 'Password berhasil direset. Silakan login dengan password baru.',
                    'Type' => 'primary'
                ]);
                return $this->redirect(Director::absoluteBaseURL() . '/auth/login');
            }
        }

        if ($validationResult && !$validationResult->isValid()) {
            $this->flashMessages = ArrayData::create([
                'Message' => 'Gagal reset password. Password tidak valid atau sama dengan sebelumnya. Periksa kembali password baru Anda.',
                'Type' => 'danger'
            ]);
        }

        $data = array_merge($this->getCommonData(), [
            'Title' => 'Reset Sandi',
            'Token' => $token,
            'ValidationResult' => $validationResult
        ]);

        return $this->customise($data)->renderWith(['ResetPasswordPage', 'Page']);
    }
}