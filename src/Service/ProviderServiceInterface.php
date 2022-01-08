<?php

namespace PhpConnector\Service;

use PhpConnector\Model\RefundRequest;
use PhpConnector\Model\RefundResponse;
interface ProviderServiceInterface
{
    public function processRefund(RefundRequest $request): RefundResponse;

    public function processCancellation(array $requestData): array;

    public function processCapture(array $requestData): array;

    public function createPayment($request): array;
}
