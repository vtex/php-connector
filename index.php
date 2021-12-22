<?php

require("Connector.php");

$connectorAPI = new Connector;

// assume JSON, handle requests by verb and path
$verb = $_SERVER['REQUEST_METHOD'];
$urlPieces = explode('/', $_SERVER['PATH_INFO']);
//remove first empty entry of $urlPieces
array_shift($urlPieces);
$path = $urlPieces[0];

set_exception_handler(function ($e) {
	$code = $e->getCode() ?: 400;
	header("Content-Type: application/json", false, $code);
	echo json_encode(["error" => $e->getMessage()]);
	exit;
});

$validPaths = [
    "payment-methods",
    "manifest",
    "payments",
];

//check if a proper path was given
if(!in_array($path, $validPaths)) {
	throw new Exception('Unknown endpoint', 404);
}

switch($verb) {
	case 'GET':
        if($path === 'payment-methods') {
            $response = $connectorAPI->listPaymentMethods();
        } elseif ($path === 'manifest') {
            $response = $connectorAPI->listPaymentProviderManifest();
        }

		break;

	// case 'POST':
    //     // check parameters and return proper response
    //     break;
	default:
		throw new Exception('Method Not Supported', 405);
}

header("Content-Type: application/json");
header("Accept: application/json");
echo $response;
