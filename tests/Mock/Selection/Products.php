<?php

namespace Kelemen\SimpleMapper\Tests\Mock\Selection;

use Kelemen\SimpleMapper\Tests\Mock\ActiveRow\Product;
use SimpleMapper\Selection;

class Products extends Selection
{
    /** @var string */
    protected $recordClass = Product::class;

    /**
     * Fetch only active products
     * @return Products
     */
    public function active()
    {
        $this->getSelection()->where([
            'is_hidden' => 0,
            'is_deleted' => 0
        ]);
        return $this;
    }

    /**
     * Fetch only products visible for admins
     * @return Products
     */
    public function forAdmin()
    {
        $this->getSelection()->where([
            'is_hidden' => 0
        ]);
        return $this;
    }
}