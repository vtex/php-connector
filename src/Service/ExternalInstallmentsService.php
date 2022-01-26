<?php

namespace PhpConnector\Service;

use PhpConnector\Model\CreatePaymentRequest;

class ExternalInstallmentsService
{
    private $paymentId;
    private $request;

    public function __construct(string $paymentId)
    {
        $this->paymentId = $paymentId;
        $this->request = $this->getPersistedRequest($paymentId);
    }

    private function getPersistedRequest(string $paymentId): CreatePaymentRequest
    {
        $filename = "logs/requests/authorization-{$paymentId}.json";
        $content = file_get_contents($filename);
        $requestAsArray = json_decode($content, true);

        return CreatePaymentRequest::fromArray($requestAsArray);
    }

    /** The default minimum amount per installment is 50 */
    private function calculateNumberOfInstallments($value, $minimumAmountPerInstallment = 50): int
    {
        return floor($value / $minimumAmountPerInstallment) > 0 ? floor($value / 50) : 1;
    }

    function renderHTML()
    {
        ob_start();
        $numberOfInstallments = $this->calculateNumberOfInstallments($this->request->value());
        $paymentId = $this->paymentId;
        include __DIR__ . "/../View/installments.view.php";
        return ob_get_clean();
    }
}
