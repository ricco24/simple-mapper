<?php

namespace Kelemen\SimpleMapper\Tests\Mock;

use Kelemen\SimpleMapper\Tests\Mock\ActiveRow\Product;
use Kelemen\SimpleMapper\Tests\Mock\ActiveRow\ProductCategory;
use Kelemen\SimpleMapper\Tests\Mock\ActiveRow\ProductType;
use Kelemen\SimpleMapper\Tests\Mock\Selection\ProductCategories;
use Kelemen\SimpleMapper\Tests\Mock\Selection\Products;
use Kelemen\SimpleMapper\Tests\Mock\Selection\ProductTypes;
use SimpleMapper\Structure\CustomStructure;

class Structure extends CustomStructure
{
    public function __construct()
    {
        $this
            ->registerTable('products', Product::class, Products::class)
            ->registerTable('product_types', ProductType::class, ProductTypes::class)
            ->registerTable('product_categories', ProductCategory::class, ProductCategories::class);
    }
}