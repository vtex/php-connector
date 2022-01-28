<?php

namespace PhpConnector\Service;

use PhpConnector\Model\CreatePaymentRequest;

class CustomInstallmentsService
{
    private $paymentId;
    private $paymentRequest;

    public function __construct(string $paymentId)
    {
        $this->paymentId = $paymentId;
        $this->paymentRequest = $this->getPersistedRequest($paymentId);
    }

    private function getPersistedRequest(string $paymentId): CreatePaymentRequest
    {
        $filename = "logs/requests/authorization-{$paymentId}.json";
        $content = file_get_contents($filename);
        $requestAsArray = json_decode($content, true);

        return CreatePaymentRequest::fromArray($requestAsArray);
    }

    /** The default minimum amount per installment is 50 */
    private function calculateNumberOfInstallmentsOptions($minimumAmountPerInstallment = 50): int
    {
        $value =  $this->paymentRequest->value();
        return floor($value / $minimumAmountPerInstallment) > 0 ? floor($value / 50) : 1;
    }

    public function renderHTML()
    {
        ob_start();
        $numberOfInstallments = $this->calculateNumberOfInstallmentsOptions();
        $paymentId = $this->paymentId;
        include __DIR__ . "/../View/installments.view.php";
        return ob_get_clean();
    }

    public function saveInstallmentsSelection(int $numberOfInstallments)
    {
        if (!is_dir("logs/custom-installments")) {
            mkdir("logs/custom-installments", 0777, true);
        }
        $content = json_encode(["installments" => $numberOfInstallments]);
        $filename = "logs/custom-installments/{$this->paymentId}.json";
        file_put_contents($filename, $content);
    }

    public function getReturnUrl()
    {
        return $this->paymentRequest->returnUrl();
    }
}
