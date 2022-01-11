<?php

namespace PhpConnector\Service;

use PhpConnector\Model\CreatePaymentRequest;
use PhpConnector\Model\CreatePaymentResponse;
use PhpConnector\Model\CancellationRequest;
use PhpConnector\Model\CancellationResponse;
use PhpConnector\Model\CaptureRequest;
use PhpConnector\Model\CaptureResponse;
use PhpConnector\Model\RefundRequest;
use PhpConnector\Model\RefundResponse;

class ProviderMockService implements ProviderServiceInterface
{
    private $creditCardFlow = [
        '4444333322221111' => 'Authorize',
        '4444333322221112' => 'Denied',
        '4222222222222224' => 'AsyncApproved',
        '4222222222222225' => 'AsyncDenied',
        null =>  'Redirect',
    ];

    public function createPayment(CreatePaymentRequest $request): CreatePaymentResponse
    {
        $creditCardNumber = $request->card()->cardNumber();
        $creditCardIsValid = $this->validateCreditCard($creditCardNumber);

        if ($creditCardIsValid) {
            return self::$creditCardPaymentApprovedResponse;
        } elseif ($creditCardNumber === "4222222222222225" || $creditCardNumber === "4222222222222224") {
            return self::$creditCardPaymentProcessing;
        } else {
            return self::$creditCardPaymentDeniedResponse;
        }
    }

    public function processCancellation(CancellationRequest $request): CancellationResponse
    {
        $cancellationId = $this->nextCancellationId();

        if (!is_null($request->requestId())) {
            return CancellationResponse::notSupported($request);
        }

        return CancellationResponse::approved($request, $cancellationId);
    }

    private function nextCancellationId(): string
    {
        return bin2hex(random_bytes(10));
    }

    public function processCapture(CaptureRequest $request): CaptureResponse
    {
        $captureId = $this->nextCaptureId();

        return CaptureResponse::approved($request, $captureId);
    }

    private function nextCaptureId(): string
    {
        return bin2hex(random_bytes(10));
    }

    /**
     * This functions should do some checks on the request e.g.:
     * check if the request is valid, confirm that is settled,
     * confirm that the value is less than or equal to original settlement value
     */
    public function processRefund(RefundRequest $request): RefundResponse
    {
        if ($request->value() > 100 && $request->value() < 1000) {
            $refundId = $this->nextRefundId();
            return RefundResponse::approved($request, $refundId);
        } elseif ($request->value() > 1000) {
            return RefundResponse::denied($request);
        } else {
            return RefundResponse::manual($request);
        }
    }

    private function nextRefundId(): string
    {
        return bin2hex(random_bytes(10));
    }

    private function retry($requestBody, $credentials)
    {
        sleep(1);
        $response = [
            "paymentId" =>  $requestBody['paymentId'],
            "status" => "denied",
            "authorizationId" => null,
            "tid" => "TID-7B58BE1A08",
            "code" => "OperationDeniedCode",
            "message" => "Credit card payment denied"
        ];

        $curl = curl_init();

        $payload = json_encode($response);
        curl_setopt_array($curl, [
            CURLOPT_URL => $requestBody['callbackUrl'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                "Accept: application/json",
                "Content-Type: application/json",
                "X-VTEX-API-AppKey: {$credentials["key"]}",
                "X-VTEX-API-AppToken: {$credentials["token"]}"
            ],
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            error_log($error);
        }
    }
}
