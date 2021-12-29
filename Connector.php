<?php

require("CaptureRequest.php");
require("CancellationRequest.php");
require("RefundRequest.php");
class Connector
{
    private $providerAPI = null;

    public function __construct(ProviderAPIInterface $providerAPI)
    {
        $this->providerAPI = $providerAPI;
    }

    function listPaymentMethods(): string
    {
        return json_encode([
            "paymentMethods" => [
                "Visa",
                "Mastercard",
                "American Express",
            ]
        ]);
    }

    // to test: are customFields and autoSettleDelay mandatory?
    function listPaymentProviderManifest(): string
    {
        return json_encode([
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
            ],
            "customFields" => [
                [
                    "name" => "Merchant's custom field",
                    "type" => "text"
                ],
                [
                    "name" => "Merchant's custom select field",
                    "type" => "select",
                    "options" => [
                        [
                            "text" => "Field option 1",
                            "value" => "1"
                        ],
                        [
                            "text" => "Field option 2",
                            "value" => "2"
                        ],
                        [
                            "text" => "Field option 3",
                            "value" => "3"
                        ]
                    ]
                ]
            ],
            "autoSettleDelay" => [
                "minimum" => "0",
                "maximum" => "720"
            ]
        ]);
    }

    /**
     * This function validates the request body and reaches out to the provider to process
     * the refund request and return the formatted response body.
     *
     * @param array $requestBody
     * @return string
     */
    function refundPayment(array $requestBody): array
    {
        try {
            $request = new RefundRequest(
                $requestBody['requestId'],
                $requestBody['settleId'],
                $requestBody['paymentId'],
                $requestBody['tid'],
                (float) $requestBody['value'],
                $requestBody['transactionId'],
                $requestBody['recipients'],
                $requestBody['sandboxMode']
            );
        } catch (\Throwable $th) {
            throw new Exception('Invalid Request Body', 400);
        }

        // assuming that provider expects an array as input
        $requestAsArray = $request->toArray();

        // call provider to process the request
        $providerResponseArray = $this->providerAPI->processRefund($requestAsArray);

        // format response according to PPP definitions
        $formattedResponse = [
            "paymentId" => $request->paymentId(),
            "requestId" => $request->requestId(),
            "refundId" => $providerResponseArray["refundId"],
            "value" => $providerResponseArray["value"],
        ];

        if (!is_null($providerResponseArray["code"])) {
            $formattedResponse["code"] = $providerResponseArray["code"];
        }

        if (!is_null($providerResponseArray["message"])) {
            $formattedResponse["message"] = $providerResponseArray["message"];
        }

        return [
            "responseCode" => $providerResponseArray["responseCode"],
            "responseData" => json_encode($formattedResponse)
        ];

    }

    public function cancelPayment(array $requestBody): array
    {
        try {
            $request = new CancellationRequest(
                $requestBody['paymentId'],
                $requestBody['requestId'],
                $requestBody['authorizationId'],
                $requestBody['sandboxMode']
            );
        } catch (\Throwable $th) {
            throw new Exception('Invalid Request Body', 400);
        }

        // format request info according to provider definition
        $requestAsArray = $request->toArray();

        // call provider to process the request
        $providerResponseArray = $this->providerAPI->processCancellation($requestAsArray);

        // format response according to PPP definitions
        $formattedResponse = [
            "paymentId" => $request->paymentId(),
            "requestId" => $request->requestId(),
            "cancellationId" => $providerResponseArray["cancellationId"],
        ];

        if (!is_null($providerResponseArray["code"])) {
            $formattedResponse["code"] = $providerResponseArray["code"];
        }

        if (!is_null($providerResponseArray["message"])) {
            $formattedResponse["message"] = $providerResponseArray["message"];
        }

        return [
            "responseCode" => $providerResponseArray["responseCode"],
            "responseData" => json_encode($formattedResponse)
        ];
    }

    public function capturePayment(array $requestBody): array
    {
        try {
            $request = new CaptureRequest(
                $requestBody['transactionId'],
                $requestBody['requestId'],
                $requestBody['paymentId'],
                (float) $requestBody['value'],
                $requestBody['authorizationId'],
                $requestBody['tid'],
                $requestBody['recipients'],
                $requestBody['sandboxMode']
            );
        } catch (\Throwable $th) {
            throw new Exception('Invalid Request Body', 400);
        }

        // assuming that provider expects an array as input
        $requestAsArray = $request->toArray();

        // call provider to process the request
        $providerResponseArray = $this->providerAPI->processCapture($requestAsArray);

        // format response according to PPP definitions
        $formattedResponse = [
            "paymentId" => $request->paymentId(),
            "requestId" => $request->requestId(),
            "settleId" => $providerResponseArray["settleId"],
            "value" => $providerResponseArray["value"],
        ];

        if (!is_null($providerResponseArray["code"])) {
            $formattedResponse["code"] = $providerResponseArray["code"];
        }

        if (!is_null($providerResponseArray["message"])) {
            $formattedResponse["message"] = $providerResponseArray["message"];
        }

        return [
            "responseCode" => $providerResponseArray["responseCode"],
            "responseData" => json_encode($formattedResponse)
        ];
    }
}
