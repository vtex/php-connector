<?php

namespace PhpConnector\Model;

/**
 * Buyer Class validates that the request body contains all the necessary parameters
 * with the expected type.
 */
class Buyer
{
    private $id; // at some point is docs, id is not marked as mandatory
    private $firstName;
    private $lastName;
    private $document;
    private $documentType;
    private $email;
    private $phone;
    private $isCorporate;
    private $corporateName;
    private $tradeName;
    private $corporateDocument;
    private $createdDate;

    public function __construct(
        string $id,
        string $firstName,
        string $lastName,
        string $document,
        string $documentType,
        string $email,
        string $phone,
        bool $isCorporate,
        ?string $corporateName,
        ?string $tradeName,
        ?string $corporateDocument,
        ?string $createdDate
    ) {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->document = $document;
        $this->documentType = $documentType;
        $this->email = $email;
        $this->phone = $phone;
        $this->isCorporate = $isCorporate;
        $this->corporateName = $corporateName;
        $this->tradeName = $tradeName;
        $this->corporateDocument = $corporateDocument;
        $this->createdDate = $createdDate;
    }
}
