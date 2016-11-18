<?php

require_once 'Data/BaseData.php';

use Nette\Database\Connection;
use Nette\Database\Structure;
use Nette\Caching\Storages\DevNullStorage;
use Nette\Database\Conventions\DiscoveredConventions;
use Nette\Database\Context;

class DatabaseConnection
{
    private static $context;

    private static $config = [
        'adapter' => 'mysql',
        'host' => '127.0.0.1',
        'db_name' => 'simple_mapper_test',
        'user' => 'root',
        'password' => 123
    ];

    public static function getContext()
    {
        if (!self::$context) {
            $connection = new Connection(self::$config['adapter'] . ':host=' . self::$config['host'] . ';dbname=' . self::$config['db_name'], self::$config['user'], self::$config['password']);
            $structure = new Structure($connection, new DevNullStorage());
            $conventions = new DiscoveredConventions($structure);
            $context = new Context($connection, $structure, $conventions);
            self::$context = $context;
        }

        return self::$context;
    }

    public static function clearDatabase()
    {
        $database = self::getContext();
        $database->query('DELETE FROM products_product_categories');
        $database->query('DELETE FROM product_categories');
        $database->query('DELETE FROM product_types');
        $database->query('DELETE FROM products');
    }

    public static function insertBaseData()
    {
        self::insertToTable('product_types', BaseData::$productTypes);
        self::insertToTable('products', BaseData::$products);
        self::insertToTable('product_categories', BaseData::$productCategories);
        self::insertToTable('products_product_categories', BaseData::$productsProductCategories);
    }

    private static function insertToTable($table, array $data)
    {
        foreach ($data as $row) {
            self::getContext()->table($table)->insert($row);
        }
    }

    public static function refreshDatabase()
    {
        self::clearDatabase();
        self::insertBaseData();
    }
}