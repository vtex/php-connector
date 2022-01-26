<?php

namespace PhpConnector\Service;

use PhpConnector\Model\CreatePaymentRequest;
use PhpConnector\Model\CreatePaymentResponse;
use PhpConnector\Model\CancellationRequest;
use PhpConnector\Model\CancellationResponse;
use PhpConnector\Model\CaptureRequest;
use PhpConnector\Model\CaptureResponse;
use PhpConnector\Model\RefundRequest;
use PhpConnector\Model\RefundResponse;

class ProviderMockService implements ProviderServiceInterface
{
    private static $creditCardFlow = [
        '4444333322221111' => 'authorizePayment',
        '4444333322221112' => 'denyPayment',
        '4222222222222224' => 'asyncApprove',
        '4222222222222225' => 'asyncDeny',
    ];

    private static $acquirerByCountry = [
        "Brazil" => "Cielo",
        "Chile" => "Transbank",
        "Argentina" => "Prisma Medios de Pago",
        "Colombia" => "Bancocolombia",
        "Peru" => "VisaNet",
        "undefined" => "TestPay"
    ];

    private static $delayToAutoSettleDefault = 21600;

    private $clientIsTestSuite;

    public function __construct(bool $clientIsTestSuite)
    {
        $this->clientIsTestSuite = $clientIsTestSuite;
    }

    /**
     * If it's the TestSuite calling the connector, the authorization flow will be selected
     * according to the specific credit card number send in the request
     * If it's not the TestSuite, then it will check if the credit card is valid and respond accordingly
     *
     * @param CreatePaymentRequest $request
     * @return CreatePaymentResponse
     */
    public function createPayment(CreatePaymentRequest $request): CreatePaymentResponse
    {

        $this->saveAuthorizationRequest($request);

        if (
            $this->clientIsTestSuite && $request->isCreditCardPayment()
        ) {
            $creditCardNumber = $request->card()->cardNumber();
            $flow = self::$creditCardFlow[$creditCardNumber];

            return $this->$flow($request);
        } elseif (
            !$this->clientIsTestSuite && $request->isCreditCardPayment()
        ) {
            $creditCardNumber = $request->card()->cardNumber();
            $creditCardIsValid = $this->validateCreditCard($creditCardNumber);

            if ($creditCardIsValid) {

                return $this->authorizePayment($request);
            } else {

                return $this->denyPayment($request);
            }

        } else {
            throw new \Exception("Not implemented", 501);
        }
    }

    /**
     * Our provider allows the merchant to set up a custom delay to auto settle the payment
     * after the authorization using the custom field "DelayToAutoSettle".
     * Also, it will set a different acquirer, depending on the "Country of operation" custom field.
     *
     * @param CreatePaymentRequest $request
     * @return CreatePaymentResponse
     */
    private function authorizePayment(CreatePaymentRequest $request): CreatePaymentResponse
    {
        $countryOfOperationAsString = $request->merchantSettings()->countryOfOperationAsString();

        $acquirer = self::$acquirerByCountry[$countryOfOperationAsString];

        $delayToAutoSettle = $request->merchantSettings()->delayToAutoSettle() ?? self::$delayToAutoSettleDefault;

        return CreatePaymentResponse::approved(
            $request,
            bin2hex(random_bytes(10)),
            bin2hex(random_bytes(10)),
            bin2hex(random_bytes(10)),
            $acquirer,
            $delayToAutoSettle
        );
    }

    private function denyPayment(CreatePaymentRequest $request): CreatePaymentResponse
    {
        return CreatePaymentResponse::denied(
            $request,
            bin2hex(random_bytes(10))
        );
    }

    private function asyncDeny(CreatePaymentRequest $request): CreatePaymentResponse
    {
        return CreatePaymentResponse::pending(
            $request,
            bin2hex(random_bytes(10)),
            $this->denyPayment($request),
        );
    }

    private function asyncApprove(CreatePaymentRequest $request): CreatePaymentResponse
    {
        return CreatePaymentResponse::pending(
            $request,
            bin2hex(random_bytes(10)),
            $this->authorizePayment($request),
        );
    }

    /**
     * Our Provider will not cancel a request, unless the $request id is send on the request.
     *
     * @param CancellationRequest $request
     * @return CancellationResponse
     */
    public function processCancellation(CancellationRequest $request): CancellationResponse
    {
        $cancellationId = $this->nextCancellationId();

        if (!is_null($request->requestId())) {
            return CancellationResponse::notSupported($request);
        }

        return CancellationResponse::approved($request, $cancellationId);
    }

    private function nextCancellationId(): string
    {
        return bin2hex(random_bytes(10));
    }

    /**
     * Our provider will deny the settlement of requests with value higher than 1.000.000
     *
     * @param CaptureRequest $request
     * @return CaptureResponse
     */
    public function processCapture(CaptureRequest $request): CaptureResponse
    {
        $captureId = $this->nextCaptureId();

        if ($request->value() > 1000000) {
            return CaptureResponse::denied($request);
        }

        return CaptureResponse::approved($request, $captureId);
    }

    private function nextCaptureId(): string
    {
        return bin2hex(random_bytes(10));
    }

    /**
     * Our provider accepts automatic refunds from up to 1000 money.
     * Our customers can choose to use automatic or manual refund, setting the "Type of refund" customField
     *
     * @param RefundRequest $request
     * @return RefundResponse
     */
    public function processRefund(RefundRequest $request): RefundResponse
    {
        if (
            $request->merchantSettings()->isAutomaticRefund()
            && $request->value() <= 1000
        ) {
            $refundId = $this->nextRefundId();
            return RefundResponse::approved($request, $refundId);
        } elseif (
            $request->merchantSettings()->isAutomaticRefund()
            && $request->value() > 1000
        ) {
            return RefundResponse::denied($request);
        }

        if ($request->merchantSettings()->isManualRefund()) {
            return RefundResponse::manual($request);
        }
    }

    private function nextRefundId(): string
    {
        return bin2hex(random_bytes(10));
    }

    /**
     * validates credit card with Luhn algorithm
     * https://stackoverflow.com/questions/174730/what-is-the-best-way-to-validate-a-credit-card-in-php
     *
     * @param [string] $creditCardNumber
     * @return boolean
     */
    private function validateCreditCard(string $creditCardNumber): bool
    {
        // Strip any non-digits (useful for credit card numbers with spaces and hyphens)
        $number = preg_replace('/\D/', '', $creditCardNumber);

        // Set the string length and parity
        $number_length = strlen($number);
        $parity = $number_length % 2;

        // Loop through each digit and do the maths
        $total = 0;
        for ($i = 0; $i < $number_length; $i++) {
            $digit = $number[$i];
            // Multiply alternate digits by two
            if ($i % 2 == $parity) {
                $digit *= 2;
                // If the sum is two digits, add them together (in effect)
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            // Total up the digits
            $total += $digit;
        }

        // If the total mod 10 equals 0, the number is valid
        return ($total % 10 == 0) ? true : false;
    }

    private function saveAuthorizationRequest(CreatePaymentRequest $request): void
    {
        if (!is_dir("logs/requests")) {
            mkdir("logs/requests", 0777, true);
        }
        $content = json_encode($request->asArray());
        $filename = "logs/requests/authorization-{$request->paymentId()}.json";
        file_put_contents($filename, $content);
    }
}
