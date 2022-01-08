<?php

namespace PhpConnector\Service;

interface ProviderServiceInterface
{
    public function processRefund(array $requestData): array;

    public function processCancellation(array $requestData): array;

    public function processCapture(array $requestData): array;

    public function createPayment($request): array;
}
