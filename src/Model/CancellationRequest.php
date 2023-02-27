<?php

namespace PhpConnector\Model;

/**
 * Cancel Request Class validates that the request body contains all the necessary parameters
 * with the expected type.
 *
 */
class CancellationRequest
{
    private $paymentId;
    private $requestId;
    private $authorizationId;
    private $sandboxMode;
    private $merchantSettings;


    public function __construct(
        string $paymentId,
        string $requestId,
        ?string $authorizationId,
        ?bool $sandboxMode,
        MerchantSettings $merchantSettings
    ) {
        $this->paymentId = $paymentId;
        $this->requestId = $requestId;
        $this->authorizationId = $authorizationId;
        $this->sandboxMode = $sandboxMode;
        $this->merchantSettings = $merchantSettings;
    }

    public static function fromArray(array $array): self
    {
        return new self(
            $array['paymentId'],
            $array['requestId'],
            isset($array['authorizationId']) ? $array['authorizationId'] : null,
            $array['sandboxMode'] ?? false,
            isset($array['merchantSettings']) ? MerchantSettings::fromArray($array['merchantSettings']) : new MerchantSettings(),
        );
    }

    public function paymentId(): string
    {
        return $this->paymentId;
    }

    public function requestId(): string
    {
        return $this->requestId;
    }

    public function authorizationId(): ?string
    {
        return $this->authorizationId;
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
            "paymentId" => $this->paymentId,
            "requestId" => $this->requestId,
            "authorizationId" => $this->authorizationId,
            "sandboxMode" => $this->sandboxMode,
        ];
    }
}
