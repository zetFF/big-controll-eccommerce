<?php

namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="Product",
 *     description="Product model",
 *     @OA\Xml(name="Product")
 * )
 */
class Product
{
    /**
     * @OA\Property(type="integer", format="int64", example=1)
     */
    private $id;

    /**
     * @OA\Property(type="string", example="Product Name")
     */
    private $name;

    /**
     * @OA\Property(type="string", example="product-name")
     */
    private $slug;

    /**
     * @OA\Property(type="string", example="Product description")
     */
    private $description;

    /**
     * @OA\Property(type="number", format="float", example=99.99)
     */
    private $price;

    /**
     * @OA\Property(type="integer", example=100)
     */
    private $stock;
} 