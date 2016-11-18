<?php

namespace Kelemen\SimpleMapper\Tests;

require_once 'TestBase.php';

use Kelemen\SimpleMapper\Tests\Mock\ActiveRow\Product;
use Kelemen\SimpleMapper\Tests\Mock\Selection\Products;
use DatabaseConnection;
use BaseData;
use Nette\MemberAccessException;

class SelectionTest extends TestBase
{
    public function testFetchAll()
    {
        $selection = DatabaseConnection::getContext()->table('products');
        $products = new Products($selection);

        $this->assertInstanceOf(Products::class, $products);
        foreach ($products->fetchAll() as $product) {
            $this->assertInstanceOf(Product::class, $product);
        }
    }

    public function testAdvancedFetchAllAndCount()
    {
        $selection = DatabaseConnection::getContext()->table('products')->where('type.id', 3);
        $products = new Products($selection);

        $this->assertInstanceOf(Products::class, $products);
        foreach ($products->fetchAll() as $product) {
            $this->assertInstanceOf(Product::class, $product);
        }

        $this->assertCount(2, $products);
    }

    public function testGet()
    {
        $productId = 7;
        $selection = DatabaseConnection::getContext()->table('products');
        $products = new Products($selection);
        $product = $products->get($productId);

        $this->assertEquals(BaseData::$products[$productId], $product->toArray());
    }

    public function testFetch()
    {
        $productId = 4;
        $selection = DatabaseConnection::getContext()->table('products')->where('id', $productId);
        $products = new Products($selection);
        $product = $products->fetch();

        $this->assertEquals(BaseData::$products[$productId], $product->toArray());
    }

    public function testFetchField()
    {
        $productId = 3;
        $selection = DatabaseConnection::getContext()->table('products')->where('id', $productId);
        $products = new Products($selection);
        $productTitle = $products->fetchField('title');

        $this->assertEquals(BaseData::$products[$productId]['title'], $productTitle);
    }

    public function testFetchPairs()
    {
        $selection = DatabaseConnection::getContext()->table('products');
        $products = new Products($selection);

        $pairs = $products->fetchPairs(null, 'id');
        $this->assertEquals(array_column(BaseData::$products, 'id'), $pairs);

        foreach ($products->fetchPairs('id') as $row) {
            $this->assertInstanceOf(Product::class, $row);
        }
    }

    public function testFetchAssoc()
    {
        $selection = DatabaseConnection::getContext()->table('products');
        $products = new Products($selection);

        $this->assertEquals(array_column(BaseData::$products, 'title'), $products->fetchAssoc('[]=title'));
    }

    public function testCountable()
    {
        $selection = DatabaseConnection::getContext()->table('products');
        $products = new Products($selection);

        $this->assertEquals(10, $products->count());
        $this->assertCount(10, $products);
    }

    public function testIterator()
    {
        $selection = DatabaseConnection::getContext()->table('products');
        $products = new Products($selection);

        $this->assertInstanceOf(Products::class, $products);
        foreach ($products as $product) {
            $this->assertInstanceOf(Product::class, $product);
        }
    }

    public function testArrayAccess()
    {
        // isset
        $products = $this->getProducts();
        $this->assertTrue(isset($products[3]));

        // get
        $product = $products[3];
        $this->assertInstanceOf(Product::class, $product);

        // unset
        unset($products[3]);
        $this->assertFalse(isset($products[3]));

        // set
        $products[3] = $product;
        $this->assertTrue(isset($products[3]));
    }

    public function testWrapperAggregations()
    {
        $products = $this->getProducts();
        $this->assertEquals(10, $products->aggregation("COUNT('*')"));
        $this->assertEquals(10, $products->count('*'));
        $this->assertEquals(7, $products->min('price'));
        $this->assertEquals(99, $products->max('price'));
        $this->assertEquals(444, $products->sum('price'));
    }

    public function testWrapperSelectionsException()
    {
        $product = $this->getProducts()->where('id', 2)->select('title, price')->fetch();
        $this->expectException(MemberAccessException::class);
        $product->id;
    }

    public function testWrapperSelection()
    {
        // @TODO: joinWhere, alias - i never user this, don't know how to test it

        // Simple select
        $product = $this->getProducts()->where('id', 2)->select('title, price')->fetch();
        $this->assertEquals('Product 2', $product->title);
        $this->assertEquals(20, $product->price);

        // Concat select
        $product = $this->getProducts()->where('id', 3)->select('CONCAT(?, ?) AS foo', "TOP", " product")->fetch();
        $this->assertEquals('TOP product', $product->foo);

        // Where primary
        $product = $this->getProducts()->wherePrimary(3)->fetch();
        $this->assertEquals(3, $product->id);
        $this->assertEquals('Product 3', $product->title);

        // WhereOr
        $products = $this->getProducts()->whereOr([
            'id' => [1, 2],
            'title' => 'Product 4'
        ]);
        $this->assertCount(3, $products);

        // Order
        $products = $this->getProducts()->order('id DESC');
        $i = 10;
        foreach ($products as $product) {
            $this->assertEquals('Product ' . $i, $product->title);
            $i--;
        }

        // Limit
        $products = $this->getProducts()->limit(5);
        $this->assertCount(5, $products);

        // Page
        $numberOfPages = 0;
        $products = $this->getProducts()->page(2, 2, $numberOfPages);
        $this->assertCount(2, $products);
        $this->assertEquals(5, $numberOfPages);
        $i = 3;
        foreach ($products as $product) {
            $this->assertEquals('Product ' . $i, $product->title);
            $i++;
        }

        // Group
        $products = $this->getProducts()->group('type_id');
        $this->assertEquals(5, $products->count());

        // Having
        $products = $this->getProducts()->having('type_id > 3');
        $this->assertEquals(4, $products->count());
    }

    public function testWrapperManipulation()
    {
        // Delete
        $this->assertEquals(5, $this->getProducts()->where('id > 5')->delete());
        $this->assertEquals(5, $this->getProducts()->count());

        // Insert
        $insertedProduct = $this->getProducts()->insert([
            'title' => 'Product inserted', 'image' => 'Product image inserted', 'type_id' => 5, 'price' => 100, 'is_deleted' => 0, 'is_hidden' => 0
        ]);
        $this->assertInstanceOf(Product::class, $insertedProduct);
        $this->assertEquals('Product inserted', $insertedProduct->title);
        $this->assertEquals(6, $this->getProducts()->count());

        // Update
        $this->assertEquals(6, $this->getProducts()->update(['price' => 90]));
        foreach ($this->getProducts() as $product) {
            $this->assertEquals(90, $product->price);
        }
    }

    public function testScopes()
    {
        $this->assertCount(6, $this->getProducts()->active());
        $this->assertCount(9, $this->getProducts()->forAdmin());
    }

    private function getProducts()
    {
        return new Products(DatabaseConnection::getContext()->table('products'));
    }
}