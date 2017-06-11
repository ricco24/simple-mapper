<?php

namespace Kelemen\SimpleMapper\Tests;

require_once 'DatabaseConnection.php';
require_once 'Mock/Selection/Products.php';
require_once 'Mock/Selection/ProductCategories.php';
require_once 'Mock/ActiveRow/Product.php';
require_once 'Mock/ActiveRow/ProductType.php';
require_once 'Mock/ActiveRow/Sticker.php';
require_once 'Mock/ActiveRow/ProductCategory.php';
require_once 'Mock/Repository/ProductsRepository.php';
require_once 'Mock/Repository/StickersRepository.php';

use Kelemen\SimpleMapper\Tests\Mock\ActiveRow\Product;
use Kelemen\SimpleMapper\Tests\Mock\ActiveRow\ProductCategory;
use Kelemen\SimpleMapper\Tests\Mock\ActiveRow\ProductType;
use Kelemen\SimpleMapper\Tests\Mock\ActiveRow\Sticker;
use Kelemen\SimpleMapper\Tests\Mock\Repository\ProductsRepository;
use Kelemen\SimpleMapper\Tests\Mock\Repository\StickersRepository;
use Kelemen\SimpleMapper\Tests\Mock\Selection\ProductCategories;
use Kelemen\SimpleMapper\Tests\Mock\Selection\Products;
use Kelemen\SimpleMapper\Tests\Mock\Selection\ProductTypes;
use PHPUnit_Framework_TestCase;
use DatabaseConnection;
use SimpleMapper\Mapper;
use SimpleMapper\Selection;
use SimpleMapper\Structure\BaseStructure;

class TestBase extends PHPUnit_Framework_TestCase
{
    /** @var ProductsRepository */
    protected $productsRepository;

    /** @var StickersRepository */
    protected $stickersRepository;

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
        return $this->getMapper()->getRepository(ProductsRepository::class)->findBy(['id' => $id])->fetch();
    }

    /**
     * Fetch products selection
     * @param array $where
     * @return Selection|Products
     */
    protected function getProducts(array $where = [])
    {
        $selection = $this->getMapper()->getRepository(ProductsRepository::class)->findAll();
        if ($where) {
            $selection->where($where);
        }
        return $selection;
    }

    /**
     * @return ProductsRepository
     */
    protected function getProductsRepository()
    {
        if (!$this->productsRepository) {
            $this->productsRepository = new ProductsRepository(DatabaseConnection::getContext());
        }
        return $this->productsRepository;
    }

    /**
     * @return StickersRepository
     */
    protected function getStickersRepository()
    {
        if (!$this->stickersRepository) {
            $this->stickersRepository = new StickersRepository(DatabaseConnection::getContext());
        }
        return $this->stickersRepository;
    }

    /**
     * @return Mapper
     */
    protected function getMapper()
    {
        $mapper = new Mapper(new BaseStructure());
        $mapper
            ->mapRepository($this->getProductsRepository(), Product::class, Products::class)
            ->mapRepository($this->getStickersRepository(), Sticker::class)
            ->mapTableName('product_types', ProductType::class, ProductTypes::class)
            ->mapTableName('product_categories', ProductCategory::class, ProductCategories::class);
        return $mapper;
    }
}