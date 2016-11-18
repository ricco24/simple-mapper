<?php

namespace Kelemen\SimpleMapper\Tests\Mock\Selection;

use Kelemen\SimpleMapper\Tests\Mock\ActiveRow\ProductCategory;
use SimpleMapper\Selection;

class ProductCategories extends Selection
{
    /** @var string */
    protected $recordClass = ProductCategory::class;
}