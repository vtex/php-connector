<?php

require 'vendor/autoload.php';

use PhpConnector\Connector;
use PhpConnector\Service\ProviderMockService;

// handle requests by verb and path
$verb = $_SERVER['REQUEST_METHOD'];
$urlPieces = explode('/', $_SERVER['REQUEST_URI']);

// this does not work for 'inbound-requests/:action'
$path = end($urlPieces);

$headers = getallheaders();

set_exception_handler(function ($e) {
    $code = $e->getCode() ?: 400;
    header("Content-Type: application/json", false, $code);
    echo json_encode(["error" => $e->getMessage()]);
    exit;
});


$headers = array_change_key_case($headers, CASE_UPPER);
// test suite is sending headers different from expected, should be X-VTEX-API-AppKey &
// X-VTEX-API-AppToken
$credentials = [
    "key" => $headers["X-VTEX-API-APPKEY"] ?? null,
    "token" => $headers["X-VTEX-API-APPTOKEN"] ?? null
];

error_log("appKey: {$credentials["key"]}");
error_log("appToken: {$credentials["token"]}");

$clientIsTestSuite = false;

if (isset($headers["X-VTEX-API-IS-TESTSUITE"]) && $headers["X-VTEX-API-IS-TESTSUITE"] === 'true') {
    $clientIsTestSuite = true;
}

$providerService = new ProviderMockService($clientIsTestSuite);
$connector = new Connector($providerService, $credentials);

if ($verb === 'GET') {
    switch ($path) {
        case 'payment-methods':
            $response = $connector->listPaymentMethodsAction();
            break;

        case 'manifest':
            $response = $connector->listPaymentProviderManifestAction();
            break;

        default:
            throw new Exception('Unknown endpoint', 404);
    }
} elseif ($verb === 'POST') {
    switch ($path) {
        case 'payments':
            $requestBody = json_decode(file_get_contents('php://input'), true);
            $connector->createPaymentAction($requestBody);
            break;

        case 'cancellations':
            $requestBody = json_decode(file_get_contents('php://input'), true);
            $connector->cancelPaymentAction($requestBody);
            break;

        case 'settlements':
            $requestBody = json_decode(file_get_contents('php://input'), true);
            $connector->capturePaymentAction($requestBody);
            break;

        case 'refunds':
            $requestBody = json_decode(file_get_contents('php://input'), true);
            $connector->refundPaymentAction($requestBody);
            break;

        default:
            throw new Exception('Unknown endpoint', 404);
    }
} else {
    throw new Exception('Method Not Supported', 405);
}
