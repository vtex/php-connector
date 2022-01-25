<?php

namespace PhpConnector\Model;

class CaptureResponse
{
    private $paymentId;
    private $settleId;
    private $value;
    private $code;
    private $message;
    private $requestId;

    public function __construct(
        string $paymentId,
        ?string $settleId,
        float $value,
        ?string $code,
        ?string $message,
        ?string $requestId,
        int $responseCode
    ) {
        $this->paymentId = $paymentId;
        $this->settleId = $settleId;
        $this->value = $value;
        $this->code = $code;
        $this->message = $message;
        $this->requestId = $requestId;
        $this->responseCode = $responseCode;
    }

    public static function approved(CaptureRequest $request, $settleId, $code = null): self
    {
        return new self(
            $request->paymentId(),
            $settleId,
            $request->value(),
            $code,
            "Successfully settled",
            $request->requestId(),
            200
        );
    }

    public static function denied(CaptureRequest $request, $code = null): self
    {
        return new self(
            $request->paymentId(),
            null,
            0,
            $code,
            "unable to settle request",
            $request->requestId(),
            200
        );
    }

    public function asArray(): array
    {
        $formattedResponse = [
            "paymentId" => $this->paymentId,
            "settleId" => $this->settleId,
            "value" => $this->settleId ? number_format($this->value, 2, '.', '') : 0,
            "requestId" => $this->requestId,
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
