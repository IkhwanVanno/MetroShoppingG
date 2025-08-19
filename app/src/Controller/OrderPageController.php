<?php

use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;

class OrderPageController extends PageController
{
    private static $allowed_actions = [
        'index',
        'detail',
        'cancelOrder',
        'markAsCompleted',
        'submitReview'
    ];

    private static $url_handlers = [
        'detail/$ID' => 'detail',
        'cancel/$ID' => 'cancelOrder',
        'complete/$ID' => 'markAsCompleted',
        'review/submit/$OrderID/$OrderItemID' => 'submitReview',
        '' => 'index'
    ];

    public function index(HTTPRequest $request)
    {
        if (!$this->getCurrentUser()) {
            return $this->redirect(Director::absoluteBaseURL() . '/auth/login');
        }

        $user = $this->getCurrentUser();

        $expiredOrders = Order::get()->filter([
            'MemberID' => $user->ID,
            'Status' => ['pending', 'pending_payment'],
            'PaymentStatus' => 'unpaid'
        ]);

        foreach ($expiredOrders as $order) {
            $order->checkAndCancelIfExpired();
        }

        $orders = Order::get()->filter('MemberID', $user->ID)->sort('CreateAt DESC');

        $data = array_merge($this->getCommonData(), [
            'Orders' => $orders,
            'Title' => 'Daftar Pesanan'
        ]);

        return $this->customise($data)->renderWith(['OrderListPage', 'Page']);
    }

    public function detail(HTTPRequest $request)
    {
        $orderID = $request->param('ID');

        if (!$orderID) {
            return $this->httpError(400, 'Order ID required');
        }

        $order = Order::get()->byID($orderID);

        if (!$order) {
            return $this->httpError(404, 'Order not found');
        }

        if (!$this->getCurrentUser() || $order->MemberID != $this->getCurrentUser()->ID) {
            return $this->httpError(403, 'Access denied');
        }

        $order->checkAndCancelIfExpired();

        $orderItems = OrderItem::get()->filter('OrderID', $order->ID);
        $itemsWithReviewStatus = [];

        foreach ($orderItems as $item) {
            $product = Product::get()->byID($item->ProductID);

            if (!$product) {
                continue;
            }

            $existingReview = $item->getReview();
            $canReview = $item->canBeReviewed();

            $itemData = new ArrayData([
                'ID' => $item->ID,
                'ProductID' => $item->ProductID,
                'Quantity' => $item->Quantity,
                'Price' => $item->Price,
                'Subtotal' => $item->Subtotal,
                'FormattedPrice' => number_format($item->Price, 0, '.', '.'),
                'FormattedSubtotal' => number_format($item->Subtotal, 0, '.', '.'),
                'Product' => $product,
                'RatingRange' => new ArrayList([
                    ArrayData::create(['Value' => 5]),
                    ArrayData::create(['Value' => 4]),
                    ArrayData::create(['Value' => 3]),
                    ArrayData::create(['Value' => 2]),
                    ArrayData::create(['Value' => 1]),
                ])
            ]);

            $itemsWithReviewStatus[] = [
                'Item' => $itemData,
                'HasReview' => $existingReview ? true : false,
                'Review' => $existingReview,
                'CanReview' => $canReview
            ];
        }

        $reviewStatusList = new ArrayList($itemsWithReviewStatus);

        $data = array_merge($this->getCommonData(), [
            'Order' => $order,
            'OrderItemsWithReview' => $reviewStatusList,
            'Title' => 'Detail Pesanan ' . $order->OrderCode
        ]);

        return $this->customise($data)->renderWith(['OrderDetailPage', 'Page']);
    }

    public function cancelOrder(HTTPRequest $request)
    {
        if (!$this->getCurrentUser()) {
            return $this->redirect(Director::absoluteBaseURL() . '/auth/login');
        }

        $orderID = $request->param('ID');
        $order = Order::get()->filter([
            'ID' => $orderID,
            'MemberID' => $this->getCurrentUser()->ID
        ])->first();

        if (!$order) {
            $this->getRequest()->getSession()->set('OrderError', 'Pesanan tidak ditemukan');
            return $this->redirectBack();
        }

        if ($order->cancelOrder()) {
            $this->getRequest()->getSession()->set('OrderSuccess', 'Pesanan berhasil dibatalkan');
        } else {
            $this->getRequest()->getSession()->set('OrderError', 'Pesanan tidak dapat dibatalkan');
        }

        return $this->redirect(Director::absoluteBaseURL() . '/order/detail/' . $orderID);
    }

    public function markAsCompleted(HTTPRequest $request)
    {
        if (!$this->getCurrentUser()) {
            return $this->redirect(Director::absoluteBaseURL() . '/auth/login');
        }

        $orderID = $request->param('ID');
        $order = Order::get()->filter([
            'ID' => $orderID,
            'MemberID' => $this->getCurrentUser()->ID
        ])->first();

        if (!$order) {
            $this->getRequest()->getSession()->set('OrderError', 'Pesanan tidak ditemukan');
            return $this->redirectBack();
        }

        if ($order->markAsCompleted()) {
            $this->getRequest()->getSession()->set('OrderSuccess', 'Pesanan telah dikonfirmasi selesai');
        } else {
            $this->getRequest()->getSession()->set('OrderError', 'Pesanan tidak dapat diselesaikan');
        }

        return $this->redirect(Director::absoluteBaseURL() . '/order/detail/' . $orderID);
    }

    public function submitReview(HTTPRequest $request)
    {
        if (!$this->getCurrentUser()) {
            return $this->redirect(Director::absoluteBaseURL() . '/auth/login');
        }

        $orderID = $request->param('OrderID');
        $orderItemID = $request->param('OrderItemID');

        $order = Order::get()->filter([
            'ID' => $orderID,
            'MemberID' => $this->getCurrentUser()->ID
        ])->first();

        if (!$order || $order->Status != 'completed') {
            $this->getRequest()->getSession()->set('ReviewError', 'Pesanan belum selesai atau tidak dapat direview');
            return $this->redirectBack();
        }

        $orderItem = OrderItem::get()->filter([
            'ID' => $orderItemID,
            'OrderID' => $orderID
        ])->first();

        if (!$orderItem) {
            $this->getRequest()->getSession()->set('ReviewError', 'Item pesanan tidak ditemukan');
            return $this->redirectBack();
        }

        if ($orderItem->hasReview()) {
            $this->getRequest()->getSession()->set('ReviewError', 'Item pesanan ini sudah direview');
            return $this->redirectBack();
        }

        $rating = (int) $request->postVar('rating');
        $message = trim($request->postVar('message'));
        $showname = (bool) $request->postVar('showname');

        if (!$rating || $rating < 1 || $rating > 5) {
            $this->getRequest()->getSession()->set('ReviewError', 'Rating harus antara 1-5');
            return $this->redirectBack();
        }

        if (!$message || strlen($message) < 5) {
            $this->getRequest()->getSession()->set('ReviewError', 'Pesan review minimal 5 karakter');
            return $this->redirectBack();
        }

        $review = Review::create();
        $review->ProductID = $orderItem->ProductID;
        $review->MemberID = $this->getCurrentUser()->ID;
        $review->OrderItemID = $orderItem->ID;
        $review->Rating = $rating;
        $review->Message = $message;
        $review->ShowName = $showname;
        $review->write();

        $this->getRequest()->getSession()->set('ReviewSuccess', 'Review berhasil ditambahkan');
        return $this->redirect(Director::absoluteBaseURL() . '/order/detail/' . $orderID);
    }
}