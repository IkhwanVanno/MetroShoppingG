<?php

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Security\Member;

class VerifyController extends PageController
{
    private static $allowed_actions = ['index'];

    public function index(HTTPRequest $request)
    {
        $token = $request->getVar('token');
        $member = Member::get()->filter('VerificationToken', $token)->first();

        if ($member && !$member->IsVerified) {
            $member->IsVerified = true;
            $member->VerificationToken = null;
            $member->write();
            $this->Content = "Email berhasil diverifikasi. Silakan Login";
        } else {
            $this->Content = null;
        }

        return $this->renderWith(['Verify', 'Page']);
    }
}
