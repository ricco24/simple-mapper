<?php

namespace Kelemen\SimpleMapper\Tests;

require_once 'DatabaseConnection.php';
require_once 'Mock/Selection/Products.php';
require_once 'Mock/Selection/ProductCategories.php';
require_once 'Mock/ActiveRow/Product.php';
require_once 'Mock/ActiveRow/ProductType.php';
require_once 'Mock/ActiveRow/ProductCategory.php';
require_once 'Mock/Repository/ProductRepository.php';
require_once 'Mock/Repository/ProductSoftDeleteRepository.php';
require_once 'Mock/Repository/ProductCategoryRepository.php';

use Kelemen\SimpleMapper\Tests\Mock\ActiveRow\Product;
use Kelemen\SimpleMapper\Tests\Mock\ActiveRow\ProductCategory;
use Kelemen\SimpleMapper\Tests\Mock\ActiveRow\ProductType;
use Kelemen\SimpleMapper\Tests\Mock\Repository\ProductCategoryRepository;
use Kelemen\SimpleMapper\Tests\Mock\Repository\ProductRepository;
use Kelemen\SimpleMapper\Tests\Mock\Repository\ProductSoftDeleteRepository;
use Kelemen\SimpleMapper\Tests\Mock\Selection\ProductCategories;
use Kelemen\SimpleMapper\Tests\Mock\Selection\Products;
use Kelemen\SimpleMapper\Tests\Mock\Selection\ProductTypes;
use PHPUnit_Framework_TestCase;
use DatabaseConnection;
use SimpleMapper\Structure\CustomStructure;

class TestBase extends PHPUnit_Framework_TestCase
{
    /** @var ProductRepository */
    protected $productsRepository;

    /** @var ProductSoftDeleteRepository */
    protected $productsSoftDeletedRepository;

    /** @var ProductCategoryRepository */
    protected $productCategoryRepository;

    /**
     * Setup before every test
     */
    public function setUp()
    {
        DatabaseConnection::refreshDatabase();
    }

    /**
     * Fetch one product
     * @param int $id
     * @return Product
     */
    protected function getProduct($id)
    {
        $p = DatabaseConnection::getContext()->table('products')->wherePrimary($id)->fetch();
        return new Product($p, $this->getStructure());
    }

    /**
     * Fetch products selection
     * @param array $where
     * @return Products
     */
    protected function getProducts(array $where = [])
    {
        $selection = DatabaseConnection::getContext()->table('products');
        if ($where) {
            $selection->where($where);
        }
        return new Products($selection, $this->getStructure());
    }

    /**
     * @return ProductRepository
     */
    protected function getProductsRepository()
    {
        if (!$this->productsRepository) {
            $this->productsRepository = new ProductRepository(DatabaseConnection::getContext());
        }
        return $this->productsRepository;
    }

    /**
     * @return ProductSoftDeleteRepository
     */
    protected function getProductsSoftDeletedRepository()
    {
        if (!$this->productsSoftDeletedRepository) {
            $this->productsSoftDeletedRepository = new ProductSoftDeleteRepository(DatabaseConnection::getContext());
        }
        return $this->productsSoftDeletedRepository;
    }

    /**
     * @return ProductCategoryRepository
     */
    protected function getProductCategoryRepository()
    {
        if (!$this->productCategoryRepository) {
            $this->productCategoryRepository = new ProductCategoryRepository(DatabaseConnection::getContext());
        }
        return $this->productCategoryRepository;
    }

    /**
     * Build structure
     * @return CustomStructure
     */
    protected function getStructure()
    {
        $structure = new CustomStructure();
        $structure ->registerTable('products', Product::class, Products::class, $this->getProductsRepository())
            ->registerTable('product_types', ProductType::class, ProductTypes::class)
            ->registerTable('product_categories', ProductCategory::class, ProductCategories::class, $this->getProductCategoryRepository());
        return $structure;
    }

    /**
     * Build structure
     * @return CustomStructure
     */
    protected function getStructureSoftDeletedProducts()
    {
        $structure = new CustomStructure();
        $structure ->registerTable('products', Product::class, Products::class, $this->getProductsSoftDeletedRepository())
            ->registerTable('product_types', ProductType::class, ProductTypes::class)
            ->registerTable('product_categories', ProductCategory::class, ProductCategories::class, $this->getProductCategoryRepository());
        return $structure;
    }
}