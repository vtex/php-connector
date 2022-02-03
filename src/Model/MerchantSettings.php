<?php

namespace PhpConnector\Model;

class MerchantSettings
{
    private $countryOfOperation;
    private $refundType;

    private static $automaticRefund = 1;
    private static $manualRefund = 2;

    private static $countriesList = [
        1 => "Brazil",
        2 => "Chile",
        3 => "Argentina",
        4 => "Colombia",
        5 => "Peru"
    ];

    public function __construct(
        ?int $countryOfOperation = null,
        ?int $refundType = null
    ) {
        $this->countryOfOperation = $countryOfOperation;
        $this->refundType = $refundType;
    }

    public static function fromArray(array $array): self
    {
        $key2 = array_search("Country of operation", array_column($array, 'name'));
        $countryOfOperation = is_int($key2) ? $array[$key2]["value"] : null;

        $key3 = array_search("Type of refund", array_column($array, 'name'));
        $refundType = is_int($key3) ? $array[$key3]["value"] : null;

        return new self(
            $countryOfOperation,
            $refundType
        );
    }

    public function countryOfOperation(): ?int
    {
        return $this->countryOfOperation;
    }

    public function refundType(): ?int
    {
        return $this->refundType;
    }

    /**
     * The condition here makes automatic refund the default refund type
     *
     * @return boolean
     */
    public function isAutomaticRefund(): bool
    {
        return $this->refundType === self::$automaticRefund || is_null($this->refundType);
    }

    public function isManualRefund(): bool
    {
        return $this->refundType === self::$manualRefund;
    }

    public function countryOfOperationAsString(): string
    {
        return isset($this->countryOfOperation) ? self::$countriesList[$this->countryOfOperation] : "undefined";
    }
}
