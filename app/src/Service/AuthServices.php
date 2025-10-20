<?php

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Environment;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\ValidationException;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\IdentityStore;
use SilverStripe\Security\Member;
use SilverStripe\Security\MemberAuthenticator\LoginHandler;
use SilverStripe\Security\MemberAuthenticator\MemberAuthenticator;
use SilverStripe\SiteConfig\SiteConfig;

class AuthServices
{
    public function getGoogleAccessToken($code, $config)
    {
        $postData = [
            'code' => $code,
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'redirect_uri' => $config['redirect_uri'],
            'grant_type' => 'authorization_code'
        ];

        $ch = curl_init($config['token_url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local development

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception('Failed to exchange code for token. HTTP Code: ' . $httpCode);
        }

        return json_decode($response, true);
    }

    public function getGoogleUserInfo($accessToken, $config)
    {
        $ch = curl_init($config['userinfo_url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local development

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception('Failed to get user info. HTTP Code: ' . $httpCode);
        }

        return json_decode($response, true);
    }

    public function processLogin(HTTPRequest $request)
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

    public function processRegister(HTTPRequest $request)
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

    public function processForgotPassword(HTTPRequest $request, $emailParam = null)
    {
        $email = $emailParam ?: $request->postVar('forgot_email');
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

        $resetToken = sha1(uniqid() . time());
        $member->ResetPasswordToken = $resetToken;
        $member->ResetPasswordExpiry = date('Y-m-d H:i:s', time() + 3600);
        $member->write();

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

    public function processResetPassword(HTTPRequest $request, Member $member)
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
