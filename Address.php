<?php

/**
 * Address Class validates that the request body contains all the necessary parameters
 * with the expected type.
 */
class Address
{
    private $country;
    private $street;
    private $number;
    private $complement;
    private $neighborhood;
    private $postalCode; // not camel case in docs for shippingAddress
    private $city;
    private $state;

    public function __construct(
        string $country,
        string $street,
        string $number,
        string $complement,
        string $neighborhood,
        string $postalCode,
        string $city,
        string $state
    ) {
        $this->country = $country;
        $this->street = $street;
        $this->number = $number;
        $this->complement = $complement;
        $this->neighborhood = $neighborhood;
        $this->postalCode = $postalCode;
        $this->city = $city;
        $this->state = $state;
    }
}
