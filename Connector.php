<?php

// To-do: starting using namespace instead of require etc.
require("RefundRequest.php");
class Connector
{
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

    function refundPayment(array $requestBody): string
    {
        $refundRequest = new RefundRequest(
            $requestBody['requestId'],
            $requestBody['settleId'],
            $requestBody['paymentId'],
            $requestBody['tid'],
            (float) $requestBody['value'],
            $requestBody['transactionId'],
            $requestBody['recipients']
        );

        $response = $this->processRefund($refundRequest);


        return json_encode($refundRequest->toArray());
    }

    /**
     * This function process the refund request and return the proper response body
     *
     * @param RefundRequest $request
     * @return void
     */
    private function processRefund(RefundRequest $request)
    {
        // reach out to the provider API to process the refund using information from the request
        $providerResponse = $this->providerAPI->processRefund($request);

        $formattedResponse = [
            "requestId" => $request->requestId(),
            "paymentId" => $request->paymentId(),
            "responseData" => [
                "statusCode" => $providerResponse->statusCode(),
                "contentType" => $providerResponse->contentType(),
                "content" => $providerResponse->content(),
            ],
        ];

        if (isset($providerResponse->code())) {
            $formattedResponse["code"] = $providerResponse->code();
        }

        if (isset($providerResponse->message())) {
            $formattedResponse["message"] = $providerResponse->message();
        }

        return $formattedResponse;
    }
}
