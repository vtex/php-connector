<?php

namespace PhpConnector\Model;

/**
 * Card Class validates that the request body contains all the necessary parameters
 * with the expected type.
 */
class Card
{
    private $holder;
    private $number;
    private $csc;
    private $expirationMonth;
    private $expirationYear;
    private $document;

    public function __construct(
        string $holder,
        string $number,
        string $csc,
        string $expirationMonth,
        string $expirationYear,
        ?string $document
    ) {
        $this->holder = $holder;
        $this->number = $number;
        $this->csc = $csc;
        $this->expirationMonth = $expirationMonth;
        $this->expirationYear = $expirationYear;
        $this->document = $document;
    }

    public function cardNumber(): string
    {
        return $this->number;
    }
}
