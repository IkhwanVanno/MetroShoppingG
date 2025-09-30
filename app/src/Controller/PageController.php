<?php

namespace {

    use SilverStripe\CMS\Controllers\ContentController;
    use SilverStripe\Control\Email\Email;
    use SilverStripe\Control\HTTPRequest;
    use SilverStripe\Core\Convert;
    use SilverStripe\Forms\EmailField;
    use SilverStripe\Forms\FieldList;
    use SilverStripe\Forms\Form;
    use SilverStripe\Forms\FormAction;
    use SilverStripe\Forms\RequiredFields;
    use SilverStripe\Forms\TextareaField;
    use SilverStripe\Forms\TextField;
    use SilverStripe\ORM\ArrayList;
    use SilverStripe\Security\Security;
    use SilverStripe\SiteConfig\SiteConfig;
    use SilverStripe\View\ArrayData;

    class PageController extends ContentController
    {
        private static $allowed_actions = [
            "Index",
            "ContactForm",
        ];

        protected $flashMessages = null;
        public function getFlashMessages()
        {
            return $this->flashMessages;
        }
        protected function init()
        {
            parent::init();

            $session = $this->getRequest()->getSession();
            $flash = $session->get('FlashMessage');

            if ($flash) {
                $this->flashMessages = ArrayData::create($flash);
                $session->clear('FlashMessage');
            }
        }

        protected function getCommonData()
        {
            return [
                "Brand" => Brand::get(),
                "SocialMedia" => SocialMedia::get(),
                "DeliveryMethod" => DeliveryMethod::get(),
                "PaymentMethod" => PaymentMethod::get(),
                "CustomSiteConfig" => SiteConfig::current_site_config(),
                "IsLoggedIn" => $this->isLoggedIn(),
                "CurrentUser" => $this->getCurrentUser(),
                "CartItemCount" => $this->getCartItemCount(),
                "FavoriteCount" => $this->getFavoriteCount(),
                "MembershipTier" => $this->getMembershipTier(),
                "MembershipTierName" => $this->getMembershipTierName(),
                "MembershipProgress" => $this->getMembershipProgress()
            ];
        }

        public function index(HTTPRequest $request)
        {
            $carouselImages = CarouselImage::get();
            $categories = Category::get();
            $contacts = Contact::get();

            $currentDateTime = date('Y-m-d H:i:s');
            $eventShops = EventShop::get()
                ->filter('EndDate:GreaterThan', $currentDateTime)
                ->sort('EndDate', 'ASC');

            $products = Product::get();

            $flashsale = FlashSale::get()->filter('Status', 'active');

            $verticalCategoryFilter = $request->getVar('vertical_category');
            $verticalFilteredProducts = $this->getFilteredProducts($verticalCategoryFilter);

            $horizontalCategoryFilter = $request->getVar('horizontal_category');
            $horizontalFilteredProducts = $this->getFilteredProducts($horizontalCategoryFilter);

            $data = array_merge($this->getCommonData(), [
                "CarouselImage" => $carouselImages,
                "Category" => $categories,
                "Contact" => $contacts,
                "EventShop" => $eventShops,
                "FlashSale" => $flashsale,
                "Product" => $products,
                "VerticalFilteredProducts" => $verticalFilteredProducts,
                "VerticalCategoryFilter" => $verticalCategoryFilter,
                "HorizontalFilteredProducts" => $horizontalFilteredProducts,
                "HorizontalCategoryFilter" => $horizontalCategoryFilter,
            ]);

            return $this->customise($data)->renderWith('Page');
        }
        public function EventShopGrouped()
        {
            $eventShop = EventShop::get();
            $grouped = new ArrayList();
            $chunk = [];

            foreach ($eventShop as $event) {
                $chunk[] = $event;

                if (count($chunk) === 2) {
                    $grouped->push(new ArrayList($chunk));
                    $chunk = [];
                }
            }

            if (!empty($chunk)) {
                $grouped->push(new ArrayList($chunk));
            }

            return $grouped;
        }

        // Authentication methods
        protected function getCurrentUser()
        {
            return Security::getCurrentUser();
        }

        protected function isLoggedIn()
        {
            return Security::getCurrentUser() !== null;
        }

        // Cart and Favorite counts
        public function getCartItemCount()
        {
            if ($this->isLoggedIn()) {
                $user = $this->getCurrentUser();
                if ($user && $user->exists()) {
                    $count = CartItem::get()->filter('MemberID', $user->ID)->sum('Quantity');
                    return $count ? (int) $count : 0;
                }
            }
            return 0;
        }

        public function getFavoriteCount()
        {
            if ($this->isLoggedIn()) {
                $user = $this->getCurrentUser();
                if ($user && $user->exists()) {
                    $count = Favorite::get()->filter('MemberID', $user->ID)->count();
                    return $count ? (int) $count : 0;
                }
            }
            return 0;
        }

        public function getMembershipTier()
        {
            if ($this->isLoggedIn()) {
                $user = $this->getCurrentUser();
                if ($user && $user->exists()) {
                    return MembershipService::getMembershipTier($user->ID);
                }
            }
            return null;
        }

        public function getMembershipTierName()
        {
            if ($this->isLoggedIn()) {
                $user = $this->getCurrentUser();
                if ($user && $user->exists()) {
                    $tier = MembershipService::getMembershipTier($user->ID);
                    return MembershipService::getMembershipTierName($tier);
                }
            }
            return 'Member';
        }

        public function getMembershipProgress()
        {
            if ($this->isLoggedIn()) {
                $user = $this->getCurrentUser();
                if ($user && $user->exists()) {
                    return ArrayData::create(MembershipService::getProgressToNextTier($user->ID));
                }
            }
            return null;
        }

        // Product filtering
        public function getFilteredProducts($categoryId = null, $searchQuery = null)
        {
            $products = Product::get()->filter('Stok:GreaterThan', 0);

            if ($categoryId && $categoryId != 'all') {
                $products = $products->filter('CategoryID', $categoryId);
            }

            if ($searchQuery) {
                $products = $products->filterAny([
                    'Name:PartialMatch' => $searchQuery,
                ]);
            }

            return $products;
        }

        // Contact form methods
        public function ContactForm()
        {
            $nameField = TextField::create('UserName', 'Your Name :')
                ->addExtraClass('form-field')
                ->setAttribute('placeholder', 'Enter your name')
                ->setAttribute('required', true)
                ->setAttribute('style', 'width:100%; padding:8px; margin-bottom:12px;');

            $emailField = EmailField::create('UserEmail', 'Your Email :')
                ->addExtraClass('form-field')
                ->setAttribute('placeholder', 'Enter your email')
                ->setAttribute('required', true)
                ->setAttribute('style', 'width:100%; padding:8px; margin-bottom:12px;');

            $messageField = TextareaField::create('Message', 'Message :')
                ->addExtraClass('form-field')
                ->setAttribute('rows', 4)
                ->setAttribute('placeholder', 'Write your message here')
                ->setAttribute('required', true)
                ->setAttribute('style', 'width:100%; padding:8px; margin-bottom:12px;');

            $fields = FieldList::create($nameField, $emailField, $messageField);

            $actions = FieldList::create(
                FormAction::create('handleContactSubmit', 'SEND MESSAGE')
                    ->addExtraClass('form-button')
                    ->setAttribute('style', 'width:100%; padding:10px; background:#333; color:white; border:none; cursor:pointer;')
            );

            $validator = RequiredFields::create('UserName', 'UserEmail', 'Message');

            $form = Form::create($this, 'ContactForm', $fields, $actions, $validator);

            // Tambahkan border dan padding ke seluruh form
            $form->addExtraClass('custom-contact-form');
            $form->setAttribute('style', 'border:1px solid #ccc; padding:20px; max-width:500px; margin:auto;');

            $form->enableSecurityToken();

            return $form;
        }

        public function handleContactSubmit($data, Form $form, HTTPRequest $request)
        {
            try {
                $userName = Convert::raw2xml($data['UserName']);
                $userEmail = Convert::raw2xml($data['UserEmail']);
                $message = Convert::raw2xml($data['Message']);

                if (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
                    $form->sessionMessage('Invalid email!', 'bad');
                    return $this->redirectBack();
                }

                $contact = Contact::create();
                $contact->UserName = $userName;
                $contact->UserEmail = $userEmail;
                $contact->Message = $message;
                $contact->write();

                $this->sendContactEmail($userName, $userEmail, $message);
                $form->sessionMessage('Message sent successfully!', 'good');

            } catch (Exception $e) {
                error_log('Contact form error: ' . $e->getMessage());
                $form->sessionMessage('Error sending message. Please try again.', 'bad');
            }

            return $this->redirectBack();
        }

        private function sendContactEmail($userName, $userEmail, $message)
        {
            $siteConfig = SiteConfig::current_site_config();

            if (!$siteConfig->Email) {
                return;
            }

            $adminEmails = $this->parseMultipleEmails($siteConfig->Email);

            if (empty($adminEmails)) {
                return;
            }

            foreach ($adminEmails as $adminEmail) {
                $email = new Email();
                $email->setTo($adminEmail);
                $email->setFrom($adminEmails[0]);
                $email->setReplyTo($userEmail);
                $email->setSubject("Contact message from: {$userName}");
                $email->setHTMLTemplate('CustomEmail');
                $email->setData([
                    'Name' => $userName,
                    'SenderEmail' => $userEmail,
                    'MessageContent' => $message,
                    'SiteName' => $siteConfig->Title,
                ]);

                $email->send();
            }
        }

        private function parseMultipleEmails($emailString)
        {
            $emails = explode(',', $emailString);
            $validEmails = [];

            foreach ($emails as $email) {
                $email = trim($email);
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $validEmails[] = $email;
                }
            }

            return $validEmails;
        }

        public function getContactForm()
        {
            return $this->ContactForm();
        }
    }
}