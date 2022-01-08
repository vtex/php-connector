<?php

namespace PhpConnector\Service;

use PhpConnector\Model\RefundRequest;
use PhpConnector\Model\RefundResponse;
use PhpConnector\Model\CancellationRequest;
use PhpConnector\Model\CancellationResponse;

interface ProviderServiceInterface
{
    public function processRefund(RefundRequest $request): RefundResponse;

    public function processCancellation(CancellationRequest $request): CancellationResponse;

    public function processCapture(array $requestData): array;

    public function createPayment($request): array;
}
