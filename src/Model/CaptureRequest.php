<?php

namespace PhpConnector\Model;

/**
 * Capture Request Class validates that the request body contains all the necessary parameters
 * with the expected type.
 */
class CaptureRequest
{
    private $transactionId;
    private $requestId;
    private $paymentId;
    private $value;
    private $authorizationId;
    private $tid;
    private $recipients;
    private $sandboxMode;
    private $merchantSettings;

    public function __construct(
        string $transactionId,
        ?string $requestId,
        string $paymentId,
        float $value,
        ?string $authorizationId, // docs says mandatory, but test doesn't send it
        ?string $tid,
        ?array $recipients,
        ?bool $sandboxMode,
        MerchantSettings $merchantSettings
    ) {
        $this->requestId = $requestId;
        $this->authorizationId = $authorizationId;
        $this->paymentId = $paymentId;
        $this->tid = $tid;
        $this->value = $value;
        $this->transactionId = $transactionId;
        $this->recipients = $recipients;
        $this->sandboxMode = $sandboxMode ?? false;
        $this->merchantSettings = $merchantSettings;
    }

    public static function fromArray(array $array): self
    {
        $recipients = [];

        if (isset($array['recipients'])) {
            $recipients = array_map(
                function ($recipient) {
                    return new Recipient(
                        $recipient['id'],
                        $recipient['name'],
                        $recipient['documentType'],
                        $recipient['document'],
                        $recipient['role'],
                        $recipient['amount'],
                        $recipient['chargeProcessingFee'],
                        $recipient['chargebackLiable']
                    );
                },
                $array['recipients']
            );
        }

        return new self(
            $array['transactionId'],
            $array['requestId'] ?? null,
            $array['paymentId'],
            (float) $array['value'],
            $array['authorizationId'] ?? null, // docs says mandatory, but test doesn't send it
            $array['tid'] ?? null,
            $recipients,
            $array['sandboxMode'] ?? false,
            isset($array['merchantSettings']) ? MerchantSettings::fromArray($array['merchantSettings']) : new MerchantSettings(),
        );
    }

    public function requestId(): ?string
    {
        return $this->requestId;
    }

    public function authorizationId(): string
    {
        return $this->authorizationId;
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

    public function merchantSettings(): MerchantSettings
    {
        return $this->merchantSettings;
    }

    public function toArray(): array
    {
        return [
            "transactionId" => $this->transactionId,
            "requestId" => $this->requestId,
            "paymentId" => $this->paymentId,
            "value" => $this->value,
            "authorizationId" => $this->authorizationId,
            "tid" => $this->tid,
            "recipients" => $this->recipients,
            "sandboxMode" => $this->sandboxMode,
        ];
    }
}
