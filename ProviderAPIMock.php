<?php

require("ProviderAPIInterface.php");

class ProviderAPIMock implements ProviderAPIInterface
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
        "paymentId" => "01693EB95BE443AC85874E395CD91565",
        "status" => "approved",
        "authorizationId" => "AUT-09DC5E8F03",
        "tid" => "TID-7B58BE1A08",
        "nsu" => "NSU-107521E866",
        "acquirer" => "TestPay",
        "code" => "200",
        "message" => null,
        "delayToAutoSettle" => 21600,
        "delayToAutoSettleAfterAntifraud" => 1800,
        "delayToCancel" => 21600,
        "maxValue" => 1000,
    ];

    private static $creditCardPaymentDeniedResponse = [
        "paymentId" => "01693EB95BE443AC85874E395CD91565",
        "status" => "denied",
        "authorizationId" => null,
        "tid" => null,
        "nsu" => null,
        "acquirer" => null,
        "code" => "200",
        "message" => "Credit card payment denied",
        "delayToAutoSettle" => 21600,
        "delayToAutoSettleAfterAntifraud" => 1800,
        "delayToCancel" => 21600,
        "maxValue" => 1000,
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
            throw new Exception('Cannot refund', 500);
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
        $creditCardIsValid = $this->validateCreditCard($request->card()->cardNumber());

        if ($creditCardIsValid) {
            return self::$creditCardPaymentApprovedResponse;
        } else {
            return self::$creditCardPaymentDeniedResponse;
        }
    }

    private function validateCreditCard($creditCardNumber): bool
    {
        $cards = array(
            "visa" => "(4\d{12}(?:\d{3})?)",
            "amex" => "(3[47]\d{13})",
            "jcb" => "(35[2-8][89]\d\d\d{10})",
            "maestro" => "((?:5020|5038|6304|6579|6761)\d{12}(?:\d\d)?)",
            "solo" => "((?:6334|6767)\d{12}(?:\d\d)?\d?)",
            "mastercard" => "(5[1-5]\d{14})",
            "switch" => "(?:(?:(?:4903|4905|4911|4936|6333|6759)\d{12})|(?:(?:564182|633110)\d{10})(\d\d)?\d?)",
        );
        $names = array("Visa", "American Express", "JCB", "Maestro", "Solo", "Mastercard", "Switch");
        $matches = array();
        $pattern = "#^(?:" . implode("|", $cards) . ")$#";
        $result = preg_match($pattern, str_replace(" ", "", $creditCardNumber), $matches);

        return ($result > 0) ? $names[sizeof($matches) - 2] : false;
    }
}
