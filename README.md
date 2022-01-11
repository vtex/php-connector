# PHP Payment Connector

## Disclaimer

This project implements the [VTEX Payment Provider Protocol](https://help.vtex.com/en/tutorial/payment-provider-protocol) using the PHP language.

For the moment, only the payment flow for Credit Card is defined.

The goal of the project is to verify that the [documentation](https://developers.vtex.com/vtex-developer-docs/reference/payment-flow) is providing clear information and to create code examples for the partners to use.

The [Test Suite](https://help.vtex.com/en/tutorial/payment-provider-protocol#3-payment-provider-homologation) is being used to verify the endpoints, and
for the moment, the **Authorize**, **Denied**, **Cancel**, **AsyncApproved** and **AsyncDenied** tests are passing.

## How to use the project

You can clone the project and serve it locally with the PHP built-in web server by running the following command on the project folder:

`php -S localhost:8000 index.php`

and to test locally, you can use your favorite API client such as Insomnia or Postman.

If you want to test it with the Test Suite, then you should set up the project in a serve so that if follows the [endpoint requirements](https://developers.vtex.com/vtex-rest-api/reference/payment-provider-protocol-api-overview#endpoint-requirements)

- Must use a standard subdomain/domain name, and not a IP address.
- Must be served over HTTPS on port 443 with TLS 1.2 support.
- Must respond in less than 5 seconds when running the tests.
- Must respond in less than 20 seconds when in production.

For that I used Heroku, as the setup was almost out-of-the-box.

## Endpoints defined

Here is a snippet of the response for the endpoints defined:

### Payment Methods endpoint
```json
{
	"paymentMethods": [
		{
			"name": "Visa",
			"allowsSplit": "onAuthorize"
		},
		{
			"name": "Mastercard",
			"allowsSplit": "onCapture"
		},
		{
			"name": "American Express",
			"allowsSplit": "disabled"
		}
	]
}
```

### Manifest endpoint
```json
{
	"paymentMethods": [
		{
			"name": "Visa",
			"allowsSplit": "onAuthorize"
		},
		{
			"name": "Mastercard",
			"allowsSplit": "onCapture"
		},
		{
			"name": "American Express",
			"allowsSplit": "disabled"
		}
	]
}
```

### Create Payment endpoint
```json
{
	"paymentId": "88ca7cd5-c9d4-47a8-8237-90dc26ecb550",
	"status": "approved",
	"authorizationId": "38c01875956f18d6ea9d",
	"tid": "f23b1b4b1258da499720",
	"nsu": "7148a83359c730a4a2f0",
	"acquirer": "TestPay",
	"code": "OperationDeniedCode",
	"message": "Credit card payment denied",
	"delayToAutoSettle": 21600,
	"delayToAutoSettleAfterAntifraud": 1800,
	"delayToCancel": 21600,
	"maxValue": 1000
}
```

### Cancel Payment endpoint
```json
{
	"paymentId": "B2F246B3CE46469FBDD23039868E95D0",
	"cancellationId": null,
	"requestId": "007B7D9B3BB4440982D8B6BA04126B01",
	"code": "cancel-manually",
	"message": "This payment needs to be manually cancelled"
}
```

### Capture Payment endpoint
```json
{
	"paymentId": "73269832-b471-44f0-b437-4d4304dea378",
	"settleId": "1fbc07a384eba708f8c0",
	"value": 159.89,
	"requestId": null,
	"message": "Successfully settled"
}
```

### Refund Payment endpoint
```json
{
	"paymentId": "F5C1A4E20D3B4E07B7E871F5B5BC9F91",
	"refundId": "0cd98c33e5593f3332cf",
	"requestId": "LA4E20D3B4E07B7E871F5B5BC9F91",
	"value": 101,
	"message": "Successfully refunded"
}
```
