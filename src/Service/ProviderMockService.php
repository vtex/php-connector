<?php

namespace PhpConnector\Service;

use PhpConnector\Model\CreatePaymentRequest;
use PhpConnector\Model\CreatePaymentResponse;
use PhpConnector\Model\CancellationRequest;
use PhpConnector\Model\CancellationResponse;
use PhpConnector\Model\CaptureRequest;
use PhpConnector\Model\CaptureResponse;
use PhpConnector\Model\RefundRequest;
use PhpConnector\Model\RefundResponse;

class ProviderMockService implements ProviderServiceInterface
{
    private $creditCardFlow = [
        '4444333322221111' => 'authorizePayment',
        '4444333322221112' => 'denyPayment',
        '4222222222222224' => 'asyncApprove',
        '4222222222222225' => 'asyncDeny',
    ];

    private $isTestRequest;

    public function __construct(bool $isTestRequest)
    {
        $this->isTestRequest = $isTestRequest;
    }

    public function createPayment(CreatePaymentRequest $request): CreatePaymentResponse
    {
        if ($this->isTestRequest && $request->isCreditCardPayment()) {
            $creditCardNumber = $request->card()->cardNumber();
            $flow = $this->creditCardFlow[$creditCardNumber];
            return $this->$flow($request);
        } else {
            throw new \Exception("Not implemented", 501);
        }
    }

    private function authorizePayment(CreatePaymentRequest $request): CreatePaymentResponse
    {
        return CreatePaymentResponse::approved(
            $request,
            bin2hex(random_bytes(10)),
            bin2hex(random_bytes(10)),
            bin2hex(random_bytes(10)),
            "TestPay"
        );
    }

    private function denyPayment(CreatePaymentRequest $request): CreatePaymentResponse
    {
        return CreatePaymentResponse::denied(
            $request,
            bin2hex(random_bytes(10))
        );
    }

    private function asyncApprove(CreatePaymentRequest $request): CreatePaymentResponse
    {
        return CreatePaymentResponse::pending(
            $request,
            bin2hex(random_bytes(10)),
            $this->authorizePayment($request),
        );
    }


    public function processCancellation(CancellationRequest $request): CancellationResponse
    {
        $cancellationId = $this->nextCancellationId();

        if (!is_null($request->requestId())) {
            return CancellationResponse::notSupported($request);
        }

        return CancellationResponse::approved($request, $cancellationId);
    }

    private function nextCancellationId(): string
    {
        return bin2hex(random_bytes(10));
    }

    public function processCapture(CaptureRequest $request): CaptureResponse
    {
        $captureId = $this->nextCaptureId();

        return CaptureResponse::approved($request, $captureId);
    }

    private function nextCaptureId(): string
    {
        return bin2hex(random_bytes(10));
    }

    /**
     * This functions should do some checks on the request e.g.:
     * check if the request is valid, confirm that is settled,
     * confirm that the value is less than or equal to original settlement value
     */
    public function processRefund(RefundRequest $request): RefundResponse
    {
        if ($request->value() > 100 && $request->value() < 1000) {
            $refundId = $this->nextRefundId();
            return RefundResponse::approved($request, $refundId);
        } elseif ($request->value() > 1000) {
            return RefundResponse::denied($request);
        } else {
            return RefundResponse::manual($request);
        }
    }

    private function nextRefundId(): string
    {
        return bin2hex(random_bytes(10));
    }
}
