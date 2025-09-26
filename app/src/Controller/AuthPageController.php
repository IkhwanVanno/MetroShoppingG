<?php

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
    ];

    private static $url_handlers = [
        'login' => 'login',
        'logout' => 'logout',
        'register' => 'register',
        'forgot-password' => 'forgotPassword',
        'reset-password' => 'resetPassword'
    ];

    public function login(HTTPRequest $request)
    {
        $flash = $request->getSession()->get('FlashMessage');
        if ($flash) {
            $this->flashMessages = ArrayData::create($flash);
            $request->getSession()->clear('FlashMessage');
        }

        $validationResult = null;

        if ($request->isPOST()) {
            $validationResult = $this->processLogin($request);

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
            $validationResult = $this->processRegister($request);

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
            $validationResult = $this->processForgotPassword($request);

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
            $validationResult = $this->processResetPassword($request, $member);

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

        $member = Member::create();
        $member->FirstName = $firstName;
        $member->Surname = $lastName;
        $member->Email = $userEmail;
        $member->VerificationToken = sha1(uniqid());
        $member->IsVerified = false;
        $member->write();
        $member->addToGroupByCode('site-users');
        $member->changePassword($password1);

        $verifyLink = rtrim($baseURL) . '/verify?token=' . $member->VerificationToken;

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

    private function processForgotPassword(HTTPRequest $request)
    {
        $email = $request->postVar('forgot_email');
        $result = ValidationResult::create();

        if (!$email) {
            $result->addError('Email harus diisi.');
            return $result;
        }

        $member = Member::get()->filter('Email', $email)->first();

        if (!$member) {
            $result->addError('Email tidak ditemukan.');
            return $result;
        }

        // Generate reset token
        $resetToken = sha1(uniqid() . time());
        $member->ResetPasswordToken = $resetToken;
        $member->ResetPasswordExpiry = date('Y-m-d H:i:s', time() + 3600); // 1 jam
        $member->write();

        // Send reset email
        $baseURL = Environment::getEnv('SS_BASE_URL');
        $SiteConfig = SiteConfig::current_site_config();
        $emails = explode(',', $SiteConfig->Email);
        $CompanyEmail = trim($emails[0]);

        $resetLink = rtrim($baseURL) . '/auth/reset-password?token=' . $resetToken;

        $emailObj = \SilverStripe\Control\Email\Email::create()
            ->setTo($email)
            ->setFrom($CompanyEmail)
            ->setSubject('Reset Password Anda')
            ->setHTMLTemplate('CustomEmail')
            ->setData([
                'Name' => $member->FirstName,
                'SenderEmail' => $email,
                'MessageContent' => "
                    Kami menerima permintaan untuk reset password akun Anda.
                    Klik link berikut untuk reset password (berlaku 1 jam):
                    {$resetLink}
                    
                    Jika Anda tidak meminta reset password, abaikan email ini.",
                'SiteName' => $SiteConfig->Title,
            ]);

        $emailObj->send();

        $result->addMessage('Link reset password telah dikirim ke email Anda.');
        return $result;
    }

    private function processResetPassword(HTTPRequest $request, Member $member)
    {
        $password1 = $request->postVar('new_password_1');
        $password2 = $request->postVar('new_password_2');

        $result = ValidationResult::create();

        if (!$password1 || !$password2) {
            $result->addError('Password harus diisi.');
            return $result;
        }

        if ($password1 !== $password2) {
            $result->addError('Password tidak cocok.');
            return $result;
        }

        if (strlen($password1) < 6) {
            $result->addError('Password minimal 6 karakter.');
            return $result;
        }

        // Update password dan hapus token
        $member->changePassword($password1);
        $member->ResetPasswordToken = null;
        $member->ResetPasswordExpiry = null;
        try {
            $member->write();
            $result->addMessage('Password berhasil direset.');
        } catch (ValidationException $e) {
            $result->addError('Password tidak valid atau sama dengan sebelumnya. Periksa kembali password baru Anda.');
        }

        return $result;
    }
}