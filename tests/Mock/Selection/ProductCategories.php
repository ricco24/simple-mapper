<?php

namespace Kelemen\SimpleMapper\Tests\Mock\Selection;

use Kelemen\SimpleMapper\Tests\Mock\ActiveRow\ProductCategory;
use SimpleMapper\Selection;

class ProductCategories extends Selection
{
    protected $recordClass = ProductCategory::class;
}