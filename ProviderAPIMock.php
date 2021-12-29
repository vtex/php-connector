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
    public function processRefund($requestArray): array
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


    public function processCancellation($requestArray): array
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
}
