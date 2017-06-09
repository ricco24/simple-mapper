<?php

namespace Kelemen\SimpleMapper\Tests\Mock\Repository;

use Kelemen\SimpleMapper\Tests\Mock\Selection\ProductCategories;
use SimpleMapper\Repository;
use SimpleMapper\Scope\Scope;

/**
 * @method ProductCategories scopeIdTest(array $categories)
 */
class ProductCategoryRepository extends Repository
{
    /** @var string */
    protected static $tableName = 'product_categories';

    /**
     * Register scopes
     * @return array
     */
    public function getScopes(): array
    {
        return [
            new Scope('idTest', function (array $categories) {
                return [
                    self::getTableName() . '.id' => $categories
                ];
            })
        ];
    }
}