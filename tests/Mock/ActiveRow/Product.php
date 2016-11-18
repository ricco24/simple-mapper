<?php

namespace Kelemen\SimpleMapper\Tests\Mock\ActiveRow;

use SimpleMapper\ActiveRow;

class Product extends ActiveRow
{
    public function getType()
    {
        return $this->getReference('type', ProductType::class);
    }

    public function getCategories()
    {
        return $this->getMMRelated('product_categories', 'product_category', ProductCategory::class);
    }


}