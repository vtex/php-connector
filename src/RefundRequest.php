<?php

namespace PhpConnector;

/**
 * Refund Request Class validates that the request body contains all the necessary parameters
 * with the expected type.
 * To-do: check if tid is really mandatory as documentation request params says. (Example doesn't show it)
 */
class RefundRequest
{
    private $requestId;
    private $settleId;
    private $paymentId;
    private $tid;
    private $value;
    private $transactionId;
    private $recipients;
    private $sandboxMode;

    public function __construct(
        string $requestId,
        string $settleId,
        string $paymentId,
        ?string $tid,
        float $value,
        string $transactionId,
        ?array $recipients,
        ?bool $sandboxMode
    ) {
        $this->requestId = $requestId;
        $this->settleId = $settleId;
        $this->paymentId = $paymentId;
        $this->tid = $tid;
        $this->value = $value;
        $this->transactionId = $transactionId;
        $this->recipients = $recipients;
        $this->sandboxMode = $sandboxMode ?? false;
    }

    public function requestId(): string
    {
        return $this->requestId;
    }

    public function settleId(): string
    {
        return $this->settleId;
    }

    public function paymentId(): string
    {
        return $this->paymentId;
    }

    public function tid(): ?string
    {
        return $this->tid;
    }

    public function value(): float
    {
        return $this->value;
    }

    public function transactionId(): string
    {
        return $this->transactionId;
    }

    public function recipients(): ?array
    {
        return $this->recipients;
    }

    public function sandboxMode(): bool
    {
        return $this->sandboxMode;
    }

    public function toArray(): array
    {
        return [
            "requestId" => $this->requestId,
            "settleId" => $this->settleId,
            "paymentId" => $this->paymentId,
            "tid" => $this->tid,
            "value" => $this->value,
            "transactionId" => $this->transactionId,
            "recipients" => $this->recipients,
            "sandboxMode" => $this->sandboxMode,
        ];
    }
}
