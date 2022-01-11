<?php

namespace PhpConnector\Model;

use PhpConnector\Model\CreatePaymentRequest;

class CreatePaymentResponse
{

    private static $creditCardPaymentDeniedResponse = [
        "status" => "denied",
        "authorizationId" => null,
        "tid" => "TID-7B58BE1A08",
        "code" => "OperationDeniedCode",
        "message" => "Credit card payment denied"
    ];

    private static $creditCardPaymentProcessing = [
        "status" => "undefined",
        "tid" => "TID-7B58BE1A08",
    ];

    private static $creditCardPaymentApprovedResponse = [
        "status" => "approved",
        "authorizationId" => "AUT-09DC5E8F03",
        "tid" => "TID-7B58BE1A08",
        "nsu" => "NSU-107521E866",
        "acquirer" => "TestPay",
        "code" => "OperationApprovedCode",
        "message" => null,
        "delayToAutoSettle" => 21600,
        "delayToAutoSettleAfterAntifraud" => 1800,
        "delayToCancel" => 21600,
        "maxValue" => 1000,
    ];

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

    public function __construct(
        ?string $paymentId,
        string $status,
        ?string $authorizationId,
        string $tid,
        ?string $nsu,
        ?string $acquirer,
        ?string $code,
        ?string $message,
        ?int $delayToAutoSettle,
        ?int $delayToAutoSettleAfterAntiFraud,
        ?int $delayToCancel,
        ?float $maxValue
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
    }

    public static function approved(CreatePaymentRequest $request, $status, $authorizationId, $tid, $nsu, $acquirer): self
    {
        return new self(
            $request->paymentId(),
            $status,
            $authorizationId,
            $tid,
            $nsu,
            $acquirer,
            "OperationApprovedCode",
            "Approved",
            21600,
            1800,
            21600,
            1000
        );
    }

    public function asArray(): array
    {
        if ($this->status === 'approved') {
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
        } elseif ($this->status === 'denied') {
            $formattedResponse = [
                "paymentId" => $this->paymentId,
                "status" => $this->status,
                "tid" => $this->tid,
                "code" => $this->code,
                "message" => $this->message,
            ];
        } else {
            $formattedResponse = [
                "paymentId" => $this->paymentId,
                "status" => $this->status,
                "tid" => $this->tid,
            ];
        }

        return $formattedResponse;
    }

    public function responseCode(): int
    {
        return 200;
    }
}
