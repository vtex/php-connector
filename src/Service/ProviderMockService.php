<?php

namespace PhpConnector\Service;

use PhpConnector\Model\CancellationRequest;
use PhpConnector\Model\CancellationResponse;
use PhpConnector\Model\CaptureRequest;
use PhpConnector\Model\CaptureResponse;
use PhpConnector\Model\RefundRequest;
use PhpConnector\Model\RefundResponse;

class ProviderMockService implements ProviderServiceInterface
{
    private static $creditCardPaymentApprovedResponse = [
        "status" => "approved",
        "authorizationId" => "AUT-09DC5E8F03",
        "tid" => "TID-7B58BE1A08",
        "nsu" => "NSU-107521E866",
        "acquirer" => "TestPay",
        "code" => "OperationApprovedCode",
        "message" => null,
        "delayToAutoSettle" => 21600,
        "delayToAutoSettleAfterAntifraud" => 1800,
        "delayToCancel" => 21600,
        "maxValue" => 1000,
    ];

    private static $creditCardPaymentDeniedResponse = [
        "status" => "denied",
        "authorizationId" => null,
        "tid" => "TID-7B58BE1A08",
        "code" => "OperationDeniedCode",
        "message" => "Credit card payment denied"
    ];

    private static $creditCardPaymentProcessing = [
        "status" => "undefined",
        "tid" => "TID-7B58BE1A08",
    ];


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

    public function createPayment($request): array
    {
        $creditCardNumber = $request->card()->cardNumber();
        $creditCardIsValid = $this->validateCreditCard($creditCardNumber);

        if ($creditCardIsValid) {
            return self::$creditCardPaymentApprovedResponse;
        } elseif ($creditCardNumber === "4222222222222225") {
            return self::$creditCardPaymentProcessing;
        } else {
            return self::$creditCardPaymentDeniedResponse;
        }
    }

    /**
     * validates credit card with Luhn algorithm
     * https://stackoverflow.com/questions/174730/what-is-the-best-way-to-validate-a-credit-card-in-php
     *
     * @param [string] $creditCardNumber
     * @return boolean
     */
    private function validateCreditCard(string $creditCardNumber): bool
    {
        // Strip any non-digits (useful for credit card numbers with spaces and hyphens)
        $number = preg_replace('/\D/', '', $creditCardNumber);

        // Set the string length and parity
        $number_length = strlen($number);
        $parity = $number_length % 2;

        // Loop through each digit and do the maths
        $total = 0;
        for ($i = 0; $i < $number_length; $i++) {
            $digit = $number[$i];
            // Multiply alternate digits by two
            if ($i % 2 == $parity) {
                $digit *= 2;
                // If the sum is two digits, add them together (in effect)
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            // Total up the digits
            $total += $digit;
        }

        // If the total mod 10 equals 0, the number is valid
        return ($total % 10 == 0) ? true : false;
    }
}
