<?php

namespace Kelemen\SimpleMapper\Tests\Mock\Selection;

use Kelemen\SimpleMapper\Tests\Mock\ActiveRow\Product;
use SimpleMapper\Selection;

class Products extends Selection
{
    protected $recordClass = Product::class;

    public function active()
    {
        $this->getSelection()->where([
            'is_hidden' => 0,
            'is_deleted' => 0
        ]);
        return $this;
    }

    public function forAdmin()
    {
        $this->getSelection()->where([
            'is_hidden' => 0
        ]);
        return $this;
    }
}