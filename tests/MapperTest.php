<?php

namespace Kelemen\SimpleMapper\Tests;

use Kelemen\SimpleMapper\Tests\Mock\Repository\ProductsRepository;

require_once 'TestBase.php';

class MapperTest extends TestBase
{
    public function testBasics()
    {
        $mapper = $this->getMapper();
        $this->assertInstanceOf(ProductsRepository::class, $mapper->getRepository(ProductsRepository::class));
    }
}