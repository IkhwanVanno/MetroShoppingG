<?php

use SilverStripe\Control\Director;
use SilverStripe\Control\Email\Email;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Environment;
use SilverStripe\Dev\Debug;
use SilverStripe\SiteConfig\SiteConfig;
use Dompdf\Dompdf;
use Dompdf\Options;

class InvoiceController extends PageController
{
    private static $allowed_actions = [
        'generateInvoice',
        'sendInvoiceEmail',
        'downloadInvoice'
    ];

    private static $url_handlers = [
        'generate/$ID' => 'generateInvoice',
        'send/$ID' => 'sendInvoiceEmail',
        'download/$ID' => 'downloadInvoice'
    ];

    /**
     * Generate PDF invoice
     */
    public function generateInvoice(HTTPRequest $request)
    {
        $orderID = $request->param('ID');

        if (!$orderID) {
            return $this->httpError(400, 'Order ID required');
        }

        $order = Order::get()->byID($orderID);

        if (!$order) {
            return $this->httpError(404, 'Order not found');
        }

        // Check if user owns the order or is admin
        $currentUser = $this->getCurrentUser();
        if (!$currentUser || ($order->MemberID != $currentUser->ID && !$currentUser->inGroup('administrators'))) {
            return $this->httpError(403, 'Access denied');
        }

        $pdfContent = $this->generatePDFContent($order);

        return HTTPResponse::create($pdfContent, 200)
            ->addHeader('Content-Type', 'application/pdf')
            ->addHeader('Content-Disposition', 'inline; filename="Invoice-' . $order->OrderCode . '.pdf"');
    }

    /**
     * Send invoice via email
     */
    public function sendInvoiceEmail(HTTPRequest $request)
    {
        $orderID = $request->param('ID');

        if (!$orderID) {
            return $this->httpError(400, 'Order ID required');
        }

        $order = Order::get()->byID($orderID);

        if (!$order) {
            return $this->httpError(404, 'Order not found');
        }

        // Check if user owns the order or is admin
        $currentUser = $this->getCurrentUser();
        if (!$currentUser || ($order->MemberID != $currentUser->ID && !$currentUser->inGroup('administrators'))) {
            return $this->httpError(403, 'Access denied');
        }

        $result = $this->sendInvoiceToMember($order);

        if ($result) {
            $request->getSession()->set('InvoiceSuccess', 'Invoice berhasil dikirim ke email');
        } else {
            $request->getSession()->set('InvoiceError', 'Gagal mengirim invoice ke email');
        }

        return $this->redirect(Director::absoluteBaseURL() . '/order/detail/' . $orderID);
    }

    /**
     * Download invoice PDF
     */
    public function downloadInvoice(HTTPRequest $request)
    {
        $orderID = $request->param('ID');

        if (!$orderID) {
            return $this->httpError(400, 'Order ID required');
        }

        $order = Order::get()->byID($orderID);

        if (!$order) {
            return $this->httpError(404, 'Order not found');
        }

        // Check if user owns the order or is admin
        $currentUser = $this->getCurrentUser();
        if (!$currentUser || ($order->MemberID != $currentUser->ID && !$currentUser->inGroup('administrators'))) {
            return $this->httpError(403, 'Access denied');
        }

        $pdfContent = $this->generatePDFContent($order);

        return HTTPResponse::create($pdfContent, 200)
            ->addHeader('Content-Type', 'application/pdf')
            ->addHeader('Content-Disposition', 'attachment; filename="Invoice-' . $order->OrderCode . '.pdf"');
    }

    /**
     * Prepare invoice data (shared for both PDF and Email)
     */
    private function prepareInvoiceData($order)
    {
        $siteConfig = SiteConfig::current_site_config();
        $paymentFee = $this->calculatePaymentFee($order);
        $grandTotal = $order->TotalPrice + $order->ShippingCost + $paymentFee;

        return [
            'Order' => $order,
            'Member' => $order->Member(),
            'OrderItems' => $order->OrderItem(),
            'ShippingAddress' => $order->ShippingAddress(),
            'SiteConfig' => $siteConfig,
            'PaymentFee' => $paymentFee,
            'FormattedPaymentFee' => 'Rp ' . number_format($paymentFee, 0, '.', '.'),
            'GrandTotalWithFee' => $grandTotal,
            'FormattedGrandTotalWithFee' => 'Rp ' . number_format($grandTotal, 0, '.', '.'),
            'InvoiceDate' => date('Y-m-d H:i:s'),
            'InvoiceNumber' => 'INV-' . $order->OrderCode . '-' . date('Ymd')
        ];
    }

    /**
     * Generate PDF content from template
     */
    public function generatePDFContent($order)
    {
        $data = $this->prepareInvoiceData($order);
        $template = $this->customise($data)->renderWith('InvoicePDF');

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($template);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * Send invoice to member via email
     */
    public function sendInvoiceToMember($order)
    {
        try {
            $member = $order->Member();
            $SiteConfig = SiteConfig::current_site_config();
            $emails = explode(',', $SiteConfig->Email);
            $CompanyEmail = trim($emails[0]);
            $pdfContent = $this->generatePDFContent($order);
            $emailData = $this->prepareInvoiceData($order);
            $tempFile = tempnam(sys_get_temp_dir(), 'invoice_');
            file_put_contents($tempFile, $pdfContent);

            $email = Email::create()
                ->setHTMLTemplate('InvoiceEmail')
                ->setData($emailData)
                ->setFrom($CompanyEmail)
                ->setTo($member->Email)
                ->setSubject('Invoice Pembayaran - ' . $order->OrderCode)
                ->addAttachmentFromData(
                    $pdfContent,
                    'Invoice-' . $order->OrderCode . '.pdf',
                    'application/pdf'
                );

            if ($SiteConfig->logo && $SiteConfig->logo->exists()) {
                $logoName = $SiteConfig->logo->Name;
                $fullLogoPath = BASE_PATH . '/public/assets/Uploads/' . $logoName;

                if (file_exists($fullLogoPath)) {
                    $logoData = file_get_contents($fullLogoPath);
                    $imageInfo = getimagesize($fullLogoPath);
                    $logoMimeType = $imageInfo['mime'] ?? 'image/png';
                    $logoExtension = pathinfo($logoName, PATHINFO_EXTENSION);
                    $logoFilename = 'company-logo.' . $logoExtension;

                    $email->addAttachmentFromData(
                        $logoData,
                        $logoFilename,
                        $logoMimeType,
                    );

                    $emailData['LogoCID'] = 'cid:' . $logoFilename;
                    error_log('Logo attached as inline with CID: ' . $emailData['LogoCID']);
                } else {
                    error_log('Logo file not found: ' . $fullLogoPath);
                }
            }

            $email->setData($emailData);
            $email->send();

            if (file_exists($tempFile)) {
                unlink($tempFile);
            }

            return true;

        } catch (Exception $e) {
            error_log('InvoiceController::sendInvoiceToMember - Error: ' . $e->getMessage());

            if (isset($tempFile) && file_exists($tempFile)) {
                unlink($tempFile);
            }

            return false;
        }
    }

    /**
     * Calculate payment fee based on payment method
     */
    private function calculatePaymentFee($order)
    {
        // Get payment fee from Duitku service
        try {
            $duitku = new DuitkuService();
            $paymentMethods = $duitku->getPaymentMethods($order->TotalPrice);

            foreach ($paymentMethods as $method) {
                if ($method['paymentMethod'] == $order->PaymentMethod) {
                    return $method['totalFee'];
                }
            }
        } catch (Exception $e) {
            error_log('InvoiceController::calculatePaymentFee - Error: ' . $e->getMessage());
        }

        return 0;
    }

    /**
     * Static method to automatically send invoice after payment
     */
    public static function sendInvoiceAfterPayment($order)
    {
        $controller = new InvoiceController();
        return $controller->sendInvoiceToMember($order);
    }
}