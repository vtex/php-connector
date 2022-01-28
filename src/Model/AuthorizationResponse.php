<?php

namespace PhpConnector\Model;


class AuthorizationResponse
{
    private $paymentId;
    private $status;
    private $authorizationId;
    private $tid;
    private $nsu;
    private $acquirer;
    private $code;
    private $message;
    private $delayToAutoSettle;
    private $delayToAutoSettleAfterAntiFraud;
    private $delayToCancel;
    private $maxValue;
    private $retryResponse;
    private $paymentUrl;
    private $paymentMethod;

    public function __construct(
        string $status,
        string $tid,
        ?string $paymentId,
        ?self $retryResponse = null,
        ?string $code = null,
        ?string $message = null,
        ?string $paymentUrl = null,
        ?string $paymentMethod = "creditCard",
        ?string $authorizationId = null,
        ?string $nsu = null,
        ?string $acquirer = null,
        ?int $delayToAutoSettle = null,
        ?int $delayToAutoSettleAfterAntiFraud = null,
        ?int $delayToCancel = null,
        ?float $maxValue = null
    ) {
        $this->paymentId = $paymentId;
        $this->status = $status;
        $this->authorizationId = $authorizationId;
        $this->tid = $tid;
        $this->nsu = $nsu;
        $this->acquirer = $acquirer;
        $this->code = $code;
        $this->message = $message;
        $this->delayToAutoSettle = $delayToAutoSettle;
        $this->delayToAutoSettleAfterAntiFraud = $delayToAutoSettleAfterAntiFraud;
        $this->delayToCancel = $delayToCancel;
        $this->maxValue = $maxValue;
        $this->paymentUrl = $paymentUrl;
        $this->paymentMethod = $paymentMethod;
        $this->retryResponse = $retryResponse;
    }

    public static function approved(
        string $paymentId,
        string $authorizationId,
        string $tid,
        string $nsu,
        string $acquirer,
        int $delayToAutoSettle
    ): self
    {
        return new self(
            "approved",
            $tid,
            $paymentId,
            null,
            "OperationApprovedCode",
            "Approved",
            null,
            "creditCard",
            $authorizationId,
            $nsu,
            $acquirer,
            $delayToAutoSettle,
            1800,
            21600,
            null,
        );
    }

    public static function denied(string $paymentId, string $tid): self
    {
        return new self(
            "denied",
            $tid,
            $paymentId,
            null,
            "OperationDeniedCode ",
            "Credit card payment denied",
        );
    }

    public static function pending($paymentId, $tid, $retryResponse): self
    {
        return new self(
            "undefined",
            $tid,
            $paymentId,
            $retryResponse
        );
    }

    public static function redirect(string $paymentId, string $tid, self $retryResponse): self
    {

        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http';
        $paymentURL = "{$protocol}://{$_SERVER['SERVER_NAME']}/installments.php?paymentId={$paymentId}";

        return new self(
            "undefined",
            $tid,
            $paymentId,
            $retryResponse,
            null,
            null,
            $paymentURL,
            "myRedirectPaymentMethod"
        );
    }

    public static function approvedRedirect(
        string $paymentId,
        string $authorizationId,
        string $tid,
        int $delayToAutoSettle
    ): self {
        return new self(
            "approved",
            $tid,
            $paymentId,
            null,
            null,
            "Payment with custom installments approved",
            null,
            "myRedirectPaymentMethod",
            $authorizationId,
            null,
            null,
            $delayToAutoSettle,
            1800,
            21600,
            null,
        );
    }

    public function asArray(): array
    {
        if ($this->paymentMethod === "creditCard" && $this->status === "approved") {
            $formattedResponse = [
                "paymentId" => $this->paymentId,
                "status" => $this->status,
                "authorizationId" => $this->authorizationId,
                "tid" => $this->tid,
                "nsu" => $this->nsu,
                "acquirer" => $this->acquirer,
                "code" => $this->code,
                "message" => $this->message,
                "delayToAutoSettle" => $this->delayToAutoSettle,
                "delayToAutoSettleAfterAntifraud" => $this->delayToAutoSettleAfterAntiFraud,
                "delayToCancel" => $this->delayToCancel,
                "maxValue" => $this->maxValue,
            ];
        } elseif ($this->paymentMethod === "creditCard" && $this->status === 'denied') {
            $formattedResponse = [
                "paymentId" => $this->paymentId,
                "status" => $this->status,
                "tid" => $this->tid,
                "code" => $this->code,
                "message" => $this->message,
            ];
        } elseif ($this->paymentMethod === "creditCard" && $this->status === "undefined") {
            $formattedResponse = [
                "paymentId" => $this->paymentId,
                "status" => $this->status,
                "tid" => $this->tid,
            ];
        } elseif ($this->paymentMethod === "myRedirectPaymentMethod" && $this->status === "approved") {
            $formattedResponse = [
                "paymentId" => $this->paymentId,
                "status" => $this->status,
                "authorizationId" => $this->authorizationId,
                "tid" => $this->tid,
                "code" => $this->code,
                "message" => $this->message,
                "delayToAutoSettle" => $this->delayToAutoSettle,
                "delayToAutoSettleAfterAntifraud" => $this->delayToAutoSettleAfterAntiFraud,
                "delayToCancel" => $this->delayToCancel,
            ];
        } elseif ($this->paymentMethod === "myRedirectPaymentMethod" && $this->status === "undefined") {
            $formattedResponse = [
                "paymentId" => $this->paymentId,
                "status" => $this->status,
                "tid" => $this->tid,
                "paymentUrl" => $this->paymentUrl,
            ];
        }

        return $formattedResponse;
    }

    public function responseCode(): int
    {
        return 200;
    }

    public function retryResponse(): ?self
    {
        return $this->retryResponse;
    }
}
