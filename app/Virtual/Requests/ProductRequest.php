<?php

namespace App\Virtual\Requests;

/**
 * @OA\Schema(
 *     title="Product Request",
 *     description="Product request body data",
 *     type="object",
 *     required={"name", "price", "stock"}
 * )
 */
class ProductRequest
{
    /**
     * @OA\Property(type="string", example="New Product")
     */
    public $name;

    /**
     * @OA\Property(type="string", example="Product description")
     */
    public $description;

    /**
     * @OA\Property(type="number", format="float", example=99.99)
     */
    public $price;

    /**
     * @OA\Property(type="integer", example=100)
     */
    public $stock;

    /**
     * @OA\Property(type="array", @OA\Items(type="integer"), example={1,2,3})
     */
    public $category_ids;
} 