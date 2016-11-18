<?php

namespace Kelemen\SimpleMapper\Tests;

require_once 'DatabaseConnection.php';
require_once 'Mock/Selection/Products.php';
require_once 'Mock/Selection/ProductCategories.php';
require_once 'Mock/ActiveRow/Product.php';
require_once 'Mock/ActiveRow/ProductType.php';
require_once 'Mock/ActiveRow/ProductCategory.php';

use PHPUnit_Framework_TestCase;
use DatabaseConnection;

class TestBase extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        DatabaseConnection::refreshDatabase();
    }
}