<?php

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Security\Member;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\MemberAuthenticator\MemberAuthenticator;

class ProfilePageController extends PageController
{
    private static $allowed_actions = [
        'updateProfile'
    ];

    private static $url_segment = "profile";

    public function index(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            return $this->redirect('auth/login');
        }

        if ($request->isPOST()) {
            $result = $this->processProfileUpdate($request);

            if ($result->isValid()) {
                $this->getRequest()->getSession()->set('ProfileUpdateSuccess', 'Profil berhasil diperbarui!');
            } else {
                $errorMessages = [];
                foreach ($result->getMessages() as $message) {
                    $errorMessages[] = $message['message'];
                }
                $this->getRequest()->getSession()->set('ProfileUpdateErrors', implode('<br>', $errorMessages));
            }

            return $this->redirectBack();
        }

        $user = $this->getCurrentUser();
        $member = Member::get()->byID($user->ID);

        $data = array_merge($this->getCommonData(), [
            "Member" => $member,
            "UpdateSuccess" => $this->getRequest()->getSession()->get('ProfileUpdateSuccess'),
            "UpdateErrors" => $this->getRequest()->getSession()->get('ProfileUpdateErrors'),
        ]);

        $this->getRequest()->getSession()->clear('ProfileUpdateSuccess');
        $this->getRequest()->getSession()->clear('ProfileUpdateErrors');

        return $this->customise($data)->renderWith(["ProfilePage", "Page"]);
    }

    private function processProfileUpdate(HTTPRequest $request)
    {
        $user = $this->getCurrentUser();
        $member = Member::get()->byID($user->ID);

        $result = ValidationResult::create();

        if (!$member) {
            $result->addError('User tidak ditemukan.');
            return $result;
        }

        $firstName = trim($request->postVar('first_name'));
        $lastName = trim($request->postVar('last_name'));
        $email = trim($request->postVar('email'));
        $currentPassword = $request->postVar('current_password');
        $newPassword = $request->postVar('new_password');
        $confirmPassword = $request->postVar('confirm_password');

        if ($email && $email !== $member->Email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $result->addError('Format email tidak valid.');
                return $result;
            }

            $existingMember = Member::get()->filter('Email', $email)->exclude('ID', $member->ID)->first();
            if ($existingMember) {
                $result->addError('Email sudah digunakan oleh user lain.');
                return $result;
            }
        }

        if ($newPassword || $confirmPassword || $currentPassword) {
            if (!$currentPassword) {
                $result->addError('Password saat ini diperlukan untuk mengubah password.');
                return $result;
            }

            if (!$newPassword) {
                $result->addError('Password baru tidak boleh kosong.');
                return $result;
            }

            if (!$confirmPassword) {
                $result->addError('Konfirmasi password tidak boleh kosong.');
                return $result;
            }

            if ($newPassword !== $confirmPassword) {
                $result->addError('Password baru dan konfirmasi password tidak sama.');
                return $result;
            }

            if (strlen($newPassword) < 8) {
                $result->addError('Password baru minimal 8 karakter.');
                return $result;
            }

            $authenticator = new MemberAuthenticator();
            $loginData = [
                'Email' => $member->Email,
                'Password' => $currentPassword
            ];

            $tempResult = ValidationResult::create();
            $authenticatedMember = $authenticator->authenticate($loginData, $request, $tempResult);

            if (!$authenticatedMember || $authenticatedMember->ID !== $member->ID) {
                $result->addError('Password saat ini salah.');
                return $result;
            }
        }

        try {
            if ($firstName) {
                $member->FirstName = $firstName;
            }

            if ($lastName) {
                $member->Surname = $lastName;
            }

            if ($email && $email !== $member->Email) {
                $member->Email = $email;
            }

            $member->write();

            if ($newPassword) {
                $member->changePassword($newPassword);
            }

        } catch (Exception $e) {
            $result->addError('Terjadi kesalahan saat memperbarui profil.');
        }

        return $result;
    }
}