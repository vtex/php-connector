<?php

namespace PhpConnector;

use PhpConnector\Service\ProviderServiceInterface;
use PhpConnector\Model\CreatePaymentRequest;
use PhpConnector\Model\CancellationRequest;
use PhpConnector\Model\CaptureRequest;
use PhpConnector\Model\RefundRequest;

class Connector
{
    private $isTestRequest;
    private $providerService = null;

    public function __construct(bool $isTestRequest, ProviderServiceInterface $providerService)
    {
        $this->isTestRequest = $isTestRequest;
        $this->providerService = $providerService;
    }

    function listPaymentMethodsAction(): array
    {
        return [
            "paymentMethods" => [
                "Visa",
                "Mastercard",
                "American Express",
            ]
        ];
    }

    /**
     * To-improve: TestSuit could test this endpoint.
     */
    function listPaymentProviderManifestAction(): array
    {
        return [
            "paymentMethods" => [
                [
                    "name" => "Visa",
                    "allowsSplit" => "onAuthorize"
                ],
                [
                    "name" => "Mastercard",
                    "allowsSplit" => "onCapture"
                ],
                [
                    "name" => "American Express",
                    "allowsSplit" => "disabled"
                ],
            ]
        ];
    }

    public function createPayment(array $requestBody): array
    {
        try {
            $request = CreatePaymentRequest::fromArray($requestBody);
        } catch (\Throwable $th) {
            throw new \Exception('Invalid Request Body', 400);
        }

        $paymentResponse = $this->providerService->createPayment($request);

        return [
            "responseCode" => $paymentResponse->responseCode(),
            "responseData" => $paymentResponse->asArray()
        ];
    }

    public function cancelPayment(array $requestBody): array
    {
        try {
            $request = CancellationRequest::fromArray($requestBody);
        } catch (\Throwable $th) {
            throw new \Exception('Invalid Request Body', 400);
        }

        $cancellationResponse = $this->providerService->processCancellation($request);

        return [
            "responseCode" => $cancellationResponse->responseCode(),
            "responseData" => $cancellationResponse->asArray()
        ];
    }


    public function capturePayment(array $requestBody): array
    {
        try {
            $request = CaptureRequest::fromArray($requestBody);
        } catch (\Throwable $th) {
            throw new \Exception('Invalid Request Body', 400);
        }

        $settlementResponse = $this->providerService->processCapture($request);

        return [
            "responseCode" => $settlementResponse->responseCode(),
            "responseData" => $settlementResponse->asArray()
        ];
    }

    function refundPayment(array $requestBody): array
    {
        try {
            $request = RefundRequest::fromArray($requestBody);
        } catch (\Throwable $th) {
            throw new \Exception('Invalid Request Body', 400);
        }

        $refundResponse = $this->providerService->processRefund($request);

        return [
            "responseCode" => $refundResponse->responseCode(),
            "responseData" => $refundResponse->asArray()
        ];
    }
}
