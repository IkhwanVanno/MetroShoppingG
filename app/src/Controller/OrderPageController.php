<?php

use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;

class OrderPageController extends PageController
{
    private static $allowed_actions = [
        'index',
        'detail',
        'submitReview',
        'cancelOrder',
        'markAsCompleted'
    ];

    private static $url_handlers = [
        'detail/$ID' => 'detail',
        'submit-review' => 'submitReview',
        'cancel/$ID' => 'cancelOrder',
        'complete/$ID' => 'markAsCompleted',
        '' => 'index'
    ];

    /**
     * Show order list
     */
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

    /**
     * Show order detail
     */
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

        $orderItems = $order->OrderItem();
        $itemsWithReviewStatus = [];

        foreach ($orderItems as $item) {
            $existingReview = Review::get()->filter([
                'ProductID' => $item->ProductID,
                'MemberID' => $this->getCurrentUser()->ID
            ])->first();

            $itemsWithReviewStatus[] = [
                'Item' => $item,
                'HasReview' => $existingReview ? true : false,
                'Review' => $existingReview
            ];
        }

        $data = array_merge($this->getCommonData(), [
            'Order' => $order,
            'OrderItemsWithReview' => $itemsWithReviewStatus,
            'Title' => 'Detail Pesanan ' . $order->OrderCode
        ]);

        return $this->customise($data)->renderWith(['OrderDetailPage', 'Page']);
    }

    /**
     * Cancel order
     */
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

    /**
     * Mark order as completed (for delivered orders)
     */
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

    /**
     * Submit product review
     */
    public function submitReview(HTTPRequest $request)
    {
        if (!$this->getCurrentUser()) {
            return HTTPResponse::create(json_encode(['error' => 'Unauthorized']), 401)
                ->addHeader('Content-Type', 'application/json');
        }

        if (!$request->isPOST()) {
            return HTTPResponse::create(json_encode(['error' => 'Method not allowed']), 405)
                ->addHeader('Content-Type', 'application/json');
        }

        $productID = $request->postVar('product_id');
        $orderID = $request->postVar('order_id');
        $rating = (int) $request->postVar('rating');
        $message = $request->postVar('message');

        // Validation
        if (!$productID || !$orderID || !$rating || $rating < 1 || $rating > 5) {
            return HTTPResponse::create(json_encode(['error' => 'Invalid data']), 400)
                ->addHeader('Content-Type', 'application/json');
        }

        $user = $this->getCurrentUser();

        // Check if order belongs to user and is completed
        $order = Order::get()->filter([
            'ID' => $orderID,
            'MemberID' => $user->ID,
            'Status' => 'completed'
        ])->first();

        if (!$order) {
            return HTTPResponse::create(json_encode(['error' => 'Order not found or not completed']), 404)
                ->addHeader('Content-Type', 'application/json');
        }

        // Check if product is in this order
        $orderItem = OrderItem::get()->filter([
            'OrderID' => $orderID,
            'ProductID' => $productID
        ])->first();

        if (!$orderItem) {
            return HTTPResponse::create(json_encode(['error' => 'Product not found in order']), 404)
                ->addHeader('Content-Type', 'application/json');
        }

        // Check if review already exists
        $existingReview = Review::get()->filter([
            'ProductID' => $productID,
            'MemberID' => $user->ID
        ])->first();

        if ($existingReview) {
            return HTTPResponse::create(json_encode(['error' => 'Review already exists']), 400)
                ->addHeader('Content-Type', 'application/json');
        }

        // Create review
        $review = Review::create();
        $review->ProductID = $productID;
        $review->MemberID = $user->ID;
        $review->Rating = $rating;
        $review->Message = $message;
        $review->write();

        return HTTPResponse::create(json_encode(['success' => 'Review submitted successfully']), 200)
            ->addHeader('Content-Type', 'application/json');
    }
}