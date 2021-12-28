<?php

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
     * Validates the request Body for a refund and process the refund
     *
     * @param array $requestBody
     * @return string
     */
    function refundPayment(array $requestBody): string
    {
        $refundRequest = new RefundRequest(
            $requestBody['requestId'],
            $requestBody['settleId'],
            $requestBody['paymentId'],
            $requestBody['tid'],
            (float) $requestBody['value'],
            $requestBody['transactionId'],
            $requestBody['recipients'],
            $requestBody['sandboxMode']
        );

        $response = $this->processRefund($refundRequest);


        return json_encode($response);
    }

    /**
     * This function should reach out to the provider to process the refund request
     * and return the formatted response body.
     * @param RefundRequest $request
     * @return array
     */
    private function processRefund(RefundRequest $request): array
    {
        // format request info according to provider definition
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

        return $formattedResponse;
    }
}
