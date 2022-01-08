<?php

namespace PhpConnector\Model;

use PhpConnector\Model\CancellationRequest;

class CancellationResponse
{
    private $paymentId;
    private $message;
    private $code;
    private $cancellationId;
    private $requestId;

    public function __construct(
        string $paymentId,
        ?string $message,
        ?string $code,
        ?string $cancellationId,
        string $requestId,
        int $responseCode
    ) {
        $this->paymentId = $paymentId;
        $this->message = $message;
        $this->code = $code;
        $this->cancellationId = $cancellationId;
        $this->requestId = $requestId;
        $this->responseCode = $responseCode;
    }

    public static function approved(CancellationRequest $request, $cancellationId, $code = null): self
    {
        return new self(
            $request->paymentId(),
            "Successfully cancelled",
            $code,
            $cancellationId,
            $request->requestId(),
            200
        );
    }

    public static function notSupported(CancellationRequest $request): self
    {
        return new self(
            $request->paymentId(),
            "This payment needs to be manually cancelled",
            "cancel-manually",
            null,
            $request->requestId(),
            501
        );
    }

    public function asArray(): array
    {
        $formattedResponse = [
            "paymentId" => $this->paymentId,
            "cancellationId" => $this->cancellationId,
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
