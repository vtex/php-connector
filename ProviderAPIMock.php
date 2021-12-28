<?php

require("ProviderAPIInterface.php");

class ProviderAPIMock implements ProviderAPIInterface
{

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
        if ($requestArray["value"] < 100) {
            return $this->refundInFull($requestArray);
        } else {
            return $this->refundNotSupported();
        }
    }

    private function nextRefundId(): int
    {
        return 1;
    }

    private function refundInFull($requestArray): array
    {
        $refundId = $this->nextRefundId();

        // Do some transactions to execute the refund
        $refundFulfilled = true;

        // return proper response
        if ($refundFulfilled) {
            return [
                "refundId" => $refundId,
                "value" => $requestArray["value"],
                "message" => "Successfully refunded",
            ];
        } else {
            return [
                "refundId" => null,
                "value" => 0,
                "code" => 500,
                "message" => "Unable to refund payment",
            ];
        }
    }


    private function refundNotSupported()
    {
        return [
            "refundId" => null,
            "value" => 0,
            "code" => "refund-manually",
            "message" => "Refund should be done manually",
        ];
    }
}
