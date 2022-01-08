<?php

require 'vendor/autoload.php';

use PhpConnector\Connector;
use PhpConnector\Service\ProviderMockService;

// handle requests by verb and path
$verb = $_SERVER['REQUEST_METHOD'];
$urlPieces = explode('/', $_SERVER['REQUEST_URI']);

// this does not work for 'inbound-requests/:action'
$path = end($urlPieces);

/* question: should we ensure Content-Type: application/json and Accept: application/json
 * for all endpoints?
 * What does it mean that X-VTEX-API headers are "optional configuration"?
 * Checked: vtex headers are not sent on gets
 */
$headers = getallheaders();

// test suite is sending headers different from expected, should be X-VTEX-API-AppKey &
// X-VTEX-API-AppToken
$credentials = [
    "key" => $headers["X-Vtex-Api-Appkey"] ?? null,
    "token" => $headers["X-Vtex-Api-Apptoken"] ?? null
];

$isTestRequest = false;

if (isset($headers["X-Vtex-Api-Is-Testsuite"]) && $headers["X-Vtex-Api-Is-Testsuite"] === 'true') {
    $isTestRequest = true;
}

set_exception_handler(function ($e) {
	$code = $e->getCode() ?: 400;
	header("Content-Type: application/json", false, $code);
	echo json_encode(["error" => $e->getMessage()]);
	exit;
});

$providerService = new ProviderMockService;
$connector = new Connector($isTestRequest, $providerService);

if ($verb === 'GET') {
    switch ($path) {
        case 'payment-methods':
            $response = $connector->listPaymentMethodsAction();
            break;

        case 'manifest':
            $response = $connector->listPaymentProviderManifest();
            break;

        default:
            throw new Exception('Unknown endpoint', 404);
    }
} elseif ($verb === 'POST') {
    switch ($path) {
        case 'payments':
            $requestBody = json_decode(file_get_contents('php://input'), true);
            $response = $connector->createPayment($requestBody);
            break;

        case 'cancellations':
            $requestBody = json_decode(file_get_contents('php://input'), true);
            $cancellationResponse = $connector->cancelPayment($requestBody);
            $responseCode = $cancellationResponse["responseCode"];
            http_response_code($responseCode);
            $response = $cancellationResponse["responseData"];
            break;

        case 'settlements':
            $requestBody = json_decode(file_get_contents('php://input'), true);
            $refundResponse = $connector->capturePayment($requestBody);
            $responseCode = $refundResponse["responseCode"];
            http_response_code($responseCode);
            $response = $refundResponse["responseData"];
            break;

        case 'refunds':
            $requestBody = json_decode(file_get_contents('php://input'), true);
            $refundResponse = $connector->refundPayment($requestBody);
            $responseCode = $refundResponse["responseCode"];
            http_response_code($responseCode);
            $response = $refundResponse["responseData"];
            break;

        default:
            throw new Exception('Unknown endpoint', 404);
    }
} else {
    throw new Exception('Method Not Supported', 405);
}

header("Content-Type: application/json");
header("Accept: application/json");
echo json_encode($response);


// this could be better, but it's very hard to set async with php
if (isset($response["status"]) && $response["status"] === 'undefined') {
    $connector->retry($requestBody, $credentials);
}