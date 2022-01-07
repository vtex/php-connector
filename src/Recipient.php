<?php

namespace PhpConnector;

/**
 * Recipient Class validates that the request body contains all the necessary parameters
 * with the expected type.
 */
class Recipient
{
    private $id;
    private $name;
    private $documentType;
    private $document;
    private $role;
    private $amount;
    private $chargeProcessingFee;
    private $chargeBackLiable; // chargebackLiable in docs

    public function __construct(
        string $id,
        string $name,
        string $documentType,
        string $document,
        string $role,
        float $amount,
        bool $chargeProcessingFee,
        bool $chargeBackLiable
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->documentType = $documentType;
        $this->document = $document;
        $this->role = $role;
        $this->amount = $amount;
        $this->chargeProcessingFee = $chargeProcessingFee;
        $this->chargeBackLiable = $chargeBackLiable;
    }
}
