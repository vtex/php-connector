<?php

/**
 * Refund Request Class validates that the request body contains all the necessary parameters
 * with the expected type.
 * To improve: name class properly - Which pattern is this?
 */
class RefundRequest
{
    private $refundId;
    private $settleId;
    private $paymentId;
    private $tid;
    private $value;
    private $transactionId;
    private $recipients;

    public function __construct(
        string $refundId,
        string $settleId,
        string $paymentId,
        string $tid,
        float $value,
        string $transactionId,
        ?array $recipients
    ) {
        $this->refundId = $refundId;
        $this->settleId = $settleId;
        $this->paymentId = $paymentId;
        $this->tid = $tid;
        $this->value = $value;
        $this->transactionId = $transactionId;
        $this->recipients = $recipients;
    }

    public function toArray(): array
    {
        return [];
    }
}
