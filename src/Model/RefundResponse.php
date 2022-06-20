<?php

namespace PhpConnector\Model;

/**
 * RefundResponse class provides different constructors to initialize
 * the refund response. It also provides a method to format the response accordingly.
 */
class RefundResponse
{
    private $paymentId;
    private $refundId;
    private $value;
    private $code;
    private $message;
    private $requestId;
    private $responseCode;

    public function __construct(
        string $paymentId,
        ?string $refundId,
        float $value,
        ?string $code,
        ?string $message,
        string $requestId,
        int $responseCode
    ) {
        $this->paymentId = $paymentId;
        $this->refundId = $refundId;
        $this->value = $value;
        $this->code = $code;
        $this->message = $message;
        $this->requestId = $requestId;
        $this->responseCode = $responseCode;
    }

    public static function approved(RefundRequest $request, $refundId, $code = null): self
    {
        return new self(
            $request->paymentId(),
            $refundId,
            $request->value(),
            $code,
            "Successfully refunded",
            $request->requestId(),
            200
        );
    }

    public static function manual(RefundRequest $request): self
    {
        return new self(
            $request->paymentId(),
            null,
            0,
            "refund-manually",
            "Refund should be done manually",
            $request->requestId(),
            501
        );
    }

    public static function denied(RefundRequest $request, $code = null): self
    {
        return new self(
            $request->paymentId(),
            null,
            0,
            $code,
            "refund denied",
            $request->requestId(),
            500
        );
    }

    /**
     * Formats the response properties as expected by the PPP specification
     *
     * @return array
     */
    public function asArray(): array
    {
        $formattedResponse = [
            "paymentId" => $this->paymentId,
            "refundId" => $this->refundId,
            "requestId" => $this->requestId,
            "value" => (float) number_format($this->value, 2, '.', ''),
        ];

        if (isset($this->code)) {
            $formattedResponse["code"] = $this->code;
        }

        if (isset($this->message)) {
            $formattedResponse["message"] = $this->message;
        }
        return $formattedResponse;
    }

    public function responseCode(): int
    {
        return $this->responseCode;
    }
}
