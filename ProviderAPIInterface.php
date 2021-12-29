<?php


interface ProviderAPIInterface
{
    public function processRefund(array $requestData): array;

    public function processCancellation(array $requestData): array;

    public function processCapture(array $requestData): array;
}
