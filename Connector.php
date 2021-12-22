<?php

class Connector
{
    function listPaymentMethods()
    {
        return json_encode([
            "paymentMethods" => [
                "Visa",
                "Mastercard",
                "Pix",
                "American Express",
                "BankInvoice",
                "Privatelabels",
                "Promissories",
            ]
        ]);
    }

    function listPaymentProviderManifest()
    {
        return json_encode([
            "paymentMethods" => [
                [
                    "name" => "Visa",
                    "allowsSplit" => "onCapture"
                ],
                [
                    "name" => "Pix",
                    "allowsSplit" => "disabled"
                ],
                [
                    "name" => "Mastercard",
                    "allowsSplit" => "onCapture"
                ],
                [
                    "name" => "American Express",
                    "allowsSplit" => "onCapture"
                ],
                [
                    "name" => "BankInvoice",
                    "allowsSplit" => "onAuthorize"
                ],
                [
                    "name" => "Privatelabels",
                    "allowsSplit" => "disabled"
                ],
                [
                    "name" => "Promissories",
                    "allowsSplit" => "disabled"
                ]
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
}
