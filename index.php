<?php

require("Connector.php");

$connectorAPI = new Connector;

// handle requests by verb and path
$verb = $_SERVER['REQUEST_METHOD'];
$urlPieces = explode('/', $_SERVER['PATH_INFO']);

// this does not work for 'inbound-requests/:action'
$path = end($urlPieces);

/* question: should we ensure Content-Type: application/json and Accept: application/json
 * for all endpoints?
 * What does it mean that X-VTEX-API headers are "optional configuration"?
 */
$headers = getallheaders();

set_exception_handler(function ($e) {
	$code = $e->getCode() ?: 400;
	header("Content-Type: application/json", false, $code);
	echo json_encode(["error" => $e->getMessage()]);
	exit;
});

if ($verb === 'GET') {
    switch ($path) {
        case 'payment-methods':
            $response = $connectorAPI->listPaymentMethods();
            break;

        case 'manifest':
            $response = $connectorAPI->listPaymentProviderManifest();
            break;

        default:
            throw new Exception('Unknown endpoint', 404);
    }
} elseif ($verb === 'POST') {
    switch ($path) {
        case 'payments':
            $response = json_encode("test-payments");
            break;

        case 'cancellations':
            $response = json_encode("test cancellations");
            break;

        case 'settlements':
            $response = json_encode("test settlements");
            break;

        case 'refunds':
            $requestBody = json_decode(file_get_contents('php://input'), true);
            $response = $connectorAPI->refundPayment($requestBody);
            break;

        default:
            throw new Exception('Unknown endpoint', 404);
    }
} else {
    throw new Exception('Method Not Supported', 405);
}

header("Content-Type: application/json");
header("Accept: application/json");
echo $response;
