<?php

/**
 * Item Class validates that the request body contains all the necessary parameters
 * with the expected type.
 */
class Item
{
    private $id; // at some point is docs, id is not marked as mandatory
    private $name;
    private $price;
    private $quantity;
    private $discount;
    private $deliveryType;
    private $categoryId;
    private $sellerId;
    private $taxRate;
    private $taxValue;

    public function __construct(
        string $id,
        string $name,
        float $price,
        int $quantity,
        int $discount,
        ?string $deliveryType,
        ?string $categoryId,
        ?string $sellerId,
        ?float $taxRate,
        ?float $taxValue
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->quantity = $quantity;
        $this->discount = $discount;
        $this->deliveryType = $deliveryType;
        $this->categoryId = $categoryId;
        $this->sellerId = $sellerId;
        $this->taxRate = $taxRate;
        $this->taxValue = $taxValue;
    }
}
