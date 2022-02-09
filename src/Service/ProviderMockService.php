<?php

namespace PhpConnector\Service;

use PhpConnector\Model\CreatePaymentRequest;
use PhpConnector\Model\AuthorizationResponse;
use PhpConnector\Model\CancellationRequest;
use PhpConnector\Model\CancellationResponse;
use PhpConnector\Model\CaptureRequest;
use PhpConnector\Model\CaptureResponse;
use PhpConnector\Model\RefundRequest;
use PhpConnector\Model\RefundResponse;

/**
 * ProviderMockService implements most of our Provider processing logic, mocking the responses for
 * each payment request. It also handles the TestSuite flows.
 */
class ProviderMockService implements ProviderServiceInterface
{
    private static $creditCardFlow = [
        '4444333322221111' => 'approveCreditCardPayment',
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

    public function __construct(bool $clientIsTestSuite = false)
    {
        $this->clientIsTestSuite = $clientIsTestSuite;
    }

    /**
     * If it's the TestSuite calling the connector, the authorization flow will be selected
     * according to the specific credit card number send in the request
     *
     * If it's not the TestSuite, then it will check if the credit card is valid and respond accordingly
     *
     * "myRedirectPaymentMethod" payment method will follow the redirect flow
     *
     * @param CreatePaymentRequest $request
     * @return AuthorizationResponse
     */
    public function authorizePayment(CreatePaymentRequest $request): AuthorizationResponse
    {

        $this->saveAuthorizationRequest($request);

        $isRedirectMethod = $request->paymentMethod() === 'myRedirectPaymentMethod';
        if ($isRedirectMethod) {
            return $this->approveAndRedirect($request);
        }

        if (!$isRedirectMethod && !$request->isCreditCardPayment()) {
            throw new \Exception("Not implemented", 501);
        }

        $creditCardNumber = $request->card()->cardNumber();
        $creditCardIsValid = $this->validateCreditCard($creditCardNumber);

        if (
            $this->clientIsTestSuite
        ) {
            $creditCardNumber = $request->card()->cardNumber();
            $flow = self::$creditCardFlow[$creditCardNumber];

            return $this->$flow($request);
        } elseif ($creditCardIsValid) {
            return $this->approveCreditCardPayment($request);
        } else {
            return $this->denyPayment($request);
        }
    }

    /**
     * Our provider allows the merchant to select the "Country of operation" custom field.
     * Depending on that, it will set a different acquirer.
     *
     * @param CreatePaymentRequest $request
     * @return AuthorizationResponse
     */
    private function approveCreditCardPayment(CreatePaymentRequest $request, string $tid = null): AuthorizationResponse
    {
        $countryOfOperationAsString = $request->merchantSettings()->countryOfOperationAsString();

        $acquirer = self::$acquirerByCountry[$countryOfOperationAsString];

        $tid = $tid ?? bin2hex(random_bytes(10));

        return AuthorizationResponse::approved(
            $request->paymentId(),
            bin2hex(random_bytes(10)),
            $tid,
            bin2hex(random_bytes(10)),
            $acquirer,
            self::$delayToAutoSettleDefault
        );
    }

    private function denyPayment(CreatePaymentRequest $request, string $tid = null): AuthorizationResponse
    {
        if (is_null($tid)) {
            $tid = bin2hex(random_bytes(10));
        }

        return AuthorizationResponse::denied(
            $request->paymentId(),
            $tid
        );
    }

    private function asyncDeny(CreatePaymentRequest $request): AuthorizationResponse
    {
        $tid = bin2hex(random_bytes(10));

        return AuthorizationResponse::pending(
            $request->paymentId(),
            $tid,
            $this->denyPayment($request, $tid),
        );
    }

    private function asyncApprove(CreatePaymentRequest $request): AuthorizationResponse
    {
        $tid = bin2hex(random_bytes(10));

        return AuthorizationResponse::pending(
            $request->paymentId(),
            bin2hex(random_bytes(10)),
            $this->approveCreditCardPayment($request, $tid),
        );
    }

    /**
     * All payments from myRedirectPaymentMethod will be approved. The Authorization Response for
     * this payment method will set a paymentURL, which will redirect the user to a custom flow.
     * Our custom flow allows the shopper to select the number of installments for the payment.
     * The installments options are dynamic, based on the payment amount.
     *
     * @param CreatePaymentRequest $request
     * @return AuthorizationResponse
     */
    private function approveAndRedirect(CreatePaymentRequest $request): AuthorizationResponse
    {

        $tid = bin2hex(random_bytes(10));

        $approvedResponse = AuthorizationResponse::approvedRedirect(
            $request->paymentId(),
            bin2hex(random_bytes(10)),
            $tid,
            self::$delayToAutoSettleDefault
        );

        return AuthorizationResponse::redirect(
            $request->paymentId(),
            $tid,
            $approvedResponse
        );
    }

    /**
     * Our Provider only cancels request when the $requestId is defined on the request body.
     *
     * @param CancellationRequest $request
     * @return CancellationResponse
     */
    public function processCancellation(CancellationRequest $request): CancellationResponse
    {
        $cancellationId = $this->nextCancellationId();

        if (!is_null($request->requestId())) {
            return CancellationResponse::approved($request, $cancellationId);

        }

        return CancellationResponse::notSupported($request);
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
        $content = json_encode($request->asArray(), JSON_UNESCAPED_SLASHES);
        $filename = "logs/requests/authorization-{$request->paymentId()}.json";
        file_put_contents($filename, $content);
    }

}
