<?php

use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Environment;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\Debug;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\IdentityStore;
use SilverStripe\Security\Member;
use SilverStripe\Security\MemberAuthenticator\LoginHandler;
use SilverStripe\Security\MemberAuthenticator\MemberAuthenticator;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\View\ArrayData;

class AuthPageController extends PageController
{
    private static $allowed_actions = [
        'login',
        'register',
        'init',
    ];

    private static $url_handlers = [
        'login' => 'login',
        'register' => 'register'
    ];
    
    public function login(HTTPRequest $request)
    {
        $validationResult = null;

        if ($request->isPOST()) {
            $validationResult = $this->processLogin($request);

            if ($validationResult->isValid()) {
                $this->getRequest()->getSession()->set('FlashMessage', [
                    'Message' => 'Login successful!',
                    'Type' => 'primary'
                ]);
                return $this->redirect(Director::absoluteBaseURL());
            }
        }

        if ($validationResult && !$validationResult->isValid()) {
            $this->flashMessages = ArrayData::create([
                'Message' => 'Login failed',
                'Type' => 'danger'
            ]);
        }

        $data = array_merge($this->getCommonData(), [
            'Title' => 'Login',
            'ValidationResult' => $validationResult
        ]);

        return $this->customise($data)->renderWith(['LoginPage', 'Page']);
    }


    public function register(HTTPRequest $request)
    {
        $validationResult = null;

        if ($request->isPOST()) {
            $validationResult = $this->processRegister($request);

            if ($validationResult->isValid()) {
                $this->getRequest()->getSession()->set('FlashMessage', [
                    'Message' => 'Registration successful! Please check your email to verify your account.',
                    'Type' => 'primary'
                ]);
                return $this->redirect(Director::absoluteBaseURL());
            }
        }

        if ($validationResult && !$validationResult->isValid()) {
            $this->flashMessages = ArrayData::create([
                'Message' => 'Registration failed',
                'Type' => 'danger'
            ]);
        }

        $data = array_merge($this->getCommonData(), [
            'Title' => 'Register',
            'ValidationResult' => $validationResult
        ]);

        return $this->customise($data)->renderWith(['RegisterPage', 'Page']);
    }

    private function processLogin(HTTPRequest $request)
    {
        $email = $request->postVar('login_email');
        $password = $request->postVar('login_password');
        $rememberMe = $request->postVar('login_remember');

        $data = [
            'Email' => $email,
            'Password' => $password,
            'Remember' => $rememberMe
        ];

        $result = ValidationResult::create();
        $authenticator = new MemberAuthenticator();
        $loginHandler = new LoginHandler('auth', $authenticator);

        if ($member = $loginHandler->checkLogin($data, $request, $result)) {
            // Tambahan: cek verifikasi
            if (!$member->IsVerified) {
                $result->addError('Akun Anda belum diverifikasi. Silakan cek email.');
                return $result;
            }

            if (!$member->inGroup('site-users')) {
                Injector::inst()->get(IdentityStore::class)->logOut($request);
                $result->addError('Invalid credentials.');
            } else {
                $loginHandler->performLogin($member, $data, $request);
            }
        }

        return $result;
    }

    private function processRegister(HTTPRequest $request)
    {
        $baseURL = Environment::getEnv('SS_BASE_URL');
        $ngrokUrl = Environment::getEnv('NGROK_URL');

        $firstName = $request->postVar('register_first_name');
        $lastName = $request->postVar('register_last_name');
        $userEmail = $request->postVar('register_email');
        $password1 = $request->postVar('register_password_1');
        $password2 = $request->postVar('register_password_2');

        $SiteConfig = SiteConfig::current_site_config();
        $emails = explode(',', $SiteConfig->Email);
        $CompanyEmail = trim($emails[0]);

        $result = ValidationResult::create();

        if ($password1 !== $password2) {
            $result->addError('Passwords do not match.');
            return $result;
        }

        if (Member::get()->filter('Email', $userEmail)->exists()) {
            $result->addError('Email already exists.');
            return $result;
        }

        // Buat member baru dengan token verifikasi
        $member = Member::create();
        $member->FirstName = $firstName;
        $member->Surname = $lastName;
        $member->Email = $userEmail;
        $member->VerificationToken = sha1(uniqid());
        $member->IsVerified = false;
        $member->write();
        $member->addToGroupByCode('site-users');
        $member->changePassword($password1);

        // Kirim email verifikasi
        $verifyLink = rtrim($ngrokUrl, '/') . '/verify?token=' . $member->VerificationToken;

        $emailObj = \SilverStripe\Control\Email\Email::create()
            ->setTo($userEmail)
            ->setFrom($CompanyEmail)
            ->setSubject('Verifikasi Email Anda')
            ->setHTMLTemplate('CustomEmail')
            ->setData([
                'Name' => $firstName,
                'SenderEmail' => $userEmail,
                'MessageContent' => "
                    Terima kasih telah mendaftar. Silakan salin link di bawah untuk memverifikasi akun Anda.
                    {$verifyLink}",
                'SiteName' => $SiteConfig->Title,
            ]);

        $emailObj->send();

        $result->addMessage('Registrasi berhasil! Silakan cek email untuk verifikasi akun.');

        return $result;
    }

}