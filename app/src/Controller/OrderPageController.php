<?php

use SilverStripe\Control\HTTPRequest;

class OrderPageController extends PageController
{
    private static $allowed_actions = [
        "index",
        "detail",
        "confirmReceived"
    ];

    private static $url_segment = "order";

    private static $url_handlers = [
        'detail/$ID' => 'detail',
        'confirm-received' => 'confirmReceived',
        '' => 'index'
    ];

    public function index(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            return $this->redirect('$BaseHref/auth-page/login');
        }

        $user = $this->getCurrentUser();
        $orders = Order::get()->filter('MemberID', $user->ID)->sort('CreatedAt DESC');

        $data = array_merge($this->getCommonData(), [
            'Orders' => $orders
        ]);

        return $this->customise($data)->renderWith(['OrderPage', 'Page']);
    }

    public function detail(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            return $this->redirect('$BaseHref/auth-page/login');
        }

        $orderID = $request->param('ID');
        $user = $this->getCurrentUser();

        $order = Order::get()->filter([
            'ID' => $orderID,
            'MemberID' => $user->ID
        ])->first();

        if (!$order) {
            return $this->httpError(404, 'Order not found');
        }

        $orderItems = OrderItem::get()->filter('OrderID', $order->ID);
        $shippingAddress = ShippingAddress::get()->filter('MemberID', $user->ID)->first();

        $data = array_merge($this->getCommonData(), [
            'Order' => $order,
            'OrderItems' => $orderItems,
            'ShippingAddress' => $shippingAddress
        ]);

        return $this->customise($data)->renderWith(['DetailOrderPage', 'Page']);
    }

    public function confirmReceived(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            $this->getResponse()->addHeader('Content-Type', 'application/json');
            return json_encode(['success' => false, 'message' => 'Not logged in']);
        }

        if ($request->isPOST()) {
            $user = $this->getCurrentUser();
            $orderID = $request->postVar('orderID');

            $order = Order::get()->filter([
                'ID' => $orderID,
                'MemberID' => $user->ID,
                'Status' => 'Shipped'
            ])->first();

            if ($order) {
                $order->Status = 'completed';
                $order->UpdatedAt = date('Y-m-d H:i:s');
                $order->write();

                $this->getResponse()->addHeader('Content-Type', 'application/json');
                return json_encode(['success' => true, 'message' => 'Order status updated']);
            }
        }

        $this->getResponse()->addHeader('Content-Type', 'application/json');
        return json_encode(['success' => false, 'message' => 'Failed to update order']);
    }
}