<?php

require 'vendor/autoload.php';

use PhpConnector\Service\CustomInstallmentsService;
use PhpConnector\Service\ProviderMockService;

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $paymentId = $_GET["paymentId"];

    $customInstallmentsService = new CustomInstallmentsService($paymentId);

    echo $customInstallmentsService->renderHTML();
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $paymentId = $_POST["paymentId"];
    $selectedInstallments =  $_POST["installments"];

    $customInstallmentsService = new CustomInstallmentsService($paymentId);
    $customInstallmentsService->saveInstallmentsSelection($selectedInstallments);

    $providerService = new ProviderMockService();

    $providerService->authorizePaymentById($paymentId);

    $redirectUrl = $customInstallmentsService->getReturnUrl();
    header("Location: {$redirectUrl}");
    exit();
}
