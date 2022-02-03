<?php

namespace PhpConnector;

use PhpConnector\Service\ProviderServiceInterface;
use PhpConnector\Model\CreatePaymentRequest;
use PhpConnector\Model\CancellationRequest;
use PhpConnector\Model\CaptureRequest;
use PhpConnector\Model\RefundRequest;

class Connector
{
    private $providerService = null;
    private $credentials;

    public function __construct(ProviderServiceInterface $providerService, array $credentials)
    {
        $this->providerService = $providerService;
        $this->credentials = $credentials;
    }

    function listPaymentMethodsAction(): void
    {
        $paymentMethods = [
            "paymentMethods" => [
                "Visa",
                "Mastercard",
                "American Express",
                "myRedirectPaymentMethod"
            ]
        ];
        $this->returnWithDefaultHeaders(200, $paymentMethods);
    }

    /**
     * To-improve: TestSuit could test this endpoint.
     */
    function listPaymentProviderManifestAction(): void
    {
        $manifest = [
            "paymentMethods" => [
                [
                    "name" => "Visa",
                    "allowsSplit" => "onAuthorize"
                ],
                [
                    "name" => "Mastercard",
                    "allowsSplit" => "onCapture"
                ],
                [
                    "name" => "American Express",
                    "allowsSplit" => "disabled"
                ],
                [
                    "name" => "myRedirectPaymentMethod",
                    "allowsSplit" => "disabled"
                ]
            ],
            "customFields" => [
                [
                    "name" => "Country of operation",
                    "type" => "select",
                    "options" => [
                        [
                            "text" => "Brazil",
                            "value" => "1"
                        ],
                        [
                            "text" => "Chile",
                            "value" => "2"
                        ],
                        [
                            "text" => "Argentina",
                            "value" => "3"
                        ],
                        [
                            "text" => "Colombia",
                            "value" => "4"
                        ],
                        [
                            "text" => "Peru",
                            "value" => "5"
                        ]
                    ]
                ],
                [
                    "name" => "Type of refund",
                    "type" => "select",
                    "options" => [
                        [
                            "text" => "Automatic Whenever Possible",
                            "value" => "1"
                        ],
                        [
                            "text" => "Manual",
                            "value" => "2"
                        ]
                    ]
                ]
            ],
            "autoSettleDelay" => [
                "minimum" => "0",
                "maximum" => "360"
            ]
        ];
        $this->returnWithDefaultHeaders(200, $manifest);
    }

    public function createPaymentAction(array $requestBody): void
    {
        try {
            $request = CreatePaymentRequest::fromArray($requestBody);
        } catch (\Throwable $th) {
            throw new \Exception('Invalid Request Body', 400);
        }

        $paymentResponse = $this->providerService->authorizePayment($request);

        $this->returnWithDefaultHeaders($paymentResponse->responseCode(), $paymentResponse->asArray());

        if (!is_null($paymentResponse->retryResponse())) {
            $this->retry($request, $paymentResponse->retryResponse()->asArray());
        }
    }

    public function cancelPaymentAction(array $requestBody): void
    {
        try {
            $request = CancellationRequest::fromArray($requestBody);
        } catch (\Throwable $th) {
            throw new \Exception('Invalid Request Body', 400);
        }

        $cancellationResponse = $this->providerService->processCancellation($request);

        $this->returnWithDefaultHeaders($cancellationResponse->responseCode(), $cancellationResponse->asArray());
    }


    public function capturePaymentAction(array $requestBody): void
    {
        try {
            $request = CaptureRequest::fromArray($requestBody);
        } catch (\Throwable $th) {
            throw new \Exception('Invalid Request Body', 400);
        }

        $settlementResponse = $this->providerService->processCapture($request);

        $this->returnWithDefaultHeaders($settlementResponse->responseCode(), $settlementResponse->asArray());
    }

    function refundPaymentAction(array $requestBody): void
    {
        try {
            $request = RefundRequest::fromArray($requestBody);
        } catch (\Throwable $th) {
            throw new \Exception('Invalid Request Body', 400);
        }

        $refundResponse = $this->providerService->processRefund($request);

        $this->returnWithDefaultHeaders($refundResponse->responseCode(), $refundResponse->asArray());
    }

    private function returnWithDefaultHeaders($responseCode, $arrayData): void
    {
        http_response_code($responseCode);
        header("Content-Type: application/json");
        header("Accept: application/json");
        echo json_encode($arrayData, JSON_UNESCAPED_SLASHES);
    }

    private function retry(CreatePaymentRequest $request, $response)
    {
        $curl = curl_init();

        $payload = json_encode($response, JSON_UNESCAPED_SLASHES);
        curl_setopt_array($curl, [
            CURLOPT_URL => $request->callbackUrl(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                "Accept: application/json",
                "Content-Type: application/json",
                "X-VTEX-API-AppKey: {$this->credentials["key"]}",
                "X-VTEX-API-AppToken: {$this->credentials["token"]}"
            ],
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            error_log($error);
        }
    }
}
