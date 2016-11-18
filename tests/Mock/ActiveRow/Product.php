<?php

namespace Kelemen\SimpleMapper\Tests\Mock\ActiveRow;

use SimpleMapper\ActiveRow;

class Product extends ActiveRow
{
    /**
     * @return ProductType
     */
    public function getType()
    {
        return $this->getReference('type', ProductType::class);
    }

    /**
     * @return ProductCategory[]
     */
    public function getCategories()
    {
        return $this->getMMRelated('product_categories', 'product_category', ProductCategory::class);
    }

    /**
     * @return ProductCategory[]
     */
    public function getSortedCategories()
    {
        $result = [];
        foreach ($this->record->related('product_categories')->order('sorting ASC') as $row) {
            $result[$row->product_category->id] = $this->prepareRecord($row->product_category, ProductCategory::class);
        }
        return $result;
    }
}