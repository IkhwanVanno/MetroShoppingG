<?php

use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Security\Security;

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

        // Check if user is logged in and owns the order
        $currentUser = Security::getCurrentUser();
        if (!$currentUser || $order->MemberID != $currentUser->ID) {
            return $this->httpError(403, 'Access denied');
        }

        if ($order->isExpired()) {
            $order->cancelOrder();
            $request->getSession()->set('PaymentError', 'Pesanan telah kedaluwarsa');
            return $this->redirect(Director::absoluteBaseURL() . 'order/detail/' . $orderID);
        }

        if (!$order->canBePaid()) {
            $request->getSession()->set('PaymentError', 'Pesanan tidak dapat dibayar');
            return $this->redirect(Director::absoluteBaseURL() . 'order/detail/' . $orderID);
        }

        $duitku = new DuitkuService();
        $response = $duitku->createTransaction($order);

        if ($response && $response['success']) {
            $transaction = PaymentTransaction::create();
            $transaction->OrderID = $order->ID;
            $transaction->PaymentGateway = 'duitku';
            $transaction->TransactionID = $response['merchantOrderId'] ?? $order->OrderCode;
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
        $request->getSession()->set('PaymentError', $errorMessage);
        return $this->redirect(Director::absoluteBaseURL() . 'order/detail/' . $orderID);
    }

    /**
     * Handle payment callback from Duitku
     */
    public function callback(HTTPRequest $request)
    {
        // Allow both POST and GET requests for ngrok compatibility
        if (!$request->isPOST() && !$request->isGET()) {
            return $this->httpError(405, 'Method not allowed');
        }

        // Handle both POST body and GET parameters
        if ($request->isPOST()) {
            $rawBody = $request->getBody();
            $data = json_decode($rawBody, true);

            // If JSON decode fails, try getting from POST data
            if (!$data) {
                $data = $request->postVars();
            }
        } else {
            // Handle GET request (some payment gateways use GET for callbacks)
            $data = $request->getVars();
        }

        if (!$data || empty($data)) {
            error_log('PaymentController::callback - No data received');
            return new HTTPResponse('No data received', 400);
        }

        error_log('PaymentController::callback - Received data: ' . json_encode($data));

        $duitku = new DuitkuService();

        // Verify callback signature
        if (!$duitku->verifyCallback($data)) {
            error_log('PaymentController::callback - Invalid signature');
            return new HTTPResponse('Invalid signature', 400);
        }

        $merchantOrderId = $data['merchantOrderId'] ?? '';
        $resultCode = $data['resultCode'] ?? '';

        if (empty($merchantOrderId)) {
            error_log('PaymentController::callback - Missing merchantOrderId');
            return new HTTPResponse('Missing merchantOrderId', 400);
        }

        $transaction = PaymentTransaction::get()->filter('TransactionID', $merchantOrderId)->first();

        if (!$transaction) {
            error_log('PaymentController::callback - Transaction not found: ' . $merchantOrderId);
            return new HTTPResponse('Transaction not found', 404);
        }

        $order = $transaction->Order();
        if (!$order) {
            error_log('PaymentController::callback - Order not found for transaction: ' . $merchantOrderId);
            return new HTTPResponse('Order not found', 404);
        }

        // Update transaction with callback data
        $transaction->ResponseData = json_encode($data);

        if ($resultCode == '00') {
            $transaction->Status = 'success';
            $order->markAsPaid();

            error_log('PaymentController::callback - Payment success for order: ' . $order->ID);
            $this->sendPaymentSuccessNotification($order);

            // Send invoice email automatically after successful payment
            try {
                InvoiceController::sendInvoiceAfterPayment($order);
                error_log('PaymentController::callback - Invoice sent for order: ' . $order->ID);
            } catch (Exception $e) {
                error_log('PaymentController::callback - Failed to send invoice for order: ' . $order->ID . ' - ' . $e->getMessage());
            }

        } else {
            $transaction->Status = 'failed';
            $order->Status = 'cancelled';
            $order->PaymentStatus = 'failed';
            $order->write();

            error_log('PaymentController::callback - Payment failed for order: ' . $order->ID . ', resultCode: ' . $resultCode);
            $this->sendPaymentFailedNotification($order);
        }

        $transaction->write();

        return new HTTPResponse('OK', 200);
    }

    /**
     * Handle return from payment page
     */
    public function return(HTTPRequest $request)
    {
        $merchantOrderId = $request->getVar('merchantOrderId');
        $resultCode = $request->getVar('resultCode');

        error_log('PaymentController::return - merchantOrderId: ' . $merchantOrderId . ', resultCode: ' . $resultCode);

        if (!$merchantOrderId) {
            return $this->redirect(Director::absoluteBaseURL() . 'order');
        }

        $transaction = PaymentTransaction::get()->filter('TransactionID', $merchantOrderId)->first();
        $order = null;

        if (!$transaction) {
            // Try to find order by OrderCode if transaction not found
            $order = Order::get()->filter('OrderCode', $merchantOrderId)->first();

            if ($order) {
                // Create transaction record if it doesn't exist
                $transaction = PaymentTransaction::create();
                $transaction->OrderID = $order->ID;
                $transaction->PaymentGateway = 'duitku';
                $transaction->TransactionID = $merchantOrderId;
                $transaction->Amount = $order->getGrandTotal();
                $transaction->Status = 'pending';
                $transaction->CreateAt = date('Y-m-d H:i:s');
                $transaction->write();
            } else {
                error_log('PaymentController::return - Order not found: ' . $merchantOrderId);
                return $this->redirect(Director::absoluteBaseURL() . 'order');
            }
        } else {
            $order = $transaction->Order();
        }

        if (!$order) {
            return $this->redirect(Director::absoluteBaseURL() . 'order');
        }

        if ($resultCode == '00') {
            $transaction->Status = 'success';
            $transaction->write();

            $order->markAsPaid();

            // Send invoice email automatically after successful payment
            try {
                InvoiceController::sendInvoiceAfterPayment($order);
                $request->getSession()->set('PaymentSuccess', 'Pembayaran berhasil! Pesanan Anda sedang diproses. Invoice telah dikirim ke email Anda.');
                error_log('PaymentController::return - Payment success and invoice sent for order: ' . $order->ID);
            } catch (Exception $e) {
                $request->getSession()->set('PaymentSuccess', 'Pembayaran berhasil! Pesanan Anda sedang diproses.');
                error_log('PaymentController::return - Payment success but invoice failed for order: ' . $order->ID . ' - ' . $e->getMessage());
            }
        } else {
            $transaction->Status = 'failed';
            $transaction->write();

            $order->Status = 'cancelled';
            $order->PaymentStatus = 'failed';
            $order->write();

            $request->getSession()->set('PaymentError', 'Pembayaran gagal atau dibatalkan. Pesanan telah dibatalkan.');
            error_log('PaymentController::return - Payment failed for order: ' . $order->ID);
        }

        return $this->redirect(Director::absoluteBaseURL() . '/order/detail/' . $order->ID);
    }

    /**
     * Send payment success notification
     */
    private function sendPaymentSuccessNotification($order)
    {
        // TODO: Implement notification logic
        error_log('PaymentController - Payment success notification for order: ' . $order->ID);
    }

    /**
     * Send payment failed notification
     */
    private function sendPaymentFailedNotification($order)
    {
        // TODO: Implement notification logic
        error_log('PaymentController - Payment failed notification for order: ' . $order->ID);
    }
}