<?php

namespace PhpConnector\Service;

use PhpConnector\Model\CreatePaymentRequest;
use PhpConnector\Model\AuthorizationResponse;
use PhpConnector\Model\CancellationRequest;
use PhpConnector\Model\CancellationResponse;
use PhpConnector\Model\CaptureRequest;
use PhpConnector\Model\CaptureResponse;
use PhpConnector\Model\RefundRequest;
use PhpConnector\Model\RefundResponse;

interface ProviderServiceInterface
{
    public function processCancellation(CancellationRequest $request): CancellationResponse;

    public function processCapture(CaptureRequest $request): CaptureResponse;

    public function processRefund(RefundRequest $request): RefundResponse;

    public function authorizePayment(CreatePaymentRequest $request): AuthorizationResponse;
}
