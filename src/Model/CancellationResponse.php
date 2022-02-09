<?php

namespace PhpConnector\Model;

use PhpConnector\Model\CancellationRequest;

/**
 * CancellationResponse class provides different constructors to initialize
 * the cancellation response. It also provides a method to format the response accordingly.
 */
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

    /**
     * Formats the response properties as expected by the PPP specification
     *
     * @return array
     */
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
