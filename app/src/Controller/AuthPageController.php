<?php

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\IdentityStore;
use SilverStripe\Security\Member;
use SilverStripe\Security\MemberAuthenticator\LoginHandler;
use SilverStripe\Security\MemberAuthenticator\MemberAuthenticator;

class AuthPageController extends PageController
{
    private static $allowed_actions = [
        'login',
        'register'
    ];

    private static $url_handlers = [
        'login' => 'login',
        'register' => 'register'
    ];

    public function login(HTTPRequest $request)
    {
        if ($request->isPOST()) {
            $result = $this->processLogin($request);

            if ($result->isValid()) {
                return $this->redirect('$BaseHref');
            }
        }

        $data = array_merge($this->getCommonData(), [
            'Title' => 'Login'
        ]);

        return $this->customise($data)->renderWith(['LoginPage', 'Page']);
    }

    public function register(HTTPRequest $request)
    {
        if ($request->isPOST()) {
            $result = $this->processRegister($request);

            if ($result->isValid()) {
                return $this->redirect('$BaseHref');
            }
        }

        $data = array_merge($this->getCommonData(), [
            'Title' => 'Register'
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
        $firstName = $request->postVar('register_first_name');
        $lastName = $request->postVar('register_last_name');
        $email = $request->postVar('register_email');
        $password1 = $request->postVar('register_password_1');
        $password2 = $request->postVar('register_password_2');

        $result = ValidationResult::create();

        // Basic validation
        if ($password1 !== $password2) {
            $result->addError('Passwords do not match.');
            return $result;
        }

        if (Member::get()->filter('Email', $email)->exists()) {
            $result->addError('Email already exists.');
            return $result;
        }

        // Create member
        $member = Member::create();
        $member->FirstName = $firstName;
        $member->Surname = $lastName;
        $member->Email = $email;
        $member->write();
        $member->addToGroupByCode('site-users');
        $member->changePassword($password1);

        // Auto login
        $data = ['Email' => $email, 'Password' => $password1, 'Remember' => 1];
        $authenticator = new MemberAuthenticator();

        if ($authenticatedMember = $authenticator->authenticate($data, $request, $result)) {
            $identityStore = Injector::inst()->get(IdentityStore::class);
            $identityStore->logIn($authenticatedMember, true, $request);
        }

        return $result;
    }
}