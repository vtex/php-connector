<?php

/**
 * Payment Request Class validates that the request body contains all the necessary
 * parameters with the expected type.
 */
class PaymentRequest
{
    private $reference;
    private $orderId;
    private $shopperInteraction;
    private $verificationOnly;
    private $transactionId;
    private $paymentId;
    private $paymentMethod;
    private $paymentMethodCustomCode; // not fully camel case on documentation
    private $merchantName;
    private $value;
    private $currency;
    private $installments;
    private $installmentsInterestRate; // typo on documentation key
    private $installmentsValue;
    private $deviceFingerprint;
    private $ipAddress;
    private $card;
    private $shippingValue;
    private $taxValue;
    private $buyer;
    private $shippingAddress;
    private $billingAddress;
    private $items;
    private $recipients; // why docs says this argument is optional? does it apply only to split payments?
    private $merchantSettings; // check if merchantName is same as here is Settings
    private $url;
    private $inboundRequestUrl;
    private $secureProxyUrl;
    private $sandboxMode;
    private $totalCartValue;
    private $callbackUrl;
    private $returnUrl;


    public function __construct(
        string $reference,
        string $orderId,
        string $shopperInteraction,
        ?bool $verificationOnly,
        string $transactionId,
        string $paymentId,
        string $paymentMethod,
        ?string $paymentMethodCustomCode, // mandatory only for co-branded and private labels
        string $merchantName,
        float $value,
        string $currency,
        int $installments, // documentation says float, but could there be 2.5 installments?
        float $installmentsInterestRate,
        float $installmentsValue,
        string $deviceFingerprint,
        string $ipAddress,
        Card $card,
        float $shippingValue,
        float $taxValue,
        Buyer $buyer,
        Address $shippingAddress,
        Address $billingAddress,
        array $items,
        ?array $recipients,
        array $merchantSettings,
        string $url,
        ?string $inboundRequestUrl,
        ?string $secureProxyUrl,
        ?bool $sandboxMode,
        float $totalCartValue,
        string $callbackUrl,
        string $returnUrl
    ) {
        $this->reference = $reference;
        $this->orderId = $orderId;
        $this->shopperInteraction = $shopperInteraction;
        $this->verificationOnly = $verificationOnly;
        $this->transactionId = $transactionId;
        $this->paymentId = $paymentId;
        $this->paymentMethod = $paymentMethod;
        $this->paymentMethodCustomCode = $paymentMethodCustomCode;
        $this->merchantName = $merchantName;
        $this->value = $value;
        $this->currency = $currency;
        $this->installments = $installments;
        $this->installmentsInterestRate = $installmentsInterestRate;
        $this->installmentsValue = $installmentsValue;
        $this->deviceFingerprint = $deviceFingerprint;
        $this->ipAddress = $ipAddress;
        $this->card = $card;
        $this->shippingValue = $shippingValue;
        $this->taxValue = $taxValue;
        $this->buyer = $buyer;
        $this->shippingAddress = $shippingAddress;
        $this->billingAddress = $billingAddress;
        $this->items = $items;
        $this->recipients = $recipients;
        $this->merchantSettings = $merchantSettings;
        $this->url = $url;
        $this->inboundRequestUrl = $inboundRequestUrl;
        $this->secureProxyUrl = $secureProxyUrl;
        $this->sandboxMode = $sandboxMode ?? false;
        $this->totalCartValue = $totalCartValue;
        $this->callbackUrl = $callbackUrl;
        $this->returnUrl = $returnUrl;
    }
}
