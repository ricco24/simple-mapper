<?php

namespace Kelemen\SimpleMapper\Tests;

use Kelemen\SimpleMapper\Tests\Mock\ActiveRow\Product;
use Kelemen\SimpleMapper\Tests\Mock\Repository\ProductRepository;
use DatabaseConnection;
use Kelemen\SimpleMapper\Tests\Mock\Repository\ProductSoftDeleteRepository;
use Kelemen\SimpleMapper\Tests\Mock\Structure;

require_once 'TestBase.php';

class RepositoryTest extends TestBase
{
    public function testInsert()
    {
        $this->getStructure();
        $product = $this->getProductsRepository()->insert([
            'title' => 'New product',
            'image' => 'image 123',
            'type_id' => 1,
            'price' => 24
        ]);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals(11, $product['id']);
        $this->assertEquals('New product', $product['title']);

        // Check if created_at/updated_at field is not zero fill (test for DateBehaviour)
        $this->assertNotEquals('30.11.-0001 00:00:00', $product['created_at']->format('d.m.Y H:i:s'));
        $this->assertNotEquals('30.11.-0001 00:00:00', $product['updated_at']->format('d.m.Y H:i:s'));
    }

    public function testUpdate()
    {
        $this->getStructure();
        $repository = $this->getProductsRepository();
        $product = $repository->findAll()->wherePrimary(1)->fetch();
        $updatedAt = $product['updated_at'];
        $updatedProduct = $repository->update($product, [
            'title' => 'Updated title'
        ]);

        $this->assertInstanceOf(Product::class, $updatedProduct);
        $this->assertEquals(1, $product['id']);
        $this->assertEquals('Updated title', $product['title']);
        $this->assertGreaterThan($updatedAt, $product['updated_at']);

        $updatedProductFresh = $repository->findAll()->wherePrimary(1)->fetch();

        $this->assertInstanceOf(Product::class, $updatedProductFresh);
        $this->assertEquals(1, $updatedProductFresh['id']);
        $this->assertEquals('Updated title', $updatedProductFresh['title']);
        $this->assertGreaterThan($updatedAt, $updatedProductFresh['updated_at']);
    }

    public function testDelete()
    {
        $this->getStructure();
        $repository = $this->getProductsRepository();
        $product = $repository->findAll()->wherePrimary(2)->fetch();
        $deleteResult = $repository->delete($product);

        $this->assertTrue($deleteResult);
        $this->assertFalse($repository->findAll()->wherePrimary(2)->fetch());
    }

    public function testSoftDelete()
    {
        $this->getStructureSoftDeletedProducts();
        $repository = $this->getProductsSoftDeletedRepository();
        $product = $repository->findAll()->wherePrimary(3)->fetch();
        $deleteResult = $repository->delete($product);

        $this->assertTrue($deleteResult);
        $this->assertInstanceOf(Product::class, $repository->findAll()->wherePrimary(3)->fetch());
    }

    public function testScopes()
    {
        $this->getStructure();
        $repository = $this->getProductsRepository();
        $this->assertEquals(10, $repository->findAll()->count('*'));
        $this->assertEquals(7, $repository->scopeAdmin()->count('*'));
        $this->assertEquals(5, $repository->scopeAdmin()->scopePriceGreater()->count('*'));
        $this->assertEquals(3, $repository->scopeAdmin()->scopePriceGreater(30)->count('*'));
    }
}