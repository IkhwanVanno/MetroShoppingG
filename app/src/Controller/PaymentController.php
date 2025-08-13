<?php

use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;

class PaymentController extends PageController
{
    private static $allowed_actions = [
        'initiate',
        'callback',
        'return'
    ];

    private static $url_handlers = [
        'initiate/$ID' => 'initiate',
        'callback' => 'callback',
        'return' => 'return'
    ];

    /**
     * Initiate payment process
     */
    public function initiate(HTTPRequest $request)
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

        if ($order->isExpired()) {
            $order->cancelOrder();
            $this->getRequest()->getSession()->set('PaymentError', 'Pesanan telah kedaluwarsa');
            return $this->redirect(Director::absoluteBaseURL() . '/order/detail/' . $orderID);
        }

        if (!$order->canBePaid()) {
            $this->getRequest()->getSession()->set('PaymentError', 'Pesanan tidak dapat dibayar');
            return $this->redirect(Director::absoluteBaseURL() . '/order/detail/' . $orderID);
        }

        $duitku = new DuitkuService();
        $response = $duitku->createTransaction($order);

        if ($response && $response['success']) {
            $transaction = PaymentTransaction::create();
            $transaction->OrderID = $order->ID;
            $transaction->PaymentGateway = 'duitku';
            $transaction->TransactionID = $order->OrderCode;
            $transaction->Amount = $order->getGrandTotal();
            $transaction->Status = 'pending';
            $transaction->CreateAt = date('Y-m-d H:i:s');
            $transaction->write();

            $order->Status = 'pending_payment';
            $order->write();

            if (isset($response['paymentUrl']) && !empty($response['paymentUrl'])) {
                return $this->redirect($response['paymentUrl']);
            }
        }

        $errorMessage = isset($response['error']) ? $response['error'] : 'Gagal membuat transaksi pembayaran';
        $this->getRequest()->getSession()->set('PaymentError', $errorMessage);
        return $this->redirect(Director::absoluteBaseURL() . '/order/detail/' . $orderID);
    }

    /**
     * Handle payment callback from Duitku
     */
    public function callback(HTTPRequest $request)
    {
        if (!$request->isPOST()) {
            return $this->httpError(405, 'Method not allowed');
        }

        $data = json_decode($request->getBody(), true);

        if (!$data) {
            return $this->httpError(400, 'Invalid JSON data');
        }

        $duitku = new DuitkuService();

        if (!$duitku->verifyCallback($data)) {
            return $this->httpError(400, 'Invalid signature');
        }

        $merchantOrderId = $data['merchantOrderId'];
        $resultCode = $data['resultCode'];

        $transaction = PaymentTransaction::get()->filter('TransactionID', $merchantOrderId)->first();

        if (!$transaction) {
            return $this->httpError(404, 'Transaction not found');
        }

        $order = $transaction->Order();

        $transaction->ResponseData = json_encode($data);

        if ($resultCode == '00') {
            $transaction->Status = 'success';
            $order->markAsPaid();

            $this->sendPaymentSuccessNotification($order);

        } else {
            $transaction->Status = 'failed';
            $order->Status = 'cancelled';
            $order->PaymentStatus = 'failed';
            $order->write();

            $this->sendPaymentFailedNotification($order);
        }

        $transaction->write();
        return HTTPResponse::create('OK', 200);
    }

    /**
     * Handle return from payment page
     */
    public function return(HTTPRequest $request)
    {
        $merchantOrderId = $request->getVar('merchantOrderId');
        $resultCode = $request->getVar('resultCode');

        if (!$merchantOrderId) {
            return $this->redirect(Director::absoluteBaseURL() . '/order');
        }

        $transaction = PaymentTransaction::get()->filter('TransactionID', $merchantOrderId)->first();

        if (!$transaction) {
            $order = Order::get()->filter('OrderCode', $merchantOrderId)->first();
            if (!$order) {
                $transaction = PaymentTransaction::create();
                $transaction->OrderID = $order->ID;
                $transaction->PaymentGateway = 'duitku';
                $transaction->TransactionID = $merchantOrderId;
                $transaction->Amount = $order->getGrandTotal();
                $transaction->Status = 'pending';
                $transaction->CreateAt = date('Y-m-d H:i:s');
                $transaction->write();
            } else {
                return $this->redirect(Director::absoluteBaseURL() . '/order');
            }
        } else {
            $order = $transaction->Order();
        }

        if ($resultCode == '00') {
            $transaction->Status = 'success';
            $transaction->write();

            $order->markAsPaid();

            $this->getRequest()->getSession()->set('PaymentSuccess', 'Pembayaran berhasil! Pesanan Anda sedang diproses.');
        } else {
            $transaction->Status = 'failed';
            $transaction->write();

            $order->Status = 'cancelled';
            $order->PaymentStatus = 'failed';
            $order->write();

            $this->getRequest()->getSession()->set('PaymentError', 'Pembayaran gagal atau dibatalkan. Pesanan telah dibatalkan.');
        }
        return $this->redirect(Director::absoluteBaseURL() . '/order/detail/' . $order->ID);
    }

    /**
     * Send payment success notification
     */
    private function sendPaymentSuccessNotification($order)
    {

    }

    /**
     * Send payment failed notification
     */
    private function sendPaymentFailedNotification($order)
    {

    }
}