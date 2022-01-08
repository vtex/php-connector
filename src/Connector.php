<?php

namespace PhpConnector;

use PhpConnector\Service\ProviderServiceInterface;
use PhpConnector\Model\CancellationRequest;
use PhpConnector\Model\CaptureRequest;
use PhpConnector\Model\RefundRequest;
use PhpConnector\Model\Card;
use PhpConnector\Model\Address;
use PhpConnector\Model\Buyer;
use PhpConnector\Model\Item;
use PhpConnector\Model\Recipient;
use PhpConnector\Model\PaymentRequest;

class Connector
{
    private $isTestRequest;
    private $providerService = null;

    public function __construct(bool $isTestRequest, ProviderServiceInterface $providerService)
    {
        $this->isTestRequest = $isTestRequest;
        $this->providerService = $providerService;
    }

    function listPaymentMethodsAction(): array
    {
        return [
            "paymentMethods" => [
                "Visa",
                "Mastercard",
                "American Express",
            ]
        ];
    }

    /**
     * To-improve: TestSuit could test this endpoint.
     */
    function listPaymentProviderManifestAction(): array
    {
        return [
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
            ]
        ];
    }

    public function createPayment(array $requestBody): array
    {
        try {
            $card = new Card(
                $requestBody['card']['holder'],
                $requestBody['card']['number'],
                $requestBody['card']['csc'],
                $requestBody['card']['expiration']['month'],
                $requestBody['card']['expiration']['year'],
                $requestBody['card']['document']
            );

            // docs says 'minicart' not in camel case, but example shows like this
            $shippingAddress = new Address(
                $requestBody['miniCart']['shippingAddress']['country'],
                $requestBody['miniCart']['shippingAddress']['street'],
                $requestBody['miniCart']['shippingAddress']['number'],
                $requestBody['miniCart']['shippingAddress']['complement'],
                $requestBody['miniCart']['shippingAddress']['neighborhood'],
                $requestBody['miniCart']['shippingAddress']['postalCode'],
                $requestBody['miniCart']['shippingAddress']['city'],
                $requestBody['miniCart']['shippingAddress']['state']
            );
            $billingAddress = new Address(
                $requestBody['miniCart']['billingAddress']['country'],
                $requestBody['miniCart']['billingAddress']['street'],
                $requestBody['miniCart']['billingAddress']['number'],
                $requestBody['miniCart']['billingAddress']['complement'],
                $requestBody['miniCart']['billingAddress']['neighborhood'],
                $requestBody['miniCart']['billingAddress']['postalCode'],
                $requestBody['miniCart']['billingAddress']['city'],
                $requestBody['miniCart']['billingAddress']['state']
            );
            $buyer = new Buyer(
                $requestBody['miniCart']['buyer']['id'],
                $requestBody['miniCart']['buyer']['firstName'],
                $requestBody['miniCart']['buyer']['lastName'],
                $requestBody['miniCart']['buyer']['document'],
                $requestBody['miniCart']['buyer']['documentType'],
                $requestBody['miniCart']['buyer']['email'],
                $requestBody['miniCart']['buyer']['phone'],
                $requestBody['miniCart']['buyer']['isCorporate'],
                $requestBody['miniCart']['buyer']['corporateName'],
                $requestBody['miniCart']['buyer']['tradeName'],
                $requestBody['miniCart']['buyer']['corporateDocument'],
                $requestBody['miniCart']['buyer']['createdDate']
            );

            $items = array_map(
                function ($item) {
                    return new Item(
                        $item['id'],
                        $item['name'],
                        (float) $item['price'],
                        (int) $item['quantity'],
                        (int) $item['discount'],
                        $item['deliveryType'] ?? null,
                        $item['categoryId'] ?? null,
                        $item['sellerId'] ?? null,
                        isset($item['taxRate']) ? (float) $item['taxRate'] : null,
                        isset($item['taxValue']) ? (float) $item['taxValue'] : null,
                    );
                },
                $requestBody['miniCart']['items']
            );

            $recipients = [];

            if (isset($requestBody['recipients'])) {
                $recipients = array_map(
                    function ($recipient) {
                        return new Recipient(
                            $recipient['id'],
                            $recipient['name'],
                            $recipient['documentType'],
                            $recipient['document'],
                            $recipient['role'],
                            $recipient['amount'],
                            $recipient['chargeProcessingFee'],
                            $recipient['chargebackLiable']
                        );
                    },
                    $requestBody['recipients']
                );
            }

            $request = new PaymentRequest(
                $requestBody['reference'],
                $requestBody['orderId'],
                $requestBody['shopperInteraction'],
                $requestBody['verificationOnly'] ?? false,
                $requestBody['transactionId'],
                $requestBody['paymentId'],
                $requestBody['paymentMethod'],
                $requestBody['paymentMethodCustomCode'],
                $requestBody['merchantName'],
                (float) $requestBody['value'],
                $requestBody['currency'],
                $requestBody['installments'],
                isset($requestBody['installmentsInterestRate']) ? (float) $requestBody['installmentsInterestRate'] : null,
                isset($requestBody['installmentsValue']) ? (float) $requestBody['installmentsValue'] : null,
                $requestBody['deviceFingerprint'],
                $requestBody['ipAddress'],
                $card,
                isset($requestBody['shippingValue']) ? (float) $requestBody['shippingValue'] : null,
                isset($requestBody['taxValue']) ? (float) $requestBody['taxValue'] : null,
                $buyer,
                $shippingAddress,
                $billingAddress,
                $items,
                $recipients,
                $requestBody['merchantSettings'] ?? null,
                $requestBody['url'] ?? null,
                $requestBody['inboundRequestUrl'] ?? null,
                $requestBody['secureProxyUrl'] ?? null,
                $requestBody['sandboxMode'] ?? false,
                isset($requestBody['totalCartValue']) ? (float) $requestBody['totalCartValue'] : null,
                $requestBody['callbackUrl'],
                $requestBody['returnUrl']
            );
        } catch (\Throwable $th) {
            throw $th;
        }
        // formats request according to provider definition
        //$requestAsArray = $request->toArray();
        $requestAsArray = [];

        // call provider to process the request
        $providerResponseArray = $this->providerService->createPayment($request);

        // returns response formatted according to PPP definitions
        $responseArray = array_merge(["paymentId" => $request->paymentId()], $providerResponseArray);
        return $responseArray;
    }

    /* private function executeAuthorization($request)
    {
        $promise = new Promise(
            function () use (&$promise) {
                //Make a request to an http server
                $httpResponse = 200;
                sleep(5);
                $promise->resolve($httpResponse);
            }
        );

        return;
    } */

    public function retry($requestBody, $credentials)
    {
        sleep(1);
        $response = [
            "paymentId" =>  $requestBody['paymentId'],
            "status" => "denied",
            "authorizationId" => null,
            "tid" => "TID-7B58BE1A08",
            "code" => "OperationDeniedCode",
            "message" => "Credit card payment denied"
        ];

        $curl = curl_init();

        $payload = json_encode($response);
        curl_setopt_array($curl, [
            CURLOPT_URL => $requestBody['callbackUrl'],
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
                "X-VTEX-API-AppKey: {$credentials["key"]}",
                "X-VTEX-API-AppToken: {$credentials["token"]}"
            ],
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            error_log($error);
        }
    }

    public function cancelPayment(array $requestBody): array
    {
        try {
            $request = CancellationRequest::fromArray($requestBody);
        } catch (\Throwable $th) {
            throw new \Exception('Invalid Request Body', 400);
        }

        $cancellationResponse = $this->providerService->processCancellation($request);

        return [
            "responseCode" => $cancellationResponse->responseCode(),
            "responseData" => $cancellationResponse->asArray()
        ];
    }


    public function capturePayment(array $requestBody): array
    {
        try {
            $request = CaptureRequest::fromArray($requestBody);
        } catch (\Throwable $th) {
            throw new \Exception('Invalid Request Body', 400);
        }

        $settlementResponse = $this->providerService->processCapture($request);

        return [
            "responseCode" => $settlementResponse->responseCode(),
            "responseData" => $settlementResponse->asArray()
        ];
    }

    function refundPayment(array $requestBody): array
    {
        try {
            $request = RefundRequest::fromArray($requestBody);
        } catch (\Throwable $th) {
            throw new \Exception('Invalid Request Body', 400);
        }

        $refundResponse = $this->providerService->processRefund($request);

        return [
            "responseCode" => $refundResponse->responseCode(),
            "responseData" => $refundResponse->asArray()
        ];
    }
}
