<?php

namespace PhpConnector;

class ProviderMockService implements ProviderServiceInterface
{

    private static $refundNotSupportedResponse = [
        "refundId" => null,
        "value" => 0,
        "code" => "refund-manually",
        "message" => "Refund should be done manually",
        "responseCode" => 501
    ];

    private static $cancellationNotSupportedResponse = [
        "refundId" => null,
        "value" => 0,
        "code" => "cancel-manually",
        "message" => "This payment needs to be manually cancelled",
        "responseCode" => 501
    ];

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

    /**
     * This functions should do some checks on the request e.g.:
     * check if the request is valid, confirm that is settled,
     * confirm that the value is less than or equal to original settlement value
     *
     * @param array $requestArray Array containing the request information
     *      $requestArray = [
     *          "requestId" => $this->requestId,
     *          "settleId" => $this->settleId,
     *          "paymentId" => $this->paymentId,
     *          "tid" => $this->tid,
     *          "value" => $this->value,
     *          "transactionId" => $this->transactionId,
     *          "recipients" => $this->recipients,
     *          sandboxMode" => $this->sandboxMode,
     *      ]
     * @return array
     */
    public function processRefund(array $requestArray): array
    {
        if ($requestArray["value"] > 100) {
            try {
                return $this->refundInFull($requestArray);
            } catch (\Throwable $th) {;
                return [
                    "refundId" => null,
                    "value" => 0,
                    "code" => $th->getCode(),
                    "message" => "Refund has failed due to an internal error",
                    "responseCode" => 500
                ];
            }
        } else {
            return self::$refundNotSupportedResponse;
        }
    }

    private function nextRefundId(): string
    {
        return bin2hex(random_bytes(10));
    }

    private function refundInFull($requestArray): array
    {
        $refundId = $this->nextRefundId();

        // Do some transactions to execute the refund
        if ($requestArray["value"] > 1000) {
            throw new \Exception('Cannot refund', 500);
        }

        return [
            "refundId" => $refundId,
            "value" => $requestArray["value"],
            "message" => "Successfully refunded",
            "responseCode" => 200
        ];
    }


    public function processCancellation(array $requestArray): array
    {
        $cancellationId = $this->nextCancellationId();

        if (isset($requestArray["authorizationId"])) {
            return self::$cancellationNotSupportedResponse;
        }

        return [
            "cancellationId" => $cancellationId,
            "value" => $requestArray["value"],
            "message" => "Successfully cancelled",
            "responseCode" => 200
        ];
    }

    private function nextCancellationId(): string
    {
        return bin2hex(random_bytes(10));
    }

    public function processCapture(array $requestArray): array
    {
        $captureId = $this->nextCaptureId();

        return [
            "settleId" => $captureId,
            "value" => $requestArray["value"],
            "message" => "Successfully settled",
            "responseCode" => 200
        ];
    }

    private function nextCaptureId(): string
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
